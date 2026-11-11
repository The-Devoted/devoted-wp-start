<?php
/**
 * Tasks that prepare and kickoff the theme.
 * Handlers for settings set by the Theme Settings options page.
 *      - see inc/options-pages/theme-settings
 */

/**
 * Suppresses the "Private: " prefix WordPress adds to a private page's
 * title on the frontend, unless the "Show 'Private:' labels on Private
 * pages?" Theme Settings checkbox is enabled.
 */
add_filter( 'private_title_format', function ( $format ) {

	$settings = devoted_get_theme_settings();

	return $settings['show_private_label'] ? $format : '%s';
} );
