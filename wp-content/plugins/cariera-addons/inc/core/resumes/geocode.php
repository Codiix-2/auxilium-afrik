<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Geocode {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'resume_manager_update_resume_data', [ $this, 'update_location_data' ], 20, 2 );
		add_action( 'resume_manager_candidate_location_edited', [ $this, 'change_location_data' ], 20, 2 );
	}

	/**
	 * Update location data - when submitting a resume
	 *
	 * @since 0.9.5
	 *
	 * @param int   $resume_id
	 * @param array $values
	 */
	public function update_location_data( $resume_id, $values ) {
		$use_google     = get_option( 'job_manager_google_maps_api_key' );
		$geocoder_class = $use_google ? '\WP_Job_Manager_Geocode' : '\Cariera_Core\Core\Job_Manager\Geocode';

		if ( apply_filters( 'cariera_addons_resumes_geolocation_enabled', true ) ) {
			$address_data = $geocoder_class::get_location_data( $values['resume_fields']['candidate_location'] );
			\WP_Job_Manager_Geocode::save_location_data( $resume_id, $address_data );
		}
	}

	/**
	 * Change a resumes location data upon editing
	 *
	 * @since 0.9.5
	 *
	 * @param  int    $resume_id
	 * @param  string $new_location
	 */
	public function change_location_data( $resume_id, $new_location ) {
		$use_google     = get_option( 'job_manager_google_maps_api_key' );
		$geocoder_class = $use_google ? '\WP_Job_Manager_Geocode' : '\Cariera_Core\Core\Job_Manager\Geocode';

		if ( apply_filters( 'cariera_addons_resumes_geolocation_enabled', true ) ) {
			$address_data = $geocoder_class::get_location_data( $new_location );
			\WP_Job_Manager_Geocode::clear_location_data( $resume_id );
			\WP_Job_Manager_Geocode::save_location_data( $resume_id, $address_data );
		}
	}
}
