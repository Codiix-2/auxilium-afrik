<?php

namespace WPJMSF\Themes\Cariera;

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
	 * Constructor when type is Enabled
	 *
	 * @since 1.0.0
	 *
	 */
	public function construct_enabled() {
//		add_action( 'search_and_filtering_listings_end_after_job', array( $this, 'job_listings_end' ) );
//		add_action( 'after_setup_theme', array( $this, 'deregister_filtering' ) );
		add_filter( 'search_and_filtering_add_meta_queries_resume_meta_key_mappings', array( $this, 'meta_key_mappings' ) );
		add_filter( 'search_and_filtering_dont_override_default_resume_templates', array( $this, 'exclude_templates' ), 10, 2 );
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

		if ( $template_name == 'job-resume-search-job-form.php' ) {
			$exclude = true;
		}

		return $exclude;
	}

	public function custom_css() {
		$css = '.wpjmsf-select-field-wrapper .select2-container .select2-selection--single .select2-selection__clear { float: none; margin-right: 5px; display: inline-block; }';
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
			'cariera_resume_filters' => array(
				'label'     => __( 'Cariera - Resume Filters', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label'      => __( 'Elementor Homepage Standard', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_resume_filters_cariera_homepage',
						'add_filter' => array(
							'hook'     => 'elementor/widget/render_content',
							'callback' => array( $this, 'homepage_widget' ),
							'args'     => 2
						)
					),
					array(
						'label'      => __( 'Elementor Homepage Boxed', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_resume_filters_cariera_homepage_boxed',
						'add_filter' => array(
							'hook'     => 'elementor/widget/render_content',
							'callback' => array( $this, 'homepage_boxed_widget' ),
							'args'     => 2
						)
					),
					array(
						'label' => __( 'Job/Resumes Homepage Tabs (Resumes)', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_cariera_job_resume_tabs_resume_form',
						'template' => 'job-resume-search-resume-form.php'
					)
				)
			)
		);

		return $locations;
	}

	/**
	 * Custom Meta Key Mappings
	 *
	 * @param $mappings
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function meta_key_mappings( $mappings ) {
		// _candidate_rate field is actually stored under '_rate' meta key

		$maps = array(
			'_candidate_rate' => '_rate',
			'_candidate_languages' => '_languages',
			'_candidate_featured_image' => '_featured_image',
			'_candidate_facebook' => '_facebook',
			'_candidate_twitter' => '_twitter',
			'_candidate_linkedin' => '_linkedin',
			'_candidate_instagram' => '_instagram',
			'_candidate_youtube' => '_youtube',
		);

		return array_merge( $mappings, $maps );
	}

	/**
	 * Check if Cariera 1.7.4+ is being used which uses new search widgets
	 *
	 * @param        $that
	 * @param string $name Elementor widget name (listing_search for search widget, listing_search_box for search boxed widget)
	 *
	 * @return bool
	 * @since 1.4.4
	 */
	private function is_new_search_widget( $that, $name = 'listing_search' ) {

		if ( $that->get_name() !== $name ) {
			return false;
		}

		$settings = $that->get_settings();

		return isset( $settings['listing_search'] ) && 'resume' === $settings['listing_search'];
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

		if ( $that->get_name() !== 'resume_search' && ! $this->is_new_search_widget( $that ) ) {
			return $content;
		}

		if ( $output = $this->type->output->output_sections_by_location( 'search_and_filtering_resume_filters_cariera_homepage', true ) ) {
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
	 * @since 1.4.4
	 *
	 */
	public function homepage_boxed_widget( $content, $that ) {

		if ( $that->get_name() !== 'resume_search_box' && ! $this->is_new_search_widget( $that, 'listing_search_box' ) ) {
			return $content;
		}

		if ( $output = $this->type->output->output_sections_by_location( 'search_and_filtering_resume_filters_cariera_homepage_boxed', true ) ) {
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
//
//	/**
//	 * Deregister Cariera Custom Get Listings
//	 *
//	 * Because Cariera theme uses it's own method for handling the output and searching of jobs, instead of filtering
//	 * on the native integration, we have to remove the theme's custom function and add back the default WP Job Manager
//	 * one so this plugin works correctly.
//	 *
//	 * @since 1.0.0
//	 *
//	 */
//	public function deregister_filtering() {
//
//		if ( class_exists( 'Cariera_Resume_Extender' ) ) {
//
//			remove_action( 'wp_print_scripts', 'cariera_job_deregister', 100 );
//
//			remove_class_action( 'job_manager_ajax_get_listings', 'Cariera_Job_Extender', 'cariera_job_manager_get_listings_result', 10 );
//
//			add_action( 'job_manager_ajax_get_listings', array( \WP_Job_Manager_Ajax::instance(), 'get_listings' ) );
//
//		}
//
//	}
}
