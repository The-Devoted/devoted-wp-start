<?php

/**
 * Registers meta boxes (custom fields).
 *
 * Every PHP file under `inc/meta-boxes/` is required automatically.
 * No filenames need to be added here.
 * 
 * Details:
 *      - Suggest one file per field group (see example).
 *      - Supports subfolders, organize field groups as you like.
 *      - Each field group is self-contained: 
 *          it defines its own uniquely named functions and hooks them itself.
 *
 * @return void
 */
function devoted_register_meta_boxes( $dir ) {

	foreach ( glob( $dir . '/*' ) as $path ) {
		if ( is_dir( $path ) ) {
			devoted_register_meta_boxes( $path );
		} elseif ( '.php' === substr( $path, -4 ) ) {
			require_once $path;
		}
	}
}
devoted_register_meta_boxes( get_parent_theme_file_path( 'inc/meta-boxes' ) );
