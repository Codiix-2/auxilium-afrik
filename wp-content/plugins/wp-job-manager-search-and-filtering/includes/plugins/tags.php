<?php

namespace WPJMSF\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tags
 *
 * Normally initialized on plugins_loaded hook from within the type class
 *
 * @package WPJMSF\Plugins
 */
class Tags extends \WPJMSF\Plugin {

	/**
	 * @var string
	 */
	public $plugin_class = 'WP_Job_Manager_Job_Tags';
	/**
	 * @var bool Allow Tag Filter to run
	 */
	public $allow_tag_filter = false;
	/**
	 * Tags constructor.
	 *
	 * @param $type
	 */
	public function __construct( $type ) {
		$this->type = $type;
		$this->remove_hooks();
	}

	/**
	 * Allow Tag Filter
	 *
	 * Because we remove the native tag filter, we need to allow it to run when alerts are actually being generated, otherwise
	 * results will not include the query for tags.
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 1.2.1
	 *
	 */
	public function allow_tag_filter( $args ) {
		$this->allow_tag_filter = true;
		return $args;
	}

	/**
	 * Remove Specific Hooks (that duplicate functionality)
	 *
	 * @since 1.0.0
	 *
	 */
	public function remove_hooks() {
		if( apply_filters( 'search_and_filtering_job_tags_remove_hooks', true, $this ) ){
			remove_class_action( 'job_manager_job_filters_search_jobs_end', 'WP_Job_Manager_Job_Tags_Shortcodes', 'show_tag_filter' );

			remove_class_action( 'job_manager_job_filters_end', 'WP_Job_Manager_Job_Tags_Shortcodes', 'job_manager_job_filters_end' );
			remove_class_filter( 'job_manager_get_listings_result', 'WP_Job_Manager_Job_Tags_Shortcodes', 'job_manager_get_listings_result' );

			add_filter( 'job_manager_alerts_get_job_listings_args', array( $this, 'allow_tag_filter' ) );
			remove_class_filter( 'job_manager_get_listings', 'WP_Job_Manager_Job_Tags_Shortcodes', 'apply_tag_filter' );
			add_filter( 'job_manager_get_listings', array( $this, 'apply_tag_filter' ), 10, 2 );
		}
	}

	/**
	 * Filter by Tag
	 *
	 * This is the native implementation from WP Job Manager Tags, and is used for when job alerts are being generated to make sure the tags are added correctly.
	 *
	 * @param $query_args
	 * @param $args
	 *
	 * @return array
	 * @since 1.2.1
	 *
	 */
	public function apply_tag_filter( $query_args, $args ) {
		if( ! $this->allow_tag_filter ){
			return $query_args;
		}

		if ( isset( $_REQUEST['form_data'] ) ) {
			$params = array();

			parse_str( $_REQUEST['form_data'], $params );

			if ( isset( $params['job_tag'] ) ) {
				$tags      = array_filter( $params['job_tag'] );
				$tag_array = array();

				foreach ( $tags as $tag ) {
					$tag         = get_term_by( 'name', $tag, 'job_listing_tag' );
					$tag_array[] = $tag->slug;
				}

				$query_args['tax_query'][] = array(
					'taxonomy' => 'job_listing_tag',
					'field'    => 'slug',
					'terms'    => $tag_array,
					'operator' => 'any' === get_option( 'job_manager_tags_filter_type', 'any' ) ? "IN" : "AND"
				);

				add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
				add_filter( 'job_manager_get_listings_custom_filter_text', array( $this, 'apply_tag_filter_text' ) );
				add_filter( 'job_manager_get_listings_custom_filter_rss_args', array( $this, 'apply_tag_filter_rss' ) );
			}
		} elseif ( ! empty( $args['search_tags'] ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => 'job_listing_tag',
				'field'    => 'slug',
				'terms'    => $args['search_tags'],
				'operator' => 'any' === get_option( 'job_manager_tags_filter_type', 'any' ) ? "IN" : "AND"
			);
		}

		return $query_args;
	}
}