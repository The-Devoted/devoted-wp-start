<?php
/**
 * Register post meta fields used by the devoted/secondary-nav block.
 * Surfaced in the Page's Document Settings sidebar (see
 * src/secondary-nav/secondary-nav-settings-panel.tsx) rather than the
 * block's own Inspector, since the block instance lives in a template part
 * (parts/sidebar-page.html), outside the editable post-content area.
 */
function devoted_register_secondary_nav_meta() {

	register_post_meta(
		'page',
		'devoted_hide_from_secondary_nav',
		array(
			'type'          => 'boolean',
			'single'        => true,
			'default'       => false,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_pages' );
			},
		)
	);

	// Per-page override for the block's `mode` attribute. render.php only
	// ever checks this on the exact page currently being viewed — no
	// fallback to a section's root page or any other ancestor — falling
	// back to the template's `mode` attribute (default 'trail') if unset.
	register_post_meta(
		'page',
		'devoted_secondary_nav_mode',
		array(
			'type'          => 'string',
			'single'        => true,
			'default'       => '',
			'show_in_rest'  => array(
				'schema' => array(
					'type' => 'string',
					'enum' => array( '', 'trail', 'full' ),
				),
			),
			'auth_callback' => function () {
				return current_user_can( 'edit_pages' );
			},
		)
	);
}
add_action( 'init', 'devoted_register_secondary_nav_meta' );
