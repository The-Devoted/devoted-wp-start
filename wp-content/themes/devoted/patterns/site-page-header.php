<?php
/**
 * Title: Page Header
 * Slug: devoted/site-page-header
 * Categories: devoted-site
 */

    // Get information about this page needed to render the title.
    $page_header_displays = get_field('page_header_displays');
    $post_type_obj = get_post_type_object( get_post_type() );

    // Check for custom labels & settings via ACF Options Pages.
    $theme_settings_posts_label = get_field('posts_section_label', 'option');
    $theme_settings_search_results_label = get_field('search_results_label', 'option');
    $theme_settings_404_page_label = get_field('404_page_label', 'option');
    $theme_settings_show_excerpt = get_field('show_excerpts_in_page_headers', 'option');

    // Init the label & display type
    $page_header_label = "";
    $page_header_is_title = true;
    $page_header_show_excerpt = false;

    if ( is_archive() ) {
        // If this is an archive, just print the archive title.
        $page_header_label = get_the_archive_title();
        $page_header_is_title = true;
        $page_header_show_excerpt = false;
    
    } elseif ( is_search() ) {
        // If this is a search query, print a Search Results title.
        $page_header_label = ($theme_settings_search_results_label) ? $theme_settings_search_results_label : 'Search Results';
        $page_header_is_title = true;
        $page_header_show_excerpt = false;

    } elseif ( is_404() ) {
        // If this is a 404 page, print a 404 title.
        $page_header_label = ($theme_settings_404_page_label) ? $theme_settings_404_page_label : 'Page Not Found';
        $page_header_is_title = true;
        $page_header_show_excerpt = false;

    } else {
        // If this is not a search or archive, determine the appropriate title.

        if ( $page_header_displays != 'title' && ($page_header_displays) ) {
            // The editor has elected to manually change what's in the page header via ACF.

            if ( $page_header_displays == 'section' ) {
                // If the editor elects the Section title option, 
                // get the pages's top level ancestor, and print that page's title.
                $post_ID = get_the_ID();
                $ancestors = devoted_get_ancestor_ids($post_ID);
                $top_level_parent = end($ancestors);

                $page_header_label = get_the_title($top_level_parent);
                $page_header_is_title = true;
                $page_header_show_excerpt = false;
            
            // If not title or section, the editor has entered a custom title.
            } else {
                $page_header_label = $page_header_displays;
                $page_header_is_title = true;
                $page_header_show_excerpt = $theme_settings_show_excerpt;
            }
        } else {
            // No manual changes to the title have been made.
            if ( $post_type_obj ) {
                // Posts display a 'news' style title in the page header.
                // The entry title below is the H1, NOT the page header label.
                if ( $post_type_obj->name == 'post' ) {
                    $page_header_label = ($theme_settings_posts_label) ? $theme_settings_posts_label : "News";
                    $page_header_is_title = false;
                    $page_header_show_excerpt = false;

                // Pages display their page title in the page header.
                // In this case, the item in the page header IS the H1.
                } elseif ( $post_type_obj->name == 'page' ) {
                    $page_header_label = get_the_title();
                    $page_header_is_title = true;
                    $page_header_show_excerpt = $theme_settings_show_excerpt;
                
                // If this is neither post nor page, assume CPT.
                // Print the CPT's plural name in the page header.
                // DON'T make it the H1, since it's not the page's title.
                // This will rely on the CPT's template to display the entry title as the H1.
                } else {
                    $page_header_label = $post_type_obj->labels->name;
                    $page_header_is_title = false;
                    $page_header_show_excerpt = false;
                }
            }
        }
    }

?>

<!-- wp:group {"backgroundColor":"primary-lighter","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-primary-lighter-background-color has-background dvo-page-header">
    <!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
    <div class="wp-block-group alignwide">
        
        <!-- wp:breadcrumbs {"separator":"\u003e","showCurrentItem":true} /-->

        <!-- wp:group {"layout":{"type":"constrained","justifyContent":"left"}} -->
        <div class="wp-block-group dvo-page-header__title">
            <?php // Print the page header label, with appropriate markup. ?>
            <?php if ( $page_header_is_title ): ?>
                <!-- wp:heading {"level":1} -->
                <h1 class="wp-block-heading"><?php echo esc_html( $page_header_label ); ?></h1>
                <!-- /wp:heading -->
            <?php else: ?>
                <!-- wp:paragraph {"fontSize":"heading-one","fontFamily":"font-secondary"} -->
                <p class="has-font-secondary-font-family has-heading-one-font-size"><?php echo esc_html( $page_header_label ); ?></p>
                <!-- /wp:paragraph -->
            <?php endif; ?>
            <?php // Print the excerpt, if elected AND it actually exists. ?>
            <?php if ( $page_header_show_excerpt && has_excerpt() ) : ?>
                <!-- wp:post-excerpt /-->
            <?php endif; ?>
        </div>
        <!-- /wp:group -->

    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->
