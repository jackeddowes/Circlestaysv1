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

// -----------------------------------------------------------------------------

global $woocommerce;

$brand = get_term_by('slug', get_query_var('term'), 'product_brand');

?>

<?php if ($thumbnail_id = get_woocommerce_term_meta($brand->term_id, 'thumbnail_id', true)): ?>
	<figure class="alignleft">
		<div <?php Everything::imageAttrs('div', array('border' => false)) ?>>
			<?php echo wp_get_attachment_image($thumbnail_id, 'logo'); ?>
		</div>
	</figure>
<?php endif; ?>

<?php echo wpautop(wptexturize($brand->description)); ?>

<hr />