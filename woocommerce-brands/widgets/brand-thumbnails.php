<?php
/**
 * @package    WooCommerce/Templates
 * @subpackage Everything
 * @since      1.0
 */

// -----------------------------------------------------------------------------

if (!defined('ABSPATH')) {
	exit;
}

?>

<?php if ($columns == 'scroller'): ?>

	<?php $size = apply_filters('woocommerce_brand_thumbnail_size', 'logo'); ?>
	<div class="movable-container content-size-logo" data-movable-container-force-touch-device="true">
		<?php foreach ($brands as $brand): ?>
			<a href="<?php echo get_term_link($brand->slug, 'product_brand'); ?>" <?php Everything::imageAttrs('a', array('border' => false, 'hover' => 'grayscale')); ?>><?php
				if ($thumbnail_id = get_woocommerce_term_meta($brand->term_id, 'thumbnail_id', true)) {
					echo wp_get_attachment_image($thumbnail_id, $size);
				}
			?></a>
		<?php endforeach; ?>
	</div>

<?php else: ?>

	<?php $size = apply_filters('woocommerce_brand_thumbnail_size', Everything::getImageSize($columns)); ?>
	<div class="columns">
		<ul>
			<?php foreach ($brands as $brand): ?>
				<li class="col-1-<?php echo $columns; ?>">
					<figure class="full-width">
						<a href="<?php echo get_term_link($brand->slug, 'product_brand'); ?>" <?php Everything::imageAttrs('a', array('border' => false, 'hover' => '')); ?>>
							<?php
								if ($thumbnail_id = get_woocommerce_term_meta($brand->term_id, 'thumbnail_id', true)) {
									echo wp_get_attachment_image($thumbnail_id, $size);
								} else {
									echo wc_placeholder_img();
								}
							?>
						</a>
					</figure>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

<?php endif; ?>