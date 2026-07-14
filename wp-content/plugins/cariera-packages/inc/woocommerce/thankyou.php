<?php

namespace Cariera_Packages\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ThankYou {

	/**
	 * Supported listing post types.
	 *
	 * @var array
	 */
	protected $listing_post_types = [
		'job_listing',
		'resume',
		'company',
		'cariera_event',
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_thankyou', [ $this, 'thank_you' ], 5 );
	}

	/**
	 * Displays a custom thank you message after a user completes their purchase.
	 *
	 * @since   0.9.0
	 * @version 0.9.16
	 *
	 * @param int $order_id The WooCommerce order ID.
	 */
	public function thank_you( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		// Loop through each purchased item.
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$product    = wc_get_product( $product_id );

			// Skip if not a valid product or not a Cariera package.
			if ( ! $product instanceof \WC_Product || ! $product->is_type( 'cariera_package' ) ) {
				continue;
			}

			// Get the package type using your helper.
			$package_type = \Cariera_Packages\Helpers::get_product_package_type( $product_id );

			// Only continue if there’s a valid package type.
			if ( empty( $package_type ) ) {
				continue;
			}

			// Check if the item has an associated published listing.
			$listing_id = $item['listing_id'] ?? '';
			if ( empty( $listing_id ) || 'publish' !== get_post_status( $listing_id ) ) {
				continue;
			}

			// Retrieve post type and determine the label for user message.
			$listing_post_type = get_post_type( $listing_id );
			$redirect_to       = in_array( $listing_post_type, $this->listing_post_types, true ) ? esc_html__( 'listing', 'cariera-packages' ) : esc_html__( 'page', 'cariera-packages' );

			$redirect = get_permalink( $listing_id );

			// Skip if we couldn’t get a permalink.
			if ( empty( $redirect ) ) {
				continue;
			}

			// Append a notice query param (optional, if used elsewhere).
			$redirect = add_query_arg( [ 'notice' => 'listing_viewed' ], $redirect );

			// Thank you message.
			$message = $this->get_thank_you_message( $package_type, $redirect_to, $redirect, $listing_id );

			// If a message was created, output it.
			if ( ! empty( $message ) ) {
				$message = apply_filters( 'cariera_packages_wc_thank_you_message', $message, $item, $package_type, $redirect, $order );
				printf( '<div class="job-manager-message">%s</div>', wp_kses_post( $message ) );
			}

			// Fire an action for extensibility.
			do_action( 'cariera_packages_wc_thank_you_after', $item, $product, $order_id, $order, $package_type );
		}
	}

	/**
	 * Build the thank you message based on the package type.
	 *
	 * @since 0.9.16
	 *
	 * @param string $package_type
	 * @param string $redirect_to
	 * @param string $redirect
	 * @param int    $listing_id
	 *
	 * TODO: Add submission package support.
	 */
	protected function get_thank_you_message( $package_type, $redirect_to, $redirect, $listing_id ) {
		$view_packages = [
			'job_view_package',
			'resume_view_package',
			'company_view_package',
			'event_view_package',
		];

		$promo_packages = [
			'job_promotional_package',
			'resume_promotional_package',
			'company_promotional_package',
			'event_promotional_package',
		];

		// View packages.
		if ( in_array( $package_type, $view_packages, true ) ) {
			return sprintf(
				/* translators: %1$s is "listing" or "page", %2$s is the listing URL */
				__( 'To go back to the %1$s you were previously viewing: <a href="%2$s">click here</a>.', 'cariera-packages' ),
				$redirect_to,
				esc_url( $redirect )
			);
		}

		// Promotional packages.
		if ( in_array( $package_type, $promo_packages, true ) ) {
			return sprintf(
				/* translators: %s is the listing title */
				__( 'You listing "%s" has been promoted successfully.', 'cariera-packages' ),
				esc_html( get_the_title( $listing_id ) )
			);
		}

		return '';
	}
}
