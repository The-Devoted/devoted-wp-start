<?php
/**
 * ------------------------------------------------------------------------------
 *
 * Registers the "Theme Options" settings page, under Settings in wp-admin.
 *
 * No fields yet — add add_settings_section()/add_settings_field() calls
 * in devoted_register_theme_options() below as the project needs them
 * (see inc/options-pages/example.php for the pattern).
 *
 * ------------------------------------------------------------------------------
 */

// The option name this page's fields will be stored under, as one
// serialized array — not one row per field.
const DEVOTED_THEME_OPTIONS_OPTION = 'devoted_theme_options';

// Passed to register_setting() and to settings_fields() in the render
// callback below; WordPress uses it to know which options a given
// settings form is allowed to write to.
const DEVOTED_THEME_OPTIONS_GROUP = 'devoted_theme_options_group';

// This page's own admin menu + add_settings_section()/add_settings_field()
// slug, under Settings.
const DEVOTED_THEME_OPTIONS_PAGE_SLUG = 'devoted-theme-options';

/**
 * ------------------------------------------------------------------------------
 * Register Setting
 *
 * Registers the option itself. Add add_settings_section()/
 * add_settings_field() calls here once this page has fields.
 */
function devoted_register_theme_options() {

	register_setting(
		DEVOTED_THEME_OPTIONS_GROUP,
		DEVOTED_THEME_OPTIONS_OPTION,
		array(
			'type'    => 'array',
			'default' => array(),
		)
	);
}
add_action( 'admin_init', 'devoted_register_theme_options' );

/**
 * ------------------------------------------------------------------------------
 * Add Settings Page
 *
 * Adds the page itself under Settings in the admin menu.
 */
function devoted_add_theme_options_page() {
	add_options_page(
		__( 'Theme Options', 'devoted' ), // Page title.
		__( 'Theme Options', 'devoted' ), // Menu label.
		'manage_options', // Capability required.
		DEVOTED_THEME_OPTIONS_PAGE_SLUG,
		'devoted_render_theme_options_page' // Render callback, below.
	);
}
add_action( 'admin_menu', 'devoted_add_theme_options_page' );

/**
 * ------------------------------------------------------------------------------
 * Render Settings Page
 *
 * The form wrapper around whatever add_settings_section()/
 * add_settings_field() registers above. Renders empty until this page has
 * fields. Submits to options.php, which handles the nonce, sanitization,
 * and redirect back to this page itself.
 */
function devoted_render_theme_options_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( DEVOTED_THEME_OPTIONS_GROUP );
			do_settings_sections( DEVOTED_THEME_OPTIONS_PAGE_SLUG );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
