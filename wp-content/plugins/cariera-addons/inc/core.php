<?php

namespace Cariera_Addons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Core {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_plugins();

		// Check addons plugin.
		add_action( 'cariera_addons_check_plugin', [ $this, 'check_addons_plugin' ] );
	}

	/**
	 * Initialize the plugins.
	 *
	 * @since   0.9.0
	 * @version 0.9.5
	 */
	private function init_plugins() {
		$enabled_features = get_option( 'cariera_addons_core_features', [] );

		$features = [
			'application-deadline' => '\Cariera_Addons\Core\Application_Deadline\Application_Deadline',
			'applications'         => '\Cariera_Addons\Core\Applications\Applications',
			'bookmarks'            => '\Cariera_Addons\Core\Bookmarks\Bookmarks',
			'job-alerts'           => '\Cariera_Addons\Core\Job_Alerts\Job_Alerts',
			'resumes'              => '\Cariera_Addons\Core\Resumes\Resumes',
			'tags'                 => '\Cariera_Addons\Core\Tags\Tags',
		];

		foreach ( $features as $key => $class ) {
			if ( ! empty( $enabled_features[ $key ] ) && class_exists( $class ) && method_exists( $class, 'instance' ) ) {
				$class::instance();
			}
		}
	}

	/**
	 * Check addons plugin.
	 *
	 * @since 1.0.1
	 */
	public function check_addons_plugin() {
		// Retrieve license key and email from options.
		$license_status  = get_option( 'cariera_license_activated' );
		$license_key     = get_option( 'Cariera_lic_Key' );
		$plugin_basename = 'cariera-addons/cariera-addons.php';

		// Ensure the function is available.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check if the license is inactive or the license key does not exist.
		if ( ! empty( $license_status ) && ! empty( $license_key ) ) {
			// \Cariera\write_log( 'License status & key are not empty.' );
			return;
		}

		if ( ! is_plugin_active( $plugin_basename ) ) {
			// \Cariera\write_log( 'Plugin does not exist.' );
			return;
		}

		// Deactivate the plugin if it's currently active.
		$result = deactivate_plugins( $plugin_basename, true );

		if ( is_wp_error( $result ) ) {
			\Cariera\write_log( sprintf( 'Failed to deactivate plugin %s: %s', $plugin_basename, $result->get_error_message() ) );
			return;
		}

		// Clear any cached data related to the plugin.
		wp_cache_delete( $plugin_basename, 'plugins' );
	}
}
