<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// WC Subscription Support.
		add_filter( 'woocommerce_subscription_product_types', [ $this, 'woocommerce_subscription_product_types' ] );

		// Save Product Meta.
		add_action( 'woocommerce_process_product_meta_cariera_package', [ $this, 'save_package_data' ] );
		add_action( 'woocommerce_process_product_meta_cariera_package_subscription', [ $this, 'save_package_data' ] );

		// Output template to product.
		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'product_data' ] );
	}

	/**
	 * Add subscription product types to WooCommerce subscriptions.
	 *
	 * @since 0.9.0
	 *
	 * @param array $types Existing subscription product types.
	 * @return array Modified subscription product types.
	 */
	public function woocommerce_subscription_product_types( $types ) {
		$types[] = 'cariera_package_subscription';
		return $types;
	}

	/**
	 * Display custom fields in the product options tab.
	 *
	 * @since   0.9.0
	 * @version 0.9.8
	 */
	public function product_data() {
		get_job_manager_template_part( 'admin/packages/wc-product-cariera', 'package', 'cariera-packages', CARIERA_PACKAGES_PATH . '/templates/' );
	}

	/**
	 * Save custom product fields.
	 *
	 * @since   0.9.0
	 * @version 0.9.17
	 *
	 * @param int $post_id The ID of the product being saved.
	 */
	public function save_package_data( $post_id ) {
		// Save meta.
		$meta_to_save = [
			// General package meta.
			'_package_type'               => '',
			'_cariera_disable_repurchase' => 'yesno',
			'_package_use_sd'             => 'yesno',

			// Job submission package meta.
			'_job_listing_limit'          => 'int',
			'_job_listing_duration'       => '',
			'_job_listing_featured'       => 'yesno',

			// Resume submission package meta.
			'_resume_limit'               => 'int',
			'_resume_duration'            => '',
			'_resume_featured'            => 'yesno',

			// View package meta.
			'_view_job_limit'             => 'int',
			'_view_company_limit'         => 'int',
			'_view_resume_limit'          => 'int',
			'_view_event_limit'           => 'int',

			// Promotion package meta.
			'_job_promotion_duration'     => 'int',
			'_company_promotion_duration' => 'int',
			'_resume_promotion_duration'  => 'int',
			'_event_promotion_duration'   => 'int',
		];

		foreach ( $meta_to_save as $meta_key => $sanitize ) {
			$value = ! empty( $_POST[ $meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) ) : ''; // phpcs:ignore
			switch ( $sanitize ) {
				case 'int':
					$value = absint( $value );
					break;
				case 'float':
					$value = floatval( $value );
					break;
				case 'yesno':
					$value = ( 'yes' === $value ) ? 'yes' : 'no';
					break;
				case 'array':
					$value = array_map( 'sanitize_text_field', (array) $value );
					break;
				default:
					$value = sanitize_text_field( $value );
			}

			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
