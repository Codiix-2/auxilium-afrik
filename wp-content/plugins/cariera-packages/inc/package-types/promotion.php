<?php
// TODO: move to abstract folder.

namespace Cariera_Packages\Package_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Promotion {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * The post type that this promotion applies to (e.g., job_listing, resume, company)
	 *
	 * @var string
	 */
	protected static $post_type;

	/**
	 * The WooCommerce package type (e.g., job_promotional_package)
	 *
	 * @var string
	 */
	protected $package_type;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'cariera_dashboard_sidebar_expiring', [ $this, 'display_expiring_promotions' ] );

		// Clean up listing when a promotion package is trashed or permantly deleted.
		add_action( 'trashed_post', [ $this, 'handle_package_deletion' ] );
		add_action( 'before_delete_post', [ $this, 'handle_package_deletion' ] );

		// Restore listing promotion when package is untrashed.
		add_action( 'untrashed_post', [ $this, 'handle_package_restore' ] );

		// Reactivate promotion when package is published.
		add_action( 'transition_post_status', [ $this, 'handle_package_publish' ], 10, 3 );
		add_action( 'save_post', [ $this, 'handle_package_direct_publish' ], 10, 3 );

		// AJAX handler for promotion requests.
		add_action( 'wp_ajax_cariera_packages_promotions', [ $this, 'promotion_ajax_request' ] );

		// Check for expired Promotions via Cron.
		add_action( 'cariera_check_expired_promotions', [ $this, 'check_for_expired_promotions' ] );
	}

	/**
	 * Display expiring promotions in the dashboard sidebar
	 *
	 * @since 0.9.9
	 */
	public function display_expiring_promotions() {
		static $already_ran = false;

		// Exit immediately if this function already ran.
		if ( $already_ran ) {
			return;
		}

		$already_ran = true;

		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		$promotional_options = get_option( 'cariera_packages_promotional_package', [] );

		// Return early if none of the promotion types are active.
		if ( ! array_filter( $promotional_options ) ) {
			return;
		}

		$promotions = self::check_for_expiring_soon_promotions();

		get_job_manager_template(
			'dashboard/expiring-promotions.php',
			[
				'promotions' => $promotions,
			],
			'cariera-packages',
			CARIERA_PACKAGES_PATH . '/templates/'
		);
	}

	/**
	 * Activate a promotion package for a given listing.
	 *
	 * @since   0.9.9
	 * @version 1.0.0
	 *
	 * @param int      $package_id The ID of the promotion package to activate.
	 * @param int|bool $listing_id Optional. The ID of the listing to promote.
	 */
	public static function activate_promotion( $package_id, $listing_id = false ) {
		global $wpdb;

		$package = get_post( $package_id );

		// Validate package post.
		if ( ! $package || \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE !== $package->post_type ) {
			return false;
		}

		if ( ! $listing_id ) {
			$listing_id = get_post_meta( $package_id, 'cariera_packages_promoted_listing_id', true );
		}

		// Validate listing post.
		if ( ! $listing_id ) {
			return false;
		}

		$listing = get_post( $listing_id );
		if ( ! $listing ) {
			return false;
		}

		// Add package info to listing.
		update_post_meta( $listing->ID, '_promo_package_id', $package_id );

		// Add listing info to package.
		update_post_meta( $package_id, 'cariera_packages_promoted_listing_id', $listing->ID );

		// If listing already featured, no need to re-feature.
		if ( get_post_meta( $listing->ID, '_featured', true ) ) {
			return true;
		}

		// Make listing featured.
		update_post_meta( $listing->ID, '_featured', 1 );
		$wpdb->update( $wpdb->posts, [ 'menu_order' => -1 ], [ 'ID' => $listing->ID ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- We need to update menu_order directly here for promotion sorting.

		// Clear WPJM caches for specific post types.
		$instance = static::instance();
		$instance->clear_listing_cache( $listing->post_type );

		// Handle expiry.
		$duration = absint( get_post_meta( $package_id, 'cariera_packages_listing_promotion_duration', true ) );
		$expires  = '';

		if ( $duration ) {
			$expires = date( 'Y-m-d H:i:s', strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
		}

		// Save expiration data on both package and listing.
		if ( $expires ) {
			update_post_meta( $package_id, 'cariera_packages_promotion_expires', $expires );
			update_post_meta( $listing->ID, '_promotion_expires', $expires );
		}

		// Update package with expiry date and active status.
		wp_update_post(
			[
				'ID'          => $package_id,
				'post_status' => 'publish',
			]
		);

		do_action( 'cariera_packages_listing_promotion_started', $listing->ID, $package_id );

		do_action_deprecated( 'cariera_listing_promotion_started', [ $listing->ID, $package_id ], '0.9.9', 'cariera_packages_listing_promotion_started' );

		return true;
	}

	/**
	 * Remove featured status and listing meta when a promotion package is deleted
	 *
	 * @since   0.9.9
	 * @version 1.0.0
	 *
	 * @param int $package_id
	 */
	public function handle_package_deletion( $package_id ) {
		$package = get_post( $package_id );

		// Only target promotion packages.
		if ( ! $package || \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE !== $package->post_type ) {
			return;
		}

		// Only for job_promotional_package type.
		$package_type = \Cariera_Packages\Helpers::get_package_type( $package_id );
		if ( $package_type !== $this->package_type ) {
			return;
		}

		// Get the listing this package was promoting.
		$listing_id = get_post_meta( $package_id, 'cariera_packages_promoted_listing_id', true );
		if ( ! $listing_id ) {
			return;
		}

		$listing = get_post( $listing_id );
		if ( ! $listing || $listing->post_type !== static::$post_type ) {
			return;
		}

		// Remove featured status and package meta.
		update_post_meta( $listing->ID, '_featured', 0 );
		update_post_meta( $listing->ID, '_promo_package_id', '' );

		// Reset menu order if it was changed.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- We need to update menu_order directly here for promotion sorting.
		$wpdb->update(
			$wpdb->posts,
			[ 'menu_order' => 0 ],
			[ 'ID' => $listing->ID ]
		);

		// Clear WPJM caches for job listings.
		\WP_Job_Manager_Cache_Helper::get_transient_version( 'get_job_listings', true );

		do_action( 'cariera_packages_listing_promotion_ended', $listing->ID, $package_id );

		do_action_deprecated( 'cariera_listing_promotion_ended', [ $listing->ID, $package_id ], '0.9.9', 'cariera_packages_listing_promotion_ended' );
	}

	/**
	 * Restore promotion if a promotion package is restored from trash
	 *
	 * @since 0.9.9
	 *
	 * @param int $package_id
	 */
	public function handle_package_restore( $package_id ) {
		$package = get_post( $package_id );

		if ( ! $package || \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE !== $package->post_type ) {
			return;
		}

		$package_type = \Cariera_Packages\Helpers::get_package_type( $package_id );
		if ( $package_type !== $this->package_type ) {
			return;
		}

		$listing_id = get_post_meta( $package_id, 'cariera_packages_promoted_listing_id', true );
		if ( ! $listing_id ) {
			return;
		}

		self::activate_promotion( $package_id, $listing_id );
	}

	/**
	 * Reactivate promotion when package is re-published
	 *
	 * @since 0.9.9
	 *
	 * @param string  $new_status
	 * @param string  $old_status
	 * @param WP_POST $post
	 */
	public function handle_package_publish( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status ) {
			return;
		}

		if ( \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE !== $post->post_type ) {
			return;
		}

		$package_type = \Cariera_Packages\Helpers::get_package_type( $post->ID );
		if ( $package_type !== $this->package_type ) {
			return;
		}

		$listing_id = get_post_meta( $post->ID, 'cariera_packages_promoted_listing_id', true );
		if ( ! $listing_id ) {
			return;
		}

		// Reactivate promotion when package is re-published.
		self::activate_promotion( $post->ID, $listing_id );
	}

	/**
	 * Activate promotion when package is directly published (not via status transition)
	 *
	 * @since 0.9.9
	 *
	 * @param int     $post_id
	 * @param WP_POST $post
	 * @param bool    $update
	 */
	public function handle_package_direct_publish( $post_id, $post, $update ) {
		// Only run when it’s newly published (not just updated).
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE !== $post->post_type ) {
			return;
		}

		if ( 'publish' !== $post->post_status ) {
			return;
		}

		$package_type = \Cariera_Packages\Helpers::get_package_type( $post_id );
		if ( $package_type !== $this->package_type ) {
			return;
		}

		$listing_id = get_post_meta( $post_id, 'cariera_packages_promoted_listing_id', true );
		if ( ! $listing_id ) {
			return;
		}

		self::activate_promotion( $post_id, $listing_id );
	}

	/**
	 * Add the chosen promotion package to cart and proceed to checkout.
	 *
	 * @since   0.9.9
	 * @version 0.9.11
	 *
	 * @throws \Exception If the request is invalid or processing fails.
	 */
	public function promotion_ajax_request() {
		check_ajax_referer( '_cariera_core_nonce', 'security' );
		$process = ! empty( $_POST['process'] ) ? sanitize_text_field( wp_unslash( $_POST['process'] ) ) : 'buy-package';

		try {
			// Validate request.
			if ( ! is_user_logged_in() || empty( $_POST['listing_id'] ) ) {
				throw new \Exception( __( 'Could not process request.', 'cariera-packages' ), 10 );
			}

			$package_id   = isset( $_POST['package_id'] ) ? absint( $_POST['package_id'] ) : 0;
			$package_type = isset( $_POST['package_type'] ) ? sanitize_text_field( wp_unslash( $_POST['package_type'] ) ) : '';

			// Verify it's a published listing and editable by current user.
			$listing = get_post( absint( $_POST['listing_id'] ) );
			if ( ! ( $listing && 'publish' === $listing->post_status ) ) {
				throw new \Exception( __( 'Could not process request.', 'cariera-packages' ), 11 );
			}

			// Process when a user buys a new promotion package.
			if ( 'buy-package' === $process ) {
				if ( empty( $_POST['package_id'] ) ) {
					throw new \Exception( __( 'Could not process request.', 'cariera-packages' ), 20 );
				}

				// Init "buy_function".
				$this->buy_package( $package_id, $listing->ID, $package_type );

				return wp_send_json(
					[
						'status'   => 'success',
						'redirect' => add_query_arg(
							[ 't' => time() ],
							WC()->cart->get_cart_contents_count() > 1 ? wc_get_cart_url() : wc_get_checkout_url()
						),
					]
				);
			}

			if ( 'use-package' === $process ) {
				if ( empty( $_POST['package_id'] ) ) {
					throw new \Exception( __( 'Could not process request.', 'cariera-packages' ), 20 );
				}

				$this->activate_promotion( $package_id, $listing->ID );

				return wp_send_json(
					[
						'status'   => 'success',
						'redirect' => add_query_arg(
							't',
							time(),
							$this->redirect_promotion_activation( $package_type )
						),
					]
				);
			}
		} catch ( \Exception $e ) {
			return wp_send_json(
				[
					'status'  => 'error',
					'message' => $e->getMessage(),
					'code'    => $e->getCode(),
				]
			);
		}
	}

	/**
	 * Buy promotion package
	 *
	 * @since 0.9.9
	 *
	 * @param int    $product_id
	 * @param int    $listing_id
	 * @param string $package_type
	 *
	 * @throws \Exception If the product is invalid or cannot be added to cart.
	 */
	public function buy_package( $product_id, $listing_id, $package_type ) {
		$product = wc_get_product( absint( $product_id ) );

		// Validate product.
		if ( ! ( $product && $product->is_type( [ 'cariera_package' ] ) && $product->is_purchasable() ) ) {
			throw new \Exception( esc_html__( 'Could not process request.', 'cariera-packages' ) );
		}

		// Validate package type.
		$product_package_type = \Cariera_Packages\Helpers::get_product_package_type( $product_id );
		if ( empty( $product_package_type ) || $product_package_type !== $package_type ) {
			throw new \Exception( esc_html__( 'Incorrect package type for this promotion.', 'cariera-packages' ) );
		}

		// Remove old promotion packages for this listing from the cart, if any.
		if ( is_array( WC()->cart->cart_contents ) ) {
			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( empty( $cart_item['listing_id'] ) || empty( $cart_item['data'] ) ) {
					continue;
				}

				// Only consider cariera packages.
				if ( ! in_array( $cart_item['data']->get_type(), [ 'cariera_package' ], true ) ) {
					continue;
				}

				// Check if it's the same package type as current integration.
				$current_type = \Cariera_Packages\Helpers::get_product_package_type( $cart_item['product_id'] );
				if ( $current_type !== $package_type ) {
					continue;
				}

				// Remove promotion package if it belongs to the listing currently being promoted.
				if ( absint( $cart_item['listing_id'] ) === absint( $listing_id ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
		}

		// Add product to cart with listing_id provided in the cart item data.
		WC()->cart->add_to_cart(
			$product->get_id(),
			1,
			'',
			'',
			[
				'listing_id' => $listing_id,
			]
		);
	}

	/**
	 * Clear listing cache based on post type.
	 *
	 * @since 0.9.9
	 *
	 * @param string $post_type The post type of the listing.
	 */
	protected function clear_listing_cache( $post_type ) {
		switch ( $post_type ) {
			case 'job_listing':
				\WP_Job_Manager_Cache_Helper::get_transient_version( 'get_job_listings', true );
				break;

			case 'company':
				\WP_Job_Manager_Cache_Helper::get_transient_version( 'cariera_get_company_listings', true );
				break;

			case 'resume':
				\WP_Job_Manager_Cache_Helper::get_transient_version( 'get_resume_listings', true );
				break;

			case 'event':
				// Add your custom cache clearing logic for events here if needed.
				break;

			default:
				// Optional: log or do nothing.
				break;
		}
	}

	/**
	 * Redirect after activating promotion via user promotion package.
	 *
	 * @since 0.9.11
	 *
	 * @param string $package_type
	 */
	protected function redirect_promotion_activation( $package_type ) {

		switch ( $package_type ) {
			// Job Promotion Package.
			case 'job_promotional_package':
				$redirect = get_permalink( get_option( 'job_manager_job_dashboard_page_id' ) );
				break;

			// Company Promotion Package.
			case 'company_promotional_package':
				$redirect = get_permalink( get_option( 'cariera_company_dashboard_page' ) );
				break;

			// Resume Promotion Package.
			case 'resume_promotional_package':
				$redirect = get_permalink( get_option( 'resume_manager_candidate_dashboard_page_id' ) );
				break;

			// Event Promotion Package.
			case 'event_promotional_package':
				$redirect = get_permalink( get_option( 'cariera_events_dashboard_page' ) );
				break;

			default:
				// Do nothing.
				break;
		}

		return $redirect;
	}

	/**
	 * Check and trash expired promotion packages.
	 *
	 * @since   0.9.9
	 * @version 0.9.13
	 */
	public function check_for_expired_promotions() {
		// Use WordPress timezone-aware current time.
		$now = current_time( 'mysql' );

		// Get expired packages.
		$args = [
			'post_type'      => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'meta_query'     => [ // phpcs:ignore
				[
					'key'     => 'cariera_packages_promotion_expires',
					'value'   => $now,
					'compare' => '<',
					'type'    => 'DATETIME',
				],
			],
		];

		$expired_packages = get_posts( $args );

		// Expire each package.
		foreach ( $expired_packages as $package_id ) {
			$this->expire_package( $package_id );
		}
	}

	/**
	 * Change status to expired afte promotion has ended and remove package related data
	 *
	 * @since 0.9.9
	 *
	 * @param int $package_id
	 */
	public function expire_package( $package_id ) {
		global $wpdb;

		$package    = get_post( $package_id );
		$listing_id = get_post_meta( $package_id, 'cariera_packages_promoted_listing_id', true );

		if ( ! $package || \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE !== $package->post_type ) {
			return false;
		}

		if ( $listing_id ) {
			$listing = get_post( $listing_id );

			// Make the listing normal again.
			update_post_meta( $listing->ID, '_featured', 0 );

			// phpcs:ignore
			$wpdb->update(
				$wpdb->posts,
				[ 'menu_order' => 0 ],
				[
					'ID'         => $listing_id,
					'menu_order' => -1,
				]
			);

			// Clear WPJM caches on expiry.
			$this->clear_listing_cache( $listing->post_type );

			// Delete other promotion data from listing meta.
			delete_post_meta( $listing_id, '_promo_package_id' );
		}

		// Delete package.
		wp_trash_post( $package_id );

		// Fire generic action hook.
		do_action( 'cariera_packages_listing_promotion_ended', $listing->ID, $package_id );

		do_action_deprecated( 'cariera_listing_promotion_ended', [ $listing->ID, $package_id ], '0.9.9', 'cariera_packages_listing_promotion_ended' );

		return true;
	}

	/**
	 * Check for promotions that are expiring soon.
	 *
	 * @since 0.9.9
	 */
	public static function check_for_expiring_soon_promotions() {
		$current_user = wp_get_current_user();

		$days_notice            = 8;
		$notice_before_datetime = current_datetime()->add( new \DateInterval( 'P' . $days_notice . 'D' ) );

		$promotions = get_posts(
			[
				'post_type'      => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
				'post_status'    => 'publish',
				'author'         => $current_user->ID,
				'fields'         => 'ids',
				'orderby'        => 'cariera_packages_promotion_expires',
				'order'          => 'ASC',
				'posts_per_page' => 5,
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Used in production with no issues.
					[
						'key'     => 'cariera_packages_promotion_expires',
						'value'   => 0,
						'compare' => '>',
					],
					[
						'key'     => 'cariera_packages_promotion_expires',
						'value'   => $notice_before_datetime->format( 'Y-m-d' ),
						'compare' => '<',
					],
				],
			]
		);

		return $promotions;
	}
}
