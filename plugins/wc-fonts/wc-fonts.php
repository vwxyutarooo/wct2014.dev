<?php
/*
 * Plugin Name: WordCamp.org Fonts
 */

class WordCamp_Fonts_Plugin {

	protected $options;

	// Runs when file is loaded
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		
		add_action( 'wp_head', array( $this, 'wp_head_typekit' ), 102 ); // after safecss_style
		add_action( 'wp_head', array( $this, 'wp_head_google_web_fonts' ) );
	}

	/**
	 * Runs during init, loads the options array.
	 */
	function init() {
		$this->options = (array) get_option( 'wc-fonts-options', array() );
	}
	
	/**
	 * Provides the <head> output for Typekit settings.
	 */
	function wp_head_typekit() {
		if ( ! isset( $this->options['typekit-id'] ) || empty( $this->options['typekit-id'] ) )
			return;
			
		printf( '<script type="text/javascript" src="http://use.typekit.com/%s.js"></script>' . "\n", $this->options['typekit-id'] );
		printf( '<script type="text/javascript">try{Typekit.load();}catch(e){}</script>' );
	}
	
	/**
	 * Provides the <head> output for Google Web Fonts
	 */
	function wp_head_google_web_fonts() {
		if ( ! isset( $this->options['google-web-fonts'] ) || empty( $this->options['google-web-fonts'] ) )
			return;

		printf( '<style>%s</style>', $this->options['google-web-fonts'] );
	}

	/**
	 * Runs during admin init, does Settings API
	 */
	function admin_init() {
		register_setting( 'wc-fonts-options', 'wc-fonts-options', array( $this, 'validate_options' ) );
		add_settings_section( 'general', '', '__return_null', 'wc-fonts-options' );

		add_settings_field( 'typekit-id', 'Typekit ID', array( $this, 'field_typekit_id' ), 'wc-fonts-options', 'general' );
		add_settings_field( 'google-web-fonts', 'Google Web Fonts', array( $this, 'field_google_web_fonts' ), 'wc-fonts-options', 'general' );
	}

	/**
	 * Runs during admin_menu, adds a Fonts section to Appearance
	 */
	function admin_menu() {
		$fonts = add_theme_page( 'Fonts', 'Fonts', 'edit_theme_options', 'wc-fonts-options', array( $this, 'render_admin_page' ) );
	}
	
	/**
	 * Uses the Settings API to render the Appearance > Fonts page
	 */
	function render_admin_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?><h2>Fonts</h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
					settings_fields( 'wc-fonts-options' );
					do_settings_sections( 'wc-fonts-options' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Settings API field for the Typekit ID
	 */
	function field_typekit_id() {
		$value = isset( $this->options['typekit-id'] ) ? $this->options['typekit-id'] : '';
		?>
		<input type="text" name="wc-fonts-options[typekit-id]" value="<?php echo esc_attr( $value ); ?>" class="regular-text code" />
		<p class="description">Enter your Typekit Kit ID. No links, no javascript, just the id, we'll handle the rest for you.</p>
		<?php
	}

	/**
	 * Settings API field for the Google Web Fonts URLs
	 */
	function field_google_web_fonts() {
		$value = isset( $this->options['google-web-fonts'] ) ? $this->options['google-web-fonts'] : '';
		?>
		<textarea rows="5" name="wc-fonts-options[google-web-fonts]" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">Paste the Google Web Fonts @import URLs in this area, each one on a separate line.</p>
		<?php
	}

	/**
	 * Triggered by the Settings API upon settings save.
	 */
	function validate_options( $input ) {
		$output = $this->options;

		if ( isset( $input['typekit-id'] ) )
			$output['typekit-id'] = preg_replace( '/[^0-9a-zA-Z]+/', '', $input['typekit-id'] );

		if ( isset( $input['google-web-fonts'] ) ) {
			$fonts = array();
			$lines = explode( "\n", $input['google-web-fonts'] );
			foreach ( $lines as $line ) {
				$matches = array();
				$url = preg_match( '#fonts\.googleapis\.com/css\?family=[^\)\'"]+#', $line, $matches );
				if ( $matches ) $url = esc_url_raw( 'http://' . $matches[0] );

				if ( ! $url )
					continue;

				$url = parse_url( $url );

				if ( $url['host'] != 'fonts.googleapis.com' )
					continue;

				if ( ! preg_match( '/^family=(.+)/i', $url['query'] ) )
					continue;

				$url = $url['scheme'] . '://' . $url['host'] . $url['path'] . '?' . $url['query'];
				$import = "@import url('" . esc_url_raw( $url ) . "');";
				$fonts[] = $import;
			}
			$output['google-web-fonts'] = implode( "\n", $fonts );
		}

		return $output;
	}
}

// Go!
new WordCamp_Fonts_Plugin;