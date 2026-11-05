<?php
/**
 * Registers custom taxonomies.
 *
 * To add a taxonomy: copy a file in `inc/taxonomies/` (or write a new one),
 * then add its filename to the list below.
 * 
 * This file does NOT autoload taxonomies, since load order matters during registration.
 * To register a taxonomy to a post type, that post type must exist first.
 */
$devoted_taxonomy_files = array(
	'example.php',
);

foreach ( $devoted_taxonomy_files as $devoted_taxonomy_file ) {
	require_once get_parent_theme_file_path( 'inc/taxonomies/' . $devoted_taxonomy_file );
}
