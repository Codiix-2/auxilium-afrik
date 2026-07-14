<?php
/**
 * Plugin Name: Cariera Core
 * Plugin URI:  https://1.envato.market/cariera
 * Description: This is the Core plugin for the Cariera Theme.
 * Version:     2.0.0
 * Author:      Gnodesign
 * Author URI:  https://1.envato.market/gnodesign
 * Text Domain: cariera-core
 * Domain Path: /lang
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'autoload.php';

final class Cariera_Core {

	/**
	 * The single instance of the class.
	 *
	 * @var Cariera_Core
	 */
	private static $instance = null;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '2.0.0';

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor function.
	 */
	public function __construct() {
		$this->check_core_plugin();

		// Define Constants.
		$this->define_constants();

		// Initialize Core installation.
		new \Cariera_Core\Install();

		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		// Main Actions.
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ], 10 );

		// Plugin links.
		add_filter( 'plugin_row_meta', [ $this, 'plugin_links' ], 10, 2 );

		// Include files.
		$this->include_files();
	}

	/**
	 * Define the constants
	 *
	 * @since 1.5.5
	 */
	protected function define_constants() {
		define( 'CARIERA_CORE', __FILE__ );
		define( 'CARIERA_CORE_VERSION', $this->version );
		define( 'CARIERA_URL', plugins_url( '', __FILE__ ) );
		define( 'CARIERA_CORE_PATH', __DIR__ );
		define( 'CARIERA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'CARIERA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	}

	/**
	 * Initializes plugin.
	 *
	 * @since   1.5.5
	 * @version 1.8.2
	 */
	public function init_plugin() {
		if ( ! $this->cariera_is_active() ) {
			return;
		}

		// Add Actions.
		add_action( 'init', [ $this, 'localization_init' ] );
		add_action( 'init', [ $this, 'image_sizes' ] );

		// Initialize the whole core plugin.
		$this->init();
	}

	/**
	 * Loading Text Domain file for translations
	 *
	 * @since 1.5.5
	 */
	public function localization_init() {
		load_plugin_textdomain( 'cariera-core', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Adds image sizes
	 *
	 * @since 1.5.5
	 */
	public function image_sizes() {
		add_image_size( 'cariera-avatar', 500, 500, true );
	}

	/**
	 * This will initialize the whole core plugin.
	 *
	 * @since 1.7.2
	 */
	protected function init() {
		\Cariera_Core\Init::instance();
	}

	/**
	 * Include files
	 *
	 * @since   1.7.2
	 * @version 2.0.0
	 */
	private function include_files() {
		// Add files if needed.
	}

	/**
	 * Checks if Cariera Theme is active
	 *
	 * @since 1.8.2
	 */
	public function required_notices() {
		$screen  = get_current_screen();
		$screens = apply_filters( 'cariera_core_admin_screen_ids', [ 'dashboard', 'plugins', 'themes' ] );

		// If Cariera or the child theme have not been installed or activated.
		if ( 'cariera' === get_option( 'template' ) ) {
			return;
		}

		if ( null !== $screen && in_array( $screen->id, $screens, true ) ) {
			$this->display_error( __( '<strong>Cariera Core</strong> requires <strong>Cariera Theme</strong> to be installed and activated.', 'cariera-core' ) );
		}
	}

	/**
	 * Display error message notice in the admin.
	 *
	 * @since 1.8.2
	 *
	 * @param mixed $message
	 */
	private function display_error( $message ) {
		echo '<div class="error">';
		echo '<p>' . wp_kses_post( $message ) . '</p>';
		echo '</div>';
	}

	/**
	 * Check if Cariera theme is active
	 *
	 * @since 1.8.2
	 */
	private function cariera_is_active() {
		// If Cariera Theme is active.
		if ( 'cariera' !== get_option( 'template' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check core plugin
	 *
	 * @since   1.8.4
	 * @version 1.9.3
	 */
	private function check_core_plugin() {
		if ( ! empty( get_option( 'cariera_license_activated' ) ) && ! empty( get_option( 'Cariera_lic_Key' ) ) ) {
			return;
		}

		// Ensure the function is available.
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Deactivate the plugin.
		if ( defined( 'CARIERA_PLUGIN_BASENAME' ) && is_plugin_active( CARIERA_PLUGIN_BASENAME ) ) {
			deactivate_plugins( CARIERA_PLUGIN_BASENAME, true, false );
		}
	}

	/**
	 * Plugin extra links.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $links
	 * @param string $file
	 */
	public function plugin_links( $links, $file ) {
		if ( CARIERA_PLUGIN_BASENAME !== $file ) {
			return $links;
		}

		$extra_links = [
			'changelog' => [
				'url'     => 'https://1.envato.market/cariera-changelog',
				'label'   => esc_html__( 'Changelog', 'cariera-core' ),
				'new_tab' => true,
			],
		];

		foreach ( $extra_links as $link ) {
			$attributes = '';
			if ( ! empty( $link['new_tab'] ) ) {
				$attributes .= ' target="_blank" rel="noopener noreferrer"';
			}

			$links[] = '<a href="' . esc_url( $link['url'] ) . '"' . $attributes . '>' . $link['label'] . '</a>';
		}

		return $links;
	}
}

Cariera_Core::instance();
