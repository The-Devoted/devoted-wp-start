<?php
/**
 * Server-side render callback for a dynamic block.
 *
 * Not wired up in this example — the block currently saves static markup via
 * save.tsx. To make the block dynamic instead: delete save.tsx (return `null`
 * from it, or remove it and its import in index.ts), then reference this file
 * from block.json with `"render": "file:./render.php"`.
 *
 * Available variables: $attributes (array), $content (string, the saved
 * inner markup), $block (WP_Block instance).
 */

$message = $attributes['message'] ?? '';
$wrapper_attributes = get_block_wrapper_attributes();
?>
<p <?php echo $wrapper_attributes; ?>>
	<?php echo esc_html( $message ); ?>
</p>
