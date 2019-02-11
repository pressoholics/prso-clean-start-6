<?php
/**
 * Block Name:
 *
 * Description
 */

// create id attribute for specific styling
$id = 'block-' . $block['id'];
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $block['className'] ); ?>">

	<?php if( prso_is_gutenberg_editor_request() ): ?>

		<div class="panel">
			<label>Block Name:</label>
		</div>

	<?php endif; ?>

</div>
