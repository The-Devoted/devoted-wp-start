<?php
/**
 * Registers an example custom post type.
 *
 * Reference registration. 
 * To register a new CPT:
 *      - Copy this function & rename it
 *      - Add your post type name, slug, and labels
 *      - Set options as needed
 * 
 * The CPT will be automatically registered by post-types.php.
 * Delete this file if the project doesn't need the example.
 */
function devoted_register_example_post_type() {

	$labels = array(
		'name'          => _x( 'Examples', 'post type general name', 'devoted' ),
		'singular_name' => _x( 'Example', 'post type singular name', 'devoted' ),
		'menu_name'     => _x( 'Examples', 'admin menu', 'devoted' ),
		'add_new_item'  => __( 'Add New Example', 'devoted' ),
		'edit_item'     => __( 'Edit Example', 'devoted' ),
		'view_item'     => __( 'View Example', 'devoted' ),
		'all_items'     => __( 'All Examples', 'devoted' ),
		'search_items'  => __( 'Search Examples', 'devoted' ),
		'not_found'     => __( 'No examples found.', 'devoted' ),
	);

	register_post_type(
		'example',
		array(
			'label'                 => __( 'Examples', 'devoted' ),
			'labels'                => $labels,
			'description'           => __( 'Example post type for site development.', 'devoted' ),
			'public'                => true,                        // Visible on the front end and in wp-admin.
			'exclude_from_search'   => false,                       // Include in front-end search results.
			'publicly_queryable'    => true,                        // Has single/archive URLs on the front end.
			'show_ui'               => true,                        // Gets admin edit screens + list table.
			'show_in_menu'          => true,                        // Shown in the admin menu (true = top-level item, or pass a parent slug).
			'show_in_admin_bar'     => true,                        // Shown in the "+ New" toolbar menu.
			'show_in_nav_menus'     => true,                        // Selectable in Appearance > Menus.
			'show_in_rest'          => true,                        // Exposed to the block editor + REST API.
			'rest_base'             => 'example',                   // REST URL base; this is the default (post type slug).
			'rest_namespace'        => 'wp/v2',                     // REST namespace; this is the core default.
			'rest_controller_class' => 'WP_REST_Posts_Controller',  // Core default; swap for a custom controller if needed.
			'menu_position'         => null,                        // Admin menu order; null = after Comments.
			'menu_icon'             => 'dashicons-star-filled',     // Dashicon name or a data: URI SVG.
			'hierarchical'          => true,                        // Supports parent/child + Page Attributes, like `page`.
			'capability_type'       => 'post',                      // Reuses regular Post capabilities. Pair with `map_meta_cap => true` for a separate custom capability set.
			'supports'              => array( 'title', 'editor', 'thumbnail', 'author', 'revisions', 'excerpt', 'page-attributes' ),
			'taxonomies'            => array( 'category', 'post_tag' ),
			'has_archive'           => true,                        // Enables an archive URL (e.g. /examples/).
			'rewrite'               => array(
				'slug'       => 'examples',

                // `with_front` => false keeps this CPT's URLs at the top level
				// instead of anything added to the permalink structure (e.g. /news/).
				'with_front' => false,
			),
			'query_var'             => true,    // Enables ?example=<slug> queries (true = post type name).
			'can_export'            => true,    // Included in Tools > Export.
			'register_meta_box_cb'  => null,    // Add custom meta boxes on the edit screen.
			'delete_with_user'      => null,    // Delete this author's posts if their account is deleted; null defers to capability_type.
		)
	);
}
add_action( 'init', 'devoted_register_example_post_type' );
