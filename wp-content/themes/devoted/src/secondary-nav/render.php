<?php
/**
 * Server-side render callback for the Secondary Navigation block.
 *
 * Shows the current page's section navigation: a link to the section's
 * top-level page (always the first <li> in the same <ul> as everything
 * else — unlike the old core/page-list-based pattern, which couldn't
 * include the root link inside its own generated list), then either:
 *
 * - `trail` (default): a single-link path through each intermediate
 *   ancestor, then the current page's siblings, current page highlighted.
 * - `full`: the entire nested tree under the section root, current page
 *   highlighted wherever it falls.
 *
 * Pages toggled via the "Hide from secondary navigation" Document Settings
 * panel (devoted_hide_from_secondary_nav post meta) are excluded from
 * sibling/tree listings, along with their descendants. The root link and
 * the trail's ancestor path are never hidden, since they exist to let a
 * visitor navigate back up regardless of any individual page's listing
 * preference.
 *
 * `mode` comes from exactly two places, no others: the `mode` attribute
 * (set once per template; defaults to `trail` if absent) and the page
 * currently being viewed's own "Secondary nav style" Document Settings
 * override (devoted_secondary_nav_mode post meta), which takes precedence
 * when set. There is no fallback to a section's root page or any other
 * ancestor — a page's nav style is either declared by the template or set
 * explicitly on that exact page.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Unused — fully dynamic block.
 * @param WP_Block $block      Block instance, used for postId context.
 */

$post_id = $block->context['postId'] ?? get_the_ID();

if ( ! $post_id || 'page' !== get_post_type( $post_id ) ) {
	return '';
}

$ancestors = devoted_get_ancestor_ids( $post_id );
$root_id   = $ancestors ? end( $ancestors ) : $post_id;
$root_page = get_post( $root_id );

if ( ! $root_page ) {
	return '';
}

$mode_override = get_post_meta( $post_id, 'devoted_secondary_nav_mode', true );
$mode          = in_array( $mode_override, array( 'trail', 'full' ), true )
	? $mode_override
	: ( $attributes['mode'] ?? 'trail' );

$all_pages = get_pages( array(
	'child_of'    => $root_id,
	'sort_column' => 'menu_order, post_title',
	'sort_order'  => 'ASC',
) );

$children_by_parent = array();
foreach ( $all_pages as $page ) {
	$children_by_parent[ $page->post_parent ][] = $page;
}

$is_hidden = function ( $page_id ) {
	return (bool) get_post_meta( $page_id, 'devoted_hide_from_secondary_nav', true );
};

// Builds an opening <li> (with link) for a nav item; caller is responsible
// for appending any nested markup and the closing </li>.
$item_open = function ( $page_id, $title, $url, $extra_class, $level ) use ( $post_id ) {
	$is_current = ( (int) $page_id === (int) $post_id );

	$classes = array(
		'wp-block-pages-list__item',
		'dvo--secondary-nav__' . $extra_class,
		'dvo--secondary-nav__level-' . $level,
	);
	if ( $is_current ) {
		$classes[] = 'current-menu-item';
	}

	return sprintf(
		'<li class="%1$s"><a href="%2$s"%3$s>%4$s</a>',
		esc_attr( implode( ' ', $classes ) ),
		esc_url( $url ),
		$is_current ? ' aria-current="page"' : '',
		esc_html( $title )
	);
};

// Renders a flat <ul> of $parent_id's visible children, current page
// highlighted. Used for the trail's final (sibling) level and reused by
// the full tree renderer at every level.
$render_children_list = function ( $parent_id, $extra_class, $level ) use ( &$children_by_parent, &$is_hidden, &$item_open ) {
	$children = $children_by_parent[ $parent_id ] ?? array();
	if ( ! $children ) {
		return '';
	}

	$markup = '<ul>';
	foreach ( $children as $child ) {
		if ( $is_hidden( $child->ID ) ) {
			continue;
		}
		$markup .= $item_open( $child->ID, get_the_title( $child ), get_permalink( $child ), $extra_class, $level ) . '</li>';
	}
	return $markup . '</ul>';
};

// Recursively renders the entire nested tree under $parent_id.
$render_tree = function ( $parent_id, $level ) use ( &$render_tree, &$children_by_parent, &$is_hidden, &$item_open ) {
	$children = $children_by_parent[ $parent_id ] ?? array();
	if ( ! $children ) {
		return '';
	}

	$markup = '<ul>';
	foreach ( $children as $child ) {
		if ( $is_hidden( $child->ID ) ) {
			continue;
		}
		$markup .= $item_open( $child->ID, get_the_title( $child ), get_permalink( $child ), 'tree-item', $level );
		$markup .= $render_tree( $child->ID, $level + 1 );
		$markup .= '</li>';
	}
	return $markup . '</ul>';
};

// Recursively renders the trail: one <li> per remaining ancestor, then the
// sibling list at the base case. Recursion (rather than manual tag
// counting) keeps every opened <ul>/<li> reliably matched to its closer.
$render_trail = function ( $remaining_ancestor_ids, $siblings_parent_id, $level ) use ( &$render_trail, &$render_children_list, &$item_open ) {
	if ( ! $remaining_ancestor_ids ) {
		return $render_children_list( $siblings_parent_id, 'sibling-item', $level );
	}

	$ancestor_id = array_shift( $remaining_ancestor_ids );
	$ancestor    = get_post( $ancestor_id );
	if ( ! $ancestor ) {
		return $render_trail( $remaining_ancestor_ids, $siblings_parent_id, $level );
	}

	return '<ul>'
		. $item_open( $ancestor->ID, get_the_title( $ancestor ), get_permalink( $ancestor ), 'trail-item', $level )
		. $render_trail( $remaining_ancestor_ids, $siblings_parent_id, $level + 1 )
		. '</li></ul>';
};

if ( 'full' === $mode ) {
	$inner_markup = $render_tree( $root_id, 1 );
} else {
	// $ancestors is closest-parent-first, root-last. The intermediate
	// ancestors are everything strictly between the root (already printed
	// below) and the current page's immediate parent (whose children — the
	// current page's siblings — are the trail's final level).
	$intermediate_ancestor_ids = count( $ancestors ) > 1
		? array_slice( array_reverse( $ancestors ), 1, -1 )
		: array();

	// If the current page has no parent, it IS the root, and there's no
	// separate "siblings" level — show the root's own children instead
	// (matches the old pattern's "print the nav for this section" case).
	$siblings_parent_id = $ancestors ? $ancestors[0] : $root_id;

	$inner_markup = $render_trail( $intermediate_ancestor_ids, $siblings_parent_id, 1 );
}

$root_li = $item_open( $root_page->ID, get_the_title( $root_page ), get_permalink( $root_page ), 'root-item', 0 )
	. $inner_markup
	. '</li>';

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'dvo--secondary-nav',
) );

?>
<nav <?php echo $wrapper_attributes; ?> aria-label="secondary">
	<ul><?php echo $root_li; ?></ul>
</nav>
