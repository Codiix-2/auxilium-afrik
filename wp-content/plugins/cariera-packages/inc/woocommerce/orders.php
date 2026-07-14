<?php

namespace Cariera_Packages\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Orders {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Change Order Statuses.
		add_action( 'woocommerce_order_status_processing', [ $this, 'order_paid' ] );
		add_action( 'woocommerce_order_status_completed', [ $this, 'order_paid' ] );

		add_action( 'woocommerce_order_status_cancelled', [ $this, 'order_cancelled' ] );
		add_action( 'woocommerce_order_status_refunded', [ $this, 'order_cancelled' ] );
	}

	/**
	 * Order change status to process Cariera packages when the order is paid.
	 *
	 * @since   0.9.0
	 * @version 0.9.15
	 *
	 * @param int $order_id The order ID.
	 */
	public function order_paid( $order_id ) {
		// Get the order object.
		$order = wc_get_order( $order_id );

		// Ensure the order is valid and hasn't been processed before.
		if ( ! $order instanceof \WC_Order || get_post_meta( $order_id, 'cariera_packages_processed', true ) ) {
			return;
		}

		// Define handlers for each package type.
		$handlers = [
			// Job Packages.
			'job_submission_package'      => [ '\Cariera_Packages\Integration\Job\Submission', 'create_user_submission_package' ],
			'job_promotional_package'     => [ '\Cariera_Packages\Integration\Job\Promotion', 'create_user_promotion_package' ],
			'job_view_package'            => [ '\Cariera_Packages\Integration\Job\View', 'create_user_view_package' ],

			// Company Packages.
			'company_promotional_package' => [ '\Cariera_Packages\Integration\Company\Promotion', 'create_user_promotion_package' ],
			'company_view_package'        => [ '\Cariera_Packages\Integration\Company\View', 'create_user_view_package' ],

			// Resume Packages.
			'resume_submission_package'   => [ '\Cariera_Packages\Integration\Resume\Submission', 'create_user_submission_package' ],
			'resume_promotional_package'  => [ '\Cariera_Packages\Integration\Resume\Promotion', 'create_user_promotion_package' ],
			'resume_view_package'         => [ '\Cariera_Packages\Integration\Resume\View', 'create_user_view_package' ],

			// Event Packages.
			'event_promotional_package'   => [ '\Cariera_Packages\Integration\Event\Promotion', 'create_user_promotion_package' ],
			'event_view_package'          => [ '\Cariera_Packages\Integration\Event\View', 'create_user_view_package' ],
		];

		// Process each order item.
		foreach ( $order->get_items() as $item ) {
			$product    = wc_get_product( $item->get_product_id() );
			$listing_id = $item['listing_id'] ?? '';
			$user_id    = $order->get_customer_id();

			// Skip invalid or non-Cariera package products.
			if ( ! $product instanceof \WC_Product || ! $user_id || ! $product->is_type( 'cariera_package' ) ) {
				continue;
			}

			// Retrieve the package type from the product.
			$product_type = method_exists( $product, 'package_type' ) ? $product->package_type() : null;

			if ( empty( $product_type ) || empty( $handlers[ $product_type ] ) ) {
				continue;
			}

			$handler  = $handlers[ $product_type ];
			$quantity = (int) $item->get_quantity();

			$user_package_id = false;
			// Process each package unit.
			for ( $i = 0; $i < $quantity; $i++ ) {
				$user_package_id = call_user_func( $handler, $user_id, $product->get_id(), $order_id, $listing_id );
			}

			$this->attach_submission_package_listing( $item, $order, $user_package_id, $product_type );
		}

		// Mark the order as processed to prevent repeated execution.
		update_post_meta( $order_id, 'cariera_packages_processed', true );

		do_action( 'cariera_packages_wc_order_paid', $order_id, $order );
	}

	/**
	 * Attach submission package listing.
	 *
	 * @since   0.9.13
	 * @version 0.9.16
	 *
	 * @param \WC_Order_Item $item
	 * @param \WC_Order      $order
	 * @param int            $user_package_id
	 * @param string         $product_type
	 */
	public function attach_submission_package_listing( $item, $order, $user_package_id, $product_type ) {
		// Supported submission package types.
		$submission_package_types = [ 'job_submission_package', 'resume_submission_package' ];

		if ( ! in_array( $product_type, $submission_package_types, true ) || ! $user_package_id ) {
			return;
		}

		global $wpdb;

		$listing_ids = [];

		// Listings from cancelled package order.
		$meta_listing_ids = $wpdb->get_col( // phpcs:ignore
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s",
				'_cancelled_package_order_id',
				$order->get_id()
			)
		);

		if ( $meta_listing_ids ) {
			$listing_ids = array_merge( $listing_ids, array_map( 'absint', $meta_listing_ids ) );
		}

		// TODO: Add more listing types for submissions.
		// Listings from order item meta.
		$potential_ids = [
			'job_id'    => isset( $item['job_id'] ) ? $item['job_id'] : null,
			'resume_id' => isset( $item['resume_id'] ) ? $item['resume_id'] : null,
		];

		// Allow modification of submission listing IDs before attaching.
		$potential_ids = apply_filters( 'cariera_packages_orders_attach_submission_listing_ids', $potential_ids, $item, $order, $user_package_id, $product_type );

		foreach ( $potential_ids as $id ) {
			if ( $id ) {
				$listing_ids[] = absint( $id );
			}
		}

		// Remove duplicates and empty values.
		$listing_ids = array_unique( array_filter( $listing_ids ) );
		if ( empty( $listing_ids ) ) {
			return;
		}

		// Process each listing.
		foreach ( $listing_ids as $listing_id ) {
			$status = get_post_status( $listing_id );

			if ( in_array( $status, [ 'pending_payment', 'expired' ], true ) ) {
				\Cariera_Packages\Helpers::approve_listing_with_package( $listing_id, $order->get_user_id(), $user_package_id );
				delete_post_meta( $listing_id, '_cancelled_package_order_id' );
			}

			// Handle renewal products.
			$renewal_product_ids = get_post_meta( $listing_id, '_renewal_pending_product_id', false );
			foreach ( $renewal_product_ids as $renewal_product_id ) {
				if ( (int) $renewal_product_id === $item['product_id'] ) {
					\Cariera_Packages\Integration\Job::handle_listing_renewal( $item['product_id'], $user_package_id, $listing_id );
					break;
				}
			}
		}
	}

	/**
	 * Handles order cancellation by marking associated Cariera packages as cancelled.
	 *
	 * @since 0.9.0
	 *
	 * @param int $order_id The ID of the order being cancelled.
	 */
	public function order_cancelled( $order_id ) {
		// Get the order object based on the order ID.
		$order = wc_get_order( $order_id );

		// Loop through each item in the order.
		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item['product_id'] );

			// Check if the product is of type 'cariera_package' and if the order has a valid customer ID.
			if ( $product->is_type( [ 'cariera_package' ] ) && $order->get_customer_id() ) {

				// Get the associated packages based on the order ID and product type.
				$packages = \Cariera_Packages\Helpers::get_packages_by_order_id( $order_id );

				// Check if any packages are found.
				if ( ! empty( $packages ) ) {
					// Loop through each package and update its status to 'cancelled'.
					foreach ( $packages as $package_id ) {
						$package_data = [
							'ID'            => $package_id,
							'post_date'     => current_time( 'mysql' ),
							'post_date_gmt' => current_time( 'mysql', 1 ),
							'post_status'   => 'cancelled',
						];
						wp_update_post( $package_data );
					}
				}
			}
		}

		// Update the order meta to mark that packages were cancelled.
		update_post_meta( $order_id, 'cariera_packages_cancelled', true );
	}
}
