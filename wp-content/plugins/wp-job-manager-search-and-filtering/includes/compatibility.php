<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Compatibility
 *
 * @package WPJMSF
 */
class Compatibility {

	/**
	 * Compatibility constructor.
	 */
	public function __construct() {

		/**
		 * Autoptimize
		 */
		add_filter( 'autoptimize_filter_js_exclude', array( $this, 'autoptimize_filter_exclude' ), 99999 );
		add_filter( 'autoptimize_filter_css_exclude', array( $this, 'autoptimize_filter_exclude' ), 99999 );

		/**
		 * SiteGround Optimizer
		 */
		add_filter( 'sgo_js_async_exclude', array( $this, 'sgo_exclude' ), 9999 );
		add_filter( 'sgo_css_combine_exclude', array( $this, 'sgo_exclude' ), 9999 );
		add_filter( 'sgo_javascript_combine_exclude', array( $this, 'sgo_exclude' ), 9999 );
		add_filter( 'sgo_js_minify_exclude', array( $this, 'sgo_exclude' ), 9999 );
		add_filter( 'sgo_css_minify_exclude', array( $this, 'sgo_exclude' ), 9999 );
	}

	/**
	 * Exclude CSS/JS From SiteGround Optimizer
	 *
	 * @param $handles
	 *
	 * @return mixed
	 * @since 1.1.0
	 *
	 */
	public function sgo_exclude( $handles ) {
		// Since CSS and JS use same handles, can use same method
		$sf_handles = array( 'wpjm-search-filtering-frontend-edit', 'wpjm-search-filtering-frontend', 'wpjm-search-filtering-frontend-edit-mobile', 'wpjm-search-filtering-admin' );

		foreach( (array) $sf_handles as $sf_handle ){
			if ( ! in_array( $sf_handle, $handles ) ) {
				$handles[] = $sf_handle;
			}
		}

		return $handles;
	}

	/**
	 * Add S&F JS/CSS to Exclude Optimize for Autoptimize Plugin
	 *
	 * @param $exclude
	 *
	 * @return array|mixed|string
	 * @since 1.1.0
	 *
	 */
	public function autoptimize_filter_exclude( $exclude ) {

		$exclude_path = WPJM_SEARCH_FILTERING_PATH_RELATIVE . '/assets/';

		if( ! is_array( $exclude ) && strpos( $exclude, $exclude_path ) === FALSE ){
			$maybe_comma = strlen( $exclude ) > 0 ? ',' : '';
			$exclude .= "{$maybe_comma}{$exclude_path}";
		} elseif( is_array( $exclude ) && ! in_array( $exclude_path, $exclude ) ){
			$exclude[] = $exclude_path;
		}

		return $exclude;
	}
}