<?php

/**
 * ------------------------------------------------------------------------------
 * 
 * Registers an example field group containing one of each common field type.
 *
 * Reference implementation. 
 * To create new field groups:
 *      - Copy this file.
 *      - Rename meta keys, post type(s), variables to align with your needs.
 *      - Adjust the field markup as needed.
 * 
 * Delete this file if your project doesn't need custom fields.
 * 
 * ------------------------------------------------------------------------------
 */



/**
 * Define what post types should display this field group.
 * Must be an array, even for a single post type.
 */
const DEVOTED_EXAMPLE_META_POST_TYPES = array( 'post', 'page', 'example' );

// Define options for fields that accept them (radio, select, multi-select)
const DEVOTED_EXAMPLE_RADIO_OPTIONS = array(
	'option-1' => 'Option 1',
	'option-2' => 'Option 2',
	'option-3' => 'Option 3',
);

const DEVOTED_EXAMPLE_SELECT_OPTIONS = array(
	'option-1' => 'Option 1',
	'option-2' => 'Option 2',
	'option-3' => 'Option 3',
);

const DEVOTED_EXAMPLE_MULTISELECT_OPTIONS = array(
	'option-1' => 'Option 1',
	'option-2' => 'Option 2',
	'option-3' => 'Option 3',
);


/**
 * ------------------------------------------------------------------------------
 * Register Meta
 *
 * Defines each field in the group.
 */
function devoted_register_example_meta() {
	foreach ( DEVOTED_EXAMPLE_META_POST_TYPES as $post_type ) {

		register_post_meta(
			$post_type,
			'devoted_example_text',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_textarea',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field', // Preserves line breaks.
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_number',
			array(
				'type'              => 'string', // Declared string, not integer, so register_meta allows a true empty default; sanitize_callback still coerces to int on save.
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => function ( $value ) {
					return intval( $value );
				},
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_email',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => 'sanitize_email',
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_url',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw', // The non-display sanitizer for URLs headed to storage; esc_url() is for echoing into HTML.
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_checkbox',
			array(
				'type'              => 'boolean',
				'single'            => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_radio',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => 'option-1',
				'sanitize_callback' => function ( $value ) {
					return devoted_sanitize_example_option( $value, DEVOTED_EXAMPLE_RADIO_OPTIONS );
				},
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_select',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => function ( $value ) {
					return devoted_sanitize_example_option( $value, DEVOTED_EXAMPLE_SELECT_OPTIONS );
				},
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_multiselect',
			array(
				'type'              => 'array',
				'single'            => true,
				'default'           => array(),
				'sanitize_callback' => function ( $values ) {
					return devoted_sanitize_example_options( $values, DEVOTED_EXAMPLE_MULTISELECT_OPTIONS );
				},
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_rich_text',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => 'wp_kses_post', // Preserves safe HTML — this holds rich text, not plain text.
			)
		);

		register_post_meta(
			$post_type,
			'devoted_example_image',
			array(
				'type'              => 'string', // Stores the attachment ID as a string so register_meta allows a true empty default; sanitize_callback still coerces to int on save.
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => 'absint',
			)
		);
	}
}
add_action( 'init', 'devoted_register_example_meta' );


/**
 * ------------------------------------------------------------------------------
 * Sanitizers
 * 
 * Sanitize inputs for fields with pre-defined options (radio, selects).
 *      WordPress does not have a built-in "must match defined values" sanitizer,
 *      so these must be sanitized manually.
 * Only a key from the given $options array is ever a valid value.
 */

//  Single-value sanitizer (radio, single-select).
function devoted_sanitize_example_option( $value, array $options ) {
	return array_key_exists( $value, $options ) ? $value : '';
}

// Multi-select sanitizer.
function devoted_sanitize_example_options( $values, array $options ) {
	return array_values( array_intersect( (array) $values, array_keys( $options ) ) );
}


/**
 * ------------------------------------------------------------------------------
 * Add Meta Box
 * 
 * Registers the meta box itself.
 */
function devoted_add_example_meta_box() {
	add_meta_box(
		'devoted_example_fields',           // Box ID.
		__( 'Example Fields', 'devoted' ),  // Box title.
		'devoted_render_example_meta_box',  // Render callback, below.
		DEVOTED_EXAMPLE_META_POST_TYPES,    // Post type(s): a string or an array of slugs.
		'normal',                           // Where to add the fields: normal (below content) | side (sidebar)
		'high'                              // Priority within its context.
	);
}
add_action( 'add_meta_boxes', 'devoted_add_example_meta_box' );


/**
 * ------------------------------------------------------------------------------
 * Enqueue Meta Box Assets
 * 
 * Loads WP's media library JS & CSS.
 *
 * Only needed for the image field. 
 * If your field group doesn't have an image picker, delete this function and its add_action call.
 */
function devoted_enqueue_example_meta_box_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	if ( ! in_array( get_current_screen()->post_type, DEVOTED_EXAMPLE_META_POST_TYPES, true ) ) {
		return;
	}
	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'devoted_enqueue_example_meta_box_assets' );


