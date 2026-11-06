<?php
/**
 * Registers custom post types.
 *
 * Every PHP file under `inc/post-types/` is required automatically.
 * No filenames need to be added here.
 * 
 * See `inc/post-types/example.php` for an example implementation.
 *
 * @return void
 */
function devoted_register_post_types() {

	$post_types_dir = get_parent_theme_file_path( 'inc/post-types' );

	foreach ( glob( $post_types_dir . '/*.php' ) as $file ) {
		require_once $file;
	}
}
devoted_register_post_types();
