<?php

namespace Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Geocode {
	const OSM_NOMINATIM_API_URL = 'https://nominatim.openstreetmap.org/search';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Use OpenStreetMap geocoding if Google Maps API key is not set.
		if ( get_option( 'job_manager_google_maps_api_key' ) ) {
			return;
		}

		// Add actions for updating and editing job locations.
		add_filter( 'job_manager_geolocation_endpoint', [ $this, 'add_geolocation_endpoint_query_args' ], 10, 2 );
		add_action( 'job_manager_update_job_data', [ $this, 'update_job_location_data' ], 20, 2 );
		add_action( 'job_manager_job_location_edited', [ $this, 'change_job_location_data' ], 20, 2 );

		// Add actions for updating and editing resume locations.
		add_filter( 'resume_manager_geolocation_enabled', '__return_false' );
		add_action( 'resume_manager_update_resume_data', [ $this, 'update_resume_location_data' ], 10, 2 );
		add_action( 'resume_manager_candidate_location_edited', [ $this, 'change_resume_location_data' ], 10, 2 );
	}

	/**
	 * Adds necessary query arguments for Nominatim API request.
	 *
	 * @since 1.8.9
	 *
	 * @param string $geocode_endpoint_url
	 * @param string $raw_address
	 */
	public function add_geolocation_endpoint_query_args( $geocode_endpoint_url, $raw_address ) {
		// Use Nominatim API and add necessary query parameters.
		$geocode_endpoint_url = add_query_arg( 'q', rawurlencode( $raw_address ), self::OSM_NOMINATIM_API_URL );
		$geocode_endpoint_url = add_query_arg( 'format', 'json', $geocode_endpoint_url );
		$geocode_endpoint_url = add_query_arg( 'addressdetails', 1, $geocode_endpoint_url );

		return $geocode_endpoint_url;
	}

	/**
	 * Gets Location Data from OpenStreetMap (Nominatim API).
	 *
	 * @since 1.8.9
	 *
	 * @param string $raw_address
	 *
	 * TODO: This needs to be improved.
	 */
	public static function get_location_data( $raw_address ) {
		$raw_address = trim( $raw_address );

		if ( empty( $raw_address ) ) {
			return false;
		}

		// Check transient cache for geocoded address data.
		$transient_name   = 'jm_geocode_' . md5( $raw_address );
		$geocoded_address = get_transient( $transient_name );

		if ( false === $geocoded_address ) {
			try {
				// Fetch data from Nominatim API.
				$response = wp_remote_get(
					self::OSM_NOMINATIM_API_URL,
					[
						'timeout'     => 15,
						'httpversion' => '1.1',
						'user-agent'  => 'WordPress/WP-Job-Manager-' . JOB_MANAGER_VERSION . '; ' . get_bloginfo( 'url' ),
						'redirection' => 1,
						'sslverify'   => false,
						'body'        => [
							'q'              => $raw_address,
							'format'         => 'json',
							'addressdetails' => 1,
						],
					]
				);

				// Process the response.
				$geocoded_address = json_decode( wp_remote_retrieve_body( $response ) );

				if ( ! empty( $geocoded_address ) ) {
					set_transient( $transient_name, $geocoded_address, DAY_IN_SECONDS * 7 );
				} else {
					return new \WP_Error( 'error', __( 'Geocoding error', 'cariera-core' ) );
				}
			} catch ( \Exception $e ) {
				return new \WP_Error( 'error', $e->getMessage() );
			}
		}

		// Prepare location data.
		$address = [];
		if ( ! empty( $geocoded_address[0] ) ) {
			$address['lat']               = sanitize_text_field( $geocoded_address[0]->lat );
			$address['long']              = sanitize_text_field( $geocoded_address[0]->lon );
			$address['formatted_address'] = sanitize_text_field( $geocoded_address[0]->display_name );

			// Map Nominatim data to location components.
			$address_data             = $geocoded_address[0]->address;
			$address['street_number'] = isset( $address_data->house_number ) ? sanitize_text_field( $address_data->house_number ) : false;
			$address['street']        = isset( $address_data->road ) ? sanitize_text_field( $address_data->road ) : false;
			$address['city']          = isset( $address_data->city ) ? sanitize_text_field( $address_data->city ) : false;
			$address['state_short']   = isset( $address_data->state_code ) ? sanitize_text_field( $address_data->state_code ) : false;
			$address['state_long']    = isset( $address_data->state ) ? sanitize_text_field( $address_data->state ) : false;
			$address['postcode']      = isset( $address_data->postcode ) ? sanitize_text_field( $address_data->postcode ) : false;
			$address['country_short'] = isset( $address_data->country_code ) ? sanitize_text_field( $address_data->country_code ) : false;
			$address['country_long']  = isset( $address_data->country ) ? sanitize_text_field( $address_data->country ) : false;
		}

		return $address;
	}

	/**
	 * Updates location data when a job is updated.
	 *
	 * @since   1.8.9
	 * @version 1.9.8
	 *
	 * @param int   $job_id
	 * @param array $data
	 */
	public function update_job_location_data( $job_id, $data ) {
		// Skip if importer is running.
		if ( \Cariera_Core\Importer\Importer::$importing ) {
			return;
		}

		if ( apply_filters( 'cariera_geolocation_enabled', true ) && isset( $data['job']['job_location'] ) ) {
			$address_data = self::get_location_data( $data['job']['job_location'] );
			\WP_Job_Manager_Geocode::save_location_data( $job_id, $address_data );
		}
	}

	/**
	 * Changes location data when the job location is edited.
	 *
	 * @since   1.8.9
	 * @version 2.0.0
	 *
	 * @param int   $job_id
	 * @param array $new_location
	 */
	public function change_job_location_data( $job_id, $new_location ) {
		// Skip if importer is running.
		if ( \Cariera_Core\Importer\Importer::$importing ) {
			return;
		}

		if ( apply_filters( 'cariera_geolocation_enabled', true ) ) {
			$address_data = self::get_location_data( $new_location );
			\WP_Job_Manager_Geocode::clear_location_data( $job_id );
			\WP_Job_Manager_Geocode::save_location_data( $job_id, $address_data );
		}
	}

	/**
	 * Updates location data when a resume is updated.
	 *
	 * @since   1.8.9
	 * @version 1.9.8
	 *
	 * @param int   $resume_id
	 * @param array $data
	 */
	public function update_resume_location_data( $resume_id, $data ) {
		// Skip if importer is running.
		if ( \Cariera_Core\Importer\Importer::$importing ) {
			return;
		}

		if ( apply_filters( 'cariera_geolocation_enabled', true ) && isset( $data['resume_fields']['candidate_location'] ) ) {
			$address_data = self::get_location_data( $data['resume_fields']['candidate_location'] );
			\WP_Job_Manager_Geocode::save_location_data( $resume_id, $address_data );
		}
	}

	/**
	 * Changes location data when the resume location is edited.
	 *
	 * @since   1.8.9
	 * @version 1.9.8
	 *
	 * @param int   $resume_id
	 * @param array $new_location
	 */
	public function change_resume_location_data( $resume_id, $new_location ) {
		// Skip if importer is running.
		if ( \Cariera_Core\Importer\Importer::$importing ) {
			return;
		}

		if ( apply_filters( 'cariera_geolocation_enabled', true ) ) {
			$address_data = self::get_location_data( $new_location );
			\WP_Job_Manager_Geocode::clear_location_data( $resume_id );
			\WP_Job_Manager_Geocode::save_location_data( $resume_id, $address_data );
		}
	}
}
