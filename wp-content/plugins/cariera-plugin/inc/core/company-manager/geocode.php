<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Geocode {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'cariera_update_company_data', [ $this, 'update_location_data' ], 20, 2 );
		add_action( 'cariera_company_manager_company_location_edited', [ $this, 'change_location_data' ], 20, 2 );
	}

	/**
	 * Update location data - when submitting a company
	 *
	 * @since   1.4.6
	 * @version 1.8.9
	 *
	 * @param int   $company_id
	 * @param array $values
	 */
	public function update_location_data( $company_id, $values ) {
		$use_google     = get_option( 'job_manager_google_maps_api_key' );
		$geocoder_class = $use_google ? '\WP_Job_Manager_Geocode' : '\Cariera_Core\Core\Job_Manager\Geocode';

		if ( apply_filters( 'cariera_company_manager_geolocation_enabled', true ) ) {
			$address_data = $geocoder_class::get_location_data( $values['company_fields']['company_location'] );
			\WP_Job_Manager_Geocode::save_location_data( $company_id, $address_data );
		}
	}

	/**
	 * Change a companies location data upon editing
	 *
	 * @since   1.4.6
	 * @version 1.8.9
	 *
	 * @param int    $company_id
	 * @param string $new_location
	 */
	public function change_location_data( $company_id, $new_location ) {
		$use_google     = get_option( 'job_manager_google_maps_api_key' );
		$geocoder_class = $use_google ? '\WP_Job_Manager_Geocode' : '\Cariera_Core\Core\Job_Manager\Geocode';

		if ( apply_filters( 'cariera_company_manager_geolocation_enabled', true ) ) {
			$address_data = $geocoder_class::get_location_data( $new_location );
			\WP_Job_Manager_Geocode::clear_location_data( $company_id );
			\WP_Job_Manager_Geocode::save_location_data( $company_id, $address_data );
		}
	}
}
