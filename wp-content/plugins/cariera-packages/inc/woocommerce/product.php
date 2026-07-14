<?php

namespace Cariera_Packages\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Disable repeat package purchase.
		add_filter( 'cariera_package_is_purchasable', [ $this, 'disable_repeat_purchase' ], 10, 2 );
		add_filter( 'woocommerce_is_purchasable', [ $this, 'disable_repeat_purchase' ], 10, 2 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'purchase_disabled_message' ], 31 );
	}

	/**
	 * Disables repeat purchase for packages.
	 *
	 * @since 0.9.2
	 *
	 * @param mixed $purchasable
	 * @param mixed $product
	 */
	public function disable_repeat_purchase( $purchasable, $product ) {
		if ( ! $product->is_type( 'cariera_package' ) ) {
			return $purchasable;
		}

		if ( $product->get_meta( '_cariera_disable_repurchase' ) !== 'yes' ) {
			return $purchasable;
		}

		if ( wc_customer_bought_product( wp_get_current_user()->user_email, get_current_user_id(), $product->get_id() ) ) {
			$purchasable = false;
		}

		return $purchasable;
	}

	/**
	 * "Purchase disabled" message if package has been purchased.
	 *
	 * @since 0.9.2
	 */
	public function purchase_disabled_message() {
		global $product;

		if ( ! $product->is_type( 'cariera_package' ) ) {
			return false;
		}

		if ( $product->get_meta( '_cariera_disable_repurchase' ) !== 'yes' ) {
			return false;
		}

		if ( wc_customer_bought_product( wp_get_current_user()->user_email, get_current_user_id(), $product->get_id() ) ) {
			printf(
				'<div class="woocommerce"><div class="woocommerce-info wc-nonpurchasable-message">%s</div></div>',
				esc_html__( 'You\'ve already purchased this product! It can only be purchased once.', 'cariera-packages' )
			);
		}
	}
}
