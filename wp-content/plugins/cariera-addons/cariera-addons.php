<?php
/**
 * Plugin Name: Cariera Addons
 * Plugin URI:  https://1.envato.market/cariera
 * Description: Cariera Addons is an all-in-one plugin that enhances WP Job Manager functionality exclusively for the Cariera theme.
 * Version:     1.1.0
 * Author:      Gnodesign
 * Author URI:  https://1.envato.market/gnodesign
 * Text Domain: cariera-addons
 * Domain Path: /lang
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'autoload.php';

final class Cariera_Addons {

	/**
	 * Main instance
	 *
	 * @var Cariera_Addons|null
	 */
	private static $instance = null;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '1.1.0';

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
		$this->check_addons_plugin();

		// Define constants.
		$this->define_constants();

		// Load the Install class.
		new Cariera_Addons\Install();

		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		// Init main functions when plugin loads.
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ], 9 );

		// Plugin extra links.
		add_filter( 'plugin_row_meta', [ $this, 'plugin_links' ], 10, 2 );
	}

	/**
	 * Define the constants
	 *
	 * @since 0.9.0
	 */
	public function define_constants() {
		define( 'CARIERA_ADDONS_VERSION', $this->version );
		define( 'CARIERA_ADDONS_PLUGIN', __FILE__ );
		define( 'CARIERA_ADDONS_URL', plugin_dir_url( __FILE__ ) );
		define( 'CARIERA_ADDONS_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR ) );
		define( 'CARIERA_ADDONS_ASSETS', CARIERA_ADDONS_URL . 'assets' );
		define( 'CARIERA_ADDONS_BASENAME', plugin_basename( __FILE__ ) );
	}

	/**
	 * Checks if Cariera and Cariera Companies are active
	 *
	 * @since   0.9.0
	 * @version 1.1.0
	 */
	public function required_notices() {
		$screen        = get_current_screen();
		$valid_screens = [ 'dashboard', 'plugins', 'plugins-network', 'toplevel_page_cariera_theme' ];

		// Only proceed if on a valid screen.
		if ( $screen && in_array( $screen->id, $valid_screens, true ) ) {

			// If WP Job Manager is not installed and activated.
			if ( ! class_exists( 'WP_Job_Manager' ) || ! defined( 'JOB_MANAGER_VERSION' ) ) {
				$this->display_error( __( '<strong>Cariera Addons</strong> requires <strong>WP Job Manager</strong> to be installed and activated.', 'cariera-addons' ) );
			}

			// If Cariera Core is not installed and activated.
			if ( ! class_exists( '\Cariera_Core' ) ) {
				if ( null !== $screen || in_array( $screen->id, $valid_screens, true ) ) {
					$this->display_error( __( '<strong>Cariera Addons</strong> requires <strong>Cariera Core</strong> to be installed and activated.', 'cariera-addons' ) );
				}
			}

			// If Cariera theme or child theme is not active.
			if ( 'cariera' !== get_option( 'template' ) ) {
				$this->display_error( __( '<strong>Cariera Addons</strong> requires <strong>Cariera Theme</strong> to be installed and activated.', 'cariera-addons' ) );
			}
		}
	}

	/**
	 * Display error message notice in the admin.
	 *
	 * @since 0.9.0
	 *
	 * @param mixed $message
	 */
	private function display_error( $message ) {
		echo '<div class="error">';
		echo '<p>' . wp_kses_post( $message ) . '</p>';
		echo '</div>';
	}

	/**
	 * Initializes plugin.
	 *
	 * @since 0.9.0
	 */
	public function init_plugin() {
		if ( ! $this->wpjm_active() ) {
			return;
		}

		if ( ! $this->cariera_is_active() ) {
			return;
		}

		// Init Classes.
		$this->init_classes();

		// Add actions.
		add_action( 'init', [ $this, 'localization_init' ] );
	}

	/**
	 * Initializes classes.
	 *
	 * @since   0.9.0
	 * @version 0.9.5
	 */
	public function init_classes() {
		// Initialize static instances or singletons.
		\Cariera_Addons\Assets::instance();
		\Cariera_Addons\Settings::instance();
		\Cariera_Addons\Core::instance();
		\Cariera_Addons\Integrations::instance();
	}

	/**
	 * Localization Init
	 *
	 * @since 0.9.0
	 */
	public function localization_init() {
		load_plugin_textdomain( 'cariera-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Check if WP Job Manage is installed and activated
	 *
	 * @since   0.9.0
	 * @version 0.9.11
	 */
	public static function wpjm_active() {
		$wpjm = 'wp-job-manager/wp-job-manager.php';

		if ( ! defined( 'JOB_MANAGER_VERSION' ) ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( is_plugin_active( $wpjm ) ) {
				return true;
			}

			if ( class_exists( 'WP_Job_Manager' ) ) {
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Check if Cariera and Cariera Core are active
	 *
	 * @since 0.9.0
	 */
	private function cariera_is_active() {
		// If Cariera Theme is active.
		if ( 'cariera' !== get_option( 'template' ) ) {
			return false;
		}

		// If Cariera Core is active.
		if ( ! class_exists( '\Cariera_Core' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check cariera addons plugin
	 *
	 * @since 1.0.1
	 */
	private function check_addons_plugin() {
		if ( ! empty( get_option( 'cariera_license_activated' ) ) && ! empty( get_option( 'Cariera_lic_Key' ) ) ) {
			return;
		}

		// Ensure the function is available.
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Deactivate the plugin.
		if ( defined( 'CARIERA_ADDONS_BASENAME' ) && is_plugin_active( CARIERA_ADDONS_BASENAME ) ) {
			deactivate_plugins( CARIERA_ADDONS_BASENAME, true, false );
		}
	}

	/**
	 * Plugin extra links.
	 *
	 * @since   1.0.2
	 * @version 1.1.0
	 *
	 * @param array  $links
	 * @param string $file
	 */
	public function plugin_links( $links, $file ) {
		if ( CARIERA_ADDONS_BASENAME !== $file ) {
			return $links;
		}

		$extra_links = [
			'changelog'     => [
				'url'     => 'https://docs.cariera.cc/knowledgebase/changelog/',
				'label'   => esc_html__( 'Changelog', 'cariera-addons' ),
				'new_tab' => true,
			],
			'documentation' => [
				'url'     => 'https://docs.cariera.cc/kb/cariera-addons/',
				'label'   => esc_html__( 'Documentation', 'cariera-addons' ),
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

require_once __DIR__ . '/dependency-checker.php';

if ( Cariera_Addons_Dependency_Checker::check_dependencies() ) {
	$GLOBALS['cariera_addons'] = Cariera_Addons::instance();
}
