<?php

namespace Cariera_Packages\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Resume {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		\Cariera_Packages\Integration\Resume\Submission::instance();
		\Cariera_Packages\Integration\Resume\Promotion::instance();
		\Cariera_Packages\Integration\Resume\View::instance();

		// Add pending_payment status to valid submit statuses.
		add_filter( 'resume_manager_valid_submit_resume_statuses', [ '\Cariera_Packages\Helpers', 'add_pending_payment_status' ] );

		// Add pending_payment status to dashboard args.
		add_filter( 'cariera_addons_get_dashboard_resumes_args', [ $this, 'filter_dashboard_resumes_args' ] );
	}

	/**
	 * Filter resume dashboard args to also list pending_payment status.
	 *
	 * @since 0.9.20
	 *
	 * @param array $resume_dashboard_args Job dashboard args to filter.
	 */
	public function filter_dashboard_resumes_args( $resume_dashboard_args ) {
		$resume_dashboard_args['post_status'][] = 'pending_payment';

		return $resume_dashboard_args;
	}
}
