<?php

namespace WPJMSF\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GeoLocation
 *
 * @package WPJMSF
 *
 * @since   1.0.0
 *
 */
class GeoLocation {

	/**
	 * @var string Meta key used for search radius
	 */
	public $radius_meta_key = 'c_search_radius';
	/**
	 * @var string
	 */
	public $lat_meta_key = 'search_lat';
	public $lat_lng_meta_key = 'search_lat_lng';
	/**
	 * @var string
	 */
	public $lng_meta_key = 'search_lng';
	/**
	 * @var
	 */
	public $distances;
	/**
	 * @var \WPJMSF\Search
	 */
	public $search;
	/**
	 * @var array
	 */
	public $distance_config = array(
		'radius_unit' => 'mi',
		'precision' => 2
	);

	/**
	 * GeoLocation constructor.
	 *
	 * @param \WPJMSF\Search $search
	 */
	public function __construct( $search ) {
		$this->search = $search;
	}

	/**
	 * Get "search_location" meta key used based on type
	 *
	 * @return string
	 * @since 1.1.9
	 *
	 */
	public function get_type_location_meta_key() {
		return $this->search->type->slug === 'resume' ? 'candidate_location' : 'job_location';
	}

	/**
	 * Get Search Location
	 *
	 * This method tries first the core search_location field, and if no value exists, will attempt to pull
	 * from the type specific meta field.
	 *
	 * @return array|false|mixed
	 * @since 1.0.0
	 *
	 */
	public function get_location_field_value() {
		$address = $this->search->get_field_values( 'search_location' );
		if( ! empty( $address ) ){
			return $address;
		}

		$type_meta_key = $this->get_type_location_meta_key();
		return $this->search->get_field_values( $type_meta_key );
	}

	/**
	 * Output Distance Wrapper
	 *
	 * @param bool|\WP_Post $passed_post
	 *
	 * @since 1.1.26
	 */
	public function output_distance( $passed_post = false ) {
		global $post;
		$listing = $passed_post && ( $passed_post instanceof \WP_Post ) ? $passed_post : $post;
		if( ! $listing || empty( $this->distances ) || ! $listing->ID || ! array_key_exists( $listing->ID, $this->distances ) ){
			return false;
		}

		$mi_radius_unit = __( 'mi', 'wp-job-manager-search-and-filtering' );
		$km_radius_unit = __( 'km', 'wp-job-manager-search-and-filtering' );

		$distance = round( $this->distances[ $listing->ID ]['distance'], $this->distance_config['precision'] );
		$radius_unit = $this->distance_config['radius_unit'] === 'mi' ? $mi_radius_unit : $km_radius_unit;
		echo '<span class="' . $this->search->type->slug . '-distance-wrap">' . esc_attr( $distance ) . ' ' . esc_attr( $radius_unit ) . '</span>';
		return true;
	}

	/**
	 * Maybe Add Search Radius Handling
	 *
	 * @param $query_args
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function add_queries( $query_args ) {
		$search_radius = $this->search->get_custom_values( $this->radius_meta_key );
		if( $search_radius === false || $search_radius === '' || $search_radius === null ){
			return $query_args;
		}

		$search_radius = floatval( $search_radius );
		$lat_lng = $this->search->get_custom_values( $this->lat_lng_meta_key );

		$order = $this->search->get_field_values( 'order' );
		$order = ! empty( $order ) ? ( strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC' ) : 'ASC';

		/**
		 * Support for range slider (or any other "min/max" field type)
		 */
		$radius_max = $this->search->get_custom_values( 'c_search_radius_sf_2' );
		if ( ! empty( $radius_max ) ) {
			$min_compare     = $this->search->get_field_compare( 'c_search_radius', '>=' );
			$max_compare     = $this->search->get_field_compare( 'c_search_radius_sf_2', '<=' );
			$distance_having = "distance {$min_compare} {$search_radius} AND distance {$max_compare} {$radius_max}";
		} else {
			$distance_compare = $this->search->get_field_compare( 'c_search_radius', '<=' );
			$distance_having  = "distance {$distance_compare} {$search_radius}";
		}

		$post_ids     = array();
		// Zero out distances
		$this->distances = array();

