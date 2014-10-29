<?php
/**
 * wct2014 functions and definitions
 *
 * Set up the content width value based on the theme's design.
 *
 * @see twentyfourteen_content_width()
 *
 * @since wct2014 1.0
 */

/**
 * Enqueue scripts and styles for the front end.
 */
function wct2014_scripts() {
	wp_enqueue_style( 'wct2014', get_stylesheet_directory_uri() . '/wct2014.css', array( 'twentyfourteen-style' ) );
}
add_filter( 'wp_enqueue_scripts', 'wct2014_scripts' );

/**
 * add extra classes to body.
 */
function wct2014_body_class( $classes ) {
	if ( is_page_template( 'page-templates/page-sessions.php' ) ) {
		$classes[] = 'full-width';
	}
	return $classes;
}
add_filter( 'body_class', 'wct2014_body_class' );