<?php

namespace Cariera_Packages\Integration\Job;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Promotion extends \Cariera_Packages\Package_Types\Promotion {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected static $post_type = 'job_listing';

	/**
	 * Package type
	 *
	 * @var string
	 */
	protected $package_type = 'job_promotional_package';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		// Dashboard Action.
		add_action( 'job_manager_job_dashboard_column_actions', [ $this, 'dashboard_action' ], 10, 2 );

		// Include 'promote-listing' modal in the footer.
		add_action( 'wp_footer', [ $this, 'get_promotions_modal' ] );
	}

	/**
	 * Add "Promote" action to the Jobs Dashboard
	 *
	 * @since   0.9.9
	 * @version 0.9.18
	 *
	 * @param WP_POST $listing
	 * @param array   $actions
	 */
	public function dashboard_action( $listing, $actions ) {
		$promotions = get_option( 'cariera_packages_promotional_package' );

		if ( empty( $promotions ) || empty( $promotions[ self::$post_type ] ) ) {
			return;
		}

		if ( 'publish' !== $listing->post_status ) {
			return;
		}

		if ( get_post_meta( $listing->ID, '_featured', true ) ) {
			return;
		}

		echo '<a href="#cariera-packages-promotions" class="cariera-packages listing-dashboard-action-promote job-dashboard-action-promote popup-with-zoom-anim" data-listing-id="' . esc_attr( $listing->ID ) . '"><i class="las la-bolt"></i><span>' . esc_html__( 'Promote', 'cariera-packages' ) . '</span></a>';
	}

	/**
	 * Output 'Choose Promotion' modal in Jobs Dashboard
	 *
	 * @since   0.9.9
	 * @version 0.9.11
	 */
	public function get_promotions_modal() {
		global $post;

		$promotions        = get_option( 'cariera_packages_promotional_package' );
		$listing_dashboard = get_option( 'job_manager_job_dashboard_page_id' );

		if ( empty( $promotions ) || empty( $promotions[ static::$post_type ] ) ) {
			return;
		}

		if ( ! $post || (int) $post->ID !== (int) $listing_dashboard ) {
			return;
		}

		$user_id       = get_current_user_id();
		$products      = \Cariera_Packages\Helpers::get_package_products( $this->package_type );
		$user_packages = \Cariera_Packages\Helpers::get_user_packages( $user_id, $this->package_type, true );

		// Load template.
		get_job_manager_template(
			'dashboard/choose-promotion.php',
			[
				'title'         => esc_html__( 'Job Promotion', 'cariera-packages' ),
				'products'      => $products,
				'packages'      => $user_packages,
				'type'          => $this->package_type,
				'duration_meta' => '_job_promotion_duration',
			],
			'cariera-packages',
			CARIERA_PACKAGES_PATH . '/templates/'
		);
	}

	/**
	 * Create a promotion package for a user.
	 *
	 * @since 0.9.9
	 *
	 * @param int      $user_id
	 * @param int      $product_id
	 * @param int      $order_id
	 * @param int|null $listing_id
	 */
	public static function create_user_promotion_package( $user_id, $product_id, $order_id, $listing_id = null ) {
		// Validate the user ID.
		if ( empty( $user_id ) || ! is_numeric( $user_id ) || ! get_user_by( 'id', $user_id ) ) {
			return false;
		}

		// Get the WooCommerce product.
		$package = wc_get_product( $product_id );

		// Validate product type; only proceed for valid package types.
		if ( ! $package || ! $package->is_type( [ 'cariera_package' ] ) ) {
			return false;
		}

		// Get package type.
		$package_type = \Cariera_Packages\Helpers::get_product_package_type( $product_id );

		// Prepare the post arguments.
		$args = apply_filters(
			'cariera_packages_job_promotion_package_data',
			[
				'post_title'  => $package->get_title(),
				'post_status' => 'publish',
				'post_type'   => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
			],
			$user_id,
			$product_id,
			$order_id
		);

		// Create the new promotion package post.
		$promotion_id = wp_insert_post( $args );

		// If the post creation fails, return false.
		if ( ! $promotion_id || is_wp_error( $promotion_id ) ) {
			return false;
		}

		// General meta data prefix.
		$prefix = 'cariera_packages_';
		update_post_meta( $promotion_id, "{$prefix}product_id", $product_id );
		update_post_meta( $promotion_id, "{$prefix}order_id", $order_id );
		update_post_meta( $promotion_id, "{$prefix}user_id", $user_id );
		update_post_meta( $promotion_id, "{$prefix}package_type", $package_type );

		// Retrieve and update package-specific meta data.
		$duration = get_post_meta( $product_id, '_job_promotion_duration', true );

		// Update package-specific meta data.
		update_post_meta( $promotion_id, "{$prefix}listing_promotion_duration", $duration );

		// Activate the package promotion package.
		self::activate_promotion( $promotion_id, $listing_id );

		// Trigger a custom action hook for additional processing or integrations.
		do_action( 'cariera_packages_job_promotion_package_meta', $promotion_id, $user_id, $product_id, $order_id );

		return $promotion_id;
	}
}
