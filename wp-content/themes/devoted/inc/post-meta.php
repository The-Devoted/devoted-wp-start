<?php

/**
 * Registers REST-exposed post meta that has no classic meta box — fields
 * edited through the block editor itself (a Document Settings panel, or
 * block attributes bound to post meta), not a PHP render/save flow. For
 * fields that use a classic meta box instead, see `inc/meta-boxes.php`.
 *
 * Every PHP file under `inc/post-meta/` is required automatically.
 * No filenames need to be added here.
 *
 * Details:
 *      - Suggest one file per field group (see secondary-nav).
 *      - Supports subfolders, organize field groups as you like.
 *      - Each field group is self-contained:
 *          it defines its own uniquely named functions and hooks them itself.
 * 
 * Use case example:
 *      Template blocks like secondary nav and page header have settings, 
 *      but the user can't select those blocks in the editor.
 *      This method allows us to add settings for those blocks on the Page panel.
 *
 * @return void
 */
function devoted_register_post_meta_files( $dir ) {

	foreach ( glob( $dir . '/*' ) as $path ) {
		if ( is_dir( $path ) ) {
			devoted_register_post_meta_files( $path );
		} elseif ( '.php' === substr( $path, -4 ) ) {
			require_once $path;
		}
	}
}
devoted_register_post_meta_files( get_parent_theme_file_path( 'inc/post-meta' ) );
