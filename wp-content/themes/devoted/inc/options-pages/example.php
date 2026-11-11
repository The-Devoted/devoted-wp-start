<?php
/**
 * ------------------------------------------------------------------------------
 *
 * Registers an example Settings API options page — one option page, stored as
 * a single array, edited from its own page under Settings in wp-admin.
 *
 * Reference implementation.
 * To create your own settings page:
 *      - Copy this file.
 *      - Rename the option name, group, page slug, and functions.
 *      - Adjust the fields as needed.
 *
 * Delete this file if the project doesn't need the example.
 *
 * ------------------------------------------------------------------------------
 */

// The option name this page's fields are all stored under, as one serialized array.
const DEVOTED_EXAMPLE_SETTINGS_OPTION = 'devoted_example_settings';

// Passed to register_setting() and to settings_fields() in the render callback.
// WordPress uses it to know which options a given settings form is allowed to write to.
const DEVOTED_EXAMPLE_SETTINGS_GROUP = 'devoted_example_settings_group';

// This page's own admin menu + add_settings_section()/add_settings_field()
// slug, under Settings.
const DEVOTED_EXAMPLE_SETTINGS_PAGE_SLUG = 'devoted-example-settings';

// Single source of truth for this option's shape — used as both
// register_setting()'s default and the fallback merged in by
// devoted_get_example_settings().
const DEVOTED_EXAMPLE_SETTINGS_DEFAULTS = array(
	'text'     => '',
	'checkbox' => false,
	'select'   => '',
);

// Choices for the select field.
const DEVOTED_EXAMPLE_SETTINGS_SELECT_OPTIONS = array(
	'option-1' => 'Option 1',
	'option-2' => 'Option 2',
	'option-3' => 'Option 3',
);


/**
 * ------------------------------------------------------------------------------
 * Register Setting
 *
 * Registers the option itself, plus the section/fields that render it —
 * the Settings API ties all of these together by the page slug and
 * section ID passed below.
 */
function devoted_register_example_settings() {

	register_setting(
		DEVOTED_EXAMPLE_SETTINGS_GROUP,
		DEVOTED_EXAMPLE_SETTINGS_OPTION,
		array(
			'type'              => 'array',
			'default'           => DEVOTED_EXAMPLE_SETTINGS_DEFAULTS,
			'sanitize_callback' => 'devoted_sanitize_example_settings',
			'show_in_rest'      => false,
		)
	);

	add_settings_section(
		'devoted_example_settings_section',  // Section ID.
		__( 'Example Settings', 'devoted' ), // Section Title.
		function () {                        // Section Description. Pass '__return_false' instead of this function for no description.
			echo esc_html__( 'This is a section description.', 'devoted' );
		},
		DEVOTED_EXAMPLE_SETTINGS_PAGE_SLUG
	);

	add_settings_field(
		'devoted_example_settings_text',
		__( 'Example Text', 'devoted' ),
		'devoted_render_example_settings_text_field',
		DEVOTED_EXAMPLE_SETTINGS_PAGE_SLUG,
		'devoted_example_settings_section'
	);

	add_settings_field(
		'devoted_example_settings_checkbox',
		__( 'Example Checkbox', 'devoted' ),
		'devoted_render_example_settings_checkbox_field',
		DEVOTED_EXAMPLE_SETTINGS_PAGE_SLUG,
		'devoted_example_settings_section'
	);

	add_settings_field(
		'devoted_example_settings_select',
		__( 'Example Select', 'devoted' ),
		'devoted_render_example_settings_select_field',
		DEVOTED_EXAMPLE_SETTINGS_PAGE_SLUG,
		'devoted_example_settings_section'
	);
}
add_action( 'admin_init', 'devoted_register_example_settings' );


/**
 * ------------------------------------------------------------------------------
 * Sanitizer
 *
 * Validates and sanitizes the whole options array at once, returning a
 * clean copy with every key coerced to its expected type.
 */
function devoted_sanitize_example_settings( $input ) {
	return array(
		'text'     => sanitize_text_field( $input['text'] ?? '' ),
		'checkbox' => ! empty( $input['checkbox'] ),
		'select'   => array_key_exists( $input['select'] ?? '', DEVOTED_EXAMPLE_SETTINGS_SELECT_OPTIONS )
			? $input['select']
			: '',
	);
}

/**
 * Fetches the option with DEVOTED_EXAMPLE_SETTINGS_DEFAULTS merged in for
 * any missing keys, so the field render callbacks below never have to
 * guard against a partial or unset option value.
 */
function devoted_get_example_settings() {
	return wp_parse_args( get_option( DEVOTED_EXAMPLE_SETTINGS_OPTION, array() ), DEVOTED_EXAMPLE_SETTINGS_DEFAULTS );
}


/**
 * ------------------------------------------------------------------------------
 * Field Render Callbacks
 *
 * Add a render callback for each field / add_settings_field() call.
 * The Settings API wraps the section in a settings table.
 */
function devoted_render_example_settings_text_field() {
	$settings = devoted_get_example_settings();
	?>
	<input type="text" name="<?php echo esc_attr( DEVOTED_EXAMPLE_SETTINGS_OPTION ); ?>[text]" value="<?php echo esc_attr( $settings['text'] ); ?>" class="regular-text" />
	<?php
}

function devoted_render_example_settings_checkbox_field() {
	$settings = devoted_get_example_settings();
	?>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( DEVOTED_EXAMPLE_SETTINGS_OPTION ); ?>[checkbox]" value="1" <?php checked( $settings['checkbox'] ); ?> />
		<?php esc_html_e( 'Enabled', 'devoted' ); ?>
	</label>
	<?php
}

function devoted_render_example_settings_select_field() {
	$settings = devoted_get_example_settings();
	?>
	<select name="<?php echo esc_attr( DEVOTED_EXAMPLE_SETTINGS_OPTION ); ?>[select]">
		<option value=""><?php esc_html_e( '— None —', 'devoted' ); ?></option>
		<?php foreach ( DEVOTED_EXAMPLE_SETTINGS_SELECT_OPTIONS as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['select'], $value ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
}


/**
 * ------------------------------------------------------------------------------
 * Add Settings Page
 *
 * Adds the page itself under Settings in the admin menu.
 */
function devoted_add_example_settings_page() {
	add_options_page(
		__( 'Example Settings', 'devoted' ),    // Page title.
		__( 'Example Settings', 'devoted' ),    // Menu label.
		'manage_options',                       // Capability required.
		DEVOTED_EXAMPLE_SETTINGS_PAGE_SLUG,
		'devoted_render_example_settings_page'  // Render callback, below.
	);
}
add_action( 'admin_menu', 'devoted_add_example_settings_page' );


/**
 * ------------------------------------------------------------------------------
 * Render Settings Page
 *
 * The form wrapper around whatever add_settings_section() and
 * add_settings_field() registered above. Submits to options.php, which
 * handles the nonce, sanitization, and redirect back to this page itself.
 */
function devoted_render_example_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( DEVOTED_EXAMPLE_SETTINGS_GROUP );
			do_settings_sections( DEVOTED_EXAMPLE_SETTINGS_PAGE_SLUG );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
