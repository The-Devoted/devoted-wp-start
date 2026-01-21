<?php
/**
 *
 * @package Devoted WordPress Start
 * @author  The Devoted / Jenny Slaughter
 */

if ( ! function_exists( 'devoted_setup' ) ) {

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 *
	 * @return void
	 */
	function devoted_setup() {

		// Make theme available for translation.
		load_theme_textdomain( 'devoted', get_template_directory() . '/languages' );

		// Enqueue editor stylesheet.
		add_editor_style( get_template_directory_uri() . '/style.css' );

		// Enable excerpts for Pages
		add_post_type_support( 'page', 'excerpt' );

	}
}
add_action( 'after_setup_theme', 'devoted_setup' );

// Enqueue stylesheet.
add_action( 'wp_enqueue_scripts', 'devoted_enqueue_stylesheet' );
function devoted_enqueue_stylesheet() {

	wp_enqueue_style( 'devoted', get_template_directory_uri() . '/style.css', array(), wp_get_theme()->get( 'Version' ) );

}