<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Company_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Cariera Company Manager CPT
	 *
	 * @var \Cariera_Core\Core\Company_Manager\CPT()
	 */
	public $post_types;

	/**
	 * Cariera Company Manager Forms Handling
	 *
	 * @var \Cariera_Core\Core\Company_Manager\Forms()
	 */
	public $forms;

	/**
	 * Cariera Company Manager Settings
	 *
	 * @var \Cariera_Core\Core\Company_Manager\Settings()
	 */
	public $settings;

	/**
	 * Cariera Company Manager WPJM Integration
	 *
	 * @var \Cariera_Core\Core\Company_Manager\WPJM()
	 */
	public $wpjm;

	/**
	 * Constructor
	 */
	public function __construct() {
		require_once CARIERA_CORE_PATH . '/inc/core/company-manager/company-manager-functions.php';

		// Singleton instances.
		\Cariera_Core\Core\Company_Manager\Ajax::instance();
		\Cariera_Core\Core\Company_Manager\Dashboard::instance();
		\Cariera_Core\Core\Company_Manager\HR_Manager::instance();
		\Cariera_Core\Core\Company_Manager\Shortcodes::instance();
		\Cariera_Core\Core\Company_Manager\Search::instance();
		\Cariera_Core\Core\Company_Manager\Templates::instance();
		\Cariera_Core\Core\Company_Manager\Lifecycle::instance();

		// Init classes.
		new \Cariera_Core\Core\Company_Manager\Writepanels();
		new \Cariera_Core\Core\Company_Manager\Geocode();
		new \Cariera_Core\Core\Company_Manager\Email_Notifications();
		new \Cariera_Core\Core\Company_Manager\Bookmarks();

		// Core dependencies.
		$this->post_types = new \Cariera_Core\Core\Company_Manager\CPT();
		$this->forms      = new \Cariera_Core\Core\Company_Manager\Forms();
		$this->settings   = new \Cariera_Core\Core\Company_Manager\Settings();
		$this->wpjm       = new \Cariera_Core\Core\Company_Manager\WPJM();

		// Hooks.
		add_action( 'rest_api_init', [ $this, 'rest_init' ] );
	}

	/**
	 * Loads the REST API functionality.
	 *
	 * @since 1.5.6
	 */
	public function rest_init() {
		\Cariera_Core\Core\Company_Manager\REST_API::init();
	}
}
