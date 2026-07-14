<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Search {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Location extra fields.
		add_action( 'cariera_company_filters_location_extra', [ $this, 'extra_location_fields' ], 2 );
		add_action( 'cariera_company_filters_search_radius', [ $this, 'search_by_radius_fields' ], 2 );

		// Search by radius query.
		add_filter( 'cariera_get_companies', [ $this, 'search_by_radius_query' ], 10, 2 );
		add_action( 'cariera_company_title_after', [ $this, 'output_search_radius_distance' ], 99 );

		// Search by map bounds (move-map feature).
		add_action( 'cariera_company_search_filters_end', [ $this, 'search_map_bounds_fields' ], 10, 1 );
		add_filter( 'cariera_get_companies', [ $this, 'search_by_map_bounds_query' ], 10, 2 );
	}

	/**
	 * Extra location fields.
	 *
	 * @since 1.8.9
	 */
	public function extra_location_fields() {
		get_job_manager_template_part( 'search-fields/location-extra' );
	}

	/**
	 * Custom search by location radius for the Company search
	 *
	 * @since 1.8.9
	 */
	public function search_by_radius_fields() {
		get_job_manager_template_part( 'search-fields/radius-field' );
	}

	/**
	 * Modifying the company search query.
	 *
	 * @since   1.8.9
	 * @version 2.0.0
	 *
	 * @param array $query_args
	 * @param array $args
	 */
	public function search_by_radius_query( $query_args, $args ) {
		global $wpdb, $cariera_distances;

		// Check if form data is present and parse it.
		if ( empty( $_POST['form_data'] ) ) { // phpcs:ignore
			return $query_args;
		}

		// phpcs:ignore
		parse_str( $_POST['form_data'], $form_data );

		// Merge explicitly sent radius fields if they exist.
		if ( isset( $_POST['search_radius'] ) ) { // phpcs:ignore
			$form_data['search_radius'] = absint( wp_unslash( $_POST['search_radius'] ) ); // phpcs:ignore
		}

		if ( isset( $_POST['search_radius_status'] ) ) { // phpcs:ignore
			$form_data['search_radius_status'] = sanitize_text_field( wp_unslash( $_POST['search_radius_status'] ) ); // phpcs:ignore
		}

		// Validate required fields.
		$search_location = isset( $form_data['search_location'] ) ? sanitize_text_field( $form_data['search_location'] ) : '';
		$search_radius   = isset( $form_data['search_radius'] ) ? $form_data['search_radius'] : 0;
		$radius_status   = isset( $form_data['search_radius_status'] ) ? sanitize_text_field( $form_data['search_radius_status'] ) : '';

		if ( empty( $search_location ) || empty( $radius_status ) || $search_radius <= 0 ) {
			add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
			return $query_args;
		}

		// Get map provider.
		$map_provider = get_option( 'cariera_map_provider' );

		// Geocode the address to get latitude and longitude.
		$latlng = cariera_geocode( $search_location, $map_provider );

		if ( empty( $latlng ) ) {
			\Cariera\write_log( sprintf( 'Geocoding failed for address: %s. Radius search aborted.', $search_location ) );
			return $query_args;
		}

		// Fetch nearby listings based on geolocation and radius.
		$radius_type = get_option( 'cariera_search_radius_unit' );
		$nearbyposts = cariera_get_nearby_listings( $latlng[0], $latlng[1], $search_radius, $radius_type );

		if ( ! empty( $nearbyposts ) ) {
			if ( apply_filters( 'cariera_radius_sort_by_distance', true ) ) {
				cariera_array_sort_by_column( $nearbyposts, 'distance' );
			}

			$cariera_distances = [];

			foreach ( $nearbyposts as $post ) {
				$cariera_distances[ $post['post_id'] ] = round( $post['distance'], 2 );
			}

			$ids = array_keys( $cariera_distances );

			if ( ! empty( $ids ) ) {
				$query_args['post__in'] = $ids;
				$query_args['orderby']  = 'post__in';

				// Optionally remove meta_query filter if it exists.
				if ( isset( $query_args['meta_query'][0] ) ) {
					unset( $query_args['meta_query'][0] );
				}
			}
		}

		// Add filter to show 'reset' link for custom filters.
		add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );

		return $query_args;
	}

	/**
	 * Output the distance based on the radius search.
	 *
	 * @since 1.8.9
	 */
	public function output_search_radius_distance() {
		global $post, $cariera_distances;

		if ( empty( $cariera_distances ) || ! isset( $cariera_distances[ $post->ID ] ) ) {
			return;
		}

		$radius_unit = get_option( 'cariera_search_radius_unit', 'km' );
		$distance    = esc_attr( $cariera_distances[ $post->ID ] );

		echo '<span class="cariera-listing-distance">' . esc_html( $distance ) . '' . esc_html( $radius_unit ) . '</span>';
	}

	/**
	 * Adds hidden fields used to store the current map viewport bounds.
	 *
	 * Values are populated dynamically via JavaScript when the "search as I move the map" feature is enabled.
	 *
	 * @since 2.0.0
	 */
	public function search_map_bounds_fields() {
		?>
		<input type="hidden" name="map_bounds_sw_lat" id="map_bounds_sw_lat" value="" disabled>
		<input type="hidden" name="map_bounds_sw_lng" id="map_bounds_sw_lng" value="" disabled>
		<input type="hidden" name="map_bounds_ne_lat" id="map_bounds_ne_lat" value="" disabled>
		<input type="hidden" name="map_bounds_ne_lng" id="map_bounds_ne_lng" value="" disabled>
		<?php
	}

	/**
	 * Filters listings by the current map viewport bounds.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query_args WP_Query arguments.
	 * @param array $args       Listing query arguments.
	 */
	public function search_by_map_bounds_query( $query_args, $args ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['form_data'] ) ) {
			return $query_args;
		}

		parse_str( wp_unslash( $_POST['form_data'] ), $form_data ); // phpcs:ignore

		$required_fields = [
			'map_bounds_sw_lat',
			'map_bounds_sw_lng',
			'map_bounds_ne_lat',
			'map_bounds_ne_lng',
		];

		foreach ( $required_fields as $field ) {
			if ( ! isset( $form_data[ $field ] ) || ! is_numeric( $form_data[ $field ] ) ) {
				return $query_args;
			}
		}

		$sw_lat = (float) $form_data['map_bounds_sw_lat'];
		$sw_lng = (float) $form_data['map_bounds_sw_lng'];
		$ne_lat = (float) $form_data['map_bounds_ne_lat'];
		$ne_lng = (float) $form_data['map_bounds_ne_lng'];

		// Bail if latitude bounds are invalid.
		if ( $sw_lat >= $ne_lat ) {
			return $query_args;
		}

		// Bail if coordinates are outside valid ranges.
		if (
			$sw_lat < -90 || $sw_lat > 90 ||
			$ne_lat < -90 || $ne_lat > 90 ||
			$sw_lng < -180 || $sw_lng > 180 ||
			$ne_lng < -180 || $ne_lng > 180
		) {
			return $query_args;
		}

		if ( ! isset( $query_args['meta_query'] ) || ! is_array( $query_args['meta_query'] ) ) {
			$query_args['meta_query'] = []; //phpcs:ignore
		}

		// Filter latitude.
		$query_args['meta_query'][] = [
			'key'     => 'geolocation_lat',
			'value'   => [ $sw_lat, $ne_lat ],
			'compare' => 'BETWEEN',
			'type'    => 'DECIMAL(10,6)',
		];

		// Filter longitude.
		if ( $sw_lng <= $ne_lng ) {
			// Standard viewport.
			$query_args['meta_query'][] = [
				'key'     => 'geolocation_long',
				'value'   => [ $sw_lng, $ne_lng ],
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(10,6)',
			];
		} else {
			// Antimeridian-crossing viewport.
			$query_args['meta_query'][] = [
				'relation' => 'OR',
				[
					'key'     => 'geolocation_long',
					'value'   => [ $sw_lng, 180 ],
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(10,6)',
				],
				[
					'key'     => 'geolocation_long',
					'value'   => [ -180, $ne_lng ],
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(10,6)',
				],
			];
		}

		add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );

		return $query_args;
	}
}
