<?php

namespace WPJMSF\Themes\Cariera;

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
	 * Constructor when type is Enabled
	 *
	 * @since 1.0.0
	 *
	 */
	public function construct_enabled() {
		add_action( 'search_and_filtering_listings_end_after_job', array( $this, 'job_listings_end' ) );
		add_action( 'after_setup_theme', array( $this, 'deregister_filtering' ) );
		add_filter( 'search_and_filtering_dont_override_default_job_templates', array( $this, 'exclude_templates' ), 10, 2 );
	}

	/**
	 * Exclude Templates from Overide
	 *
	 * We check template names to see if they have "job" or "resume" in them when we override the default templates. Because cariera template names have both, we need to exclude the opposite in case Job or Resume is not enabled.
	 *
	 * @param $exclude
	 * @param $template_name
	 *
	 * @return mixed|true
	 * @since 1.2.1
	 *
	 */
	public function exclude_templates( $exclude, $template_name ) {
		if ( $template_name == 'job-resume-search-resume-form.php' ) {
			$exclude = true;
		}
		return $exclude;
	}

	public function custom_css() {
		$css = '.wpjmsf-select-field-wrapper .select2-container .select2-selection--single .select2-selection__clear { float: none; margin-right: 5px; display: inline-block; }';
		return $css;
	}

	/**
	 * Match Existing Theme Output Template
	 *
	 * @since 1.0.0
	 *
	 */
	public function job_listings_end() {
		echo '<div class="listing-loader"><div></div></div>';
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
			'cariera_job_filters' => array(
				'label'     => __( 'Cariera - Job Filters', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label'      => __( 'Elementor Homepage Standard', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_job_filters_cariera_homepage',
						'add_filter' => array(
							'hook'     => 'elementor/widget/render_content',
							'callback' => array( $this, 'homepage_widget' ),
							'args' => 2
						)
					),
					array(
						'label'      => __( 'Elementor Homepage Boxed', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_job_filters_cariera_homepage_boxed',
						'add_filter' => array(
							'hook'     => 'elementor/widget/render_content',
							'callback' => array( $this, 'homepage_boxed_widget' ),
							'args' => 2
						)
					),
					array(
						'label'      => __( 'Job/Resumes Homepage Tabs (Jobs)', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_cariera_job_resume_tabs_job_form',
						'template'   => 'job-resume-search-job-form.php',
					)
				)
			)
		);

		return $locations;
	}

	/**
	 * Check if Cariera 1.7.4+ is being used which uses new search widgets
	 *
	 * @param        $that
	 * @param string $name  Elementor widget name (listing_search for search widget, listing_search_box for search boxed widget)
	 *
	 * @return bool
	 * @since 1.4.4
	 */
	private function is_new_search_widget( $that, $name = 'listing_search' ) {

		if ( $that->get_name() !== $name ) {
			return false;
		}

		$settings = $that->get_settings();
		return isset( $settings['listing_search'] ) && 'job_listing' === $settings['listing_search'];
	}

	/**
	 * Homepage Widget Output
	 *
	 * @param $content
	 * @param $that
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function homepage_widget( $content, $that ) {

		if( $that->get_name() !== 'job_search' && ! $this->is_new_search_widget( $that ) ) {
			return $content;
		}

		if ( $output = $this->type->output->output_sections_by_location( 'search_and_filtering_job_filters_cariera_homepage', true ) ) {
			return $output;
		}

		return $content;
	}

	/**
	 * Homepage Boxes Widget Output
	 *
	 * @param $content
	 * @param $that
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function homepage_boxed_widget( $content, $that ) {
		if ( $that->get_name() !== 'job_search_box' && ! $this->is_new_search_widget( $that, 'listing_search_box' ) ) {
			return $content;
		}

		if ( $output = $this->type->output->output_sections_by_location( 'search_and_filtering_job_filters_cariera_homepage_boxed', true ) ) {
			return $output;
		}

		return $content;
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
	 * Deregister Cariera Custom Get Listings
	 *
	 * Because Cariera theme uses it's own method for handling the output and searching of jobs, instead of filtering
	 * on the native integration, we have to remove the theme's custom function and add back the default WP Job Manager
	 * one so this plugin works correctly.
	 *
	 * @since 1.0.0
	 *
	 */
	public function deregister_filtering() {

		$version = \WPJM_Search_Filtering::get_theme_name(true, false, 'version' );

		if ( class_exists( 'Cariera_Job_Extender' ) ) {

			remove_action( 'wp_print_scripts', 'cariera_job_deregister', 100 );
			remove_class_action( 'job_manager_ajax_get_listings', 'Cariera_Job_Extender', 'cariera_job_manager_get_listings_result', 10 );

			if( version_compare( '1.4.8', $version, '==' ) ){
				remove_class_action( 'job_manager_get_listings_result', 'Cariera_Job_Extender', 'get_listings_result', 10 );
			}

			add_action( 'job_manager_ajax_get_listings', array( \WP_Job_Manager_Ajax::instance(), 'get_listings' ) );

		} elseif( class_exists( '\Cariera_Core\Core\Job_Manager\Jobs_Extender' ) ){
			// Cariera 1.7.2+
			remove_class_action( 'job_manager_get_listings_result', '\Cariera_Core\Core\Job_Manager\Jobs_Extender', 'get_listings_result', 10 );
			add_action( 'job_manager_ajax_get_listings', array( \WP_Job_Manager_Ajax::instance(), 'get_listings' ) );

		}

	}
}
