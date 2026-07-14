<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_Submission {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'submit_job_form_fields', [ $this, 'application_form_field' ] );
		add_filter( 'job_manager_job_listing_data_fields', [ $this, 'applications_admin_fields' ] );
	}

	/**
	 * Add Application Form to Admin fields
	 *
	 * @since   0.9.3
	 * @version 1.0.6
	 *
	 * @param array $fields
	 */
	public function applications_admin_fields( $fields = [] ) {
		if ( ! is_admin() ) {
			return $fields;
		}

		global $post;

		// Check if post exists and has the correct post type.
		if ( ! isset( $post ) || get_post_type( $post ) !== \WP_Job_Manager_Post_Types::PT_LISTING ) {
			return $fields;
		}

		$form_field = get_submit_job_application_form_field();
		if ( ! empty( $form_field ) ) {
			$fields['_application_form']             = $form_field;
			$fields['_application_form']['priority'] = 3;
		}

		return $fields;
	}

	/**
	 * Add the job deadline field to the submission form
	 *
	 * @since 0.9.3
	 *
	 * @param array $fields
	 */
	public function application_form_field( $fields = [] ) {
		$form_field = get_submit_job_application_form_field();

		/**
		 * Filters whether to hide the application form dropdown.
		 *
		 * @param bool $show Whether to hide or not.
		 */
		if ( ! empty( $form_field ) && ! apply_filters( 'job_application_hide_form_fields_dropdown', false ) ) {
			$fields['job']['application_form']             = $form_field;
			$fields['job']['application_form']['required'] = true;
			$fields['job']['application_form']['priority'] = 7;
		}

		return $fields;
	}
}
