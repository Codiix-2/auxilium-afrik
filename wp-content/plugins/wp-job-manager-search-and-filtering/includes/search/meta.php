<?php

namespace WPJMSF\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Meta
 *
 * @package WPJMSF
 *
 * @since   1.0.0
 *
 */
class Meta {

	/**
	 * @var \WPJMSF\Search
	 */
	public $search;

	/**
	 * @var array Mapping of meta key fields to actual meta keys stored on listings as
	 */
	public $meta_key_mappings = null;

	/**
	 * Taxonomies constructor.
	 *
	 * @param \WPJMSF\Search $search
	 */
	public function __construct( $search ) {
		$this->search = $search;
	}

	/**
	 * Add Meta Queries `meta_query` to Query Args
	 *
	 *
	 * @param $query_args
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function add_queries( $query_args ) {

		$tax_fields   = $this->search->taxonomies->get_taxonomy_fields();
		$field_values = $this->search->get_field_values();
		$core_fields  = $this->search->type->get_core_data_source_fields();

		// Default skip keys is core and tax fields
		// We want to skip the core search fields, as well as taxonomy fields (those are added in another method)
		$skip_keys = array_merge( $core_fields, $tax_fields );

		// Let's also add the post_title and post_content related fields as well to skip
		$skip_keys[ $this->search->type->post_title_meta_key ]   = $this->search->type->post_title_meta_key;
		$skip_keys[ $this->search->type->post_content_meta_key ] = $this->search->type->post_content_meta_key;

		$no_value_compares = array( 'EXISTS', 'NOT EXISTS' );

		// But more can be added through filter to skip from adding to meta queries
		$skip_meta_query_keys = apply_filters( "search_and_filtering_add_meta_queries_skip_{$this->search->type->slug}_meta_keys", $skip_keys, $tax_fields, $field_values, $core_fields, $this );

		foreach ( $field_values as $meta_key => $meta_value ) {

			if ( array_key_exists( $meta_key, $skip_meta_query_keys ) || $meta_value === '' ) {
				continue;
			}

			$compare = $this->search->get_field_compare( $meta_key );
			if ( $compare && in_array( $compare, $no_value_compares ) ) {
				$meta_query = array(
					'key'     => $this->get_meta_query_meta_key( "_{$meta_key}" ),
					'compare' => $compare
				);

				$query_args['meta_query'][] = $meta_query;
				continue;
			}

			if ( $this->add_serialized_meta_queries( $meta_key, $meta_value, $query_args ) ) {
				continue;
			}

			if( is_array( $meta_value ) ){

				/**
				 * If only one value is submitted with a multi-value search (ie multiselect but only one value is selected),
				 * we just strip out that value to prevent creating a nested OR meta array that is not necessary.
				 */
				if( count( $meta_value ) === 1 ){

					$meta_value = $meta_value[0];

				} else {

					if( $this->add_multi_search_value_single_field_value_queries( $meta_key, $meta_value, $query_args ) ){
						continue;
					}

				}

			}

			$meta_query = array(
				'key'   => $this->get_meta_query_meta_key( "_{$meta_key}" ),
				'value' => $meta_value,
			);

			/**
			 * @see https://developer.wordpress.org/reference/classes/wp_meta_query/
			 */
			if ( is_numeric( $meta_value ) ) {
				$meta_query['type'] = 'NUMERIC';
			}

			if ( $compare ) {
				$meta_query['compare'] = $compare;
			}

