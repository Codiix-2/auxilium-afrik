<?php

namespace WPJMSF\Debug;
use WPJMSF\Debug;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Resume
 *
 * @package WPJMSF
 *
 * @since   1.1.14
 *
 */
class Resume extends Debug {

	/**
	 * Add Hooks for Debug Information
	 *
	 * @since 1.1.14
	 *
	 */
	public function add_hooks() {
		add_filter( 'resume_manager_get_listings_result', array( $this, 'listings_ajax_response' ), 99999999999999999, 2 );
		add_action( 'after_get_resumes', array( $this, 'set_last_query' ), 0, 2 );
		add_action( 'search_and_filtering_resume_geolocation_sql_query', array( $this, 'set_geolocation_query' ), 99999999999999999 );
	}

	/**
	 * Disable Cache
	 *
	 * @since 1.1.14
	 *
	 */
	public function disable_cache() {
		add_filter( 'get_resumes_query_args', array( $this, '_clear_resumes_cache' ), 999999999999999 );
	}

	/**
	 * Clear Resumes Cache
	 *
	 * Because there's no easy way (with filter) to disable caching for resumes, we have to delete the transient using the query_args
	 * to build the hash, right before the plugin attempts to pull the value from transient.
	 *
	 * @see https://github.com/Automattic/wp-job-manager-resumes/issues/355
	 *
	 * @param $query_args
	 *
	 * @since 1.1.14
	 *
	 */
	public function _clear_resumes_cache( $query_args ) {
		$to_hash         = defined( 'ICL_LANGUAGE_CODE' ) ? json_encode( $query_args ) . ICL_LANGUAGE_CODE : json_encode( $query_args );
		$query_args_hash = 'jm_' . md5( $to_hash ) . \WP_Job_Manager_Cache_Helper::get_transient_version( 'get_resume_listings' );
		delete_transient( $query_args_hash );
		return $query_args;
	}
}