		// Lat/Lng not sent by frontend, so we need to geocode the address
		if( empty( $lat_lng ) ){

			$address = $this->get_location_field_value();

			if( empty( $address ) ){
				return $query_args;
			}

			$lat_lng = array();
			// Check if we have multiple locations (separated by ;)
			if( ! is_array( $address ) ){
				$location_array = explode( ';', $address );
			} else {
				$location_array = $address;
			}

			foreach( (array) $location_array as $location ){
				// First check if the value is actually lat/lng (and not an address)
				$address_is_lat_lng = $this->address_is_lat_lng( $location );
				// If so, add to the array
				if( ! empty( $address_is_lat_lng ) ){
					$lat_lng[] = array(
						'lat' => $address_is_lat_lng['lat'],
						'lng' => $address_is_lat_lng['lng']
					);
				} else {
					// If not, geocode the address
					$address_lat_lng = $this->geocode_address( $location );
					if( ! empty( $address_lat_lng ) ){
						$lat_lng[] = array(
							'lat' => $address_lat_lng['lat'],
							'lng' => $address_lat_lng['lng']
						);
					}
				}
			}

			// If we still don't have any lat/lng, we can't do radius search
			if( empty( $lat_lng ) ){
				return $query_args;
			}

		}

		/**
		 * Loop through each lat/lng and perform a search for each to get listing IDs
		 */
		foreach( (array) $lat_lng as $ll ){
			$lat = $ll['lat'];
			$lng = $ll['lng'];
			if( empty( $lat ) || empty( $lng ) ){
				continue;
			}

			/**
			 * We want to pass the above values as args, as to make sure the cache is not returning incorrect data
			 * due to one of the above values being changed on frontend by users for search.
			 */
			$listings = $this->search(
				array(
					'latitude'        => $lat,
					'longitude'       => $lng,
					'radius'          => $search_radius,
					'order'           => $order,
					'distance_having' => $distance_having
				)
			);

			if( ! empty( $listings ) ){
				/**
				 * Allow for returning false to prevent outputting distances on radius search
				 */
				if ( apply_filters( "search_and_filtering_{$this->search->type->slug}_geolocation_output_distances", true, $this ) ) {
					foreach ( $listings as $listing ) {
						$this->distances[ $listing->ID ] = (array) $listing;
					}
				}

				// Pull out the IDs from the keys into an array
				$found_listing_ids = array_keys( (array) $listings );
				// Add to the array of listing IDs
				$post_ids = array_unique( array_merge( $post_ids, $found_listing_ids ) );
			}
		}

		/**
		 * IF no results found, we set value to just zero (0) to make sure no results are returned in response
		 */
		if ( empty( $post_ids ) ) {
			$post_ids = array( 0 );
		}

		$include_anywhere = $this->search->get_search_config( 'search_location', 'include_anywhere' );
		$include_anywhere = apply_filters( "search_and_filtering_{$this->search->type->slug}_radius_search_include_anywhere", $include_anywhere, $this );
		if( ! empty( $include_anywhere ) ){
			$anywhere_post_ids = $this->get_anywhere_post_ids();
			if( ! empty( $anywhere_post_ids ) ){
				$post_ids = array_unique( array_merge( $post_ids, $anywhere_post_ids ) );
			}
		}

		$query_args['post__in'] = $post_ids;
		/**
		 * This defines to order by distances (since order wil be in same order of posts in array)
		 */
		$query_args['orderby']  = 'post__in';

		$query_args = $this->remove_location_meta_query( $query_args );

