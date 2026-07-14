<?php

namespace Cariera\Integrations\WPJM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Search_Filtering {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		add_action( 'job_manager_sf_job_filters_before', [ $this, 'search_form_css' ] );
		add_action( 'resume_manager_sf_resume_filters_before', [ $this, 'search_form_css' ] );
	}

	/**
	 * Enqueue styling for WPJM Search & Filtering forms
	 *
	 * @since 1.9.4
	 */
	public function search_form_css() {
		wp_enqueue_style( 'cariera-wpjm-search-forms' );
	}
}
