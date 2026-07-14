<?php

namespace Cariera_Core\Core\Job_Manager;

use Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings extends Job_Manager {

	/**
	 * Constructor function.
	 */
	public function __construct() {
		add_filter( 'job_manager_settings', [ $this, 'settings' ] );
	}

	/**
	 * Adds Settings for Job Manager Options
	 *
	 * @since   1.0.0
	 * @version 1.8.9
	 *
	 * @param array $settings
	 */
	public function settings( $settings = [] ) {

		$settings['job_listings'][1][] = [
			'name'     => 'cariera_enable_filter_salary',
			'std'      => '1',
			'label'    => esc_html__( 'Cariera Salary', 'cariera-core' ),
			'cb_label' => esc_html__( 'Enable listing salary', 'cariera-core' ),
			'desc'     => esc_html__( 'Enabling this option will show a salary range filter in sidebar on Jobs page and salary fields on Job posting. (custom salary option)', 'cariera-core' ),
			'type'     => 'checkbox',
		];
		$settings['job_listings'][1][] = [
			'name'     => 'cariera_enable_filter_rate',
			'std'      => '1',
			'label'    => esc_html__( 'Cariera Rates', 'cariera-core' ),
			'cb_label' => esc_html__( 'Enable listing rates', 'cariera-core' ),
			'desc'     => esc_html__( 'Enabling this option will show a rate range filter in sidebar on Jobs page and rate fields on Job posting.', 'cariera-core' ),
			'type'     => 'checkbox',
		];
		$settings['job_listings'][1][] = [
			'name'       => 'cariera_job_manager_enable_career_level',
			'std'        => '1',
			'label'      => esc_html__( 'Career Level', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Enable listing career level', 'cariera-core' ),
			'desc'       => esc_html__( 'This lets users select from a list of career level when submitting a job. Note: an admin has to create career level before site users can select them.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['job_listings'][1][] = [
			'name'       => 'cariera_job_manager_enable_experience',
			'std'        => '1',
			'label'      => esc_html__( 'Experience', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Enable listing experience', 'cariera-core' ),
			'desc'       => esc_html__( 'This lets users select from a list of experience when submitting a job. Note: an admin has to create experience before site users can select them.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['job_listings'][1][] = [
			'name'       => 'cariera_job_manager_enable_qualification',
			'std'        => '1',
			'label'      => esc_html__( 'Qualification', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Enable listing qualification', 'cariera-core' ),
			'desc'       => esc_html__( 'This lets users select from a list of qualification when submitting a job. Note: an admin has to create qualification before site users can select them.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['job_listings'][1][] = [
			'name'    => 'cariera_job_manager_single_job_layout',
			'std'     => 'v1',
			'label'   => esc_html__( 'Single Job Listing Layout', 'cariera-core' ),
			'desc'    => esc_html__( 'Select the default layout version for your single job listing page.', 'cariera-core' ),
			'type'    => 'select',
			'options' => [
				'v1' => esc_html__( 'Version 1', 'cariera-core' ),
				'v2' => esc_html__( 'Version 2', 'cariera-core' ),
				'v3' => esc_html__( 'Version 3', 'cariera-core' ),
			],
		];
		$settings['job_listings'][1][] = [
			'name'     => 'cariera_job_manager_related_jobs',
			'std'      => '1',
			'label'    => esc_html__( 'Related Job Listings', 'cariera-core' ),
			'cb_label' => esc_html__( 'Enable related listings', 'cariera-core' ),
			'desc'     => esc_html__( 'Show related listings in single listing page.', 'cariera-core' ),
			'type'     => 'checkbox',
		];
		$settings['job_listings'][1][] = [
			'name'     => 'cariera_job_manager_featured_jobs',
			'std'      => '1',
			'label'    => esc_html__( 'Featured Job Listings', 'cariera-core' ),
			'cb_label' => esc_html__( 'Enable featured listings', 'cariera-core' ),
			'desc'     => esc_html__( 'Show featured listings in single listing page v1.', 'cariera-core' ),
			'type'     => 'checkbox',
		];
		// Email Setting.
		$settings['email_notifications'][1][] = [
			'name'       => 'cariera_job_manager_approved_job_notification',
			'std'        => '1',
			'label'      => esc_html__( 'Approved Job', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Approved Job Notification', 'cariera-core' ),
			'desc'       => esc_html__( 'When enabled the Employer will receive an email notification when their job get\'s approved.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];

		return $settings;
	}
}