//			$meta_query = $this->add_search_include_empty( $meta_query, $meta_key );

			$query_args['meta_query'][] = $meta_query;
			// Already default just here for sake of clarity
			// $query_args['meta_query']['relation'] = 'AND';
		}

		return $query_args;
	}

	/**
	 * Add "Empty" Values to Meta Query
	 *
	 * @param array  $meta_query
	 * @param string $meta_key
	 * @param bool   $check_config
	 *
	 * @return array
	 * @since 1.1.9
	 *
	 */
	public function add_search_include_empty( $meta_query, $meta_key, $check_config = true ) {

		/**
		 * Check if we need to include "empty" value listings as well (keep as == true for type coercion)
		 *
		 * get_search_config will return false if key does not exist (meaning still should not add as disabled by default)
		 */
		if ( $check_config && $this->search->get_search_config( $meta_key, 'search_include_empty' ) == false ) {
			return $meta_query;
		}

		$_meta_key = $this->get_meta_query_meta_key( "_{$meta_key}" );

		$empty = array(
			'relation' => 'OR',
			array(
				'key' => $_meta_key,
				'compare' => 'NOT EXISTS'
			),
			array(
				'key' => $_meta_key,
				'value' => ''
			)
		);

		$with_empty = array(
			'relation' => 'OR',
			$meta_query,
			$empty
		);

		return $with_empty;
	}

	/**
	 * Add Multi Search Value (for Single Field Value) Meta Queries
	 *
	 * Because you can search for multiple values from the S&F area, in the instance that the field is just a single value field, we have to separate
	 * out the queries for searching, otherwise the search will end up as a search for an array instead of the actual value itself.
	 *
	 * @param $meta_key
	 * @param $meta_values
	 * @param $query_args
	 *
	 * @return bool
	 * @since 1.1.9
	 *
	 */
	public function add_multi_search_value_single_field_value_queries( $meta_key, $meta_values, &$query_args ) {
		if( ! is_array( $meta_values ) || empty( $meta_values ) ){
			return false;
		}

		$compare = $this->search->get_field_compare( $meta_key );

		/**
		 * When compare is EQUALS technically that will never happen with multiple values (ie a multiselect),
		 * so we use the IN comparison to check if that value is any of the values submitted.
		 */
		if( $compare === '=' ){
			$query_args['meta_query'][] = array(
				'compare' => 'IN',
				'key'     => $this->get_meta_query_meta_key( "_{$meta_key}" ),
				'value'   => $meta_values
			);

			return true;
		}

		$meta_query_group = array(
			'relation' => 'OR',
		);

		foreach ( (array) $meta_values as $meta_value ) {

			$add_query = array(
				'key'   => $this->get_meta_query_meta_key( "_{$meta_key}" ),
				'value' => $meta_value
			);

			if ( is_numeric( $meta_value ) ) {
				$meta_query['type'] = 'NUMERIC';
			}

			if ( $compare ) {
				$add_query['compare'] = $compare;
			}

			$meta_query_group[] = $add_query;
		}

//		$meta_query_group = $this->add_search_include_empty( $meta_query_group, $meta_key );

		$query_args['meta_query'][] = $meta_query_group;

		return true;
	}

	/**
	 * Add Serialized Meta Queries
	 *
	 * Because multiple value field types are saved to meta as a serialized array, we have to format our meta queries accordingly,
	 * based on the field configuration (using OR/AND relation) to query as the serialized data.
	 *
	 * @param $meta_key
	 * @param $meta_values
	 * @param $query_args
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function add_serialized_meta_queries( $meta_key, $meta_values, &$query_args ) {

		$config = $this->search->get_search_config();

		/**
		 * First let's check if the passed config has this field configured as a "multi-value" field type,
		 * meaning the value in the database should be a serialized array
		 */
		if ( ! array_key_exists( $meta_key, $config ) || ! array_key_exists( 'multi_value', $config[ $meta_key ] ) || empty( $config[ $meta_key ]['multi_value'] ) ) {
			return false;
		}

		$queries_added    = false;
		$compare_relation = $this->search->get_field_compare_relation( $meta_key, 'AND' );
		$compare          = $this->search->get_field_compare( $meta_key );

		/**
		 * Compare technically can never be equals, so if it's not set or is = we change that
		 * to LIKE so the query actually works correctly.
		 */
		if( ! $compare || $compare === '=' ){
			$compare = 'LIKE';
		}

		if( $compare === '!=' ){
			$compare = 'NOT LIKE';
		}

		if ( $compare_relation === 'OR' ) {

			$meta_query_group = array(
				'relation' => 'OR',
			);

			foreach ( (array) $meta_values as $meta_value ) {

				$add_query = array(
					'key'   => $this->get_meta_query_meta_key( "_{$meta_key}" ),
					/**
					 * Because serialized array data is stored like a:2:{i:0;s:3:"One";i:1;s:3:"Two";} we enclose the value
					 * in double quotes to search specifically for that value in the serialized array
					 */
					'value' => '"' . $meta_value . '"',
				);

				if ( $compare ) {
					$add_query['compare'] = $compare;
				}

				$meta_query_group[] = $add_query;
			}

//			$meta_query_group = $this->add_search_include_empty( $meta_query_group, $meta_key );

			$query_args['meta_query'][] = $meta_query_group;
			$queries_added              = true;

		} elseif ( $compare_relation === 'AND' ) {

			foreach ( (array) $meta_values as $meta_value ) {

				$add_query = array(
					'key'   => $this->get_meta_query_meta_key( "_{$meta_key}" ),
					/**
					 * Because serialized array data is stored like a:2:{i:0;s:3:"One";i:1;s:3:"Two";} we enclose the value
					 * in double quotes to search specifically for that value in the serialized array
					 */
					'value' => '"' . $meta_value . '"',
				);

				if ( $compare ) {
					$add_query['compare'] = $compare;
				}

//				$add_query = $this->add_search_include_empty( $add_query, $meta_key );
				$query_args['meta_query'][] = $add_query;
			}

			$queries_added = true;
		}

		return $queries_added;
	}

	/**
	 * Get Meta Key Mappings
	 *
	 * Some themes use different meta keys to store actual values, than the meta keys that are defined
	 * in WP Job Manager.  This filter is used to define those fields, so when we build the meta query
	 * we use the correct meta key.
	 *
	 * If meta key is passed, and a mapping exists, that mapping field will be returned, otherwise the
	 * original passed meta key will be returned.
	 *
	 * This method also handles duplicate fields with "_sf_2" in them, removing it from the meta key, before
	 * checking the mappings, and also returns the clean meta key (without _sf_2 on it)
	 *
	 *
	 * @param string $meta_key  Meta key for query (by default will always have prepended underscore). Pass empty value/string to return all mappings.
	 *
	 * @return array|string
	 * @since 1.0.0
	 */
	public function get_meta_query_meta_key( $meta_key = '' ) {
		if( is_null( $this->meta_key_mappings ) ){
			/**
			 * Mappings passed through the filter MUST match the field EXACTLY as stored on the listing, the default value
			 * passed to this function will always be "_METAKEY" with prepended underscore.  This is to support fields that
			 * may not be hidden meta.
			 *
			 * Example, if "candidate_rate" field is actually stored in database as "_rate" the returned value through filter
			 * must be $mappings['_candidate_rate'] = '_rate' as all WP Job Manager fields are passed to this method with the
			 * prepended underscore (as that is the actual meta query key)
			 */
			$this->meta_key_mappings = apply_filters( "search_and_filtering_add_meta_queries_{$this->search->type->slug}_meta_key_mappings", array(), $this );
		}

		if( $meta_key ){

			/**
			 * Convert any "duplicate" meta keys to the original meta key, before adding to the
			 * meta query.  This is required as field types that support two "search sources" but use the
			 * same meta key for query, will have "_sf_2" appended to the meta key, to prevent
			 * conflicts in the frontend application (and when submitting POST data).
			 *
			 * While it may be possible to convert that single value to an array, we don't want to do that
			 * as array values are processed differently when searching, and we want to have multiple queries
			 * for the same meta key, with different comparisons, to be possible.
			 */
			$meta_key = str_replace( '_sf_2', '', $meta_key );

			return isset( $this->meta_key_mappings[ $meta_key ] ) ? $this->meta_key_mappings[ $meta_key ] : $meta_key;
		}

		return $this->meta_key_mappings;
	}
}