/**
 * ------------------------------------------------------------------------------
 * Render Meta Box
 * 
 * Render the meta box on the edit page.
 * Defines what the fields look like in the editor.
 */
function devoted_render_example_meta_box( $post ) {

	wp_nonce_field( 'devoted_save_example_meta_box', 'devoted_example_meta_box_nonce' );

	$text        = get_post_meta( $post->ID, 'devoted_example_text', true );
	$textarea    = get_post_meta( $post->ID, 'devoted_example_textarea', true );
	$number      = get_post_meta( $post->ID, 'devoted_example_number', true );
	$email       = get_post_meta( $post->ID, 'devoted_example_email', true );
	$url         = get_post_meta( $post->ID, 'devoted_example_url', true );
	$checkbox    = get_post_meta( $post->ID, 'devoted_example_checkbox', true );
	$radio       = get_post_meta( $post->ID, 'devoted_example_radio', true );
	$select      = get_post_meta( $post->ID, 'devoted_example_select', true );
	$multiselect = (array) get_post_meta( $post->ID, 'devoted_example_multiselect', true );
	$rich_text   = get_post_meta( $post->ID, 'devoted_example_rich_text', true );
	$image_id    = get_post_meta( $post->ID, 'devoted_example_image', true );

	if ( '' === $radio ) {
		$radio = 'option-1'; // register_post_meta's default only applies before a value is ever saved.
	}
	?>
	<table class="form-table">
		<tr>
			<th><label for="devoted_example_text"><?php esc_html_e( 'Example Text', 'devoted' ); ?></label></th>
			<td><input type="text" id="devoted_example_text" name="devoted_example_text" value="<?php echo esc_attr( $text ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="devoted_example_textarea"><?php esc_html_e( 'Example Textarea', 'devoted' ); ?></label></th>
			<td><textarea id="devoted_example_textarea" name="devoted_example_textarea" class="large-text" rows="4"><?php echo esc_textarea( $textarea ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="devoted_example_number"><?php esc_html_e( 'Example Number', 'devoted' ); ?></label></th>
			<td><input type="number" id="devoted_example_number" name="devoted_example_number" value="<?php echo esc_attr( $number ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="devoted_example_email"><?php esc_html_e( 'Example Email', 'devoted' ); ?></label></th>
			<td><input type="email" id="devoted_example_email" name="devoted_example_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="devoted_example_url"><?php esc_html_e( 'Example URL', 'devoted' ); ?></label></th>
			<td><input type="url" id="devoted_example_url" name="devoted_example_url" value="<?php echo esc_attr( $url ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Example Checkbox', 'devoted' ); ?></th>
			<td>
				<label>
					<input type="hidden" name="devoted_example_checkbox" value="0" />
					<input type="checkbox" name="devoted_example_checkbox" value="1" <?php checked( $checkbox ); ?> />
					<?php esc_html_e( 'Enabled', 'devoted' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Example Radio', 'devoted' ); ?></th>
			<td>
				<?php foreach ( DEVOTED_EXAMPLE_RADIO_OPTIONS as $value => $label ) : ?>
					<label>
						<input type="radio" name="devoted_example_radio" value="<?php echo esc_attr( $value ); ?>" <?php checked( $radio, $value ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
					<br />
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th><label for="devoted_example_select"><?php esc_html_e( 'Example Single Select', 'devoted' ); ?></label></th>
			<td>
				<select id="devoted_example_select" name="devoted_example_select">
					<option value=""><?php esc_html_e( '— None —', 'devoted' ); ?></option>
					<?php foreach ( DEVOTED_EXAMPLE_SELECT_OPTIONS as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $select, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="devoted_example_multiselect"><?php esc_html_e( 'Example Multi Select', 'devoted' ); ?></label></th>
			<td>
				<select id="devoted_example_multiselect" name="devoted_example_multiselect[]" multiple="multiple">
					<?php foreach ( DEVOTED_EXAMPLE_MULTISELECT_OPTIONS as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php echo in_array( $value, $multiselect, true ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="devoted_example_rich_text"><?php esc_html_e( 'Example Rich Text', 'devoted' ); ?></label></th>
			<td>
				<?php
				wp_editor(
					$rich_text,
					'devoted_example_rich_text',
					array(
						'textarea_name' => 'devoted_example_rich_text',
						'textarea_rows' => 6,
						'media_buttons' => false,
					)
				);
				?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Example Image', 'devoted' ); ?></th>
			<td>
				<input type="hidden" id="devoted_example_image" name="devoted_example_image" value="<?php echo esc_attr( $image_id ); ?>" />
				<div id="devoted_example_image_preview">
					<?php if ( $image_id ) : ?>
						<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
					<?php endif; ?>
				</div>
				<p>
					<button type="button" class="button" id="devoted_example_image_select"><?php esc_html_e( 'Select Image', 'devoted' ); ?></button>
					<button type="button" class="button" id="devoted_example_image_remove" <?php echo $image_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove Image', 'devoted' ); ?></button>
				</p>
			</td>
		</tr>
	</table>

    <!-- Image Chooser Script -->
    <!-- Remove if your field group does not contain an image field. -->
	<script>
	( function( $ ) {
		var frame;
		$( '#devoted_example_image_select' ).on( 'click', function( e ) {
			e.preventDefault();
			if ( ! frame ) {
				frame = wp.media( { title: <?php echo wp_json_encode( __( 'Select Image', 'devoted' ) ); ?>, multiple: false } );
				frame.on( 'select', function() {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					var thumb = ( attachment.sizes && attachment.sizes.thumbnail ) ? attachment.sizes.thumbnail.url : attachment.url;
					$( '#devoted_example_image' ).val( attachment.id );
					$( '#devoted_example_image_preview' ).html( '<img src="' + thumb + '" />' );
					$( '#devoted_example_image_remove' ).show();
				} );
			}
			frame.open();
		} );
		$( '#devoted_example_image_remove' ).on( 'click', function( e ) {
			e.preventDefault();
			$( '#devoted_example_image' ).val( '' );
			$( '#devoted_example_image_preview' ).empty();
			$( this ).hide();
		} );
	} )( jQuery );
	</script>
    <!-- End Image Chooser Script -->

	<?php
}


/**
 * ------------------------------------------------------------------------------
 * Save Meta Box
 * 
 * Updates the user's entered post meta data, unless:
 *      - Nonce verification fails
 *      - Autosave is happening
 *      - The user lacks the capability to edit posts.
 */
function devoted_save_example_meta_box( $post_id ) {

	if ( ! isset( $_POST['devoted_example_meta_box_nonce'] ) ||
		! wp_verify_nonce( $_POST['devoted_example_meta_box_nonce'], 'devoted_save_example_meta_box' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, 'devoted_example_text', sanitize_text_field( wp_unslash( $_POST['devoted_example_text'] ?? '' ) ) );
	update_post_meta( $post_id, 'devoted_example_textarea', sanitize_textarea_field( wp_unslash( $_POST['devoted_example_textarea'] ?? '' ) ) );
	update_post_meta( $post_id, 'devoted_example_number', intval( $_POST['devoted_example_number'] ?? 0 ) );
	update_post_meta( $post_id, 'devoted_example_email', sanitize_email( wp_unslash( $_POST['devoted_example_email'] ?? '' ) ) );
	update_post_meta( $post_id, 'devoted_example_url', esc_url_raw( wp_unslash( $_POST['devoted_example_url'] ?? '' ) ) );
	update_post_meta( $post_id, 'devoted_example_checkbox', rest_sanitize_boolean( $_POST['devoted_example_checkbox'] ?? false ) );
	update_post_meta( $post_id, 'devoted_example_radio', devoted_sanitize_example_option( wp_unslash( $_POST['devoted_example_radio'] ?? '' ), DEVOTED_EXAMPLE_RADIO_OPTIONS ) );
	update_post_meta( $post_id, 'devoted_example_select', devoted_sanitize_example_option( wp_unslash( $_POST['devoted_example_select'] ?? '' ), DEVOTED_EXAMPLE_SELECT_OPTIONS ) );
	update_post_meta( $post_id, 'devoted_example_multiselect', devoted_sanitize_example_options( wp_unslash( $_POST['devoted_example_multiselect'] ?? array() ), DEVOTED_EXAMPLE_MULTISELECT_OPTIONS ) );
	update_post_meta( $post_id, 'devoted_example_rich_text', wp_kses_post( wp_unslash( $_POST['devoted_example_rich_text'] ?? '' ) ) );
	update_post_meta( $post_id, 'devoted_example_image', absint( $_POST['devoted_example_image'] ?? 0 ) );
}
foreach ( DEVOTED_EXAMPLE_META_POST_TYPES as $devoted_example_meta_post_type ) {
	add_action( "save_post_{$devoted_example_meta_post_type}", 'devoted_save_example_meta_box' );
}
