<?php

namespace WPJMSF\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Core
 *
 * @package WPJMSF
 *
 * @since   1.0.0
 *
 */
class Core {

	/**
	 * @var \WPJMSF\Search
	 */
	public $search;

	/**
	 * Core constructor.
	 *
	 * @param \WPJMSF\Search $search
	 */
	public function __construct( $search ) {
		$this->search = $search;
	}

	/**
	 * Merge Taxonomies into Core Search Fields
	 *
	 * Because core search fields (like search_categories) can be used, as well as specific taxonomies,
	 * this method will merge any fields used with the search source as the taxonomy, with the associated
	 * core search field (if one exist)
	 *
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function merge_taxonomies( $args ) {

		$maps          = $this->search->type->get_search_maps();
		$field_values  = $this->search->get_field_values();
		$sf_taxonomies = $this->search->taxonomies->get_taxonomy_fields();

		foreach ( $sf_taxonomies as $meta_key => $taxonomy ) {

			if ( array_key_exists( $taxonomy, $maps ) && array_key_exists( $meta_key, $field_values ) && ! empty( $field_values[ $meta_key ] ) ) {
				$args_key  = $maps[ $taxonomy ];
				$tax_value = $this->search->sanitize_values_to_array( $field_values[ $meta_key ] );

				if ( ! is_array( $args[ $args_key ] ) ) {
					$args[ $args_key ] = array( $args[ $args_key ] );
				}

				$args[ $args_key ] = array_merge( $args[ $args_key ], $tax_value );
			}

		}

		return $args;
	}

	/**
	 * Generate "Include Anywhere" Location Meta Query
	 *
	 * @param $meta_key
	 *
	 * @return array
	 * @since 1.1.26
	 *
	 */
	private function generate_include_anywhere_query( $meta_key ){

		/**
		 * Add meta query to include listings without ANY value set
		 */
		$include_anywhere_query = array(
			'key'     => $meta_key,
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
					'key'     => $meta_key,
					'compare' => 'NOT EXISTS'
				);
			}
		}

		return $include_anywhere_query;
	}

	/**
	 * Find and Add Nested "Include Anywhere" Meta Query
	 *
	 * @param $mqs
	 * @param $search_location
	 * @param $search_location_keys
	 *
	 * @return false|string
	 * @since 1.1.26
	 *
	 */
	private function find_and_add_anywhere_query( &$mqs, $search_location, $search_location_keys = array( '_job_location', '_candidate_location' ) ) {

		$found = false;

		if( isset( $mqs['value'], $mqs['key'] ) ) {
			return in_array( $mqs['key'], $search_location_keys ) && $mqs['value'] === $search_location ? $mqs['key'] : false;
		}

		foreach ( $mqs as $key => $value ) {
			if ( ! is_int( $key ) ) {
				continue;
			}

			/**
			 * Nested array meta query
			 */
			if ( is_array( $value ) && ! isset( $value['key'] ) ) {
				$found_nested = $this->find_and_add_anywhere_query( $value, $search_location, $search_location_keys );
				if ( $found_nested ) {
					$mqs[ $key ][] = $this->generate_include_anywhere_query( $found_nested );
					break;
				}
			}

			if ( isset( $value['key'] ) && in_array( $value['key'], $search_location_keys ) && $value['value'] === $search_location ) {
				$found = $value['key'];
				break;
			}
		}

		return $found;
	}

	/**
	 * Check search_location include "Anywhere"
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 1.1.9
	 *
	 */
	public function check_search_location_anywhere( $args ) {
		$include_anywhere = $this->search->get_search_config( 'search_location', 'include_anywhere' );
		$search_location = $this->search->get_field_values( 'search_location' );
		/**
		 * If include_anywhere is not enabled, there is no value for search location, meta query is not set (or is empty),
		 * no need to go further.
		 */
		if( empty( $include_anywhere ) || empty( $search_location ) || ! isset( $args['meta_query'] ) || empty( $args['meta_query'] ) ){
			return $args;
		}

		$search_location_keys = apply_filters( 'search_and_filtering_core_search_location_anywhere_keys', array( '_job_location', '_candidate_location' ), $args, $search_location, $this );

		foreach ( $args['meta_query'] as $mqi => $mq ) {
			if ( is_int( $mqi ) ) {

				/**
				 * Leave as direct access on $query_args for passing as reference (doesn't seem to work passing $meta)
				 */
				$found_top_level = $this->find_and_add_anywhere_query( $args['meta_query'][ $mqi ], $search_location, $search_location_keys );
				if( $found_top_level ) {
					$args['meta_query'][ $mqi ][] = $this->generate_include_anywhere_query( $found_top_level );
				}

			}
		}

		return $args;
	}

	/**
	 * Add Core Field Values to Query Args
	 *
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function add_args( $args ) {

		$values = $this->search->get_field_values();
		$core   = $this->search->type->get_core_data_source_fields();

		foreach ( $values as $field => $value ) {

			if ( ! array_key_exists( $field, $core ) ) {
				continue;
			}

			if ( $field === 'filled' ) {
				$args['filled'] = $value !== 'false' && ! empty( $value );
			}

			if ( $field === 'remote_position' ) {
				$args['remote_position'] = $value !== 'false' && ! empty( $value );
			}

			if ( $field === 'featured' ) {
				$args['featured'] = $value !== 'false' && ! empty( $value );
				$args['orderby']  = 'featured' === $args['orderby'] ? 'date' : $args['orderby'];
			}

			if ( $field === 'search_keywords' ) {
				// This is sanitized in get_job_listings() but do it anyways just to be sure
				$args['search_keywords'] = sanitize_text_field( wp_unslash( $value ) );
			}

			if ( $field === 'search_location' ) {
				if( is_array( $value ) ){
					// strip out empty values
					$value = array_filter( $value );
					// Convert to Atlanta;New York;Chicago format to support multiselect field types
					$value = implode( ';', $value );
				}
				$args['search_location'] = sanitize_text_field( wp_unslash( $value ) );
			}

			if ( ! empty( $value ) ) {

				if ( $field === 'orderby' ) {
					$order_by = sanitize_text_field( wp_unslash( $value ) );
					/**
					 * If for some reason "distance" or "distance_featured" is set as orderby value, we need to change it to a default
					 * supported value, as the "distance" orderby is only supported when doing a "radius" search
					 */
					if( $order_by === 'distance' || $order_by === 'distance_featured'){
						$order_by = apply_filters( "search_and_filtering_{$this->search->type->slug}_core_orderby_distance_default", 'featured', $order_by, $args, $this );
					}
					$args['orderby'] = $order_by;
				}

				if ( $field === 'order' ) {
					$args['order'] = strtoupper( $value ) === 'ASC' ? 'ASC' : 'DESC';
				}

				if ( $field === 'page' ) {
					$args['page'] = absint( $value );
				}

				if ( $field === 'per_page' ) {
					$args['per_page'] = absint( $value );
				}

				if ( $field === 'post_status' ) {
					$args['post_status'] = array_filter( array_map( 'sanitize_title', wp_unslash( (array) $value ) ) );
				}

			}

		}

		return $args;
	}

	/**
	 * Toggle the posts_where filter
	 *
	 * @param bool $on
	 *
	 * @since 1.0.0
	 *
	 */
	public function toggle_posts_where_filter( $on = true ) {

		if ( ! $on ) {
			remove_filter( 'posts_where', array( $this, 'posts_where' ), 9999 );
		} elseif ( ! has_filter( 'posts_where', array( $this, 'posts_where' ) ) ) {
			add_filter( 'posts_where', array( $this, 'posts_where' ), 9999, 2 );
		}
	}

	/**
	 * Add Custom Post Specific Query Args
	 *
	 * This method adds specific post related query args (that we have to build into custom query), to the initial
	 * WP_Query arguments, so we can check for them in the core posts_where filter, and build out the custom SQL query.
	 *
	 * @param $query_args
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function add_custom_post_query_args( $query_args ) {

		$custom_added = false;
		$field_values = $this->search->get_field_values();

		if ( array_key_exists( $this->search->type->post_title_meta_key, $field_values ) && $field_values[ $this->search->type->post_title_meta_key ] !== '' ) {
			$query_args['wpjmsf_post_title'] = $field_values[ $this->search->type->post_title_meta_key ];
			$custom_added                    = true;
		}

		if ( array_key_exists( $this->search->type->post_content_meta_key, $field_values ) && $field_values[ $this->search->type->post_content_meta_key ] !== '' ) {
			$query_args['wpjmsf_post_content'] = $field_values[ $this->search->type->post_content_meta_key ];
			$custom_added                      = true;
		}

		/**
		 * Resumes does not correctly support post_status query arg, so we need to add it manually
		 */
		if( $this->search->type->slug === 'resume' && array_key_exists('post_status', $field_values ) && ! empty( $field_values['post_status'] ) ){
			$query_args['post_status'] = $field_values['post_status'];
		}

		if ( $custom_added ) {
			$this->toggle_posts_where_filter( true );
		}

		$this->search->has_posts_where = $custom_added;

		return $query_args;
	}

	/**
	 * Add WHERE clauses to query (for specific post type fields)
	 *
	 * @param $where
	 * @param $wp_query
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function posts_where( $where, $wp_query ) {

		global $wpdb;

		if ( $wpjmsf_post_title = $wp_query->get( 'wpjmsf_post_title' ) ) {
			$compare_query = $this->search->build_compare_query( $this->search->type->post_title_meta_key, $wpjmsf_post_title );
			$where         .= ' AND ' . $wpdb->posts . '.post_title ' . $compare_query;
		}

		if ( $wpjmsf_post_content = $wp_query->get( 'wpjmsf_post_content' ) ) {
			$compare_query = $this->search->build_compare_query( $this->search->type->post_content_meta_key, $wpjmsf_post_content );
			$where         .= ' AND ' . $wpdb->posts . '.post_content ' . $compare_query;
		}

		return $where;
	}
}