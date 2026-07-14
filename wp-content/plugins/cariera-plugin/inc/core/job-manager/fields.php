<?php

namespace Cariera_Core\Core\Job_Manager;

use Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Fields extends Job_Manager {

	/**
	 * Constructor
	 *
	 * @since 1.4.5
	 */
	public function __construct() {
		add_filter( 'submit_job_form_fields', [ $this, 'frontend_wpjm_extra_fields' ] );
		add_action( 'job_manager_update_job_data', [ $this, 'wpjm_update_job_data' ], 10, 2 );
		add_filter( 'job_manager_job_listing_data_fields', [ $this, 'admin_wpjm_extra_fields' ] );

		// Salary Schema Data.
		add_filter( 'wpjm_get_job_listing_structured_data', [ $this, 'salary_field_structured_data' ], 10, 2 );
		add_filter( 'wpjm_get_job_listing_structured_data', [ $this, 'location_structured_data' ], 10, 2 );
	}

	/**
	 * Adding Extra Job Fields - Front-End.
	 *
	 * @since   1.0.0
	 * @version 1.5.3
	 *
	 * @param array $fields
	 */
	public function frontend_wpjm_extra_fields( $fields ) {

		$fields['job']['apply_link'] = [
			'label'       => esc_html__( 'External "Apply for Job" link', 'cariera-core' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => esc_html__( 'http://', 'cariera-core' ),
			'priority'    => 6,
		];

		if ( get_option( 'cariera_job_manager_enable_qualification' ) ) {
			$fields['job']['job_listing_qualification'] = [
				'label'       => esc_html__( 'Job Qualification', 'cariera-core' ),
				'type'        => 'term-multiselect',
				'taxonomy'    => 'job_listing_qualification',
				'required'    => false,
				'placeholder' => esc_html__( 'Choose a job qualification', 'cariera-core' ),
				'priority'    => 7,
			];
		}

		if ( get_option( 'cariera_job_manager_enable_career_level' ) ) {
			$fields['job']['job_listing_career_level'] = [
				'label'       => esc_html__( 'Job Career Level', 'cariera-core' ),
				'type'        => 'term-select',
				'taxonomy'    => 'job_listing_career_level',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Choose a career level', 'cariera-core' ),
				'priority'    => 8,
			];
		}

		if ( get_option( 'cariera_job_manager_enable_experience' ) ) {
			$fields['job']['job_listing_experience'] = [
				'label'       => esc_html__( 'Job Experience', 'cariera-core' ),
				'type'        => 'term-select',
				'taxonomy'    => 'job_listing_experience',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Choose a job experience', 'cariera-core' ),
				'priority'    => 9,
			];
		}

		// If true Enable Rate fields.
		if ( get_option( 'cariera_enable_filter_rate' ) ) {
			$fields['job']['rate_min'] = [
				'label'       => esc_html__( 'Minimum rate/h', 'cariera-core' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 20', 'cariera-core' ),
				'priority'    => 10,
			];
			$fields['job']['rate_max'] = [
				'label'       => esc_html__( 'Maximum rate/h', 'cariera-core' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 50', 'cariera-core' ),
				'priority'    => 10.1,
			];
		}

		// If true Enable Salary fields.
		if ( get_option( 'cariera_enable_filter_salary' ) ) {
			$fields['job']['salary_min'] = [
				'label'       => esc_html__( 'Minimum Salary', 'cariera-core' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 20000', 'cariera-core' ),
				'priority'    => 11,
			];
			$fields['job']['salary_max'] = [
				'label'       => esc_html__( 'Maximum Salary', 'cariera-core' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 50000', 'cariera-core' ),
				'priority'    => 11.1,
			];
		}

		$fields['job']['hours']           = [
			'label'       => esc_html__( 'Hours per week', 'cariera-core' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => esc_html__( 'e.g. 72', 'cariera-core' ),
			'priority'    => 12,
		];
		$fields['job']['job_cover_image'] = [
			'label'              => esc_html__( 'Cover Image', 'cariera-core' ),
			'type'               => 'file',
			'required'           => false,
			'description'        => esc_html__( 'The cover image size should be at least 1600x200px', 'cariera-core' ),
			'priority'           => 13,
			'ajax'               => true,
			'multiple'           => false,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
		];

		return $fields;
	}

	/**
	 * Save the extra frontend fields.
	 *
	 * @since   1.0.0
	 * @version 1.5.3
	 *
	 * @param int   $job_id
	 * @param array $values
	 */
	public function wpjm_update_job_data( $job_id, $values ) {
		if ( isset( $values['job']['rate_min'] ) ) {
			update_post_meta( $job_id, '_rate_min', $values['job']['rate_min'] );
		}
		if ( isset( $values['job']['rate_max'] ) ) {
			update_post_meta( $job_id, '_rate_max', $values['job']['rate_max'] );
		}
		if ( isset( $values['job']['salary_min'] ) ) {
			update_post_meta( $job_id, '_salary_min', $values['job']['salary_min'] );
		}
		if ( isset( $values['job']['salary_max'] ) ) {
			update_post_meta( $job_id, '_salary_max', $values['job']['salary_max'] );
		}
		if ( isset( $values['job']['hours'] ) ) {
			update_post_meta( $job_id, '_hours', $values['job']['hours'] );
		}
		if ( isset( $values['job']['apply_link'] ) ) {
			update_post_meta( $job_id, '_apply_link', $values['job']['apply_link'] );
		}
		if ( isset( $values['job']['job_cover_image'] ) ) {
			update_post_meta( $job_id, '_job_cover_image', $values['job']['job_cover_image'] );
		}
	}

	/**
	 * Adding Extra Job Fields - Back-End.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields
	 */
	public function admin_wpjm_extra_fields( $fields ) {

		$fields['_hours'] = [
			'label'        => esc_html__( 'Hours per week', 'cariera-core' ),
			'type'         => 'text',
			'priority'     => 11,
			'placeholder'  => esc_html( 'e.g. 72' ),
			'description'  => '',
			'show_in_rest' => true,
		];

		// If true Enable Rate fields.
		if ( get_option( 'cariera_enable_filter_rate' ) ) {
			$fields['_rate_min'] = [
				'label'        => esc_html__( 'Rate/h (minimum)', 'cariera-core' ),
				'type'         => 'text',
				'priority'     => 12,
				'placeholder'  => 'e.g. 20',
				'description'  => esc_html__( 'Put just a number', 'cariera-core' ),
				'show_in_rest' => true,
			];
			$fields['_rate_max'] = [
				'label'        => esc_html__( 'Rate/h (maximum) ', 'cariera-core' ),
				'type'         => 'text',
				'priority'     => 12,
				'placeholder'  => esc_html__( 'e.g. 20', 'cariera-core' ),
				'description'  => esc_html__( 'Put just a number - you can leave it empty and set only minimum rate value ', 'cariera-core' ),
				'show_in_rest' => true,
			];
		}

		// If true Enable Salary fields.
		if ( get_option( 'cariera_enable_filter_salary' ) ) {
			$fields['_salary_min'] = [
				'label'        => esc_html__( 'Salary min', 'cariera-core' ),
				'type'         => 'text',
				'priority'     => 12,
				'placeholder'  => esc_html__( 'e.g. 20.000', 'cariera-core' ),
				'description'  => esc_html__( 'Enter the min Salary of the Job', 'cariera-core' ),
				'show_in_rest' => true,
			];
			$fields['_salary_max'] = [
				'label'        => esc_html__( 'Salary max', 'cariera-core' ),
				'type'         => 'text',
				'priority'     => 12,
				'placeholder'  => esc_html__( 'e.g. 50.000', 'cariera-core' ),
				'description'  => esc_html__( 'Maximum of salary range you can offer - you can leave it empty and set only minimum salary ', 'cariera-core' ),
				'show_in_rest' => true,
			];
		}

		$fields['_apply_link'] = [
			'label'        => esc_html__( 'External "Apply for Job" link', 'cariera-core' ),
			'type'         => 'text',
			'priority'     => 5,
			'placeholder'  => esc_html( 'http://' ),
			'description'  => esc_html__( 'If the job applying is done on external page, here\'s the place to put link to that page - it will be used instead of standard Apply form', 'cariera-core' ),
			'show_in_rest' => true,
		];

		$fields['_job_cover_image'] = [
			'label'              => esc_html__( 'Job Cover Image', 'cariera-core' ),
			'type'               => 'file',
			'priority'           => 15,
			'description'        => '',
			'multiple'           => false,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
			'show_in_rest'       => true,
		];

		return $fields;
	}

	/**
	 * Adding Salary Field to Structured Data (schema.org markup)
	 *
	 * @since   1.4.6
	 * @version 1.8.1
	 *
	 * @param array $data
	 * @param mixed $post
	 */
	public function salary_field_structured_data( $data, $post ) {
		if ( ! get_option( 'cariera_enable_filter_salary' ) ) {
			return $data;
		}

		if ( $post && $post->ID ) {
			$salary = get_post_meta( $post->ID, '_salary_min', true );

			// Here you can add values that would be considered "not a salary" to skip output for.
			$no_salary_values = [ 'Not Disclosed', 'N/A', 'TBD' ];

			// Don't add anything if empty value, or value equals something above in no salary values.
			if ( empty( $salary ) || in_array( strtolower( $salary ), array_map( 'strtolower', $no_salary_values ), true ) ) {
				return $data;
			}

			// Determine float value, stripping all non-alphanumeric characters.
			$salary_float_val = (float) filter_var( $salary, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

			if ( ! empty( $salary_float_val ) ) {
				// @see https://schema.org/JobPosting
				// Simple value:
				// $data['baseSalary'] = $salary_float_val;

				// Or using Google's Structured Data format
				// @see https://developers.google.com/search/docs/data-types/job-posting
				// This is the format Google really wants it in, so you should customize this yourself
				// to match your setup and configuration.
				$data['baseSalary'] = [
					'@type'    => 'MonetaryAmount',
					'currency' => get_option( 'cariera_currency_setting' ),
					'value'    => [
						'@type'    => 'QuantitativeValue',
						'value'    => $salary_float_val,
						// HOUR, DAY, WEEK, MONTH, or YEAR.
						'unitText' => 'YEAR',
					],
				];
			}
		}

		return $data;
	}

	/**
	 * Expand structured data with address fields.
	 *
	 * @since 1.8.3
	 *
	 * @param array   $data Existing structured data array.
	 * @param WP_Post $post The job listing post object.
	 */
	public function location_structured_data( $data, $post ) {
		$street_address   = get_post_meta( $post->ID, 'geolocation_street', true );
		$address_region   = get_post_meta( $post->ID, 'geolocation_state_short', true );
		$postal_code      = get_post_meta( $post->ID, 'geolocation_postcode', true );
		$address_locality = get_post_meta( $post->ID, 'geolocation_formatted_address', true );
		$address_country  = get_post_meta( $post->ID, 'geolocation_country_long', true );

		if ( ! empty( $street_address ) ) {
			$data['jobLocation']['address']['streetAddress'] = $street_address;
		}
		if ( ! empty( $address_region ) ) {
			$data['jobLocation']['address']['addressRegion'] = $address_region;
		}
		if ( ! empty( $postal_code ) ) {
			$data['jobLocation']['address']['postalCode'] = $postal_code;
		}
		if ( ! empty( $address_locality ) ) {
			$data['jobLocation']['address']['addressLocality'] = $address_locality;
		}
		if ( ! empty( $address_country ) ) {
			$data['jobLocation']['address']['addressCountry'] = $address_country;
		}

		return $data;
	}
}
