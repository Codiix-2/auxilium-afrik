<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AutoUpdates
 *
 * @package WPJMSF
 */
class AutoUpdates {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var \WP_Query
	 */
	public $listings;
	/**
	 * @var array
	 */
	public $results;

	/**
	 * AutoUpdates constructor.
	 *
	 * @param $type \WPJMSF\Job|\WPJMSF\Resume
	 * @param $listings \WP_Query
	 * @param $results array
	 */
	public function __construct( $type, $listings, $results ) {
		$this->type = $type;
		$this->listings = $listings;
		$this->results = $results;
	}

	/**
	 * Get Updated Results (to send with Listings response)
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function get_updated_results() {

		if( ! isset( $_POST['wpjmsf_auto_updates'] ) || empty( $_POST['wpjmsf_auto_updates'] ) ){
			return $this->results;
		}

		$found_listing_ids = wp_list_pluck( $this->listings->posts, 'ID' );

		$auto_updates = array();

		$s = new Sanitizer();
		$updates = $s->sanitize( $_POST['wpjmsf_auto_updates'] );
		if( empty( $updates ) ){
			return $this->results;
		}

		foreach( (array) $updates as $search_source => $config ){

			if ( empty( $found_listing_ids ) ) {
				/**
				 * If the auto update field is NOT apart of the current query, and there were no listings found,
				 * we want to return an empty string to remove all options from showing (since there will be none).
				 *
				 * TODO: probably need to add handling in Vue side, when a reset is made, or search query technically has no "queries" that it uses the default value/options without having to do this query handling
				 */
				if( ! $this->query_includes_field( $search_source, $config ) ){
					$auto_updates[ $search_source ] = '';
				}

				/**
				 * If the current query did include this field in the search query, we don't want to do any kind of update,
				 * as we need the user to be able to "deselect" the value, and returning empty string would cause the field
				 * to not show or not have an options (including the selected one)
				 */
				continue;
			}

			if( $config['type'] === 'tag_cloud' ){
				$tc = new TagCloud( $this->type, $config['taxonomy'] );
				$tc->set_config( $config );
				$tc->set_only_in_posts( $found_listing_ids );
				$auto_updates[ $search_source ] = $tc->get_html();
			}
		}

		if( ! empty( $auto_updates ) ){
			$this->results['auto_updates'] = apply_filters( "search_and_filtering_{$this->type->slug}_get_auto_updates_results", $auto_updates, $updates, $found_listing_ids, $this );
		}

		return $this->results;
	}

	/**
	 * Check if current query includes a specific field
	 *
	 * @param $search_source
	 * @param $config
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function query_includes_field( $search_source, $config ) {

		$includes_field = false;

		$taxonomy = isset( $config['taxonomy'] ) && ! empty( $config['taxonomy'] ) ? $config['taxonomy'] : false;

		if( $taxonomy ){

			if( isset( $this->listings->tax_query, $this->listings->tax_query->queries ) && ! empty( $this->listings->tax_query->queries ) ){
				foreach( (array) $this->listings->tax_query->queries as $tax_query ){
					if( $tax_query['taxonomy'] === $taxonomy ){
						$includes_field = true;
						break;
					}
				}
			}

		} else {

			if( isset( $this->listings->meta_query, $this->listings->meta_query->queries ) && ! empty( $this->listings->meta_query->queries ) ){

				foreach( (array) $this->listings->meta_query->queries as $meta_query ){
					if( $meta_query['meta_key'] === $search_source ){
						$includes_field = true;
						break;
					}
				}

			}

		}

		return $includes_field;
	}
}