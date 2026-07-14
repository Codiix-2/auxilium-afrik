<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug
 *
 * @package WPJMSF
 *
 * @since   1.1.14
 *
 */
class Debug {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * @var string
	 */
	public $last_query    = '';
	/**
	 * @var string
	 */
	public $geolocation_query = '';
	/**
	 * @var array[]
	 */
	public $listings_args = array( 'query_args' => array(), 'args' => array() );

	/**
	 * Debug constructor.
	 *
	 * @param $type \WPJMSF\Job|\WPJMSF\Resume
	 */
	public function __construct( $type ) {
		$this->type = $type;
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init
	 *
	 * @since 1.1.14
	 *
	 */
	public function init() {

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->add_hooks();
		$this->disable_cache();
	}

	/**
	 * Add Debug to AJAX Response
	 *
	 * @param $results
	 * @param $listings
	 *
	 * @return mixed
	 * @since 1.1.14
	 *
	 */
	public function listings_ajax_response( $results, $listings ) {
		global $wpdb;

		if( $listings && isset( $listings->query ) ){

			$results['debug'] = array(
				'request'        => isset( $listings->request ) ? htmlentities( $listings->request ) : '',
				'actual_last_query' => htmlentities( $wpdb->last_query ),
				'geolocation_query' => htmlentities( $this->geolocation_query ),
				'query'             =>  $listings->query,
				'listings_args'     =>  $this->listings_args,
				'wpjmsf'            => array(
					'fields' => $this->type->search->get_field_values(),
					'custom' => $this->type->search->get_custom_values(),
					'config' => $this->type->search->get_search_config()
				)
			);
			
		}

		return $results;
	}

	/**
	 * Is Debug Enabled?
	 *
	 * @return mixed|void
	 * @since 1.1.14
	 *
	 */
	public function is_enabled() {
		$is_enabled = false;
		$settings = get_option( $this->type->settings_option, array( 'enable_debug' => 0 ) );
		if ( is_array( $settings ) && isset( $settings['enable_debug'] ) && ! empty( $settings['enable_debug'] ) ) {
			$is_enabled = true;
		}

		return apply_filters( 'search_and_filtering_debug_is_enabled', $is_enabled, $this );
	}

	/**
	 * Set Last Query Values
	 *
	 * @param $query_args
	 * @param $args
	 *
	 * @since 1.1.14
	 *
	 */
	public function set_last_query( $query_args, $args ) {
		global $wpdb;
		$this->last_query = $wpdb->last_query;
		$this->listings_args['query_args'] = $query_args;
		$this->listings_args['args'] = $args;
	}

	/**
	 * Set Geolocation Query
	 *
	 * @param $query
	 *
	 * @return mixed
	 * @since 1.2.1
	 *
	 */
	public function set_geolocation_query( $query ) {
		$this->geolocation_query = $query;
		return $query;
	}

	/**
	 * Placeholder
	 *
	 * @since 1.1.14
	 *
	 */
	public function add_hooks() {}

	/**
	 * Placeholder
	 *
	 * @since 1.1.14
	 *
	 */
	public function disable_cache() {}
}