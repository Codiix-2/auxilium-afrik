<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooCommerce {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		new WooCommerce\Cart();
		new WooCommerce\Checkout();
		new WooCommerce\Orders();
		new WooCommerce\ThankYou();
		new WooCommerce\Product();

		// Delete User.
		add_action( 'delete_user', [ $this, 'delete_user_packages' ] );
	}

	/**
	 * Deletes all Cariera package posts associated with a given user.
	 *
	 * This function retrieves all posts of type 'cariera_package' that are associated with the given user ID
	 * (stored in the meta field '_user_id') and deletes them permanently.
	 *
	 * @since   0.9.0
	 * @version 0.9.12
	 *
	 * @param int $user_id The ID of the user whose associated packages are to be deleted.
	 */
	public function delete_user_packages( $user_id ) {
		// Exit early if no user ID is provided.
		if ( empty( $user_id ) ) {
			return;
		}

		// Get all Cariera package posts associated with the user.
		$packages = get_posts(
			[
				'post_type'  => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
				'meta_query' => [ // phpcs:ignore
					[
						'key'     => '_user_id',
						'value'   => $user_id,
						'compare' => '=',
					],
				],
			]
		);

		// Check if any packages were found.
		if ( ! empty( $packages ) && is_array( $packages ) ) {
			// Loop through each package and delete it permanently.
			foreach ( $packages as $package ) {
				wp_delete_post( $package->ID, true );
			}
		}
	}
}
