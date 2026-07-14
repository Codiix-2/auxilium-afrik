<?php

namespace Cariera_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Init {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {

		// Init Classes.
		new \Cariera_Core\Core();
		new \Cariera_Core\Elementor();
		new \Cariera_Core\Shortcodes();

		// Job Manager.
		if ( class_exists( 'WP_Job_Manager' ) ) {
			\Cariera_Core\Core\Job_Manager::instance();
		}

		// Resume Manager.
		if ( class_exists( 'WP_Job_Manager' ) && class_exists( 'WP_Resume_Manager' ) ) {
			\Cariera_Core\Core\Resume_Manager::instance();
		}

		// Demo Importer.
		require_once ABSPATH . '/wp-admin/includes/class-wp-importer.php';
		require_once CARIERA_CORE_PATH . '/inc/importer/wp-importer/WXRImporter.php';
		require_once CARIERA_CORE_PATH . '/inc/importer/wp-importer/WPImporterLogger.php';
		require_once CARIERA_CORE_PATH . '/inc/importer/wp-importer/WPImporterLoggerCLI.php';
		\Cariera_Core\Importer\Importer::instance();
		\Cariera_Core\Importer\Uninstaller::instance();

		// Extensions.
		\Cariera_Core\Extensions\Envato\Envato::instance();
		\Cariera_Core\Extensions\Captcha\Captcha::init();
		\Cariera_Core\Extensions\Social_Share\Sharer::instance();
		\Cariera_Core\Extensions\Testimonials\Testimonials::instance();

		if ( class_exists( 'WP_Job_Manager' ) && class_exists( 'WP_Resume_Manager' ) ) {
			\Cariera_Core\Extensions\Resume\Invite_Candidate::instance();
		}

		// Include Files.
		require_once CARIERA_CORE_PATH . '/inc/helpers.php';

		// Extensions.
		// TODO: This will be rewritten on the new statistics update ver: 1.7.9.
		require_once CARIERA_CORE_PATH . '/inc/extensions/dashboard/views.php';
	}
}
