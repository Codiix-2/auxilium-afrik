<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Resumes {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Post types class.
	 *
	 * @var \Cariera_Addons\Core\Resumes\Post_Types
	 */
	public $post_types;

	/**
	 * Apply with resume class.
	 *
	 * @var \Cariera_Addons\Core\Resumes\Apply
	 */
	public $apply;

	/**
	 * Form helper class.
	 *
	 * @var \Cariera_Addons\Core\Resumes\Forms
	 */
	public $forms;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		if ( class_exists( 'WP_Resume_Manager' ) ) {
			return;
		}

		// Define RM const for backward compatibility.
		$this->define_compat_constants();

		// Init main functions when plugin loads.
		$this->init_plugin();
	}

	/**
	 * Required notices when WPJM Resumes is enabled.
	 *
	 * @since   0.9.5
	 * @version 0.9.8
	 */
	public function required_notices() {
		// If WP Job Manager Resumes is installed and activated.
		if ( class_exists( 'WP_Resume_Manager' ) && defined( 'RESUME_MANAGER_PLUGIN_DIR' ) ) {
			echo '<div class="error">';
			echo '<p>' . wp_kses_post( __( 'Please deactivate <strong>WP Job Manager Resumes</strong> to enable the <strong>Cariera Addons Resumes</strong> feature.', 'cariera-addons' ) ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Define the Resume Manager constants for backward compatibility.
	 *
	 * @since 0.9.7
	 */
	private function define_compat_constants() {
		if ( ! defined( 'RESUME_MANAGER_VERSION' ) ) {
			define( 'RESUME_MANAGER_VERSION', '9999.0.0-cariera-addons-shim' );
		}
	}

	/**
	 * Init plugin
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 */
	public function init_plugin() {
		// Includes.
		require_once CARIERA_ADDONS_PATH . 'inc/core/resumes/resume-functions.php';
		require_once CARIERA_ADDONS_PATH . 'inc/core/resumes/resume-templates.php';

		// Load classes.
		$this->post_types = Post_Types::instance();
		$this->apply      = Apply::instance();
		$this->forms      = Forms::instance();

		// Init additional components.
		Ajax::instance();
		Dashboard::instance();
		Email_Notifications::init();
		Geocode::instance();
		Lifecycle::instance();
		File_Cleaner::init();
		Shortcodes::instance();
		Templates::instance();

		// Add actions.
		add_action( 'init', [ $this, 'init_user_roles' ] );
		add_action( 'init', [ $this, 'load_admin' ], 12 );
		add_action( 'rest_api_init', [ $this, 'rest_init' ] );

		// Disable resume post type page when user can not browse resumes.
		add_action( 'template_redirect', [ $this, 'disable_resume_page' ] );

		// WPJM Enhanced Select.
		add_filter( 'job_manager_enhanced_select_enabled', [ $this, 'is_enhanced_select_required_on_page' ] );

		// Create required files.
		$this->create_files();
	}

	/**
	 * Init the admin area
	 *
	 * @since 0.9.5
	 */
	public function load_admin() {
		if ( is_admin() ) {
			Admin::instance();
		}
	}

	/**
	 * Loads the REST API functionality.
	 *
	 * @since 0.9.5
	 */
	public function rest_init() {
		REST_API::init();
	}

	/**
	 * Disable resume post type page when user can not browse resumes.
	 *
	 * @since 0.9.5
	 */
	public function disable_resume_page() {
		if ( empty( $_GET['post_type'] ) || \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $_GET['post_type'] ) { // phpcs:ignore
			return;
		}

		if ( resume_manager_user_can_browse_resumes() ) {
			return;
		}

		wp_safe_redirect( home_url() );
		exit;
	}

	/**
	 * Init user roles
	 *
	 * @since 0.9.6
	 */
	public function init_user_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'administrator', 'manage_resumes' );

			// Candidate role.
			add_role(
				'candidate',
				esc_html__( 'Candidate', 'cariera-addons' ),
				[
					'read'         => true,
					'edit_posts'   => false,
					'delete_posts' => false,
				]
			);
		}
	}

	/**
	 * Create required resume files/folders.
	 *
	 * @since   0.9.6
	 * @version 0.9.11
	 */
	private function create_files() {
		$upload_dir = wp_upload_dir();

		$file_to_delete = $upload_dir['basedir'] . '/resumes/.htaccess';

		if ( file_exists( $file_to_delete ) && is_file( $file_to_delete ) ) {
			unlink( $file_to_delete );
		}

		$files = [
			[
				'base'    => $upload_dir['basedir'] . '/resumes/resume_files',
				'file'    => '.htaccess',
				'content' => 'deny from all',
			],
			[
				'base'    => $upload_dir['basedir'] . '/resumes/resume_files',
				'file'    => 'index.html',
				'content' => '',
			],
		];

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

	/**
	 * Filters if enhanced select is needed on this page.
	 *
	 * @since 0.9.6
	 *
	 * @param bool $enhanced_select_used_on_page
	 */
	public function is_enhanced_select_required_on_page( $enhanced_select_used_on_page ) {
		$enhanced_select_shortcodes = [ 'submit_resume_form', 'resumes', 'candidate_dashboard' ];
		if ( $enhanced_select_used_on_page || has_wp_resume_manager_shortcode( null, $enhanced_select_shortcodes ) ) {
			return true;
		}
		return $enhanced_select_used_on_page;
	}
}
