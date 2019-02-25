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

if ($thumbnail) {
	?>
		<figure class="full-width featured">
			<div <?php Everything::imageAttrs('div', array('border' => false)); ?>>
				<img src="<?php echo $thumbnail; ?>" />
			</div>
		</figure>
	<?php
}

echo wpautop(wptexturize(term_description()));