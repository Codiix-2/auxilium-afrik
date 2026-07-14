<?php

namespace WPJMSF\Metacleaner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Cleaner Loader
 * @version 1.0.0
 */
class Loader {

	/**
	 * Meta Cleaner Loader
	 */
	public function __construct() {
		add_filter( 'job_manager_meta_cleaner_versions', array( $this, 'meta_cleaner_versions' ), 11 );
		add_action( 'plugins_loaded', array( $this, 'meta_cleaner' ) );
	}

	/**
	 * Version Handling (to load latest version)
	 *
	 * @param $versions
	 *
	 * @since 1.1.14
	 *
	 */
	public function meta_cleaner_versions( $versions ) {
		/**
		 * If on dev environment only load this one to use source files
		 */
		if( defined( 'SMYLES_DEVN' ) && SMYLES_DEVN ){
			$versions = array();
		}

		$versions[] = array(
			'version' => '1.0.1',
			'file'    => WPJM_SEARCH_FILTERING_INCLUDES . '/metacleaner/cleaner.php',
			'assets'  => WPJM_SEARCH_FILTERING_ASSETS
		);

		return $versions;
	}

	/**
	 * Initialize Meta Cleaner
	 *
	 * @since 1.1.14
	 *
	 */
	public function meta_cleaner() {

		/**
		 * If class already exists, means another plugin has already
		 * loaded the cleaner
		 */
		if( class_exists( '\sMyles\WPJM\EMC\Cleaner') ){
			return;
		}

		$versions = apply_filters( 'job_manager_meta_cleaner_versions', array() );

		if( empty( $versions ) ){
			return;
		}

		usort( $versions, function( $a, $b ){
			return -1 * version_compare( $a['version'], $b['version'] );
		});

		if( ! isset( $versions[0], $versions[0]['file'] ) ){
			return;
		}

		require_once $versions[0]['file'];

		\sMyles\WPJM\EMC\Cleaner::get_instance( $versions[0]['assets'] );
	}
}