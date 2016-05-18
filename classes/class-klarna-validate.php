<?php
/**
 * Validates Klarna order locally by checking stock
 *
 * @link  http://www.woothemes.com/products/klarna/
 * @since 1.0.0
 *
 * @package WC_Gateway_Klarna
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class that validates Klarna orders.
 */
class WC_Gateway_Klarna_Order_Validate {

	/**
	 * Validate Klarna order
	 * Checks order items' stock status and confirms there's a chosen shipping method
	 *
	 * @since 1.0.0
	 */
	public static function validate_checkout_listener() {
		// Read the post body
		$post_body = file_get_contents( 'php://input' );

		// Convert post body into native object
		$data = json_decode( $post_body, true );

		// error_log( 'validate: ' . var_export( $data, true ) );

		$all_in_stock = true;
		$shipping_chosen = false;
		if ( get_option( 'woocommerce_manage_stock' ) == 'yes' ) {
			$cart_items = $data['cart']['items'];
			foreach ( $cart_items as $cart_item ) {
				if ( 'physical' == $cart_item['type'] ) {
					$cart_item_product = new WC_Product( $cart_item['reference'] );

					if ( ! $cart_item_product->has_enough_stock( $cart_item['quantity'] ) ) {
						$all_in_stock = false;
					}
				} elseif ( 'shipping_fee' == $cart_item['type'] ) {
					$shipping_chosen = true;
				}

			}
		}

		if ( $all_in_stock && $shipping_chosen ) {
			header( 'HTTP/1.0 200 OK' );
		} else {
			header( 'HTTP/1.0 303 See Other' );
			if ( ! $all_in_stock ) {
				header( 'Location: ' . WC()->cart->get_cart_url() );
			} else {
				header( 'Location: ' . WC()->cart->get_checkout_url() . '?no_shipping' );
			}
		}
	} // End function validate_checkout_listener

}

$wc_gateway_klarna_order_validate = new WC_Gateway_Klarna_Order_Validate();