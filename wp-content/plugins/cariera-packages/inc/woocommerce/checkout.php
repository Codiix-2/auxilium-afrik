<?php

namespace Cariera_Packages\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Checkout {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Guest checkout.
		add_filter( 'option_woocommerce_enable_guest_checkout', [ $this, 'enable_guest_checkout' ] );
		add_filter( 'option_woocommerce_enable_signup_and_login_from_checkout', [ $this, 'enable_signup_and_login_from_checkout' ] );

		// Add listing title to cart.
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'checkout_create_order_line_item' ], 10, 4 );
	}

	/**
	 * Disable Guest Checkout.
	 *
	 * @since   0.9.0
	 * @version 0.9.16
	 *
	 * @param string $value
	 */
	public function enable_guest_checkout( $value ) {
		if ( ( new Cart() )->cart_contains_package() ) {
			return 'no';
		}

		return $value;
	}

	/**
	 * Allow Signup and Login on Checkout page.
	 *
	 * @since   0.9.0
	 * @version 0.9.16
	 *
	 * @param string $value
	 */
	public function enable_signup_and_login_from_checkout( $value ) {
		remove_filter( 'option_woocommerce_enable_guest_checkout', [ $this, 'enable_guest_checkout' ] );
		$woocommerce_enable_guest_checkout = get_option( 'woocommerce_enable_guest_checkout' );
		add_filter( 'option_woocommerce_enable_guest_checkout', [ $this, 'enable_guest_checkout' ] );

		if ( 'yes' === $woocommerce_enable_guest_checkout && ( new Cart() )->cart_contains_package() ) {
			return 'yes';
		}

		return $value;
	}

	/**
	 * Set the order line item's meta data prior to being saved (WC >= 3.0.0).
	 *
	 * @since   0.9.13
	 * @version 1.0.0
	 *
	 * @param WC_Order_Item_Product $order_item
	 * @param string                $cart_item_key  The hash used to identify the item in the cart.
	 * @param array                 $cart_item_data The cart item's data.
	 * @param WC_Order              $order          The order or subscription object to which the line item relates.
	 */
	public function checkout_create_order_line_item( $order_item, $cart_item_key, $cart_item_data, $order ) {
		// Map cart keys to label and meta key.
		$items = [
			'job_id'     => [
				'label'    => __( 'Job Listing', 'cariera-packages' ),
				'meta_key' => '_job_id', // phpcs:ignore
			],
			'resume_id'  => [
				'label'    => __( 'Resume', 'cariera-packages' ),
				'meta_key' => '_resume_id', // phpcs:ignore
			],
			'listing_id' => [
				'label'    => __( 'Listing', 'cariera-packages' ),
				'meta_key' => '_listing_id', // phpcs:ignore
			],
		];

		foreach ( $items as $key => $info ) {
			if ( isset( $cart_item_data[ $key ] ) && $cart_item_data[ $key ] ) {
				$post = get_post( absint( $cart_item_data[ $key ] ) );

				if ( $post ) {
					// Add human-readable label to order item.
					$order_item->update_meta_data( $info['label'], $post->post_title );

					// Add raw post ID to order item meta.
					$order_item->update_meta_data( $info['meta_key'], $cart_item_data[ $key ] );
				}
			}
		}
	}
}
