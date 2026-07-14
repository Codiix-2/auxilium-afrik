<?php

namespace Cariera_Packages\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cart {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add to cart in single product.
		add_action( 'woocommerce_cariera_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );

		// Output listing name in cart.
		add_filter( 'woocommerce_get_item_data', [ $this, 'get_item_data' ], 10, 2 );
	}

	/**
	 * Check if cart contains standard or subscription visibility package.
	 *
	 * @since 0.9.0
	 *
	 * @return bool
	 */
	public function cart_contains_package() {
		global $woocommerce;

		if ( ! empty( $woocommerce->cart->cart_contents ) ) {
			foreach ( $woocommerce->cart->cart_contents as $cart_item ) {
				$product = $cart_item['data'];
				if ( $product instanceof \WC_Product && $product->is_type( 'cariera_package' ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Output listing name in cart.
	 *
	 * @since   0.9.12
	 * @version 0.9.20
	 *
	 * @param array $data
	 * @param array $cart_item
	 */
	public function get_item_data( $data, $cart_item ) {
		// Mapping of cart item keys to labels.
		$items = apply_filters(
			'cariera_packages_cart_item_data_fields',
			[
				'job_id'     => esc_html__( 'Job Listing', 'cariera-packages' ),
				'resume_id'  => esc_html__( 'Resume', 'cariera-packages' ),
				'listing_id' => esc_html__( 'Listing', 'cariera-packages' ),
			],
			$cart_item
		);

		foreach ( $items as $key => $label ) {
			if ( isset( $cart_item[ $key ] ) && $cart_item[ $key ] ) {
				$post = get_post( absint( $cart_item[ $key ] ) );

				if ( $post ) {
					$data[] = [
						'name'  => $label,
						'value' => $post->post_title,
					];
				}
			}
		}

		return $data;
	}

	/**
	 * Process/Handle Form Submission.
	 *
	 * @since   0.9.0
	 * @version 0.9.9
	 *
	 * @param int   $package_id
	 * @param array $meta
	 */
	public static function process_form( $package_id, $meta = [] ) {
		// Add package to the cart.
		\WC()->cart->add_to_cart( $package_id, 1, '', '', $meta );

		// Enable/Add "added to cart" message.
		wc_add_to_cart_message( $package_id );

		do_action( 'cariera_packages_wc_process_form_before_redirect', $package_id, $meta );

		// Redirect to checkout page.
		wp_safe_redirect( get_permalink( wc_get_page_id( 'checkout' ) ) );
		exit;
	}
}
