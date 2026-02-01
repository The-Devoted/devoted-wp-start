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

		// Enable excerpts for Pages.
		add_post_type_support( 'page', 'excerpt' );

		// Disable WP core patterns.
		remove_theme_support( 'core-block-patterns' );

	}
}
add_action( 'after_setup_theme', 'devoted_setup' );

// Enqueue stylesheet.
add_action( 'wp_enqueue_scripts', 'devoted_enqueue_stylesheet' );
function devoted_enqueue_stylesheet() {

	wp_enqueue_style( 'devoted', get_template_directory_uri() . '/style.css', array(), wp_get_theme()->get( 'Version' ) );

}


/**
 * Filters the list of allowed block types
 *
 * @param array|bool $allowed_block_types Array of block type slugs, or boolean to enable/disable all.
 * @param object     $block_editor_context The current block editor context.
 *
 * @return array The filtered list of allowed block types.
 */

function devoted_disallow_blocks($allowed_block_types, $block_editor_context) {

	$disallowed_blocks = array(
		'core/spacer'
	);

	// Get all registered blocks if $allowed_block_types is not already set.
	if ( ! is_array( $allowed_block_types ) || empty( $allowed_block_types ) ) {
		$registered_blocks   = WP_Block_Type_Registry::get_instance()->get_all_registered();
		$allowed_block_types = array_keys( $registered_blocks );
	}

	// Create a new array for the allowed blocks.
	$filtered_blocks = array();

	// Loop through each block in the allowed blocks list.
	foreach ( $allowed_block_types as $block ) {

		// Check if the block is not in the disallowed blocks list.
		if ( ! in_array( $block, $disallowed_blocks, true ) ) {

			// If it's not disallowed, add it to the filtered list.
			$filtered_blocks[] = $block;
		}
	}

	return $filtered_blocks;

}
add_filter( 'allowed_block_types_all', 'devoted_disallow_blocks', 10, 2 );