		return $query_args;
	}

	/**
	 * Get All "Anywhere" Post IDs
	 *
	 * This method is used to return all the post IDs for listings that would have an "Anywhere" location,
	 * meaning that they do not have a value set for the search_location (job_location/candidate_location)
	 * field.
	 *
	 * @return int[]
	 * @since 1.1.9
	 *
	 */
	public function get_anywhere_post_ids() {
		$location_mk = $this->get_type_location_meta_key();
		$post_type = $this->search->type->core_post_type;

		$include_anywhere_query = array(
			'key'     => "_{$location_mk}",
			'value'   => '',
			'compare' => '='
		);

		/**
		 * Support for Empty Meta Cleaner handling
		 */
		if ( function_exists( 'WPJM_Empty_Meta_Cleanup' ) ) {
			$emc_enabled = get_option( "job_manager_empty_meta_cleanup_{$this->search->type->slug}_enable", false );
			if ( ! empty( $emc_enabled ) ) {
				$include_anywhere_query = array(
					'key'     => "_{$location_mk}",
					'compare' => 'NOT EXISTS'
				);
			}
		}

		$query = new \WP_Query( array(
			                        'posts_per_page' => - 1,
			                        'fields'         => 'ids',
			                        'post_type'      => $post_type,
			                        'meta_query'     => array(
										$include_anywhere_query
			                        )
		                        ) );
		return $query->posts;
	}

	/**
	 * Parse Order By Parameter
	 *
	 * @param $orderby
	 *
	 * @return false|string
	 * @since 1.1.26
	 *
	 */
	protected function parse_orderby( $orderby ) {
		global $wpdb;

		// Used to filter values.
		$allowed_keys = array(
			'post_name',
			'post_author',
			'post_date',
			'post_title',
			'post_modified',
			'post_parent',
			'post_type',
			'name',
			'author',
			'date',
			'title',
			'modified',
			'parent',
			'type',
			'ID',
			'menu_order',
			'comment_count',
			'rand',
			'post__in',
			'post_parent__in',
			'post_name__in',
		);

		if ( ! in_array( $orderby, $allowed_keys, true ) ) {
			return false;
		}

		$orderby_clause = '';

		switch ( $orderby ) {
			case 'post_name':
			case 'post_author':
			case 'post_date':
			case 'post_title':
			case 'post_modified':
			case 'post_parent':
			case 'post_type':
			case 'ID':
			case 'menu_order':
			case 'comment_count':
				$orderby_clause = "{$wpdb->posts}.{$orderby}";
				break;
			case 'rand':
				$orderby_clause = 'RAND()';
				break;
			default:
				$orderby_clause = "{$wpdb->posts}.post_" . sanitize_key( $orderby );
				break;
		}

		return $orderby_clause;
	}

	/**
	 * Search via custom SQL query for Listings within specific radius
	 *
	 * @param array $args
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function search( $args = array() ) {
		global $wpdb;

		$search_config = $this->search->get_search_config( $this->radius_meta_key );
		$radius_unit = ! $search_config || ! isset( $search_config['radius_unit'] ) || ! in_array( $search_config['radius_unit'], array( 'mi', 'km' ) ) ? 'mi' : $search_config['radius_unit'];

		$custom_radius_unit = $this->search->get_custom_values('c_radius_unit');
		if( ! empty( $custom_radius_unit ) && in_array( $custom_radius_unit, array( 'mi', 'km' ) ) ){
			$radius_unit = $custom_radius_unit;
		}

		$defaults = array(
			'earth_radius' => 'mi' == $radius_unit ? 3959 : 6371,
			'orderby'      => array(),
			'order'        => 'ASC',
			'latitude'     => null,
			'longitude'    => null,
			'radius'       => null,
			'radius_max'   => null,
			'distance_having' => 'distance <= 50'
		);

		$args = wp_parse_args( $args, $defaults );

		$has_orderby_field = $this->search->get_search_config( 'orderby', 'has_field' );

		$custom_order = $this->search->get_field_values( 'order' );
		$parsed_custom_order = ! empty( $custom_order ) && in_array( $custom_order, array( 'ASC', 'DESC' ) ) ? $custom_order : false;

		if( $parsed_custom_order ){
			$args['order'] = $parsed_custom_order;
		}

		// Only set custom_orderby if a field exists on the frontend, otherwise default to distance_featured
		$custom_orderby = $has_orderby_field ? $this->search->get_field_values( 'orderby' ) : 'distance_featured';
		$custom_orderby = apply_filters( 'search_and_filtering_radius_search_orderby', $custom_orderby, $this );

		// Parsable orderby fields (ID, post_date, etc) that are not one of default values
		$parsed_orderby = $has_orderby_field ? $this->parse_orderby( $custom_orderby ) : false;

		if ( 'featured' === $custom_orderby ) {
			/**
			 * Default order for featured listings is ASC (not DESC)
			 */
			$order = $custom_order ? $custom_order : 'DESC';
			$args['orderby'][] = "$wpdb->posts.menu_order ASC";
			$args['orderby'][] = "$wpdb->posts.post_date {$order}";
			$args['orderby'][] = "$wpdb->posts.ID {$order}";
		} elseif ( 'rand_featured' === $custom_orderby ) {
			$args['orderby'][] = "$wpdb->posts.menu_order ASC";
			$args['orderby'][] = "RAND() {$args['order']}";
		} elseif ( $parsed_orderby ) {
			$args['orderby'][] = "$parsed_orderby {$args['order']}";
		} elseif( $custom_orderby === 'distance' ) {
			$args['orderby'][] = "distance {$args['order']}";
		} else {
			/**
			 * Set to distance_featured by default if no valid orderby is provided or orderby is set to "distance"
			 */
			if ( apply_filters( 'search_and_filtering_feature_listings_in_location_search', true, $this->search->type->slug, $this ) ) {
				$args['orderby'][] = "$wpdb->posts.menu_order ASC";
			}
			$args['orderby'][] = "distance {$args['order']}";
		}

		$radius_check = '4eBeRwGwSCyumXQrQDVPwwEE';
		$distance_having = $args['distance_having'];

		$sql = $wpdb->prepare( "
			SELECT $wpdb->posts.ID,
				IFNULL(
				( %s * acos( 
					cos( radians(%s) ) * 
					cos( radians( latitude.meta_value ) ) * 
					cos( radians( longitude.meta_value ) - radians(%s) ) + 
					sin( radians(%s) ) * 
					sin( radians( latitude.meta_value ) ) 
				) ), 0) 
				AS distance, latitude.meta_value AS latitude, longitude.meta_value AS longitude
				FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta 
					AS latitude 
					ON $wpdb->posts.ID = latitude.post_id
				INNER JOIN $wpdb->postmeta 
					AS longitude 
					ON $wpdb->posts.ID = longitude.post_id
				WHERE 1=1
					AND latitude.meta_key='geolocation_lat'
					AND longitude.meta_key='geolocation_long'
				HAVING {$distance_having}
				ORDER BY " . implode( ',', $args['orderby'] ),
		                       $args['earth_radius'],
		                       $args['latitude'],
		                       $args['longitude'],
		                       $args['latitude']
		);

		// Used to pull the query from debug handling
		$sql = apply_filters( "search_and_filtering_{$this->search->type->slug}_geolocation_sql_query", $sql, $args, $this );

		$result = false;

		$to_hash         = json_encode( $args );
		$query_args_hash = 'jm_' . md5( $to_hash . JOB_MANAGER_VERSION ) . \WP_Job_Manager_Cache_Helper::get_transient_version( 'get_job_listings_by_location' );

		if ( apply_filters( 'get_job_listings_cache_results', true ) ) {
			$result = get_transient( $query_args_hash );
		}

		if ( ! $result ) {
			$result = $wpdb->get_results( $sql, OBJECT_K );
			set_transient( $query_args_hash, $result, DAY_IN_SECONDS );
		}

		$this->distance_config = apply_filters( "search_and_filtering_{$this->search->type->slug}_geolocation_radius_distance_config", array(
			'radius_unit' => $radius_unit,
			'precision'   => 2
		), $this );

		return $result;
	}

	/**
	 * Check if Address is Actually Latitude/Longitude Cords
	 *
	 * @param $address
	 *
	 * @return array|false
	 * @since 1.0.0
	 *
	 */
	public function address_is_lat_lng( $address ) {

		if( $address && strpos( $address, ',' ) !== false ){

			$pieces = explode( ',', $address );

			// Must only be 2 total values, as we could be matching on an address like "123 My Way, Orlando, FL 32808"
			if( ! empty( $pieces ) && count( $pieces ) === 2 ){

				$lat = trim( $pieces[0] );
				$lng = trim( $pieces[1] );

				// Do basic validation, to make sure numeric, etc
				if( $this->validate_latitude( $lat ) && $this->validate_longitude( $lng ) ){
					return array(
						'lat'               => $lat,
						'lng'               => $lng,
						'formatted_address' => $address
					);
				}
			}
		}

		return false;
	}

	/**
	 * Get Lat/Lng from Address Value
	 *
	 * @param $address
	 *
	 * @return array|false
	 * @since 1.0.0
	 *
	 */
	public function geocode_address( $address ) {

		if( $lat_lng = $this->address_is_lat_lng( $address ) ){
			return $lat_lng;
		}

		// url encode the address
		$address = urlencode( $address );
		$api_key = get_option( 'job_manager_google_maps_api_key' );
		if( empty( $api_key ) ){
			return false;
		}

		$url = apply_filters( 'search_and_filtering_geolocation_geocode_address_google_url', "https://maps.google.com/maps/api/geocode/json?address={$address}&key={$api_key}", $address, $this );

		$resp_json = wp_remote_get( $url );
		$resp      = json_decode( wp_remote_retrieve_body( $resp_json ), true );

		// response status will be 'OK', if able to geocode given address
		if ( $resp['status'] == 'OK' ) {

			// get the important data
			$lati              = $resp['results'][0]['geometry']['location']['lat'];
			$longi             = $resp['results'][0]['geometry']['location']['lng'];
			$formatted_address = $resp['results'][0]['formatted_address'];

			// verify if data is complete
			if ( $lati && $longi && $formatted_address ) {

				// put the data in the array
				return array(
					'lat' => $lati,
					'lng' => $longi,
					'formatted_address' => $formatted_address
				);

			} else {
				return false;
			}

		} else {
			return false;
		}
	}

	/**
	 * Basic Validate Latitude (numeric and <= 90 & >= -90)
	 *
	 * @param $latitude
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function validate_latitude( $latitude ) {

		if ( ! is_numeric( $latitude ) ) {
			return false;
		}

		$latitude = floatval( $latitude );

		return $latitude <= 90 && $latitude >= - 90;
	}

	/**
	 * Basic Validate Longitude
	 *
	 * @param $longitude
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function validate_longitude( $longitude ) {

		if ( ! is_numeric( $longitude ) ) {
			return false;
		}

		$longitude = floatval( $longitude );

		return $longitude <= 180 && $longitude > - 180;
	}

	/**
	 * Remove other location meta query items from a normal query.
	 *
	 * Only applies when a radius search is happening.
	 *
	 * @param array $query_args
	 *
	 * @return array $query_args
	 * @since 1.0.0
	 *
	 */
	private function remove_location_meta_query( $query_args ) {

		if ( ! isset( $query_args['meta_query'] ) ) {
			return $query_args;
		}

		foreach ( $query_args['meta_query'] as $query_key => $meta ) {
			if( is_int( $query_key ) ){

				if( isset( $meta['key'] ) ){

					if( in_array( $meta['key'], array( '_job_location', '_candidate_location' ) ) ){
						unset( $query_args['meta_query'][$query_key] );
					}

				} else {

					/**
					 * Leave as direct access on $query_args for passing as reference (doesn't seem to work passing $meta)
					 */
					$remove = $this->search_and_remove_location_meta_query( $query_args['meta_query'][$query_key] );
					if ( $remove ) {
						unset( $query_args['meta_query'][ $query_key ] );
					}

				}

			}
		}

		return $query_args;
	}

	/**
	 * Search and Remove Location Meta Queries (called by $this->remove_location_meta_query)
	 *
	 * @param $meta_query
	 *
	 * @return bool
	 * @since 1.1.26
	 *
	 */
	private function search_and_remove_location_meta_query( &$meta_query ) {

		$remove_top_level = false;

		foreach ( $meta_query as $key => $value ) {
			if ( ! is_int( $key ) ) {
				continue;
			}

			/**
			 * Nested array meta query
			 */
			if ( is_array( $value ) && ! isset( $value['key'] ) ) {
				$remove_nested_top = $this->search_and_remove_location_meta_query( $value );
				if( $remove_nested_top ){
					unset( $meta_query[$key] );
				}
				continue;
			}

			if ( isset( $value['key'] ) && 'geolocation_formatted_address' == $value['key'] ) {
				$remove_top_level = true;
				break;
			}
		}

		return $remove_top_level;
	}
}