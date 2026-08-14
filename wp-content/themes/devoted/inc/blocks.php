<?php
/**
 * Register custom blocks.
 *
 * Blocks are authored in TypeScript under `src/<block>/` and compiled to
 * `build/<block>/` by @wordpress/scripts (`npm run build`). Every compiled
 * block that ships a `block.json` is registered automatically, so adding a new
 * block only requires creating a new folder under `src/` — no change here.
 *
 * @return void
 */
function devoted_register_blocks() {

	$build_dir = get_theme_file_path( 'build' );

	if ( ! is_dir( $build_dir ) ) {
		return;
	}

	foreach ( glob( $build_dir . '/*', GLOB_ONLYDIR ) as $block_dir ) {
		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type( $block_dir );
		}
	}
}
add_action( 'init', 'devoted_register_blocks' );

/**
 * Register the "Devoted Theme" block category for the theme's custom blocks
 * (devoted/page-header, devoted/secondary-nav, etc.).
 *
 * @param array $categories Registered block categories.
 * @return array
 */
function devoted_register_block_categories( $categories ) {

	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'devoted-theme',
				'title' => __( 'Devoted Theme', 'devoted' ),
				'icon'  => null,
			),
		)
	);
}
add_filter( 'block_categories_all', 'devoted_register_block_categories' );
