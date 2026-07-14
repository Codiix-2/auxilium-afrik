<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// CURRENTLY NOT USED, EXTENDED CLASSES INITIALIZED IN TYPE CLASSES

/**
 * Class Plugins
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Plugins {

	/**
	 * Plugins constructor.
	 *
	 */
	public function __construct() {
		$this->include_plugin_files();
	}

	function include_plugin_files( $directory = '' ){
		$directory = empty( $directory ) ? WPJM_SEARCH_FILTERING_INCLUDES . '/plugins' : $directory;

		foreach ( glob( "{$directory}/*" ) as $file ) {
			if ( is_dir( $file ) ) {
				$this->include_plugin_files( $file );
			// Make sure that actual file ends in PHP before including
			} elseif ( strtolower( substr( $file, -4 ) ) === '.php' ) {
				include_once $file;
			}
		}
	}

}
