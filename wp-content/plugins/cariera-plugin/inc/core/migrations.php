<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Migrations {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_cariera_company_jobs_count_fix', [ $this, 'company_job_count' ] );
		add_action( 'wp_ajax_cariera_geolocate_listings', [ $this, 'geolocate_listings' ] );
		add_action( 'wp_ajax_cariera_db_cleanup', [ $this, 'db_cleanup' ] );
	}

	/**
	 * Check if the migrations are enabled.
	 *
	 * @since 1.8.3
	 */
	protected function is_enabled() {
		return get_option( 'cariera_migrations' );
	}

	/**
	 * Get migration items.
	 *
	 * @since   1.8.3
	 * @version 2.0.0
	 */
	public static function get_migration_items() {
		$migration_items = [
			[
				'name'        => esc_html__( 'Company Job Count', 'cariera-core' ),
				'action'      => 'cariera_company_jobs_count_fix',
				'type'        => '', // link - to make it a link.
				'link'        => '',
				'addon'       => '', // Addon name.
				'btn_title'   => esc_html__( 'Fix Job Count', 'cariera-core' ),
				'description' => esc_html__( 'Update the "_active_jobs" meta for company listings. Clicking the button will calculate the active jobs for each company and save the result as updated meta data.', 'cariera-core' ),
			],
			[
				'name'        => esc_html__( 'Geolocate Listings', 'cariera-core' ),
				'action'      => 'cariera_geolocate_listings',
				'type'        => '', // link - to make it a link.
				'link'        => '',
				'addon'       => '', // Addon name.
				'btn_title'   => esc_html__( 'Generate Geolocation Data', 'cariera-core' ),
				'description' => esc_html__( 'Start generating geolocation data for all your listings. Ensure that you\'ve added a valid Google API Key in "WP Dashboard → Job Manager → Settings".', 'cariera-core' ),
			],
			[
				'name'        => esc_html__( 'Database Cleanup', 'cariera-core' ),
				'action'      => 'cariera_db_cleanup',
				'type'        => '', // link - to make it a link.
				'link'        => '',
				'addon'       => '', // Addon name.
				'btn_title'   => esc_html__( 'Clean Database', 'cariera-core' ),
				'description' => esc_html__( 'By clicking "Clean Database" all options that have been deleted from the theme and still exist in your database will be deleted.', 'cariera-core' ),
			],
		];

		return apply_filters( 'cariera_migration_items', $migration_items );
	}

	/**
	 * Calculate the number of job listings a company has and save them as a meta.
	 *
	 * @since   1.8.2
	 * @version 2.0.0
	 */
	public function company_job_count() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_admin_nonce', 'nonce' );

		$next_data = 50;
		$offset    = 0;

		do {
			$listings = (array) get_posts(
				[
					'post_type'      => 'company',
					'offset'         => $offset,
					'posts_per_page' => $next_data,
					'post_status'    => [ 'publish', 'pending', 'private', 'expired' ],
					'meta_query'     => [], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				]
			);

			foreach ( $listings as $listing ) {
				$jobs_count = cariera_get_the_company_job_listing_active_count( $listing->ID );

				update_post_meta( $listing->ID, '_active_jobs', $jobs_count );
			}
			$offset = ( ! $offset ) ? $next_data : $offset + $next_data;
		} while ( ! empty( $listings ) );

		wp_send_json_success( 'Active job listings are calculated for each company!' );
	}

	/**
	 * Generate geolocation data for all listings.
	 *
	 * @since   1.8.3
	 * @version 2.0.0
	 */
	public function geolocate_listings() {
		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_admin_nonce', 'nonce' );

		$google_maps_api_key = get_option( 'job_manager_google_maps_api_key' );
		$use_google_api      = ! empty( $google_maps_api_key );

		// Define the post types to be processed.
		$post_types = [ 'job_listing', 'resume', 'company' ];

		// Settings for batch processing.
		$batch_size      = 50; // Number of listings processed per batch.
		$total_processed = 0;  // Counter for processed listings.
		$max_retries     = 3;  // Max number of retries for geolocation failures.
		$last_id         = 0;  // Used for ID-based batching to avoid skipped posts.
		$post_count      = 0; // Number of posts returned in the current batch.

		// Ensure script doesn't timeout for large datasets.
		set_time_limit( 0 );

		// Process listings in batches until no more matching posts are found.
		do {
			$query = new \WP_Query(
				[
					'post_type'              => $post_types,
					'posts_per_page'         => $batch_size,
					'post_status'            => [ 'publish', 'private', 'expired' ],
					'orderby'                => 'ID',
					'order'                  => 'ASC',
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'post__not_in'           => $last_id ? range( 1, $last_id ) : [],
					'meta_query'             => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'OR',
						[
							'key'     => 'geolocation_lat',
							'compare' => 'NOT EXISTS',
						],
						[
							'key'     => 'geolocation_long',
							'compare' => 'NOT EXISTS',
						],
						[
							'key'   => 'geolocation_lat',
							'value' => '',
						],
						[
							'key'   => 'geolocation_long',
							'value' => '',
						],
					],
				]
			);

			if ( is_wp_error( $query ) ) {
				wp_send_json_error( 'Failed to query posts for geolocation. ' . $query->get_error_message() );
			}

			// Stop processing if no listings are returned.
			if ( empty( $query->posts ) ) {
				break;
			}

			// Loop through the listings and process them.
			foreach ( $query->posts as $listing_id ) {
				$post_type = get_post_type( $listing_id );

				// Choose the location meta key based on the post type.
				switch ( $post_type ) {
					case 'job_listing':
						$location = get_post_meta( $listing_id, '_job_location', true );
						break;

					case 'resume':
						$location = get_post_meta( $listing_id, '_candidate_location', true );
						break;

					case 'company':
						$location = get_post_meta( $listing_id, '_company_location', true );
						break;

					default:
						$location = false;
				}

				// Skip listings with missing location data.
				if ( ! $location ) {
					\Cariera\write_log( sprintf( 'Missing address for %s #%d', $post_type, $listing_id ) );
					continue;
				}

				// Retry mechanism in case geolocation fails.
				$geocoded = false;
				$attempt  = 0;

				while ( $attempt < $max_retries && false === $geocoded ) {
					if ( $use_google_api ) {
						// Use Google Maps API geocoding.
						$geocoded = \WP_Job_Manager_Geocode::generate_location_data( $listing_id, $location );
					} else {
						// Use fallback geocoding method.
						$geocoded = \Cariera_Core\Core\Job_Manager\Geocode::get_location_data( $location );
						if ( $geocoded ) {
							\WP_Job_Manager_Geocode::save_location_data( $listing_id, $geocoded );
						}
					}

					// If geocoding failed, retry until max attempts.
					if ( false === $geocoded ) {
						++$attempt;

						if ( $attempt < $max_retries ) {
							\Cariera\write_log( sprintf( 'Retrying geolocation for %s #%d (%s)', $post_type, $listing_id, $location ) );
						}
					}
				}

				// Log the result of the geolocation attempt.
				if ( false !== $geocoded ) {
					\Cariera\write_log( sprintf( 'Geolocation successful for %s #%d (%s)', $post_type, $listing_id, $location ) );
				} else {
					\Cariera\write_log( sprintf( 'Failed to geolocate %s #%d (%s) after %d attempts', $post_type, $listing_id, $location, $max_retries ) );
				}

				// Update last processed ID so the next batch continues from here.
				$last_id = $listing_id;

				// Increase processed counter.
				++$total_processed;
			}

			// Post processing after each batch - can be used for logging or progress updates.
			$post_count = count( $query->posts );

			// Continue processing while the batch size is fully returned.
		} while ( $post_count === $batch_size );

		// Return success response with total processed listings.
		wp_send_json_success( sprintf( 'Geolocation completed. Total listings processed: %d.', $total_processed ) );
	}

	/**
	 * Delete options from the database that do not exist anymore.
	 *
	 * @since   1.8.5
	 * @version 1.9.3
	 */
	public function db_cleanup() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_admin_nonce', 'nonce' );

		// Get all Kirki options.
		$theme_options = [
			// Kirki Options.
			'cariera_body_color',
			'cariera_header_style',
			'cariera_footer_style',
			'header_quick_search',
			'cariera_body_style',
			'cariera_body_bg',
			'cariera_body_bg_horizontal',
			'cariera_body_bg_vertical',
			'cariera_body_bg_repeats',
			'cariera_body_bg_attachments',
			'cariera_body_bg_size',
			'cariera_dashboard_page_enable',
			'cariera_dashboard_job_alerts_page_enable',
			'cariera_dashboard_bookmark_page_enable',
			'cariera_dashboard_applied_jobs_page_enable',
			'cariera_dashboard_user_packages_page_enable',
			'cariera_dashboard_orders_page_enable',
			'cariera_dashboard_job_submission_page_enable',
			'cariera_dashboard_company_submission_page_enable',
			'cariera_dashboard_resume_submission_page_enable',
			'cariera_dashboard_profile_page_enable',
			'cariera_max_radius_search_value',
			'cariera_job_location_autocomplete',
			'cariera_map_restriction',
			'cariera_job_auto_location',
			'cariera_radius_unit',
			'cariera_max_radius_search_value',
			'cariera_map_height',
			'cariera_recaptcha_login',
			'cariera_recaptcha_register',
			'cariera_recaptcha_forgotpass',

			// Native Options.
		];

		$deleted = [];
		foreach ( $theme_options as $option ) {
			if ( delete_option( $option ) ) {
				$deleted[] = $option;
			}
		}

		wp_send_json_success( sprintf( 'Deleted %d deprecated options from the database!', count( $deleted ) ) );
	}
}
