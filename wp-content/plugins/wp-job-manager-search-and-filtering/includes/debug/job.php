<?php

namespace WPJMSF\Debug;
use WPJMSF\Debug;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Job
 *
 * @package WPJMSF
 *
 * @since   1.1.14
 *
 */
class Job extends Debug {

	/**
	 * Add Hooks for Debug Information
	 *
	 * @since 1.1.14
	 *
	 */
	public function add_hooks() {
		add_filter( 'job_manager_get_listings_result', array( $this, 'listings_ajax_response' ), 99999999999999999, 2 );
		add_action( 'after_get_job_listings', array( $this, 'set_last_query' ), 0, 2 );
		add_action( 'search_and_filtering_job_geolocation_sql_query', array( $this, 'set_geolocation_query' ), 99999999999999999 );
	}

	/**
	 * Disable Cache
	 *
	 * @since 1.1.14
	 *
	 */
	public function disable_cache() {
		add_filter( 'get_job_listings_cache_results', '__return_false' );
	}
}