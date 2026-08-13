<?php
/**
 * Server-side render callback for the Page Header block.
 *
 * `variant` is set once per template (see readme.md for the mapping) and
 * determines how the title/label is derived — it replaces the old pattern's
 * is_archive()/is_search()/is_404() runtime branching, since each template
 * already implies which variant applies.
 *
 * @param array $attributes Block attributes.
 */

$variant      = $attributes['variant'] ?? 'page';
$label        = $attributes['label'] ?? '';
$show_excerpt = $attributes['showExcerpt'] ?? true;
$separator    = $attributes['separator'] ?? '>';

$page_header_label        = '';
$page_header_is_title     = true;
$page_header_show_excerpt = false;

switch ( $variant ) {

	case 'page':
		$page_header_label        = get_the_title();
		$page_header_is_title     = true;
		$page_header_show_excerpt = $show_excerpt;
		break;

	case 'post':
		// single.html is also WordPress's fallback template for any custom
		// post type that doesn't have its own single-{post_type}.html — so
		// this can't assume the current post is actually a 'post'.
		$post_type = get_post_type();
		if ( $post_type && 'post' !== $post_type ) {
			$post_type_obj      = get_post_type_object( $post_type );
			$page_header_label  = $post_type_obj ? $post_type_obj->labels->name : '';
		} else {
			$page_header_label = $label ? $label : __( 'News', 'devoted' );
		}
		$page_header_is_title     = false;
		$page_header_show_excerpt = false;
		break;

	case 'cpt':
		$post_type_obj             = get_post_type_object( get_post_type() );
		$page_header_label         = $label ? $label : ( $post_type_obj ? $post_type_obj->labels->name : '' );
		$page_header_is_title      = false;
		$page_header_show_excerpt  = false;
		break;

	case 'archive':
		$page_header_label        = get_the_archive_title();
		$page_header_is_title     = true;
		$page_header_show_excerpt = false;
		break;

	case 'search':
		$page_header_label        = $label ? $label : __( 'Search Results', 'devoted' );
		$page_header_is_title     = true;
		$page_header_show_excerpt = false;
		break;

	case '404':
		$page_header_label        = $label ? $label : __( 'Page Not Found', 'devoted' );
		$page_header_is_title     = true;
		$page_header_show_excerpt = false;
		break;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'dvo--page-header dvo--page-header--' . esc_attr( $variant ) . ' wp-block-group has-global-padding is-layout-constrained wp-block-group-is-layout-constrained',
) );

$breadcrumbs_markup = do_blocks(
	'<!-- wp:breadcrumbs ' . wp_json_encode( array(
		'separator'       => $separator,
		'showCurrentItem' => true,
	) ) . ' /-->'
);

?>
<div <?php echo $wrapper_attributes; ?>>
	<div class="wp-block-group alignwide is-layout-flow wp-block-group-is-layout-flow">
		<?php echo $breadcrumbs_markup; ?>
		<div class="dvo--page-header__title">
			<?php if ( $page_header_is_title ) : ?>
				<h1 class="wp-block-heading"><?php echo esc_html( $page_header_label ); ?></h1>
			<?php else : ?>
				<p class="has-font-secondary-font-family has-heading-one-font-size"><?php echo esc_html( $page_header_label ); ?></p>
			<?php endif; ?>
			<?php if ( $page_header_show_excerpt && has_excerpt() ) : ?>
				<?php echo do_blocks( '<!-- wp:post-excerpt /-->' ); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
