<?php

namespace WPJMSF\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WorkScout {

	/**
	 * @var \WPJMSF\Themes
	 */
	public $themes;

	/**
	 * WorkScout constructor.
	 *
	 * @param $themes \WPJMSF\Themes
	 */
	public function __construct( $themes ) {
		$this->themes = $themes;
		add_filter( 'job_manager_ajax_filters_ajax_data', array( $this, 'job_manager_ajax_filters_ajax_data' ) );
	}

	/**
	 * Add WorkScout Params to AJAX Data
	 *
	 * @param $data
	 *
	 * @return mixed
	 * @since 1.1.26
	 *
	 */
	public function job_manager_ajax_filters_ajax_data( $data ) {
		$new_data = array(
			'single_job_text' => esc_html__( 'job offer', 'workscout', 'wp-job-manager-search-and-filtering' ),
			'plural_job_text' => esc_html__( 'job offers', 'workscout', 'wp-job-manager-search-and-filtering' ),
		);

		return array_merge( $data, $new_data );
	}

	/**
	 * Add Custom Template Path for WorkScout templates
	 *
	 * This method should be called by \WPJMSF\Themes\WorkScout\Job or \WPJMSF\Themes\WorkScout\Resume class
	 * ONLY when that type is enabled (to prevent overriding template when not enabled).
	 *
	 * @since 1.0.0
	 *
	 */
	public static function add_template_path_filter() {
		add_filter( 'workscout_core_template_paths', array( __CLASS__, 'template_paths' ) );
	}

	/**
	 * Return Custom Templates for Template Paths
	 *
	 * @param $paths
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public static function template_paths( $paths ) {
		// Set at priority 5 to be top priority above theme/plugin priority
		$paths[5] = WPJM_SEARCH_FILTERING_PATH . '/templates/workscout/';
		return $paths;
	}
}
