<?php

namespace WPJMSF\Themes\WorkScout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job extends \WPJMSF\Theme {

	/**
	 * Theme constructor.
	 */
	public function construct() {

	}

	/**
	 * Custom CSS to Output
	 *
	 *
	 * @return string
	 * @since 0.1.1
	 *
	 */
	public function custom_css() {

		// Required to adjust the custom checkbox handling from WorkScout
		$css = '
		input[type=checkbox].wpjmsf-field-checkbox-input + label {
			padding-left: 0px;
		}
		.wpjmsf-slider-field-wrapper.checkboxes label:before {
			position: relative;
		}
		.section-wrapper.checkboxes label:before {
			margin-top: 4px;
		}
		.select2-container .select2-selection--single .select2-selection__clear {
			display: inline-block;
		}
		';
		return $css;
	}

	/**
	 * Constructor when type is Enabled
	 *
	 * @since 1.0.0
	 *
	 */
	public function wp_enabled() {
		add_action( 'wp_enqueue_scripts', array( $this, 'deregister_filtering' ), 9999999 );
		/**
		 * Add template path filter only right before query for sidebar-resumes template
		 */
		add_action( 'workscout_core_get_template_part_sidebar-jobs', array( '\WPJMSF\Themes\WorkScout', 'add_template_path_filter' ) );
		add_action( 'job_manager_sf_job_filters_after', array( $this, 'add_loader' ) );
	}

	/**
	 * Add Listings Loader to Output
	 *
	 * For some reason when show_filters="true" in the jobs shortcode, it uses the template files which doesn't
	 * add the listings loader.  To deal with this we just output it ourselves.
	 *
	 * @param $atts
	 *
	 * @since 1.1.0
	 *
	 */
	public function add_loader( $atts ) {
		echo '<div class="listings-loader"><div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>';
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

	/**
	 * Deregister WorkScout Job Custom Filtering JS
	 *
	 * @since 1.0.0
	 *
	 */
	public function deregister_filtering() {

		if ( wp_script_is( 'workscout-wp-job-manager-ajax-filters', 'registered' ) ) {
			wp_deregister_script( 'workscout-wp-job-manager-ajax-filters' );
		} elseif ( wp_script_is( 'workscout-wp-job-manager-ajax-filters', 'enqueued' ) ) {
			wp_dequeue_script( 'workscout-wp-job-manager-ajax-filters' );
		}

	}

	/**
	 * Add Custom WorkScout Field Types
	 *
	 *
	 * @param $groups
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function field_type_groups( $groups ) {

//		$groups['workscout'] = array(
//			'label'  => __( 'WorkScout Job Custom Field Types' ),
//			'fields' => array(
//				'listify_search_radius_slider' => array(
//					'label'    => __( 'Search Radius Slider' ),
//					'custom'   => true,
//					'inputs'   => array(
//						'search_radius',
//						'search_lat',
//						'search_lng'
//					),
//					'supports' => array( 'no_search_source' )
//				),
//				'workscout_salary' => array(
//					'label'    => __( 'WorkScout Salary Slider' ),
//					'custom'   => true,
//					'supports' => array( 'no_search_source' )
//				),
//			)
//		);

		return $groups;
	}

	/**
	 * WorkScout Specific Job Output Locations
	 *
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function output_locations() {

		$locations = array(
			'workscout_job_filters' => array(
				'label'     => __( 'WorkScout - Job Listings Page', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label'      => __( 'Jobs Sidebar', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_filters_workscout_job_sidebar'
					),
					array(
						'label'      => __( 'Above Job Results', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_filters_workscout_job_above_results',
						'add_filter' => array(
							'hook'     => 'workscout_jobs_show_search_keywords',
							'callback' => array( $this, 'show_search_keywords' )
						)
					)
				)
			),
			'workscout_job_filters_home' => array(
				'label'     => __( 'WorkScout - Job Homepage', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label'      => __( 'Homepage Template Top', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_filters_workscout_job_homepage',
						'add_filter' => array(
							'hook'     => 'workscout_template_home_job_intro_banner_search_form',
							'callback' => array( $this, 'show_homepage_template' )
						)
					),
					array(
						'label'      => __( 'Homepage Template Advanced', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_filters_workscout_job_homepage_advanced',
						'add_filter' => array(
							'hook'     => 'workscout_template_home_job_intro_banner_search_form_advanced',
							'callback' => array( $this, 'show_homepage_template_advanced' )
						)
					)
				)
			)
		);

		return $locations;
	}

	/**
	 * Show Homepage Template?
	 *
	 * @param $show
	 *
	 * @return false
	 * @since 1.0.0
	 *
	 */
	public function show_homepage_template( $show ) {

		if ( $this->type->output->is_enabled() && $this->type->output->output_sections_by_location( 'search_and_filtering_filters_workscout_job_homepage' ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Show Homepage Template?
	 *
	 * @param $show
	 *
	 * @return false
	 * @since 1.0.0
	 *
	 */
	public function show_homepage_template_advanced( $show ) {

		if ( $this->type->output->is_enabled() && $this->type->output->output_sections_by_location( 'workscout_template_home_job_intro_banner_search_form_advanced' ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * search_and_filtering_filters_workscout_job_above_results
	 *
	 * @param $show
	 *
	 * @return false
	 * @since 1.0.0
	 *
	 */
	public function show_search_keywords( $show ) {

		/**
		 * This will trigger the output for this section, if once exists, otherwise will return false
		 */
		if( $this->type->output->is_enabled() && $this->type->output->output_sections_by_location( 'search_and_filtering_filters_workscout_job_above_results' ) ){
			/**
			 * If we do have custom section for this area, return false to prevent WorkScout from outputting normal search keywords field
			 */
			return false;
		}

		return $show;
	}
}
