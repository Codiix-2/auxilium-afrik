<?php

namespace WPJMSF\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Regions
 *
 * Normally initialized on plugins_loaded hook from within the type class
 *
 * @package WPJMSF\Plugins
 */
class Regions extends \WPJMSF\Plugin {

	public $plugin_class = 'Astoundify_Job_Manager_Regions';

	/**
	 * Regions constructor.
	 *
	 * @param $type
	 */
	public function __construct( $type ) {
		$this->type = $type;
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99999 );
	}

	/**
	 * Check to Dequeue
	 *
	 * @since 1.1.0
	 *
	 */
	public function enqueue_scripts() {
		if( apply_filters( 'search_and_filtering_regions_plugin_dequeue_scripts', true ) ){
			if( wp_script_is( 'job-regions' ) ){
				wp_dequeue_script( 'job-regions' );
			}
		}
	}
}