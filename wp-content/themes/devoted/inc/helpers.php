<?php
/**
 * Get Ancestor IDs
 *
 * Given an entry ID, return an array of the IDs of the entry's ancestors in
 * ascending order (closest ancestor to the entry first, furthest/top-level
 * entry last in the array).
 */
function devoted_get_ancestor_ids( $post_ID ) {

	$ancestors = [];
	$parent    = wp_get_post_parent_id( $post_ID );

	// If a page has no ancestor, wp_get_post_parent_id returns 0.
	while ( $parent != 0 ) {
		$ancestors[] = $parent;
		$parent      = wp_get_post_parent_id( $parent );
	}

	return $ancestors;
}
