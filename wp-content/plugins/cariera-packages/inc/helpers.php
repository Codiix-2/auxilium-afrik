<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpers {

	/**
	 * Meta key prefix for Cariera packages.
	 */
	const META_PREFIX = 'cariera_packages_';

	/**
	 * Retrieves WooCommerce products of a specific type.
	 *
	 * @since   0.9.0
	 * @version 0.9.12
	 *
	 * @param string|array $package_type The product type(s) to filter by. Defaults to 'cariera_package'.
	 *
	 * @return WP_Post[] Array of WP_Post objects representing the matching products.
	 */
	public static function get_package_products( $package_type = 'all' ) {
		$meta_query = [];

		// Add package type filtering if it's not set to 'all'.
		if ( 'all' !== $package_type ) {
			$meta_query[] = [
				'key'     => '_package_type',
				'value'   => sanitize_text_field( $package_type ),
				'compare' => '=',
			];
		}

		// Query arguments for retrieving products.
		$query_args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => [ 'cariera_package', 'cariera_package_subscription' ],
				],
			],
		];

		// Fetch products matching the query.
		$products = get_posts( $query_args );

		return $products;
	}

	/**
	 * Retrieves package IDs associated with a specific order.
	 *
	 * @since   0.9.0
	 * @version 0.9.12
	 *
	 * @param int    $order_id     The ID of the WooCommerce order.
	 * @param string $package_type The type of package to filter by. Defaults to 'all' (no filtering).
	 *                              Use specific types like 'job_view_package' to filter results.
	 *
	 * @return int[] Array of package post IDs matching the criteria.
	 */
	public static function get_packages_by_order_id( $order_id, $package_type = 'all' ) {
		// Meta query to filter packages by order ID and optionally by package type.
		$meta_query = [
			[
				'key'     => self::META_PREFIX . 'order_id',
				'value'   => $order_id,
				'compare' => '=',
			],
		];

		// Add package type filtering if not set to 'all'.
		if ( 'all' !== $package_type ) {
			$meta_query[] = [
				'key'     => self::META_PREFIX . 'package_type',
				'value'   => $package_type,
				'compare' => '=',
			];
		}

		// Query arguments for retrieving packages.
		$query_args = [
			'post_type'      => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'fields'         => 'ids',
		];

		// Fetch package IDs matching the query.
		$package_ids = get_posts( $query_args );

		return $package_ids;
	}

	/**
	 * Get user packages
	 *
	 * @since   0.9.0
	 * @version 1.1.0
	 *
	 * @param int    $user_id
	 * @param string $package_type
	 * @param bool   $active
	 */
	public static function get_user_packages( $user_id, $package_type = 'all', $active = false ) {
		static $cache = [];

		// Validate user ID.
		if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
			return [];
		}

		// Fetch ALL packages for this user once and cache them by user ID only.
		if ( ! isset( $cache[ $user_id ] ) ) {
			$cache[ $user_id ] = get_posts(
				[
					'post_type'              => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
					'post_status'            => 'publish',
					'posts_per_page'         => -1,
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'fields'                 => 'ids',
					// Performance optimizations.
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'cache_results'          => true,
					'meta_query'             => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						[
							'key'     => self::META_PREFIX . 'user_id',
							'value'   => absint( $user_id ),
							'compare' => '=',
						],
					],
				]
			);

			// Prime all post meta in ONE query.
			if ( ! empty( $cache[ $user_id ] ) ) {
				update_meta_cache( 'post', $cache[ $user_id ] );
				_prime_post_caches( $cache[ $user_id ], false, false );
			}
		}

		$package_ids = $cache[ $user_id ];

		// Filter by package type in PHP instead of via a new query.
		if ( 'all' !== $package_type ) {
			$type_key          = self::META_PREFIX . 'package_type';
			$filtered_packages = [];

			foreach ( $package_ids as $package_id ) {
				if ( get_post_meta( $package_id, $type_key, true ) === $package_type ) {
					$filtered_packages[] = $package_id;
				}
			}

			$package_ids = $filtered_packages;
		}

		// Return all matched packages if $active is not required.
		if ( ! $active ) {
			return $package_ids;
		}

		// Filter packages based on their type and active status.
		$valid_packages = [];
		foreach ( $package_ids as $package_id ) {
			// Get actual package type.
			$current_package_type = get_post_meta( $package_id, self::META_PREFIX . 'package_type', true );

			switch ( $current_package_type ) {

				// Job Submission Package.
				case 'job_submission_package':
					$limit = get_post_meta( $package_id, self::META_PREFIX . 'job_submission_limit', true );
					$count = get_post_meta( $package_id, self::META_PREFIX . 'job_submission_count', true );

					// Check if the job submission limit is not exceeded.
					if ( empty( $limit ) || $count < absint( $limit ) ) {
						$valid_packages[] = $package_id;
					}
					break;

				// Resume Submission Package.
				case 'resume_submission_package':
					$limit = get_post_meta( $package_id, self::META_PREFIX . 'resume_submission_limit', true );
					$count = get_post_meta( $package_id, self::META_PREFIX . 'resume_submission_count', true );

					// Check if the resume submission limit is not exceeded.
					if ( empty( $limit ) || $count < absint( $limit ) ) {
						$valid_packages[] = $package_id;
					}
					break;

				// Promotional Packages.
				case 'job_promotional_package':
				case 'company_promotional_package':
				case 'resume_promotional_package':
				case 'event_promotional_package':
					$listing_id = get_post_meta( $package_id, self::META_PREFIX . 'promoted_listing_id', true );
					if ( empty( $listing_id ) ) {
						$valid_packages[] = $package_id;
					}
					break;

				// Job View Package.
				case 'job_view_package':
					$view_job_limit = get_post_meta( $package_id, self::META_PREFIX . 'view_job_limit', true );
					$viewed_jobs    = get_post_meta( $package_id, self::META_PREFIX . 'viewed_jobs', true );

					// Ensure the viewed jobs are properly formatted.
					$viewed_jobs_count = ! empty( $viewed_jobs ) ? count( array_filter( explode( ',', $viewed_jobs ), 'is_numeric' ) ) : 0;

					// Check if the job limit is not exceeded.
					if ( empty( $view_job_limit ) || $viewed_jobs_count < absint( $view_job_limit ) ) {
						$valid_packages[] = $package_id;
					}
					break;

				// Company View Package.
				case 'company_view_package':
					$view_company_limit = get_post_meta( $package_id, self::META_PREFIX . 'view_company_limit', true );
					$viewed_companies   = get_post_meta( $package_id, self::META_PREFIX . 'viewed_companies', true );

					// Ensure the viewed companies are properly formatted.
					$viewed_companies_count = ! empty( $viewed_companies ) ? count( array_filter( explode( ',', $viewed_companies ), 'is_numeric' ) ) : 0;

					// Check if the company limit is not exceeded.
					if ( empty( $view_company_limit ) || $viewed_companies_count < absint( $view_company_limit ) ) {
						$valid_packages[] = $package_id;
					}
					break;

				// Resume View Package.
				case 'resume_view_package':
					$view_resume_limit = get_post_meta( $package_id, self::META_PREFIX . 'view_resume_limit', true );
					$viewed_resumes    = get_post_meta( $package_id, self::META_PREFIX . 'viewed_resumes', true );

					// Ensure the viewed resumes are properly formatted.
					$viewed_resumes_count = ! empty( $viewed_resumes ) ? count( array_filter( explode( ',', $viewed_resumes ), 'is_numeric' ) ) : 0;

					// Check if the resume limit is not exceeded.
					if ( empty( $view_resume_limit ) || $viewed_resumes_count < absint( $view_resume_limit ) ) {
						$valid_packages[] = $package_id;
					}
					break;

				// Event View Package.
				case 'event_view_package':
					$view_event_limit = get_post_meta( $package_id, self::META_PREFIX . 'view_event_limit', true );
					$viewed_events    = get_post_meta( $package_id, self::META_PREFIX . 'viewed_events', true );

					// Ensure the viewed events are properly formatted.
					$viewed_events_count = ! empty( $viewed_events ) ? count( array_filter( explode( ',', $viewed_events ), 'is_numeric' ) ) : 0;

					// Check if the event limit is not exceeded.
					if ( empty( $view_event_limit ) || $viewed_events_count < absint( $view_event_limit ) ) {
						$valid_packages[] = $package_id;
					}
					break;

				default:
					// Add logic for other package types if needed.
					$valid_packages[] = $package_id;
					break;
			}
		}

		return $valid_packages;
	}

	/**
	 * Get the package type metadata of the Cariera Package CPT.
	 *
	 * @since   0.9.9
	 * @version 0.9.12
	 *
	 * @param int $package_id The ID of the Cariera package.
	 */
	public static function get_package_type( $package_id ) {
		if ( empty( $package_id ) || ! is_numeric( $package_id ) ) {
			return null;
		}

		$package_type = get_post_meta( $package_id, self::META_PREFIX . 'package_type', true );

		return ! empty( $package_type ) ? $package_type : null;
	}

	/**
	 * Get the package type form th WC product.
	 *
	 * @since 0.9.9
	 *
	 * @param int|\WC_Product $product Product ID or WC_Product object.
	 */
	public static function get_product_package_type( $product ) {
		// Allow passing either product ID or WC_Product object.
		$product_id = $product instanceof \WC_Product ? $product->get_id() : absint( $product );

		if ( ! $product_id ) {
			return null;
		}

		$package_type = get_post_meta( $product_id, '_package_type', true );

		return ! empty( $package_type ) ? $package_type : null;
	}

	/**
	 * Validate user package
	 *
	 * @since 0.9.12
	 *
	 * @param int $user_id
	 * @param int $package_id
	 */
	public static function user_package_is_valid( $user_id, $package_id ) {
		if ( empty( $user_id ) || empty( $package_id ) ) {
			return false;
		}

		$package_id = absint( $package_id );

		// Ensure post exists and is correct CPT.
		if ( get_post_type( $package_id ) !== \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE ) {
			return false;
		}

		// Check ownership.
		$owner_id = get_post_meta( $package_id, self::META_PREFIX . 'user_id', true );

		if ( absint( $owner_id ) !== absint( $user_id ) ) {
			return false;
		}

		// Determine package type.
		$package_type = get_post_meta( $package_id, self::META_PREFIX . 'package_type', true );

		if ( empty( $package_type ) ) {
			return false;
		}

		$active_packages = self::get_user_packages( $user_id, $package_type, true );

		return in_array( $package_id, $active_packages, true );
	}

	/**
	 * Approve listing with Cariera package.
	 *
	 * @since   0.9.12
	 * @version 0.9.15
	 *
	 * @param int $listing_id The   listing post ID.
	 * @param int $user_id          The user ID.
	 * @param int $user_package_id  The user package ID.
	 * @param int $package_id       The Cariera package ID.
	 *
	 * TODO: Check if this needs improving. Also new CPT need to be added.
	 */
	public static function approve_listing_with_package( $listing_id, $user_id, $user_package_id, $package_id = null ) {
		$listing_id      = absint( $listing_id );
		$user_id         = absint( $user_id );
		$user_package_id = absint( $user_package_id );

		// This makes the $package_id param truly optional and safe for all callers.
		if ( $package_id ) {
			$package_id = absint( $package_id );
		} else {
			$package_id = absint( get_post_meta( $user_package_id, self::META_PREFIX . 'product_id', true ) );
		}

		// Validate user package ownership and availability.
		if ( ! self::user_package_is_valid( $user_id, $user_package_id ) ) {
			return false;
		}

		$post_type = get_post_type( $listing_id );

		if ( ! $post_type ) {
			return false;
		}

		$resumed_post_status = get_post_meta( $listing_id, '_post_status_before_package_pause', true );

		if ( ! empty( $resumed_post_status ) ) {
			$listing = [
				'ID'          => $listing_id,
				'post_status' => $resumed_post_status,
			];

			delete_post_meta( $listing_id, '_post_status_before_package_pause' );
		} else {
			$listing = [ 'ID' => $listing_id ];

			switch ( $post_type ) {
				case 'job_listing':
					if ( ! class_exists( 'WP_Job_Manager_Form' ) ) {
						include_once JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php';
						include_once JOB_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-job-manager-form-submit-job.php';
					}

					if ( method_exists( '\WP_Job_Manager_Form_Submit_Job', 'apply_scheduled_date' ) ) {
						$job_schedule_listing_date = get_post_meta( $listing_id, '_job_schedule_listing', true );
						\WP_Job_Manager_Form_Submit_Job::apply_scheduled_date( $listing, $job_schedule_listing_date );
					} else {
						$listing['post_date']     = current_time( 'mysql' );
						$listing['post_date_gmt'] = current_time( 'mysql', 1 );
					}

					$listing['post_status'] = get_option( 'job_manager_submission_requires_approval' ) ? 'pending' : 'publish';
					delete_post_meta( $listing_id, '_job_expires' );
					break;

				case 'resume':
					$listing['post_status'] = get_option( 'resume_manager_submission_requires_approval' ) ? 'pending' : 'publish';
					break;

				default:
					$listing['post_status'] = 'publish';
					break;
			}
		}

		// Store both the user package instance and the underlying product ID on the listing.
		update_post_meta( $listing_id, '_user_package_id', $user_package_id );
		update_post_meta( $listing_id, '_package_id', $package_id );

		// Update post first — safer to confirm publish before touching package counts.
		$updated = wp_update_post( $listing, true );

		if ( is_wp_error( $updated ) ) {
			return false;
		}

		// Increment submission package count.
		if ( apply_filters( 'job_manager_job_listing_affects_package_count', true, $listing_id ) ) {
			$package_type = get_post_meta( $user_package_id, self::META_PREFIX . 'package_type', true );
			self::increase_submission_package_count( $user_package_id, $package_type );
		}

		return true;
	}

	/**
	 * Increase submission package usage count.
	 *
	 * @since   0.9.13
	 * @version 0.9.15
	 *
	 * @param int    $user_package_id User package post ID.
	 * @param string $package_type    Package type slug.
	 */
	public static function increase_submission_package_count( $user_package_id, $package_type ) {
		$user_package_id = absint( $user_package_id );
		$package_type    = sanitize_key( $package_type );

		if ( ! $user_package_id || ! $package_type ) {
			return false;
		}

		// TODO: Check this for more package types support.
		$meta_key_map = [
			'job_submission_package'    => self::META_PREFIX . 'job_submission_count',
			'resume_submission_package' => self::META_PREFIX . 'resume_submission_count',
		];

		$meta_key = isset( $meta_key_map[ $package_type ] ) ? $meta_key_map[ $package_type ] : '';

		// Filter the meta key used to store submission count.
		$meta_key = apply_filters( 'cariera_packages_increase_submission_meta_key', $meta_key, $package_type, $user_package_id );

		if ( empty( $meta_key ) ) {
			return false;
		}

		$current   = (int) get_post_meta( $user_package_id, $meta_key, true );
		$new_count = $current + 1;

		$updated = update_post_meta( $user_package_id, $meta_key, $new_count );

		return (bool) $updated;
	}

	/**
	 * Decrease submission package usage count.
	 *
	 * @since   0.9.13
	 * @version 0.9.15
	 *
	 * @param int    $user_package_id User package post ID.
	 * @param string $package_type    Package type slug.
	 */
	public static function decrease_submission_package_count( $user_package_id, $package_type ) {
		$user_package_id = absint( $user_package_id );
		$package_type    = sanitize_key( $package_type );

		if ( ! $user_package_id || ! $package_type ) {
			return false;
		}

		// TODO: Check this for more package types support.
		$meta_key_map = [
			'job_submission_package'    => self::META_PREFIX . 'job_submission_count',
			'resume_submission_package' => self::META_PREFIX . 'resume_submission_count',
		];

		$meta_key = isset( $meta_key_map[ $package_type ] ) ? $meta_key_map[ $package_type ] : '';

		// Filter the meta key used to store submission count.
		$meta_key = apply_filters( 'cariera_packages_decrease_submission_meta_key', $meta_key, $package_type, $user_package_id );

		if ( empty( $meta_key ) ) {
			return false;
		}

		$current   = (int) get_post_meta( $user_package_id, $meta_key, true );
		$new_count = max( 0, $current - 1 );

		$updated = update_post_meta( $user_package_id, $meta_key, $new_count );

		return (bool) $updated;
	}

	/**
	 * Add the `pending_payment` status to the submission statuses.
	 *
	 * @since 0.9.15
	 *
	 * @param array $statuses Array of submission statuses.
	 */
	public static function add_pending_payment_status( $statuses ) {
		if ( ! in_array( 'pending_payment', $statuses, true ) ) {
			$statuses[] = 'pending_payment';
		}

		return $statuses;
	}
}
