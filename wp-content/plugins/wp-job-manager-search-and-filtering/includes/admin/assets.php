<?php

namespace WPJMSF\Admin;
use WPJMSF\Assets as RootAssets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Assets
 *
 * @package WPJMSF\Admin
 */
class Assets {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * Assets constructor.
	 *
	 * @param $type
	 */
	public function __construct( $type ) {
		$this->type = $type;
		add_action( 'admin_enqueue_scripts', array( $this, 'register' ), 5 );
	}

	/**
	 * Register Admin Assets
	 *
	 * @since 1.0.1
	 *
	 */
	public function register() {
		RootAssets::register_scripts( $this->get_scripts() );
		RootAssets::register_styles( $this->get_styles() );
	}

	/**
	 * Get all registered scripts
	 *
	 * @return array
	 */
	public function get_scripts() {

		$debug = defined( 'SMYLES_DEVN' ) && SMYLES_DEVN ? '' : '.min';

		$scripts = array(
			'wpjm-search-filtering-vendor' => array(
				'src'       => WPJM_SEARCH_FILTERING_ASSETS . "/js/vendor{$debug}.js",
				'version'   => ! empty( $debug ) ? WPJM_SEARCH_FILTERING_VERSION : filemtime( WPJM_SEARCH_FILTERING_PATH . "/assets/js/vendor.js" ),
				'in_footer' => true
			),
			'wpjm-search-filtering-admin'         => array(
				'src'       => WPJM_SEARCH_FILTERING_ASSETS . "/js/admin{$debug}.js",
				'deps'      => array( 'wpjm-search-filtering-vendor' ),
				'version'   => ! empty( $debug ) ? WPJM_SEARCH_FILTERING_VERSION : filemtime( WPJM_SEARCH_FILTERING_PATH . '/assets/js/admin.js' ),
				'in_footer' => true,
				'i18n'      => true
			)
		);

		return apply_filters( 'search_and_filtering_assets_admin_get_scripts', $scripts, $this );
	}

	/**
	 * Get registered styles
	 *
	 * @return array
	 */
	public function get_styles() {

		$prefix = defined( 'SMYLES_DEVN' ) && SMYLES_DEVN ? '' : '.min';

		$styles = array(
			'wpjm-search-filtering-admin'         => array(
				'src' => WPJM_SEARCH_FILTERING_ASSETS . "/css/admin{$prefix}.css",
			),
		);

		return apply_filters( 'search_and_filtering_assets_admin_get_styles', $styles, $this );
	}
}
