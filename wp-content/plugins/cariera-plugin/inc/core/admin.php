<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Settings page.
	 *
	 * @var \Cariera_Core\Core\Settings()
	 */
	private $settings_page;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add submenu pages to "WP Dashboard -> Cariera".
		add_action( 'admin_menu', [ $this, 'add_menu_item' ], 11 );

		// Init Settings.
		$this->settings_page = new \Cariera_Core\Core\Settings();
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @since   1.4.8
	 * @version 1.8.5
	 */
	public function add_menu_item() {
		// Settings Page.
		add_submenu_page(
			'cariera_theme',
			esc_html__( 'Settings', 'cariera-core' ),
			esc_html__( 'Settings', 'cariera-core' ),
			'manage_options',
			'cariera_settings',
			[ $this, 'settings_output' ]
		);

		// Migrations Page.
		if ( 1 === absint( get_option( 'cariera_migrations' ) ) ) {
			add_submenu_page(
				'cariera_theme',
				esc_html__( 'Migrations', 'cariera-core' ),
				esc_html__( 'Migrations', 'cariera-core' ),
				'manage_options',
				'cariera_migrations',
				[ $this, 'migrations_output' ]
			);
		}

		// Documentation Page.
		add_submenu_page(
			'cariera_theme',
			esc_html__( 'Documentation', 'cariera-core' ),
			esc_html__( 'Documentation', 'cariera-core' ),
			'manage_options',
			'cariera_documentation',
			function () {}
		);

		// Debug log.
		if ( 1 === absint( get_option( 'cariera_debug' ) ) ) {
			add_submenu_page(
				'cariera_theme',
				'Debug Log',
				'Debug Log',
				'manage_options',
				'cariera_debug_log',
				[ $this, 'debug_log_output' ]
			);
		}
	}

	/**
	 * Settings output.
	 *
	 * @since 1.7.3
	 */
	public function settings_output() {
		\Cariera_Core\Core\Settings::instance()->settings_output();
	}

	/**
	 * Display debug log.
	 *
	 * @since   1.8.0
	 * @version 1.8.5
	 */
	public function debug_log_output() {
		if ( ! get_option( 'cariera_debug' ) ) {
			return;
		}

		// Path to the log file.
		$log_file_path = WP_CONTENT_DIR . '/debug.log';

		// Load template.
		cariera_get_template(
			'backend/debug-log.php',
			[
				'log_file_path' => $log_file_path,
				'log_content'   => file_get_contents( $log_file_path ),
			]
		);
	}

	/**
	 * Display migrations.
	 *
	 * @since   1.8.3
	 * @version 2.0.0
	 */
	public function migrations_output() {
		if ( ! get_option( 'cariera_migrations' ) ) {
			return;
		}

		wp_enqueue_style( 'cariera-core-migrations' );
		wp_enqueue_script( 'cariera-core-migrations' );

		cariera_get_template_part( 'backend/migrations' );
	}
}
