<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Fields
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Search {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var array Processed values from filter and $_REQUEST['wpjmsf_fields']
	 */
	public $values = array();
	/**
	 * @var array Processed values from filter and $_REQUEST['wpjmsf_custom']
	 */
	public $custom_values = array();
	/**
	 * @var array Processed configuration values from filter and $_REQUEST['wpjmsf_config']
	 */
	public $config = array();
	/**
	 * @var \WPJMSF\Search\Taxonomies
	 */
	public $taxonomies;
	/**
	 * @var \WPJMSF\Search\Meta
	 */
	public $meta;
	/**
	 * @var \WPJMSF\Search\Core
	 */
	public $core;
	/**
	 * @var \WPJMSF\Search\GeoLocation
	 */
	public $geolocation;
	/**
	 * @var \WPJMSF\Search\NoValue
	 */
	public $novalue;
	/**
	 * @var bool Whether or not the "posts_where" filter was added in core class
	 */
	public $has_posts_where = false;

	/**
	 * Search constructor.
	 *
	 * @param $type \WPJMSF\Job|\WPJMSF\Resume
	 */
	public function __construct( $type ) {
		$this->type = $type;
		$this->meta = new Search\Meta( $this );
		$this->taxonomies = new Search\Taxonomies( $this );
		$this->core = new Search\Core( $this );
		$this->geolocation = new Search\GeoLocation( $this );
		$this->novalue = new Search\NoValue( $this );
	}

	/**
	 * Get Listing Results (right before listings returned)
	 *
	 * @param array     $results
	 * @param \WP_Query $listings
	 *
	 * @return array|mixed
	 * @since 1.0.0
	 *
	 */
	public function get_listings_results( $results, $listings ) {
		$results = $this->maybe_add_showing_results( $results, $listings );
		$au = new AutoUpdates( $this->type, $listings, $results );
		$results = $au->get_updated_results();
		$results['post_count'] = $listings->found_posts;
		return $results;
	}

	/**
	 * Maybe add Showing Results text
	 *
	 * This MUST stay in main SEARCH class (called by Job/Resume class)
	 *
	 * By default, WP Job Manager only will show the "Search completed.  Found X Records..." in the returned results for AJAX
	 * query for listings, when one of the internally defined fields was used for search/filtering.  This method will check if
	 * there was any other kind of custom query on the results set, and will make sure to set that showing value if so.
	 *
	 * This method should be called by a filter for returning the frontend results, ie for jobs it is job_manager_get_listings_result,
	 * resumes is resume_manager_get_listings_result
	 *
	 * Specific filter on Jobs is job_manager_get_listings_custom_filter_text
	 * Specific filter on Resumes is resume_manager_get_resumes_custom_filter_text
	 *
	 * TODO: Resumes add the "location" output in showing verbage ( see resume_manager_get_resumes_custom_filter_text )
	 *
	 * @param array $result
	 * @param \WP_Query $listings
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function maybe_add_showing_results( $result, $listings ) {

		$custom_results = false;

		if ( $listings && isset( $listings->query ) ) {
			// Check if there was a custom meta query
			if ( isset( $listings->query['meta_query'] ) && ! empty( $listings->query['meta_query'] ) ) {
				$custom_results = true;
			}
			// Check if there was a custom taxonomy query
			if ( isset( $listings->query['tax_query'] ) && ! empty( $listings->query['tax_query'] ) ) {
				$custom_results = true;
			}

			if( $this->has_posts_where ){
				$custom_results = true;
			}
		}

		/**
		 * Only set the value if there is no existing "value" already set for "showing" (meaning normal search did not trigger it)
		 */
		if ( $custom_results && isset( $result['showing'] ) && empty( $result['showing'] ) ) {
			$message = sprintf( _n( 'Search completed. Found %d matching record.', 'Search completed. Found %d matching records.', $listings->found_posts, 'wp-job-manager-search-and-filtering' ), $listings->found_posts );

			$result['showing'] = apply_filters( "search_and_filtering_get_{$this->type->slug}_listings_custom_filter_text", $message, $listings, $this );

			/**
			 * Also pass through normal filter for customizations from themes, plugins, etc.
			 */
			if( method_exists( $this->type, 'showing_filter' ) ){
				$result['showing'] = $this->type->showing_filter( $result['showing'], $listings->found_posts );
			}

			$result['showing_all'] = true;
		}

		return $result;
	}

	/**
	 * Get Field Values from wpjmsf_fields in $_REQUEST
	 *
	 * This method obtains the search values for the custom fields (under wpjmsf_fields in $_REQUEST),
	 * and passed it through filter setting $this->fields
	 *
	 * @param string $field
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_field_values( $field = '' ){
		if( empty( $this->values ) ){
			$fields = isset( $_REQUEST['wpjmsf_fields'] ) ? $_REQUEST['wpjmsf_fields'] : array();
			$fields = wpjmsf_sanitize_value( $fields );
			$this->values = apply_filters( 'search_and_filtering_search_get_field_values', $fields, $this );
		}

		if ( $field ) {
			return isset( $this->values[ $field ] ) ? $this->values[ $field ] : false;
		}

		return $this->values;
	}

	/**
	 * Get Field Values from wpjmsf_custom in $_REQUEST
	 *
	 * This method obtains the search values for the custom fields (under wpjmsf_custom in $_REQUEST),
	 * and passed it through filter setting $this->fields
	 *
	 * @param string $field
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_custom_values( $field = '' ) {

		if ( empty( $this->custom_values ) ) {
			$fields       = isset( $_REQUEST['wpjmsf_custom'] ) ? $_REQUEST['wpjmsf_custom'] : array();
			$fields       = wpjmsf_sanitize_value( $fields );
			$this->custom_values = apply_filters( 'search_and_filtering_search_get_custom_values', $fields, $this );
		}

		if ( $field ) {
			return isset( $this->custom_values[ $field ] ) ? $this->custom_values[ $field ] : false;
		}

		return $this->custom_values;
	}

	/**
	 * Get Search Config from wpjmsf_config in $_REQUEST
	 *
	 * This method pulls the search config from `wpjmsf_config` in $_REQUEST and sets $this->config to the values
	 * after passing through a filter
	 *
	 *
	 * @param string $field         Field to get configuration for
	 * @param string $config_key    (Optional) configuration key to return
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_search_config( $field = '', $config_key = '' ){
		if( empty( $this->config ) ){
			$config = isset( $_REQUEST['wpjmsf_config'] ) ? $_REQUEST['wpjmsf_config'] : array();
			$config = wpjmsf_sanitize_value( $config );
			$this->config = apply_filters( 'search_and_filtering_search_get_search_config', $config, $this );
		}

		if( $field ){
			$to_return = isset( $this->config[ $field ] ) ? $this->config[ $field ] : false;
			if( empty( $config_key ) ){
				return $to_return;
			}

			return ! empty( $to_return ) && isset( $to_return[ $config_key ] ) ? $to_return[ $config_key ] : false;
		}

		return $this->config;
	}

	/**
	 * Add Query Args to RSS Feed URL
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 1.1.26
	 *
	 */
	public function add_rss_args( $args ) {
		$config = $this->get_search_config();
		$custom = $this->get_custom_values();
		$fields = $this->get_field_values();

		$wpjmsf_fields = array();
		$wpjmsf_config = array();
		$wpjmsf_custom = array();

		if( ! empty( $fields ) ){
			foreach( (array) $fields as $meta_key => $value ){
				if( $value === '' || is_null( $value ) || (is_array( $value ) && empty( $value )) ){
					continue;
				}

				$wpjmsf_fields[ $meta_key ] = $value;

				/**
				 * Check for valid configuration for this field to also include
				 */
				if( array_key_exists( $meta_key, $config ) ){
					$not_empty_config = array();
					foreach( (array) $config[$meta_key] as $config_key => $config_value ){
						if( $config_value !== '' && $config_value !== null ){
							$not_empty_config[ $config_key ] = $config_value;
						}
					}

					if( ! empty( $not_empty_config ) ){
						$wpjmsf_config[ $meta_key ] = $not_empty_config;
					}
				}
			}
		}

		if ( ! empty( $custom ) ) {
			foreach ( (array) $custom as $meta_key => $value ) {
				if ( $value === '' || is_null( $value ) || ( is_array( $value ) && empty( $value ) ) ) {
					continue;
				}

				$wpjmsf_custom[ $meta_key ] = $value;

				/**
				 * Check for valid configuration for this field to also include
				 */
				if ( array_key_exists( $meta_key, $config ) ) {
					$not_empty_config = array();
					foreach ( (array) $config[ $meta_key ] as $config_key => $config_value ) {
						if ( $config_value !== '' && $config_value !== null ) {
							$not_empty_config[ $config_key ] = $config_value;
						}
					}

					if ( ! empty( $not_empty_config ) ) {
						$wpjmsf_config[ $meta_key ] = $not_empty_config;
					}
				}
			}
		}

		if( ! empty( $wpjmsf_fields ) ){
			$args['wpjmsf_fields'] = $wpjmsf_fields;
		}

		if( ! empty( $wpjmsf_custom ) ){
			$args['wpjmsf_custom'] = $wpjmsf_custom;
		}

		/**
		 * Only add custom query if field values or custom values exist (already handled above but for sanity check)
		 */
		if ( ( ! empty( $wpjmsf_fields ) || ! empty( $wpjmsf_custom ) ) && ! empty( $wpjmsf_config ) ) {
			$args['wpjmsf_config'] = $wpjmsf_config;
		}

		return $args;
	}

	/**
	 * Check if $_REQUEST has S&F Queries
	 *
	 * @return bool
	 * @since 1.1.26
	 *
	 */
	public function has_sf_queries() {
		return ( isset( $_REQUEST['wpjmsf_fields'] ) && ! empty( $_REQUEST['wpjmsf_fields'] ) ) || ( isset( $_REQUEST['wpjmsf_custom'] ) && ! empty( $_REQUEST['wpjmsf_custom'] ) );
	}

	/**
	 * Get Field Configuration 'compare' value to use in meta query
	 *
	 * This method will look through the search config, checking if there is something defined for the "compare" value,
	 * and return that (for the specified field)
	 *
	 * @param string $field
	 * @param mixed  $default
	 *
	 * @return bool|string
	 * @since 1.0.0
	 */
	public function get_field_compare( $field, $default = false ){
		$valid_compares = array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', 'RLIKE' );
		$config = $this->get_search_config();
		if( array_key_exists( $field, $config ) && array_key_exists( 'compare', $config[ $field ] ) ){
			return ! empty( $config[ $field ]['compare'] ) && in_array( $config[ $field ]['compare'], $valid_compares ) ? $config[ $field ]['compare'] : $default;
		}
		return $default;
	}

	/**
	 * Get Field Configuration 'compare' relation value to use in meta query
	 *
	 * This method will look through the search config, checking if there is something defined for the "compare_relation" value,
	 * and return that (for the specified field)
	 *
	 * @param string      $field
	 * @param bool|string $default  Value to return if one is not found for field configuration
	 *
	 * @return bool|string
	 * @since 1.0.0
	 */
	public function get_field_compare_relation( $field, $default = false ) {

		$valid_relations = array( 'AND', 'OR' );
		$config          = $this->get_search_config();

		if ( array_key_exists( $field, $config ) && array_key_exists( 'compare_relation', $config[ $field ] ) ) {
			return ! empty( $config[ $field ]['compare_relation'] ) && in_array( $config[ $field ]['compare_relation'], $valid_relations ) ? $config[ $field ]['compare_relation'] : $default;
		}

		return $default;
	}

	/**
	 * Modify Get Listings Args Filter
	 *
	 * This MUST stay in main SEARCH class (called by Job/Resume class)
	 *
	 * This method is called by filter on Job/Resumes before get_job_listings() or get_resumes() fn is called
	 *
	 *
	 * @param $args
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_listings_args( $args ) {
		// We need to do this before core processing, to make sure AND and include_children have correct params
		$args = $this->core->merge_taxonomies( $args );
		$args = $this->core->add_args( $args );
		return $args;
	}

	/**
	 * Get Listings Query Args Filter
	 *
	 * This MUST stay in main SEARCH class (called by Job/Resume class)
	 *
	 * This method is called by filter right before the actual query is made, specifically for the `query_args` before the
	 * `tax_query` and `meta_query` are unset (without any values)
	 *
	 *
	 * @param $query_args
	 * @param $args
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_listings_query_args( $query_args, $args = array() ) {
		$query_args = $this->taxonomies->add_queries( $query_args );
		$query_args = $this->meta->add_queries( $query_args );
		$query_args = $this->core->add_custom_post_query_args( $query_args );
		$query_args = $this->core->check_search_location_anywhere( $query_args );

		return $query_args;
	}

	/**
	 * Get Listings Query Args After Filter (after removing meta/tax empty queries)
	 *
	 * @param $query_args
	 *
	 * @return array|mixed
	 * @since 1.0.0
	 *
	 */
	public function get_listings_query_args_after( $query_args, $args = array() ) {
		// MUST be ran after all other queries, to remove location related queries from mangling results
		$query_args = $this->geolocation->add_queries( $query_args );
		$query_args = $this->novalue->add_queries( $query_args );

		// Just for good measures, since this method is called after WP Job Manager has already done same thing
		if ( empty( $query_args['meta_query'] ) ) {
			unset( $query_args['meta_query'] );
		}

		if ( empty( $query_args['tax_query'] ) ) {
			unset( $query_args['tax_query'] );
		}

		return $query_args;
	}

	/**
	 * Call After Query is Ran
	 *
	 * This MUST stay in main SEARCH class (called by Job/Resume class)
	 *
	 * This method should be invoked by a hook or filter called AFTER the WP_Query is ran, so we can remove
	 * any custom added filters for native core WordPress area (like posts_where, etc)
	 *
	 * @since 1.0.0
	 *
	 */
	public function after_query() {
		$this->core->toggle_posts_where_filter( false );
	}

	/**
	 * Build Compare Query for SQL
	 *
	 * @param string   $meta_key    Meta key to check for compare value
	 * @param string   $value
	 * @param string   $default     LIKE
	 * @param string[] $supported   array( '!=', '=', 'LIKE', 'NOT LIKE' ) supported comparisons, otherwise reverts to LIKE
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function build_compare_query( $meta_key, $value, $default = 'LIKE', $supported = array( '!=', '=', 'LIKE', 'NOT LIKE' ) ) {
		global $wpdb;

		$compare = $this->get_field_compare( $meta_key );
		$compare = ! empty( $compare ) ? $compare : $default;
		$compare = in_array( $compare, $supported ) ? $compare : 'LIKE';
		// ie: 'LIKE'
		$compare_query = $compare;
		$wildcard = in_array( $compare, array( 'LIKE', 'NOT LIKE' ) ) ? '%': '';

		$value = esc_sql( $wpdb->esc_like( $value ) );
		// LIKE '%something%'
		// = 'abcd'
		$compare_query .= " '{$wildcard}{$value}{$wildcard}'";

		return $compare_query;
	}

	/**
	 * Sanitize Values to Array
	 *
	 *
	 * @param $values
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function sanitize_values_to_array( $values ){

		if ( is_array( $values ) ) {
			$values = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $values ) ) );
		} else {
			$values = array_filter( array( sanitize_text_field( wp_unslash( $values ) ) ) );
		}

		return $values;
	}
}