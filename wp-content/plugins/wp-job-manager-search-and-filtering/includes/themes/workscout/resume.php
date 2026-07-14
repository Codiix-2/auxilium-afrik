<?php

namespace WPJMSF\Themes\WorkScout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Resume extends \WPJMSF\Theme {

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
		';

		return $css;
	}

	/**
	 * Constructor when type is Enabled
	 *
	 * @since 1.0.0
	 *
	 */
	public function construct_enabled() {
		add_action( 'wp_enqueue_scripts', array( $this, 'deregister_filtering' ), 9999999 );
		/**
		 * Add template path filter only right before query for sidebar-resumes template
		 */
		add_action( 'workscout_core_get_template_part_sidebar-resumes', array( '\WPJMSF\Themes\WorkScout', 'add_template_path_filter' ) );
	}

	/**
	 * Deregister WorkScout Job Custom Filtering JS
	 *
	 * @since 1.0.0
	 *
	 */
	public function deregister_filtering() {

		if ( wp_script_is( 'workscout-wp-resume-manager-ajax-filters', 'registered' ) ) {
			wp_deregister_script( 'workscout-wp-resume-manager-ajax-filters' );
		} elseif ( wp_script_is( 'workscout-wp-resume-manager-ajax-filters', 'enqueued' ) ) {
			wp_dequeue_script( 'workscout-wp-resume-manager-ajax-filters' );
		}

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
	 * WorkScout Specific Resume Output Locations
	 *
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function output_locations() {

		$locations = array(
			'workscout_resume_filters' => array(
				'label'     => __( 'WorkScout - Resume Listings Page', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label' => __( 'Resumes Sidebar', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_filters_workscout_resume_sidebar'
					),
					array(
						'label'      => __( 'Above Resume Results', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_filters_workscout_resume_above_results',
						'add_filter' => array(
							'hook'     => 'workscout_resumes_show_search_keywords',
							'callback' => array( $this, 'show_search_keywords' )
						)
					)
				)
			)
		);

		return $locations;
	}

	/**
	 * search_and_filtering_filters_workscout_resume_above_results
	 *
	 * @param $show
	 *
	 * @return boolean
	 * @since 1.0.0
	 *
	 */
	public function show_search_keywords( $show ) {

		/**
		 * This will trigger the output for this section, if one exists, otherwise will return false
		 */
		if ( $this->type->output->output_sections_by_location( 'search_and_filtering_filters_workscout_resume_above_results' ) ) {
			/**
			 * If we do have custom section for this area, return false to prevent WorkScout from outputting normal search keywords field
			 */
			return false;
		}

		return false;
	}
}
