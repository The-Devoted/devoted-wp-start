<?php

/**
 * ------------------------------------------------------------------------------
 *
 * Registers the "Theme Settings" settings page, under Settings in wp-admin.
 *
 * No fields yet — add add_settings_section()/add_settings_field() calls
 * in devoted_register_theme_settings() below as the project needs them
 * (see inc/options-pages/example.php for the pattern).
 *
 * ------------------------------------------------------------------------------
 */

// The option name this page's fields are all stored under, as one serialized array.
const DEVOTED_THEME_SETTINGS_OPTION = 'devoted_theme_settings';

// Passed to register_setting() and to settings_fields() in the render callback.
// WordPress uses it to know which options a given settings form is allowed to write to.
const DEVOTED_THEME_SETTINGS_GROUP = 'devoted_theme_settings_group';

// Slug for this page in the admin menu.
const DEVOTED_THEME_SETTINGS_PAGE_SLUG = 'devoted-theme-settings';

// Set defaults.
const DEVOTED_THEME_SETTINGS_DEFAULTS = array(
    'show_private_in_nav' => true,
    'show_private_label'  => false,
);

/**
 * ------------------------------------------------------------------------------
 * Register Setting
 *
 * Registers the option itself. Add add_settings_section()/
 * add_settings_field() calls here once this page has fields.
 */
function devoted_register_theme_settings()
{

    register_setting(
        DEVOTED_THEME_SETTINGS_GROUP,
        DEVOTED_THEME_SETTINGS_OPTION,
        array(
            'type'    => 'array',
            'default'           => DEVOTED_THEME_SETTINGS_DEFAULTS,
            'sanitize_callback' => 'devoted_sanitize_theme_settings',
            'show_in_rest'      => false,
        )
    );

    add_settings_section(
        'devoted_theme_settings_section',   // Section ID.
        __('Theme Settings', 'devoted'),    // Section Title.
        function () {                       // Section Description. Pass '__return_false' instead of this function for no description.
            echo esc_html__('Settings for your theme.', 'devoted');
        },
        DEVOTED_THEME_SETTINGS_PAGE_SLUG    // Admin Slug
    );

    add_settings_field(
        'devoted_theme_settings_show_private_in_nav',                   // ID for this field
        __('Show Private pages in automatic navigation?', 'devoted'),   // Label for the field
        'devoted_render_theme_settings_show_private_in_nav',            // Function that renders the field markup.
        DEVOTED_THEME_SETTINGS_PAGE_SLUG,                               // Admin slug.
        'devoted_theme_settings_section'                                // Section this field is a part of.
    );

    add_settings_field(
        'devoted_theme_settings_show_private_label',                 // ID for this field
        __('Show "Private:" labels on Private pages?', 'devoted'),   // Label for the field
        'devoted_render_theme_settings_show_private_label',          // Function that renders the field markup.
        DEVOTED_THEME_SETTINGS_PAGE_SLUG,                             // Admin slug.
        'devoted_theme_settings_section'                              // Section this field is a part of.
    );
}
add_action('admin_init', 'devoted_register_theme_settings');


/**
 * ------------------------------------------------------------------------------
 * Sanitizer
 *
 * Validates and sanitizes the whole options array at once, returning a
 * clean copy with every key coerced to its expected type.
 */
function devoted_sanitize_theme_settings($input)
{
    $input = (array) $input;
    return array(
        'show_private_in_nav' => ! empty($input['show_private_in_nav']),
        'show_private_label'  => ! empty($input['show_private_label']),
    );
}


/**
 * Fetches the option with defaults merged in for
 * any missing keys, so the field render callbacks below never have to
 * guard against a partial or unset option value.
 */
function devoted_get_theme_settings()
{
    return wp_parse_args(get_option(DEVOTED_THEME_SETTINGS_OPTION, array()), DEVOTED_THEME_SETTINGS_DEFAULTS);
}


/**
 * ------------------------------------------------------------------------------
 * Field Render Callbacks
 *
 * Add a render callback for each field / add_settings_field() call.
 * The Settings API wraps the section in a settings table.
 */
function devoted_render_theme_settings_show_private_in_nav()
{
    $settings = devoted_get_theme_settings();
?>
    <label>
        <input type="checkbox" name="<?php echo esc_attr(DEVOTED_THEME_SETTINGS_OPTION); ?>[show_private_in_nav]" value="1" <?php checked($settings['show_private_in_nav']); ?> />
        <?php esc_html_e('Enabled', 'devoted'); ?>
    </label>
<?php
}

function devoted_render_theme_settings_show_private_label()
{
    $settings = devoted_get_theme_settings();
?>
    <label>
        <input type="checkbox" name="<?php echo esc_attr(DEVOTED_THEME_SETTINGS_OPTION); ?>[show_private_label]" value="1" <?php checked($settings['show_private_label']); ?> />
        <?php esc_html_e('Enabled', 'devoted'); ?>
    </label>
<?php
}


/**
 * ------------------------------------------------------------------------------
 * Add Settings Page
 *
 * Adds the page itself under Settings in the admin menu.
 */
function devoted_add_theme_settings_page()
{
    add_options_page(
        __('Theme Settings', 'devoted'),      // Page title.
        __('Theme Settings', 'devoted'),      // Menu label.
        'manage_options',                     // Capability required.
        DEVOTED_THEME_SETTINGS_PAGE_SLUG,
        'devoted_render_theme_settings_page'  // Render callback.
    );
}
add_action('admin_menu', 'devoted_add_theme_settings_page');

/**
 * ------------------------------------------------------------------------------
 * Render Settings Page
 *
 * The form wrapper around whatever add_settings_section()/
 * add_settings_field() registers above. Renders empty until this page has
 * fields. Submits to options.php, which handles the nonce, sanitization,
 * and redirect back to this page itself.
 */
function devoted_render_theme_settings_page()
{

    if (! current_user_can('manage_options')) {
        return;
    }
?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields(DEVOTED_THEME_SETTINGS_GROUP);
            do_settings_sections(DEVOTED_THEME_SETTINGS_PAGE_SLUG);
            submit_button();
            ?>
        </form>
    </div>
<?php
}
