<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Packages {

	/**
	 * Initialize the package manager.
	 *
	 * @since 0.9.0
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register_package_types' ] );
		add_filter( 'product_type_selector', [ __CLASS__, 'add_package_types_to_selector' ] );
		add_filter( 'woocommerce_product_class', [ __CLASS__, 'map_product_classes' ], 10, 2 );
	}

	/**
	 * Register custom WooCommerce product types.
	 *
	 * @since 0.9.0
	 */
	public static function register_package_types() {
		// Packages.
		new \Cariera_Packages\Packages\Cariera_Package();

		// TODO: WC Subscriptions support.
		if ( class_exists( '\WC_Subscriptions' ) ) {
			// new \Cariera_Packages\Packages\Cariera_Package_Subscription();
		}

		// Add new packages here in the future.
		do_action( 'cariera_packages_register_types' );
	}

	/**
	 * Add custom product types to the WooCommerce product selector.
	 *
	 * @since 0.9.0
	 *
	 * @param array $types Existing product types.
	 * @return array Modified product types.
	 */
	public static function add_package_types_to_selector( $types ) {
		$types['cariera_package'] = esc_html__( 'Cariera Package', 'cariera-packages' );

		// TODO: WC Subscriptions support.
		if ( class_exists( '\WC_Subscriptions' ) ) {
			// $types['cariera_package_subscription'] = esc_html__( 'Cariera Package Subscription', 'cariera-packages' );
		}

		// Add new package types dynamically.
		return apply_filters( 'cariera_packages_product_types', $types );
	}

	/**
	 * Map product classes to product types.
	 *
	 * @since 0.9.0
	 *
	 * @param string $classname Default product class.
	 * @param string $product_type Product type being checked.
	 */
	public static function map_product_classes( $classname, $product_type ) {

		switch ( $product_type ) {
			case 'cariera_package':
				$classname = '\Cariera_Packages\Packages\Cariera_Package';
				break;

			case 'cariera_package_subscription':
				if ( class_exists( '\WC_Subscriptions' ) ) {
					$classname = '\Cariera_Packages\Packages\Cariera_Package_Subscription';
				}
				break;
		}

		return apply_filters( 'cariera_packages_product_class', $classname, $product_type );
	}
}
