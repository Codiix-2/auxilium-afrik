<?php

namespace WPJMSF\Search\NoValue;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Meta
 *
 * @package WPJMSF
 *
 * @since   1.1.9
 *
 */
class Meta {

	/**
	 * @var \WPJMSF\Search\NoValue
	 */
	public $novalue;

	/**
	 * @var array Meta keys found during processing for meta (used to check if we need to combine queries after)
	 */
	private $modified_queries = array();

	/**
	 * @param $novalue \WPJMSF\Search\NoValue
	 */
	public function __construct( $novalue ) {
		$this->novalue = $novalue;
	}

	/**
	 * Process and Generate New Meta Queries
	 *
	 * @return bool
	 * @since 1.1.14
	 *
	 */
	public function process() {

		if ( ! isset( $this->novalue->query['meta_query'] ) || empty( $this->novalue->query['meta_query'] ) ) {
			return false;
		}

		$this->novalue->query['meta_query'] = $this->process_queries( $this->novalue->query['meta_query'] );

		$this->rebuild_query();

		return true;
	}

	/**
	 * Process Queries (checking to add empty query)
	 *
	 * @param $queries
	 *
	 * @return mixed
	 * @since 1.1.14
	 *
	 */
	public function process_queries( $queries, $return_false_if_not_processed = false ) {

		$processed = false;
		$relation = isset( $queries['relation'] ) ? $queries['relation'] : 'AND';

		/**
		 * We don't currently do anything for fields that have "include_empty" enabled but no value is submitted with the search,
		 * as technically there's no reason to add extra meta queries.
		 */
		foreach ( $queries as $mqi => $mq ) {

			/**
			 * If it's not numeric, that means it's probably a 'relation' key, so we skip
			 */
			if ( ! is_numeric( $mqi ) ) {
				continue;
			}

			/**
			 * Means this is a top level meta query
			 */
			if ( isset( $mq['key'] ) ) {

				$actual_meta_key = $this->get_actual_meta_key( $mq['key'] );

				/**
				 * Not one of our fields that should include empty values, skip to next
				 */
				if ( ! array_key_exists( $actual_meta_key, $this->novalue->include_empties ) ) {
					continue;
				}

				/**
				 * Handle multiple separate queries for same meta key
				 *
				 * Based on being AND at top level
				 *
				 * If we've already generated the 'no value' meta query, we don't want to generate it again,
				 * so instead we modify the original query array, to use AND as the operator (instead of just that query)
				 */
				if ( array_key_exists( $actual_meta_key, $this->modified_queries ) ) {

					if( strtoupper( $relation ) === 'OR' ){

						/**
						 * Relation is OR
						 *
						 * As this point, we already have an OR relation setup for the empty field values,
						 * so we just add the additional query to the OR query
						 */
						$new_modified_query = array_merge(
							$this->modified_queries[ $actual_meta_key ],
							array( $mq )
						);

						$this->modified_queries[ $actual_meta_key ] = $new_modified_query;
						$processed = true;
					} else {

						/**
						 * Relation is AND
						 */

						/**
						 * Original query is always going to be at index 0 and NOT EXIST at 1, empty string at 2
						 */
						$original       = $this->modified_queries[ $actual_meta_key ][0];
						$combined_query = array(
							'relation' => 'AND',
							$original,
							$mq
						);

						$this->modified_queries[ $actual_meta_key ][0] = $combined_query;
						$processed = true;
					}

				} else {

					if ( strtoupper( $relation ) === 'OR' ) {
					} else {
					}

					$new_meta_query                             = $this->add_empty( $mq, $mq['key'] );
					$this->modified_queries[ $actual_meta_key ] = $new_meta_query;
					$processed = true;
				}

			} else {

				$nested_processed = $this->process_queries( $queries[ $mqi ], true );
				if( $nested_processed ){
					$processed = true;
					$queries = $nested_processed;
				}
				if( isset( $queries['relation'] ) ){
					unset( $queries['relation'] );
				}
			}

			if( $processed ){
				/**
				 * Remove the original query, we will add it back after all processing is done
				 */
				unset( $queries[ $mqi ] );
			}

		}

		return $return_false_if_not_processed && ! $processed ? false : $queries;

	}

	/**
	 * Add "Empty" Values to Meta Query
	 *
	 * @param array  $meta_query
	 * @param string $_meta_key
	 *
	 * @return array
	 * @since 1.1.9
	 *
	 */
	public function add_empty( $meta_query, $_meta_key ) {

		$with_empty = array(
			'relation' => 'OR',
			$meta_query,
			array(
				'key'     => $_meta_key,
				'compare' => 'NOT EXISTS'
			)
		);

		return $with_empty;
	}

