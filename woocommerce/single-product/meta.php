<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>

<?php // # BEGIN Everything ?>

<?php if ( Everything::to( 'woocommerce/product/social_buttons/visible' ) && count( Everything::to_( 'woocommerce/product/social_buttons/items' )->values() ) > 0 ) : ?>

	<hr />

	<div class="product_social_buttons">

		<?php

			$items = Everything::to_( 'woocommerce/product/social_buttons/items' );

			foreach ( array_keys( $items->options ) as $item ) :
				$media[ 'media_'.$item ] = $items->value( $item );
			endforeach;

			Everything::shortcodeOutput( 'social_buttons', array( 'size' => 'small' ) + $media );

		?>

	</div>

<?php endif; ?>

<?php if ( Everything::to( 'woocommerce/product/meta/visible' ) && count( Everything::to_( 'woocommerce/product/meta/items' )->values() ) > 0 ) : ?>

	<hr />

	<p class="product_meta">

		<?php do_action( 'woocommerce_product_meta_start' ); ?>

		<?php

			foreach ( Everything::to( 'woocommerce/product/meta/items' ) as $item ) :
				switch ( $item ) :

					case 'sku' :
						if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) :
							?>
								<span class="sku_wrapper"><?php _e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : __( 'N/A', 'woocommerce' ); ?></span></span>
							<?php
						endif;
						break;

					case 'categories' :
						echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' );
						break;

					case 'tags' :
						echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' );
						break;

					case 'brands' :
						$GLOBALS[ 'WC_Brands' ]->show_brand();
						break;

				endswitch;
			endforeach;

		?>

		<?php do_action( 'woocommerce_product_meta_end' ); ?>

	</p>

<?php endif; ?>

<?php // # END Everything ?>