<?php

/**
 * Registers Settings API options pages — WordPress's native replacement
 * for a global "options" admin page (what ACF's Options Pages provided).
 * For per-post fields instead, see `inc/meta-boxes.php` (classic meta
 * boxes) or `inc/post-meta.php` (REST-only fields with no classic box).
 *
 * Every PHP file under `inc/options-pages/` is required automatically.
 * No filenames need to be added here.
 *
 * Details:
 *      - Suggest one file per settings page (see example).
 *      - Supports subfolders, organize settings pages as you like.
 *      - Each settings page is self-contained:
 *          it defines its own uniquely named functions and hooks them itself.
 *
 * @return void
 */
function devoted_register_options_files( $dir ) {

	foreach ( glob( $dir . '/*' ) as $path ) {
		if ( is_dir( $path ) ) {
			devoted_register_options_files( $path );
		} elseif ( '.php' === substr( $path, -4 ) ) {
			require_once $path;
		}
	}
}
devoted_register_options_files( get_parent_theme_file_path( 'inc/options-pages' ) );
