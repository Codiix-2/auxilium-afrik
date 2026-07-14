<?php

namespace Cariera_Packages\Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_View_Subscription extends \WC_Product_Subscription {

	/**
	 * Constructor for the subscription product type.
	 *
	 * @param mixed $product The product ID or object.
	 */
	public function __construct( $product = 0 ) {
		parent::__construct( $product );
	}

	/**
	 * Get the product type.
	 *
	 * @since 0.9.0
	 *
	 * @return string The product type identifier.
	 */
	public function get_type() {
		return 'cariera_package_subscription';
	}

	/**
	 * Check if the product is of a given type.
	 *
	 * @since 0.9.0
	 *
	 * @param string|array $type The product type(s) to check.
	 * @return bool True if the product is of the given type, otherwise false.
	 */
	public function is_type( $type ) {
		return ( 'cariera_package_subscription' == $type || ( is_array( $type ) && in_array( 'cariera_package_subscription', $type, true ) ) ) ? true : parent::is_type( $type );
	}

	/**
	 * Get the "Add to Cart" URL for the product.
	 *
	 * @since 0.9.0
	 *
	 * @return string The Add to Cart URL.
	 */
	public function add_to_cart_url() {
		$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

		return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
	}

	/**
	 * Check if the product is sold individually.
	 *
	 * @since 0.9.0
	 *
	 * @return bool True if sold individually, otherwise false.
	 */
	public function is_sold_individually() {
		return apply_filters( 'cariera_packages_' . $this->get_type() . '_is_sold_individually', true );
	}

	/**
	 * Check if the product is purchasable.
	 *
	 * @since 0.9.0
	 *
	 * @return bool True if purchasable, otherwise false.
	 */
	public function is_purchasable() {
		return true;
	}

	/**
	 * Check if the product is virtual.
	 *
	 * @since 0.9.0
	 *
	 * @return bool True if virtual, otherwise false.
	 */
	public function is_virtual() {
		return true;
	}
}
