<?php
/**
 * Register block pattern categories.
 */
function devoted_register_block_pattern_categories() {

	register_block_pattern_category(
		'devoted-landing',
		array(
			'label'       => __( 'Landing', 'devoted' ),
			'description' => __( 'Patterns for Landing Pages', 'devoted' ),
		)
	);
	register_block_pattern_category(
		'devoted-site',
		array(
			'label'       => __( 'Site', 'devoted' ),
			'description' => __( 'Site building pieces such as global header and footer', 'devoted' ),
		)
	);
	register_block_pattern_category(
		'devoted-text',
		array(
			'label'       => __( 'Text', 'devoted' ),
			'description' => __( 'Text-based patterns.', 'devoted' ),
		)
	);

}
add_action( 'init', 'devoted_register_block_pattern_categories' );
