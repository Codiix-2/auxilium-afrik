<?php

namespace Cariera_Packages\Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class representing the Job View Package product type.
 */
class Cariera_Package extends \WC_Product_Simple {

	/**
	 * Constructor
	 *
	 * @param mixed $product The product ID or object.
	 */
	public function __construct( $product = 0 ) {
		parent::__construct( $product );
	}

	/**
	 * Get internal type.
	 *
	 * @since   0.9.0
	 * @version 0.9.13
	 */
	public function get_type() {
		return 'cariera_package';
	}

	/**
	 * Checks the product type.
	 *
	 * @since   0.9.0
	 * @version 1.0.0
	 *
	 * @param mixed $type
	 */
	public function is_type( $type ) {
		return ( 'cariera_package' === $type || ( is_array( $type ) && in_array( 'cariera_package', $type, true ) ) ) ? true : parent::is_type( $type );
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
	 * Add to Cart URL
	 *
	 * @since 0.9.0
	 *
	 * @return mixed|void
	 */
	public function add_to_cart_url() {
		$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->get_id() ) ) : get_permalink( $this->get_id() );

		return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
	}

	/**
	 * Add to Cart Text
	 *
	 * @since 0.9.0
	 *
	 * @return mixed|void
	 */
	public function add_to_cart_text() {
		$text = $this->is_purchasable() && $this->is_in_stock() ? esc_html__( 'Add to cart', 'cariera-packages' ) : esc_html__( 'Read More', 'cariera-packages' );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Check if the product is purchasable.
	 *
	 * @since 0.9.0
	 *
	 * @return bool True if purchasable, otherwise false.
	 */
	public function is_purchasable() {
		/**
		 * Set to false if a package has been purchased and has "Disable repeat purchase?" enabled.
		 *
		 * @since 0.9.2
		 *
		 * @param bool $cariera_package_is_purchasable
		 * @param int  $this
		 */
		return apply_filters( 'cariera_package_is_purchasable', true, $this );
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

	/**
	 * Get the package type
	 *
	 * @since   0.9.0
	 * @version 0.9.13
	 *
	 * @return bool
	 */
	public function package_type() {
		return $this->get_meta( '_package_type' );
	}

	/**
	 * Check if Use Short Description Enabled
	 *
	 * @since 0.9.0
	 *
	 * @return bool
	 */
	public function use_short_description() {
		if ( $this->get_meta( '_package_use_sd' ) === 'yes' ) {
			return true;
		}

		return false;
	}

	/**
	 * Is this a subscription?
	 *
	 * @since 0.9.0
	 *
	 * @return bool
	 */
	public function is_subscription() {
		return false;
	}
}
