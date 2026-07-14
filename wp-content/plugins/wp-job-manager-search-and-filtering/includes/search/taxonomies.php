<?php

namespace WPJMSF\Search;

use function WPJMSF\wpjmsf_sanitize_value;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Taxonomies
 *
 * @package WPJMSF
 *
 * @since   1.0.0
 *
 */
class Taxonomies {

	/**
	 * @var \WPJMSF\Search
	 */
	public $search;

	public $taxonomies = array();

	/**
	 * Taxonomies constructor.
	 *
	 * @param \WPJMSF\Search $search
	 */
	public function __construct( $search ) {
		$this->search = $search;
	}

	/**
	 * Add Taxonomy `tax_query` to Query Args
	 *
	 *
	 * @param $query_args
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function add_queries( $query_args ) {

		$tax_fields   = $this->get_taxonomy_fields();
		$maps         = $this->search->type->get_search_maps();
		$field_values = $this->search->get_field_values();

		foreach ( $tax_fields as $meta_key => $taxonomy ) {
			// We don't need to add taxonomies that should already be inserted in with core search fields
			if ( array_key_exists( $taxonomy, $maps ) || empty( $field_values[ $meta_key ] ) ) {
				continue;
			}

			$tax_values = $this->search->sanitize_values_to_array( $field_values[ $meta_key ] );
			/**
			 * term_id by default, but if for some reason the value is not numeric, it's possible the value
			 * is a slug, so we need to use that instead.
			 */
			$field = ! empty( $tax_values) && isset( $tax_values[0] ) && ! is_numeric( $tax_values[0] ) ? 'slug' : 'term_id';

			$tax_query_to_add = array(
				'taxonomy' => $taxonomy,
				'field'    => $field,
				'terms'    => array_values( $tax_values )
			);

			$compare_relation = $this->search->get_field_compare_relation( $meta_key, 'IN' );
			/**
			 * We only want to set the AND operator if there are more than 1 values selected,
			 * and the compare relation is configured to AND (default is IN)
			 */
			if( $compare_relation === 'AND' && count( $tax_values ) > 1 ){
				$tax_query_to_add['operator'] = 'AND';
			}

			$tax_query_to_add = $this->add_search_include_empty( $tax_query_to_add, $meta_key, $taxonomy );

			$query_args['tax_query'][] = $tax_query_to_add;
		}

		return $query_args;
	}

	/**
	 * Add "Empty" Values to Taxonomy Query
	 *
	 * Similar to the meta handling, this method will just add a "NOT EXISTS" to the taxonomy query,
	 * wrapping it in an array (for a separate query).
	 *
	 * @param array  $tax_query
	 * @param string $meta_key
	 * @param string $taxonomy
	 * @param bool   $check_config
	 *
	 * @return array
	 * @since 1.1.9
	 *
	 */
	public function add_search_include_empty( $tax_query, $meta_key, $taxonomy, $check_config = true ) {

		/**
		 * Check if we need to include "empty" value listings as well (keep as == true for type coercion)
		 *
		 * get_search_config will return false if key does not exist (meaning still should not add as disabled by default)
		 */
		if ( $check_config && $this->search->get_search_config( $meta_key, 'search_include_empty' ) == false ) {
			return $tax_query;
		}

		$with_empty = array(
			'relation' => 'OR',
			array(
				'taxonomy' => $taxonomy,
				'operator' => 'NOT EXISTS'
			),
			$tax_query,
		);

		return $with_empty;
	}

	/**
	 * Get Taxonomy fields from wpjmsf_taxonomies in $_REQUEST
	 *
	 *
	 * @return array|mixed|void
	 * @since 1.0.0
	 *
	 */
	public function get_taxonomy_fields() {

		if ( empty( $this->taxonomies ) ) {
			$taxonomies       = isset( $_REQUEST['wpjmsf_taxonomies'] ) ? $_REQUEST['wpjmsf_taxonomies'] : array();
			$taxonomies       = wpjmsf_sanitize_value( $taxonomies );
			$this->taxonomies = apply_filters( 'search_and_filtering_search_get_taxonomy_fields', $taxonomies, $this );
		}

		return $this->taxonomies;
	}
}