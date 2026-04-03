<?php
/**
 * Title: Secondary Navigation
 * Slug: devoted/secondary-nav
 * Inserter: false
 */
?>

<!-- wp:group {"className":"dvo--secondary-nav", "tagName":"nav"} -->
<nav class="wp-block-group dvo--secondary-nav" role="navigation" aria-label="secondary">
    <?php

    // Get the Post ID for the entry being displayed.
    $post_ID = get_the_ID();

    // Fetch an array of this entry's ancestors.
    $ancestors = devoted_get_ancestor_ids($post_ID);

    // If this is a top level page (no parents), print the nav for this section
    // (not the top level). This is a relatively rare use case, since most
    // top-level pages use a landing template instead of the sidebar/single-page
    // template, where this pattern is primarily used.
    if (count($ancestors) == 0) {
        $nav_to_print_ID = $post_ID;
    }

    // For any page at L2 or deeper, just print the whole section.
    else {
        $nav_to_print_ID = end($ancestors);
    }

    // Print a link to the top level of the section, then print the children.
    echo '<!-- wp:paragraph -->';
    echo '<p><a href="' . get_page_link($nav_to_print_ID) . '" class="dvo--secondary-nav__top-level-link">' . get_the_title($nav_to_print_ID) . '</a></p>';
    echo '<!-- /wp:paragraph -->';
    echo '<!-- wp:page-list {"parentPageID":' . $nav_to_print_ID . '} /-->';


    ?>
</nav>
<!-- /wp:group -->
