<?php
namespace Cariera_Addons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Integrations {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_integrations();
	}

	/**
	 * Initialize all plugin integrations
	 *
	 * @since 0.9.5
	 */
	private function init_integrations() {
		// Class Aliases.
		\Cariera_Addons\Integrations\Aliases::load_aliases();

		// Jetpack Integration.
		if ( class_exists( '\Jetpack' ) ) {
			\Cariera_Addons\Integrations\Jetpack::init();
		}

		// AIOSEO Integration.
		if ( function_exists( 'aioseo' ) ) {
			\Cariera_Addons\Integrations\AIOSEO::init();
		}

		// Yoast SEO integration.
		if ( class_exists( '\WPSEO_Options' ) ) {
			\Cariera_Addons\Integrations\Yoast::init();
		}
	}
}
