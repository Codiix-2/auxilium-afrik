<?php

namespace Cariera_Packages\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		\Cariera_Packages\Integration\Job\Submission::instance();
		\Cariera_Packages\Integration\Job\Promotion::instance();
		\Cariera_Packages\Integration\Job\View::instance();

		// Add pending_payment status to valid submit statuses.
		add_filter( 'the_job_status', [ $this, 'the_job_status' ], 10, 2 );
		add_filter( 'job_manager_valid_submit_job_statuses', [ '\Cariera_Packages\Helpers', 'add_pending_payment_status' ] );

		// Add pending_payment status to dashboard args.
		add_filter( 'job_manager_get_dashboard_jobs_args', [ $this, 'filter_dashboard_jobs_args' ] );
	}

	/**
	 * Filter job status label.
	 *
	 * @since 0.9.12
	 *
	 * @param string  $status
	 * @param WP_Post $job
	 */
	public function the_job_status( $status, $job ) {
		if ( isset( $job->post_status ) && 'pending_payment' === $job->post_status ) {
			$status = esc_html__( 'Pending Payment', 'cariera-packages' );
		}

		return $status;
	}

	/**
	 * Filter job dashboard args to also list pending_payment status.
	 *
	 * @since 0.9.12
	 *
	 * @param array $job_dashboard_args Job dashboard args to filter.
	 */
	public function filter_dashboard_jobs_args( $job_dashboard_args ) {
		$job_dashboard_args['post_status'][] = 'pending_payment';

		return $job_dashboard_args;
	}

	/**
	 * Handle a job listing renewal (replaces wc_paid_listings_handle_listing_renewal).
	 *
	 * @since 0.9.13
	 *
	 * @param int  $product_id Product ID bought for renewal.
	 * @param int  $user_package_id User package ID.
	 * @param int  $job_id Job listing ID.
	 * @param bool $is_listing_subscription Optional. Whether this is a subscription product linked to the listing.
	 */
	public static function handle_listing_renewal( $product_id, $user_package_id, $job_id, $is_listing_subscription = false ) {
		// Ensure the WP Job Manager renewals helper exists.
		if ( ! class_exists( 'WP_Job_Manager_Helper_Renewals' ) ) {
			return;
		}

		// Only proceed if this is a subscription or the job can be renewed.
		if ( ! $is_listing_subscription && ! \WP_Job_Manager_Helper_Renewals::job_can_be_renewed( $job_id ) ) {
			return;
		}

		$job_post = get_post( $job_id );
		if ( ! $job_post ) {
			return; // Safety: invalid job ID.
		}

		// Renew the job listing using WPJM helper.
		\WP_Job_Manager_Helper_Renewals::renew_job_listing( $job_post );

		// Update job meta to track package and renewal.
		update_post_meta( $job_id, '_user_package_id', $user_package_id );
		update_post_meta( $job_id, '_package_id', $product_id );
		delete_post_meta( $job_id, '_renewal_pending_product_id', $product_id );
		update_post_meta( $job_id, '_renewal_completed_product_id', $product_id );

		// Increase the submission count on the user package if applicable.
		if ( apply_filters( 'job_manager_job_listing_affects_package_count', true, $job_id ) ) {
			self::increase_submission_package_count( $user_package_id, 'job_submission_package' );
		}
	}
}
