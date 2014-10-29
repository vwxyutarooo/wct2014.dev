<?php

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
	die( 'Access denied.' );

if ( ! class_exists( 'TGGRShortcodeTagregator' ) ) {
	/**
	 * Handles the [tagregator] shortcode
	 *
	 * @package Tagregator
	 */
	class TGGRShortcodeTagregator extends TGGRModule {
		protected $refresh_interval, $post_types_to_class_names, $view_folder;		// $refresh_interval is in seconds
		protected static $readable_properties  = array( 'refresh_interval', 'view_folder' );
		protected static $writeable_properties = array( 'refresh_interval' );
		
		const SHORTCODE_NAME = 'tagregator';

		/**
		 * Constructor
		 * @mvc Controller
		 */
		protected function __construct() {
			$this->register_hook_callbacks();
			$this->view_folder = dirname( __DIR__ ) . '/views/'. str_replace( '.php', '', basename( __FILE__ ) );
		}

		/**
		 * Prepares site to use the plugin during activation
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {
			$this->init();
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 * @mvc Controller
		 */
		public function deactivate() {}

		/**
		 * Register callbacks for actions and filters
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'init',                                                              array( $this, 'init' ) );
			add_action( 'save_post',                                                         array( $this, 'prefetch_media_items' ), 10, 2 );
			add_action( 'wp_ajax_'.        Tagregator::PREFIX . 'render_latest_media_items', array( $this, 'render_latest_media_items' ) );
			add_action( 'wp_ajax_nopriv_'. Tagregator::PREFIX . 'render_latest_media_items', array( $this, 'render_latest_media_items' ) );

			add_shortcode( self::SHORTCODE_NAME,                                             array( $this, 'shortcode_tagregator' ) );
		}

		/**
		 * Initializes variables
		 * @mvc Controller
		 */
		public function init() {
			foreach ( Tagregator::get_instance()->media_sources as $class_name => $object ) {
				$this->post_types_to_class_names[ $object::POST_TYPE_SLUG ] = $class_name;
			}

			$this->refresh_interval = apply_filters( Tagregator::PREFIX . 'refresh_interval', 30 );
		}

		/**
		 * Checks if the plugin was recently updated and upgrades if necessary
		 * @mvc Controller
		 *
		 * @param string $db_version
		 */
		public function upgrade( $db_version = 0 ) {}

		/**
		 * Controller for the [tagregator] shortcode
		 * @mvc Controller
		 *
		 * @return string
		 */
		public function shortcode_tagregator( $attributes ) {
			$attributes = shortcode_atts( array( 'hashtag' => '' ), $attributes );
			$items = array();

			if ( $attributes['hashtag'] ) {
				$items = self::get_media_items( $attributes['hashtag'] );
			}

			ob_start();
			require_once( $this->view_folder . '/shortcode-tagregator.php' );
			return apply_filters( Tagregator::PREFIX . 'shortcode_output', ob_get_clean() );
		}

		/**
		 * Gets all of the items from all of the media sources that are assigned to the given hashtag
		 * @mvc Model
		 *
		 * @param string $hashtag
		 * @return array
		 */
		protected function get_media_items( $hashtag ) {
			$items = $post_types = array();

			foreach ( Tagregator::get_instance()->media_sources as $source ) {
				$post_types[] = $source::POST_TYPE_SLUG;
			}

			$term = get_term_by( 'name', $hashtag, TGGRMediaSource::TAXONOMY_HASHTAG_SLUG );
			if ( isset ( $term->slug ) ) {
				$items = get_posts( array(
					'posts_per_page'   => apply_filters( Tagregator::PREFIX . 'media_items_per_page', 30 ),
					'post_type'        => $post_types,
					'tax_query'        => array(
						array(
							'taxonomy' => TGGRMediaSource::TAXONOMY_HASHTAG_SLUG,
							'field'    => 'slug',
							'terms'    => $term->slug,
						),
					),
				) );
			}

			return $items;
		}

		/**
		 * Returns HTML markup for the latest media items
		 * This is an AJAX handler
		 * @mvc Controller
		 */
		public function render_latest_media_items() {
			$hashtag = sanitize_text_field( $_REQUEST['hashtag'] );
			$existing_item_ids = isset( $_REQUEST['existingItemIDs'] ) ? (array) $_REQUEST['existingItemIDs'] : array();
			array_walk( $existing_item_ids, 'absint' );

			$this->send_ajax_headers( 'text/html' );

			if ( empty( $hashtag ) ) {
				wp_die( -1 );
			}

			$this->import_new_items( $hashtag );
			$items = $this->get_media_items( $hashtag );
			$new_items_markup = $this->get_new_items_markup( $items, $existing_item_ids );

			wp_die( json_encode( $new_items_markup ? $new_items_markup : 0 ) );
		}

		/**
		 * Imports the latest items from media sources
		 * @mvc Controller
		 * 
		 * @param string $hashtag
		 * @param string $rate_limit 'respect' to enforce the rate limit, or 'ignore' to ignore it
		 */
		protected function import_new_items( $hashtag, $rate_limit = 'respect' ) {
			$last_fetch = get_transient( Tagregator::PREFIX . 'last_media_fetch', 0 );

			if ( 'ignore' == $rate_limit || self::refresh_interval_elapsed( $last_fetch, $this->refresh_interval ) ) {
				set_transient( Tagregator::PREFIX . 'last_media_fetch', microtime( true ) );	// do this right away to minimize the chance of race conditions
				
				foreach ( Tagregator::get_instance()->media_sources as $source ) {
					$source->import_new_items( $hashtag );
				}
			}
		}

		/**
		 * Determines if the enough time has passed since the previous media fetch
		 *
		 * @param int $last_fetch The number of seconds between the Unix epoch and the last time the data was fetched, as a float (i.e., the recorded output of microtime( true ) during the last fetch).
		 * @param int $refresh_interval The minimum number of seconds that should elapse between refreshes
		 * @return bool
		 */
		protected static function refresh_interval_elapsed( $last_fetch, $refresh_interval ) {
			$current_time = microtime( true );
			$elapsed_time = $current_time - $last_fetch;

			return $elapsed_time > $refresh_interval;
		}

		/**
		 * Builds the markup for an array of items if they don't already exist in the given array
		 *
		 * @param array $items
		 * @param array $existing_item_ids
		 * @return string
		 */
		protected function get_new_items_markup( $items, $existing_item_ids ) {
			ob_start();
			$this->render_media_items( $items, $existing_item_ids );
			$items_markup = ob_get_clean();

			return $items_markup ? $items_markup : '';
		}

		/**
		 * Builds the markup for media items, optionally skipping some items
		 *
		 * @param array $items
		 * @param array $excluded_item_ids
		 */
		protected function render_media_items( $items, $excluded_item_ids = array() ) {
			foreach ( $items as $item ) {
				if ( ! in_array( $item->ID, $excluded_item_ids ) ) {
					$GLOBALS['post'] = $item;
					setup_postdata( $GLOBALS['post'] );
					
					$post_type = get_post_type();
					$class_name = $this->post_types_to_class_names[ $post_type ];

					extract( $class_name::get_instance()->get_item_view_data( $item->ID ) );
					require( self::get_view_folder_from_post_type( $post_type ) . '/shortcode-tagregator-media-item.php' );
				}
			}

			wp_reset_postdata();
		}

		/**
		 * Determines the path to a media source's view folder based on the post type
		 *
		 * @param string $post_type
		 * @return string
		 */
		protected function get_view_folder_from_post_type( $post_type ) {
			$class_name = $this->post_types_to_class_names[ $post_type ];
			return $class_name::get_instance()->view_folder;
		}

		/**
		 * Outputs the appropriate headers for responses to AJAX requests
		 * In some cases, sending these are necessary to avoid browser quirks, especially the 200 header
		 *
		 * @param string $content_type The desired content type. e.g., 'text/html', 'application/json', etc
		 */
		protected static function send_ajax_headers( $content_type = 'text/html' ) {
			header( 'Cache-Control: no-cache, must-revalidate' );
			header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
			header( 'Content-Type: '. $content_type .'; charset=utf8' );
			header( 'Content-Type: '. $content_type );
			header( $_SERVER['SERVER_PROTOCOL'] . ' 200 OK' );
		}
		
		/**
		 * Fetches media items for a given hashtag when a post is saved, so that they'll be available immediately when the shortcode is displayed for the first time
		 * Note that this works, even though it often appears to do nothing. The problem is that Twitter's search API often returns no results,
		 * even when matching tweets exist. See https://dev.twitter.com/docs/faq#8650 more for details.
		 * 
		 * @Controller
		 * 
		 * @param int $post_id
		 * @param WP_Post $post
		 */
		public function prefetch_media_items( $post_id, $post ) {
			$ignored_actions = array( 'trash', 'untrash', 'restore' );
			
			if ( 1 !== did_action( 'save_post' ) ) {
				return;
			}

			if ( isset( $_GET['action'] ) && in_array( $_GET['action'], $ignored_actions ) ) {
				return;
			}
			
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! $post || $post->post_status == 'auto-draft' ) {
				return;
			}
			
			preg_match_all( '/' . get_shortcode_regex() . '/s', $post->post_content, $shortcodes, PREG_SET_ORDER );
			
			foreach ( $shortcodes as $shortcode ) {
				if ( self::SHORTCODE_NAME == $shortcode[2] ) {
					$attributes = shortcode_parse_atts( $shortcode[3] );
					
					if ( isset( $attributes['hashtag'] ) ) {
						$this->import_new_items( $attributes['hashtag'], 'ignore' );
					}
				}
			}
		}
	} // end TGGRShortcodeTagregator
}