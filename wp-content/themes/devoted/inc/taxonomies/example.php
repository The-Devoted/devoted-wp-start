<?php
/**
 * Registers an example taxonomy, attached to the `example` post type.
 *
 * Reference registration. 
 * To register a new taxonomy:
 *      - Copy this file & rename it.
 *      - Rename the function.
 *      - Add your taxonomy slug & labels.
 *      - Define what post types the taxonomy attaches to.
 *          - Remember that those post types must exist first!
 *      - Set options as needed.
 *      - CRITICAL: Add this file to the list in `inc/taxonomies.php`
 * 
 * Delete this file if the project doesn't need the example.
 */
function devoted_register_example_taxonomy() {

	$labels = array(
		'name'          => _x( 'Example Types', 'taxonomy general name', 'devoted' ),
		'singular_name' => _x( 'Example Type', 'taxonomy singular name', 'devoted' ),
		'menu_name'     => _x( 'Example Types', 'admin menu', 'devoted' ),
		'add_new_item'  => __( 'Add New Example Type', 'devoted' ),
		'edit_item'     => __( 'Edit Example Type', 'devoted' ),
		'view_item'     => __( 'View Example Type', 'devoted' ),
		'all_items'     => __( 'All Example Types', 'devoted' ),
		'search_items'  => __( 'Search Example Types', 'devoted' ),
		'not_found'     => __( 'No example types found.', 'devoted' ),
	);

	register_taxonomy(
		'example_type',
        array('post', 'page', 'example'), // Which post type(s) this taxonomy attaches to. Remember that the post type must exist first!
		array(
			'label'              => __( 'Example Types', 'devoted' ),
			'labels'             => $labels,
			'description'        => __( 'Example taxonomy for site development.', 'devoted' ),
			'public'             => true,               // Visible on the front end and in wp-admin.
			'publicly_queryable' => true,               // Has archive URLs on the front end.
			'hierarchical'       => true,               // Parent/child terms + a dropdown UI, like `category`. False = flat like tags.
			'show_ui'            => true,               // Gets an admin UI (term list, add/edit screens).
			'show_in_menu'       => true,               // Shown in the admin menu, nested under its post type.
			'show_in_nav_menus'  => true,               // Selectable in Appearance > Menus.
			'show_admin_column'  => true,               // Shown as a column on the post type's list table.
			'show_in_quick_edit' => true,               // Editable from Quick Edit.
			'show_tagcloud'      => false,              // Available to the (deprecated) Tag Cloud widget.
			'show_in_rest'       => true,               // Exposed to the block editor + REST API.
			'rest_base'          => 'example-types',    // REST URL base; this is the default (taxonomy slug).
			'rest_namespace'     => 'wp/v2',            // REST namespace; this is the core default.
			'query_var'          => true,               // Enables ?example_type=<slug> queries (true = taxonomy name).
			'rewrite'            => array(
				'slug'         => 'example-types',
                // `with_front` => false keeps this CPT's URLs at the top level
                // instead of anything added to the permalink structure (e.g. /news/).
                'with_front'   => false,
				'hierarchical' => true,                 // Nest child-term URLs under their parent's, e.g. /example-types/parent/child/.
			),
			'capabilities'       => array( // Reuses the regular Category capabilities. Pass a custom set here to restrict who can manage terms.
				'manage_terms' => 'manage_categories',
				'edit_terms'   => 'manage_categories',
				'delete_terms' => 'manage_categories',
				'assign_terms' => 'edit_posts',
			),
			'sort'               => false, // Whether term order (as set when assigning) is preserved and queryable.
		)
	);
}
add_action( 'init', 'devoted_register_example_taxonomy' );