	public function check_queries( $queries ) {

		/**
		 * We don't currently do anything for fields that have "include_empty" enabled but no value is submitted with the search,
		 * as technically there's no reason to add extra meta queries.
		 */

		foreach ( $queries as $mqi => $mq ) {

			/**
			 * If it's not numeric, that means it's probably a 'relation' key
			 */
			if ( ! is_numeric( $mqi ) ) {
				// TODO: maybe figure out how to check for OR when key is 'relation' (if that ever happens)
				continue;
			}

			/**
			 * Means this is a top level meta query
			 */
			if ( ! isset( $mq['key'] ) ) {

				$nested_mk_vals = $this->has_nested_include_empty( $mqi, $mq );

				if ( empty( $nested_mk_vals ) ) {
					continue;
				}

				/**
				 * At this point we know it has a nested include empty field, so we don't need to check
				 * $this->novalue->include_empties like below for non-nested queries
				 */
				$nested_relation = isset( $mq['relation'] ) ? $mq['relation'] : 'AND';

				if( strtoupper( $nested_relation ) === 'OR' ){

					$this->modified_queries[ $nested_mk_vals['actual'] ] = array_merge(
						$mq,
						array(
							'key'     => $nested_mk_vals['query'],
							'compare' => 'NOT EXISTS'
						)
					);

				} else {

				}

			} else {

				$actual_meta_key = $this->get_actual_meta_key( $mq['key'] );

				/**
				 * Not one of our fields that should include empty values, skip to next
				 */
				if ( ! array_key_exists( $actual_meta_key, $this->novalue->include_empties ) ) {
					continue;
				}

				/**
				 * Handle multiple separate queries for same meta key
				 *
				 * Based on being AND at top level
				 *
				 * If we've already generated the 'no value' meta query, we don't want to generate it again,
				 * so instead we modify the original query array, to use AND as the operator (instead of just that query)
				 */
				if ( array_key_exists( $actual_meta_key, $this->modified_queries ) ) {

					/**
					 * Original query is always going to be at index 0 and NOT EXIST at 1, empty string at 2
					 */
					$original       = $this->modified_queries[ $actual_meta_key ][0];
					$combined_query = array(
						'relation' => 'AND',
						$original,
						$mq
					);

					$this->modified_queries[ $actual_meta_key ][0] = $combined_query;

				} else {

					$new_meta_query = $this->add_empty( $mq, $mq['key'] );

					$this->modified_queries[ $actual_meta_key ] = $new_meta_query;

				}

			}

			/**
			 * Remove the original query, we will add it back after all processing is done
			 */
			unset( $queries[ $mqi ] );
		}

		return $queries;
	}

	public function has_nested_include_empty( $mqi, $mq ) {

		$has_key = false;

		foreach( $mq as $index => $query ){

			if( ! is_numeric( $index ) ){
				continue;
			}

			if( ! isset( $query['key'] ) ){

				$has_key = $this->has_nested_include_empty( $index, $query );

			} else {

				$actual_meta_key = $this->get_actual_meta_key( $mq['key'] );

				if ( array_key_exists( $actual_meta_key, $this->novalue->include_empties ) ) {
					$has_key = array(
						'actual' => $actual_meta_key,
						'query' => $mq['key']
					);
				}
			}

			if( $has_key ){
				break;
			}
		}

		return $has_key;
	}

	/**
	 * Rebuild Query
	 *
	 * Because we removed the original queries from the array, we have to rebuild it adding back our generated/modified
	 * meta queries.
	 *
	 * @since 1.1.14
	 *
	 */
	public function rebuild_query() {

		if( empty( $this->modified_queries ) ){
			return true;
		}

		/**
		 * Use array_values to strip out associative keys (since we no longer need them)
		 */
		$modified_queries = array_values( $this->modified_queries );

		/**
		 * Merge modified back into meta query.  This will also re-index the array, so we don't have to worry about
		 * doing a manual re-index from the unset in $this->process()
		 */
		$rebuilt = array_merge(
			$this->novalue->query['meta_query'],
			$modified_queries
		);

		$this->novalue->query['meta_query'] = $rebuilt;
	}

	/**
	 * Get Actual Meta Key
	 *
	 * @param $key
	 *
	 * @return array|string
	 * @since 1.1.14
	 *
	 */
	public function get_actual_meta_key( $key ) {

		/**
		 * Almost all WPJM meta fields are saved as hidden meta, which means they include a prepended underscore.
		 *
		 * To match again config we have already pulled/generated, we remove the leading underscore if it exists.
		 */
		if ( substr( $key, 0, 1 ) === '_' ) {
			$key = substr( $key, 1 );
		}

		return $this->novalue->search->meta->get_meta_query_meta_key( $key );
	}
}