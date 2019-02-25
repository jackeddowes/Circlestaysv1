<?php
/**
 * Order Customer Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-customer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>
<section class="woocommerce-customer-details">

	<?php // # BEGIN Everything ?>

	<?php // if ( $show_shipping ) : ?>

		<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses columns">
			<ul>

				<li class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1 col-1-2">

					<?php // endif; ?>

					<h2 class="woocommerce-column__title"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h2>

					<p>
						<?php echo wp_kses_post( $order->get_formatted_billing_address( __( 'N/A', 'woocommerce' ) ) ); ?>
						<?php if ( $order->get_billing_phone() ) : ?>
							<br /><span class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_billing_phone() ); ?></span>
						<?php endif; ?>
						<?php if ( $order->get_billing_email() ) : ?>
							<br /><span class="woocommerce-customer-details--email"><?php echo esc_html( $order->get_billing_email() ); ?></span>
						<?php endif; ?>
					</p>

				</li><!-- /.col-1 -->

				<?php if ( $show_shipping ) : ?>

					<li class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2 col-1-2">

						<h2 class="woocommerce-column__title"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>

						<p>
							<?php echo wp_kses_post( $order->get_formatted_shipping_address( __( 'N/A', 'woocommerce' ) ) ); ?>
						</p>

					</li><!-- /.col-2 -->

				<?php endif; ?>

			</ul>
		</section><!-- /.col2-set -->

	<?php // endif; ?>

	<?php // # END Everything ?>
	
	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>

</section>
