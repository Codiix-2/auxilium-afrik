<?php

namespace WPJMSF\Themes\Specialty;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Job
 *
 * @package WPJMSF\Themes\Specialty
 */
class Job extends \WPJMSF\Theme {

	/**
	 * Construct when type enabled
	 *
	 * @since 1.1.3
	 *
	 */
	public function construct_enabled() {
		add_filter( 'search_and_filtering_search_get_field_values', array( $this, 'search_get_field_values' ), 10, 2 );
		add_filter( 'job_manager_get_listings_args', array( $this, 'check_salary_args'), 10, 2 );
		add_filter( 'job_manager_job_listings_output', array( $this, 'check_load_more' ) );
		add_filter( 'search_and_filtering_specialty_use_part-job-filters', array( $this, 'check_part_job_filters' ) );
		add_filter( 'search_and_filtering_specialty_use_template-listing-jobs', array( $this, 'check_template_listing_jobs' ) );
	}

	/**
	 * Check if Section Configured to Output for part-job-filters.php Template
	 *
	 * @return bool
	 * @since 1.1.3
	 *
	 */
	public function check_part_job_filters() {
		return $this->type->output->has_sections_by_location( 'search_and_filtering_specialty_part-job-filters' );
	}

	/**
	 * Check if Section Configured to Output for template-listing-jobs.php Template
	 *
	 * This also checks for any output in the job hero location, as the template-listing-jobs.php file is required for that
	 * one to work.
	 *
	 * @return bool
	 * @since 1.1.3
	 *
	 */
	public function check_template_listing_jobs() {
		return $this->check_part_job_filters() || $this->type->output->has_sections_by_location( 'search_and_filtering_specialty_job_listings_sidebar' );
	}

	/**
	 * Check Load More
	 *
	 * This method is used to replace the default WPJM load more with the custom one from the theme
	 *
	 * @param $output
	 *
	 * @return mixed|string|string[]
	 * @since 1.1.3
	 *
	 */
	public function check_load_more( $output ) {

		$default = '<a class="load_more_jobs" href="#"><strong>' . esc_html__( 'Load more listings', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ) . '</strong></a>';
		$default_2 = '<a class="load_more_jobs" href="#" style="display:none;"><strong>' . esc_html__( 'Load more listings', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ) . '</strong></a>';

		$found_default = strpos( $output, $default ) !== false;
		$found_default_2 = strpos( $output, $default_2 ) !== false;

		if( $found_default || $found_default_2 ){
			$theme_version = '<div class="list-item-secondary-wrap"> <button type="button" class="btn btn-round btn-white btn-transparent btn-load-jobs load_more_jobs" style="display: none;">';
			$theme_version .= esc_html( 'Load More Jobs', 'specialty' );
			$theme_version .= '</button></div>';
			$default_replace = $found_default ? $default : $default_2;
			$output = str_replace( $default_replace, $theme_version, $output );
		}

		return $output;
	}

	/**
	 * Check Salary Args
	 *
	 * This method will check if the value passed for job_salary exists, and if so, will set the value in the args
	 * to allow the theme to handle the process of adding the query parameters to the search.
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 1.1.3
	 *
	 */
	public function check_salary_args( $args ) {

		if ( isset( $_REQUEST['wpjmsf_fields']['job_salary'] ) && ! empty( $_REQUEST['wpjmsf_fields']['job_salary'] ) && is_array( $_REQUEST['wpjmsf_fields']['job_salary'] ) ) {

			if( apply_filters( 'search_and_filtering_specialty_use_theme_salary_handling', true, $this ) ){
				if ( function_exists( 'specialty_wpjb_sanitize_salary_range' ) ) {
					$tmp_ranges = array_map( 'specialty_wpjb_sanitize_salary_range', $_REQUEST['wpjmsf_fields']['job_salary'] );
					$tmp_ranges = array_filter( $tmp_ranges );
				} else {
					$tmp_ranges = $_REQUEST['wpjmsf_fields']['job_salary'];
				}

				$args['salary_range'] = $tmp_ranges;
			}

		}

		return $args;
	}

	/**
	 * Remove job_salary from being processed by S&F plugin
	 *
	 * @param $fields
	 * @param $that
	 *
	 * @return mixed
	 * @since 1.1.3
	 *
	 */
	public function search_get_field_values( $fields, $that ) {

		if( isset( $fields['job_salary'] ) && apply_filters( 'search_and_filtering_specialty_use_theme_salary_handling', true, $this ) ){
			unset( $fields['job_salary'] );
		}

		return $fields;
	}

	/**
	 * Custom CSS
	 *
	 * @return string
	 * @since 1.1.3
	 *
	 */
	public function custom_css() {
		$css = '
		.wpjmsf-field-wrapper.ci-select { position: unset; width: unset; height: unset; } .wpjmsf-field-wrapper.ci-select:after{ display: none; }
		.wpjmsf-field-wrapper.ci-select .select2-container:after { font-family: "FontAwesome"; content: "\f107"; font-size: 20px; position: absolute; right: 12px; transform: translateY(-50%); top: 50%; color: #484848; pointer-events: none; }
		.job_listings .showing_jobs { display: none !important; }
		.list-item-secondary-wrap button.load_more_jobs { display: inline-block;  padding: 11px 38px; border-bottom: 2px solid white; }
		';
		return $css;
	}

	/**
	 * Theme Specific Output Locations
	 *
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function output_locations() {

		$locations = array(
			'specialty_job_filters' => array(
				'label'     => __( 'Specialty - Job Listings Template', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label'      => __( 'Below Hero', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_specialty_part-job-filters',
					),
					array(
						'label'      => __( 'Sidebar', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_specialty_job_listings_sidebar',
					),
				)
			)
		);

		return $locations;
	}

	/**
	 * Initialize Custom Theme JS Handling
	 *
	 * This method tells SectionGrid.vue to load the theme specific
	 * handling, which is passed through wpjmsf_theme_config
	 *
	 * @return array|bool[]
	 * @since 1.0.0
	 *
	 */
	public function theme_config() {
		return array( 'init_handling' => true );
	}
}
