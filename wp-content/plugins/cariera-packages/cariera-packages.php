<?php
/**
 * Plugin Name: Cariera Packages
 * Plugin URI:  https://1.envato.market/cariera
 * Description: Package management solution for Cariera with full WooCommerce integration (replaces WCPL).
 * Version:     1.0.0
 * Author:      Gnodesign
 * Author URI:  https://1.envato.market/gnodesign
 * Text Domain: cariera-packages
 * Domain Path: /lang
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'autoload.php';

final class Cariera_Packages {

	/**
	 * Main instance
	 *
	 * @var Cariera_Packages|null
	 */
	private static $instance = null;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Cariera Event Manager Settings
	 *
	 * @var \Cariera_Packages\Settings()
	 */
	public $settings;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since 0.9.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor for Cariera_Packages class
	 */
	public function __construct() {
		$this->check_packages_plugin();

		// Define constants.
		$this->define_constants();

		// Activation and Deactivation hooks.
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		// Init main functions when plugin loads.
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ], 20 );

		// Plugin links.
		add_filter( 'plugin_row_meta', [ $this, 'plugin_links' ], 10, 2 );
	}

	/**
	 * Define the constants
	 *
	 * @since 0.9.0
	 */
	public function define_constants() {
		define( 'CARIERA_PACKAGES_VERSION', $this->version );
		define( 'CARIERA_PACKAGES_PLUGIN', __FILE__ );
		define( 'CARIERA_PACKAGES_URL', plugin_dir_url( __FILE__ ) );
		define( 'CARIERA_PACKAGES_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR ) );
		define( 'CARIERA_PACKAGES_ASSETS', CARIERA_PACKAGES_URL . 'assets' );
		define( 'CARIERA_PACKAGES_BASENAME', plugin_basename( __FILE__ ) );
	}

	/**
	 * Run function when plugin get's activated
	 *
	 * @since 0.9.0
	 */
	public function activate() {
		$installed = get_option( 'cariera_packages_installed' );

		if ( ! $installed ) {
			update_option( 'cariera_packages_installed', time() );
		}

		update_option( 'cariera_packages_version', CARIERA_PACKAGES_VERSION );
	}

	/**
	 * Run function when plugin get's deactivated
	 *
	 * @since 0.9.0
	 */
	public function deactivate() {
		// Nothing here for now.
	}

	/**
	 * Checks if Cariera and Cariera Companies are active
	 *
	 * @since   0.9.0
	 * @version 1.0.0
	 */
	public function required_notices() {
		$screen        = get_current_screen();
		$valid_screens = [ 'dashboard', 'plugins', 'plugins-network', 'toplevel_page_cariera_theme' ];

		// Only proceed if on a valid screen.
		if ( $screen && in_array( $screen->id, $valid_screens, true ) ) {

			// If WP Job Manager is not installed and activated.
			if ( ! class_exists( 'WP_Job_Manager' ) || ! defined( 'JOB_MANAGER_VERSION' ) ) {
				$this->display_error( __( '<strong>Cariera Packages</strong> requires <strong>WP Job Manager</strong> to be installed and activated.', 'cariera-packages' ) );
			}

			// If Cariera Core is not installed and activated.
			if ( ! class_exists( '\Cariera_Core' ) ) {
				$this->display_error( __( '<strong>Cariera Packages</strong> requires <strong>Cariera Core</strong> to be installed and activated.', 'cariera-packages' ) );
			}

			// If Cariera theme or child theme is not active.
			if ( 'cariera' !== get_option( 'template' ) ) {
				$this->display_error( __( '<strong>Cariera Packages</strong> requires <strong>Cariera Theme</strong> to be installed and activated.', 'cariera-packages' ) );
			}

			// If WooCommerce is not installed and activated.
			if ( ! class_exists( '\WooCommerce' ) ) {
				$this->display_error( __( '<strong>Cariera Packages</strong> requires <strong>WooCommerce</strong> to be installed and activated.', 'cariera-packages' ) );
			}

			// WCPL is installed and activated.
			if ( class_exists( 'WC_Paid_Listings' ) && defined( 'JOB_MANAGER_WCPL_PLUGIN_DIR' ) ) {
				$this->display_error( __( 'Please deactivate <strong>WC Paid Listings</strong> to fully use <strong>Cariera Packages</strong> features.', 'cariera-packages' ) );
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
	 * @since   0.9.0
	 * @version 0.9.9
	 */
	public function init_plugin() {
		if ( ! $this->wpjm_active() ) {
			return;
		}

		if ( ! $this->cariera_is_active() ) {
			return;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
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
	 * @version 0.9.18
	 */
	public function init_classes() {
		// Initialize static instances or singletons.
		\Cariera_Packages\Admin::instance();
		\Cariera_Packages\Admin\Metaboxes::instance();
		\Cariera_Packages\Assets::instance();
		\Cariera_Packages\Integration::instance();
		\Cariera_Packages\Migrations::instance();
		\Cariera_Packages\Packages::init();
		\Cariera_Packages\Post_Types\Cariera_Package::instance();
		\Cariera_Packages\Settings::instance();
		\Cariera_Packages\WooCommerce::instance();

		// Initialize additional components.
		new \Cariera_Packages\Writepanels();
	}

	/**
	 * Localization Init
	 *
	 * @since 0.9.0
	 */
	public function localization_init() {
		load_plugin_textdomain( 'cariera-packages', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Check if WP Job Manage is installed and activated
	 *
	 * @since   0.9.0
	 * @version 0.9.8
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
	 * Check if WP Resume Manager or Cariera Addons Resumes is installed and activated
	 *
	 * @since   0.9.0
	 * @version 0.9.9
	 */
	public static function wprm_active() {
		$wprm = 'wp-job-manager-resumes/wp-job-manager-resumes.php';

		if ( ! defined( 'RESUME_MANAGER_PLUGIN_DIR' ) ) {
			$cariera_addons = get_option( 'cariera_addons_core_features', [] );

			if ( class_exists( '\Cariera_Addons\Core\Resumes\Resumes' ) && ! empty( $cariera_addons['resumes'] ) ) {
				return true;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( is_plugin_active( $wprm ) ) {
				return true;
			}

			if ( class_exists( 'WP_Resume_Manager' ) ) {
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Check if Cariera Company Manager is activated
	 *
	 * @since 0.9.9
	 */
	public static function cariera_company_manager_active() {
		return get_option( 'cariera_company_manager_integration' );
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
	 * Check cariera packages plugin
	 *
	 * @since 0.9.18
	 */
	private function check_packages_plugin() {
		if ( ! empty( get_option( 'cariera_license_activated' ) ) && ! empty( get_option( 'Cariera_lic_Key' ) ) ) {
			return;
		}

		// Ensure the function is available.
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Deactivate the plugin.
		if ( defined( 'CARIERA_PACKAGES_BASENAME' ) && is_plugin_active( CARIERA_PACKAGES_BASENAME ) ) {
			deactivate_plugins( CARIERA_PACKAGES_BASENAME, true, false );
		}
	}

	/**
	 * Plugin links.
	 *
	 * @since   0.9.24
	 * @version 1.0.0
	 *
	 * @param array  $links
	 * @param string $file
	 */
	public function plugin_links( $links, $file ) {
		if ( CARIERA_PACKAGES_BASENAME !== $file ) {
			return $links;
		}

		$extra_links = [
			'changelog'     => [
				'url'     => 'https://docs.cariera.cc/knowledgebase/cariera-packages-changelog/',
				'label'   => esc_html__( 'Changelog', 'cariera-packages' ),
				'new_tab' => true,
			],
			'documentation' => [
				'url'     => 'https://docs.cariera.cc/kb/cariera-packages/',
				'label'   => esc_html__( 'Documentation', 'cariera-packages' ),
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

if ( Cariera_Packages_Dependency_Checker::check_dependencies() ) {
	$GLOBALS['cariera_packages'] = Cariera_Packages::instance();
}
