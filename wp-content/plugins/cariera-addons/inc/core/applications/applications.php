<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Applications {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Holds the singleton instance of the Post_Types class
	 *
	 * @var \Cariera_Addons\Core\Applications\Post_Types
	 */
	public $post_types;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		if ( class_exists( 'WP_Job_Manager_Applications' ) ) {
			return;
		}

		// Init main functions when plugin loads.
		$this->init_plugin();
	}

	/**
	 * Required notices when WPJM Applications is enabled.
	 *
	 * @since   0.9.4
	 * @version 0.9.8
	 */
	public function required_notices() {
		// If WP Job Manager Applications is installed and activated.
		if ( class_exists( 'WP_Job_Manager_Applications' ) && defined( 'JOB_MANAGER_APPLICATIONS_PLUGIN_DIR' ) ) {
			echo '<div class="error">';
			echo '<p>' . wp_kses_post( __( 'Please deactivate <strong>WP Job Manager Applications</strong> to enable the <strong>Cariera Addons Applications</strong> feature.', 'cariera-addons' ) ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Init plugin
	 *
	 * @since 0.9.3
	 */
	public function init_plugin() {
		$this->post_types = Post_Types::instance();

		Dashboard::instance();
		Default_Form::instance();
		Past_Applications::instance();
		Integration::instance();
		Job_Submission::instance();
		Apply::instance();

		// Includes.
		require_once CARIERA_ADDONS_PATH . 'inc/core/applications/application-functions.php';
		require_once CARIERA_ADDONS_PATH . 'inc/core/applications/application-templates.php';

		// Add actions.
		add_action( 'init', [ $this, 'load_admin' ], 12 );
	}

	/**
	 * Init the admin area
	 *
	 * @since   0.9.3
	 * @version 0.9.5
	 */
	public function load_admin() {
		if ( is_admin() ) {
			Admin::instance();
		}
	}
}
