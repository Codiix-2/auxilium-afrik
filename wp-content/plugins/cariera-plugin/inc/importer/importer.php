<?php

namespace Cariera_Core\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WP_Importer' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-importer.php';
}

class Importer {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Theme slug.
	 *
	 * @var string Theme slug.
	 */
	public $theme_slug = 'cariera';

	/**
	 * Demo slug.
	 *
	 * @var string Demo slug.
	 */
	public $demo_slug = '';

	/**
	 * The instance of the \Cariera_Core\Importer\Content_Importer class.
	 *
	 * @var object
	 */
	public $importer;

	/**
	 * The path of the log file.
	 *
	 * @var string
	 */
	public $log_file_path;

	/**
	 * Holds any error messages, that should be printed out at the end of the import.
	 *
	 * @var string
	 */
	public $frontend_error_messages = [];

	/**
	 * Flag to indicate import is in progress
	 *
	 * @var bool
	 */
	public static $importing = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Adding the importer template.
		add_action( 'cariera_onboarding_import', [ $this, 'importer_template' ], 30, 1 );

		// AJAX Handlers.
		$ajax_actions = [
			'fetch_demo_steps',
			'import_demo',
			'generate_child_theme',
			'skip_child_theme',
			'download_import_data',
			'download_media_package',
			'cancel_importer_content',
			'copy_images',
			'import_content_xml',
			'import_widgets_json',
			'import_customizer_json',
			'import_theme_options_json',
			'import_menus_json',
			'import_page_options_json',
			'import_elementor_json',
			'import_slider_data',
		];

		foreach ( $ajax_actions as $action ) {
			add_action( "wp_ajax_{$action}", [ $this, $action ] );
		}

		// Set importer.
		add_action( 'after_setup_theme', [ $this, 'setup_content_importer' ] );
	}

	/**
	 * Importer template
	 *
	 * @since 1.7.3
	 */
	public function importer_template() {
		cariera_get_template(
			'backend/importer/main.php',
			[
				'import_issues'        => self::get_import_issues(),
				'ignore_import_issues' => apply_filters( 'cariera_ignore_import_issues', false ),
				'theme_slug'           => $this->theme_slug,
			]
		);
	}

	/**
	 * Find all import issues
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @return array Issues array.
	 */
	public static function get_import_issues() {
		$issues = [];

		$requirements = [
			'curl_init'   => esc_html__( 'Your server does not have cURL.', 'cariera-core' ),
			'DOMDocument' => esc_html__( 'Your server does not have DOMDocument.', 'cariera-core' ),
			'fsockopen'   => esc_html__( 'Your server does not have fsockopen.', 'cariera-core' ),
			'XMLReader'   => esc_html__( 'Your server does not have XMLReader extension.', 'cariera-core' ),
			'ZipArchive'  => esc_html__( 'Your server does not have ZipArchive extension.', 'cariera-core' ),
		];

		foreach ( $requirements as $feature => $message ) {
			if ( ! ( function_exists( $feature ) || class_exists( $feature ) ) ) {
				$issues[] = $message;
			}
		}

		$max_execution_time = (int) ini_get( 'max_execution_time' );
		if ( $max_execution_time > 0 && $max_execution_time < 300 ) {
			@set_time_limit( 300 ); // phpcs:ignore.
		}

		return apply_filters( 'cariera_import_issues', $issues );
	}

	/**
	 * Get all demos.
	 *
	 * @since   1.7.3
	 * @version 1.8.8
	 */
	public static function get_import_demos() {
		$import_url = cariera_import_url();

		$demos = [
			'demo-01' => [
				'name'                => esc_html__( 'Cariera Main', 'cariera-core' ),
				'description'         => esc_html__( 'After importing this demo, your site will have all data like cariera.cc', 'cariera-core' ),
				'preview_image_url'   => CARIERA_URL . '/assets/images/importer/01.jpg',
				'import_data_url'     => $import_url . '/demo-01.zip',
				'media_package_local' => '',
				'media_package_url'   => '',
			],
		];

		return apply_filters( 'cariera_import_demos', $demos );
	}

	/**
	 * Get the local directory path for a specific demo.
	 *
	 * @since 1.9.8
	 *
	 * @param string $demo_slug Demo slug.
	 */
	public function get_demo_path( $demo_slug ) {
		return CARIERA_CORE_PATH . '/inc/importer/demo-data/' . sanitize_title( $demo_slug );
	}

	/**
	 * Get steps in a demo.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param string $demo_slug Demo slug.
	 * @return array Demo steps of a demo
	 */
	public function get_demo_steps( $demo_slug ) {
		$demos      = self::get_import_demos();
		$import_dir = $this->get_demo_path( $demo_slug );

		if ( ! is_dir( $import_dir ) ) {
			wp_send_json_error(
				[
					// translators: %s: Import directory.
					'message' => sprintf( esc_html__( 'The directory %s/ does not exist.', 'cariera-core' ), $import_dir ),
					'code'    => 'missing_directory',
				]
			);
		}

		$content_xml        = "{$import_dir}/content.xml";
		$widgets_json       = "{$import_dir}/widgets.json";
		$menus_json         = "{$import_dir}/menus.json";
		$slider_data        = "{$import_dir}/slider.zip";
		$elementor_json     = "{$import_dir}/elementor.json";
		$customizer_json    = "{$import_dir}/customizer.json";
		$theme_options_json = "{$import_dir}/theme-options.json";
		$page_options_json  = "{$import_dir}/page-options.json";
		$media_package      = isset( $demos[ $demo_slug ]['media_package_local'] ) ? $demos[ $demo_slug ]['media_package_local'] : '';
		$demo_steps         = [];

		if ( ! file_exists( $import_dir ) ) {
			// translators: %s: Import directory.
			wp_send_json_error( sprintf( esc_html__( 'The directory %s/ does not exist.', 'cariera-core' ), $import_dir ) );
		}

		// Fetch the media package in local.
		if ( file_exists( $media_package ) ) {
			$demo_steps['media_package_local'] = esc_html__( 'Media Package (on local)', 'cariera-core' );
		}

		if ( ! empty( $demos[ $demo_slug ]['media_package_url'] ) ) {
			$demo_steps['media_package_url'] = esc_html__( 'Media Package (on cloud)', 'cariera-core' );
		}

		// Fetch the content.xml file.
		if ( file_exists( $content_xml ) ) {
			$demo_steps['content_xml'] = esc_html__( 'Content (posts, pages, custom post types, categories, comments, etc..)', 'cariera-core' );
		} else {
			// translators: %s: content.xml.
			wp_send_json_error( sprintf( esc_html__( 'The file %s does not exist.', 'cariera-core' ), $content_xml ) );
		}

		// Fetch the widgets.json file.
		if ( file_exists( $widgets_json ) ) {
			$demo_steps['widgets_json'] = esc_html__( 'Widgets', 'cariera-core' );
		}

		// Fetch the menus.json file.
		if ( file_exists( $menus_json ) ) {
			$demo_steps['menus_json'] = esc_html__( 'Menus', 'cariera-core' );
		}

		// Fetch the slider.zip file.
		if ( file_exists( $slider_data ) ) {
			$demo_steps['slider_data'] = esc_html__( 'Slider Data', 'cariera-core' );
		}

		// Fetch the elementor.json file.
		if ( file_exists( $elementor_json ) ) {
			$demo_steps['elementor_json'] = esc_html__( 'Elementor Settings', 'cariera-core' );
		}

		// Fetch the widgets.json file.
		if ( file_exists( $customizer_json ) ) {
			$demo_steps['customizer_json'] = esc_html__( 'Customizer Settings', 'cariera-core' );
		}

		// Fetch the theme-options.json file.
		if ( file_exists( $theme_options_json ) ) {
			$demo_steps['theme_options_json'] = esc_html__( 'Theme Options', 'cariera-core' );
		}

		// Fetch the page-options.json file.
		if ( file_exists( $page_options_json ) ) {
			$demo_steps['page_options_json'] = esc_html__( 'After Import', 'cariera-core' );
		}

		return apply_filters( 'cariera_demo_steps', $demo_steps, $demo_slug );
	}

	/**
	 * Verify data before import content.
	 *
	 * @since 1.7.3
	 *
	 * @param string $action Action name.
	 */
	public function verify_before_call_ajax( $action ) {
		if ( ! cariera_verify_nonce( $action ) ) {
			wp_send_json_error( esc_html__( 'Invalid nonce', 'cariera-core' ) );
		}

		if ( isset( $_POST['demo_slug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->demo_slug = sanitize_text_field( wp_unslash( $_POST['demo_slug'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			wp_send_json_error( esc_html__( 'Demo slug is not defined.', 'cariera-core' ) );
		}
	}

	/**
	 * Show demo steps to form
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function fetch_demo_steps() {
		$this->verify_before_call_ajax( 'fetch_demo_steps' );

		$demos     = self::get_import_demos();
		$demo_slug = $this->demo_slug;

		// Check if we should show child theme generator first.
		if ( $this->should_show_child_theme_generator() ) {
			ob_start();
			cariera_get_template(
				'backend/importer/popup-child-theme-form.php',
				[
					'demo_slug'       => $demo_slug,
					'import_data_url' => isset( $demos[ $demo_slug ]['import_data_url'] ) ? $demos[ $demo_slug ]['import_data_url'] : '',
				]
			);
			wp_send_json_success( ob_get_clean() );
			return;
		}

		// Show download import data if URL exists.
		if ( ! empty( $demos[ $demo_slug ]['import_data_url'] ) ) {
			ob_start();

			cariera_get_template(
				'backend/importer/popup-download-import-form.php',
				[
					'import_data_url' => $demos[ $demo_slug ]['import_data_url'],
					'demo_slug'       => $demo_slug,
				]
			);
			$html = ob_get_clean();
		}

		// If URL is empty import data will be pulled locally.
		if ( empty( $demos[ $demo_slug ]['import_data_url'] ) ) {
			$demo_steps = $this->get_demo_steps( $this->demo_slug );

			ob_start();

			cariera_get_template(
				'backend/importer/popup-demo-steps-form.php',
				[
					'demo_steps' => $demo_steps,
					'demo_slug'  => $demo_slug,
				]
			);
			$html = ob_get_clean();
		}

		wp_send_json_success( $html );
	}

	/**
	 * Import Demo.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function import_demo() {
		$this->verify_before_call_ajax( 'import_demo' );

		$demos          = self::get_import_demos();
		$demo_slug      = $this->demo_slug;
		$selected_steps = [];

		// Get import steps.
		if ( isset( $_POST['selected_steps'] ) && ! empty( $_POST['selected_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Remove all empty item in steps.
			$selected_steps_str = sanitize_text_field( wp_unslash( $_POST['selected_steps'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$selected_steps     = explode( ',', $selected_steps_str ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( empty( $selected_steps ) ) {
				wp_send_json_error( esc_html__( 'No import steps found.', 'cariera-core' ) );
			}
		} else {
			wp_send_json_error( esc_html__( 'Please select at least 1 item to continue.', 'cariera-core' ) );
		}

		// If we have the media package in the theme directory, just extract it & copy all images inside to /wp-content/uploads.
		if ( in_array( 'media_package_local', $selected_steps, true ) ) {
			ob_start();
			cariera_get_template(
				'backend/importer/popup-copy-images-form.php',
				[
					'media_package_local' => $demos[ $demo_slug ]['media_package_local'],
					'selected_steps_str'  => $selected_steps_str,
					'demo_slug'           => $demo_slug,
				]
			);
			wp_send_json_success( ob_get_clean() );
		}

		// If the media package is in the cloud, we need to download it first.
		if ( in_array( 'media_package_url', $selected_steps, true ) ) {
			ob_start();
			cariera_get_template(
				'backend/importer/popup-download-images-form.php',
				[
					'selected_steps_str' => $selected_steps_str,
					'media_package_url'  => $demos[ $demo_slug ]['media_package_url'],
					'demo_slug'          => $demo_slug,
				]
			);
			wp_send_json_success( ob_get_clean() );
		}

		// Get import content form.
		$demo_steps           = $this->get_demo_steps( $demo_slug );
		$import_content_steps = $this->get_import_content_steps( $demo_steps, $selected_steps );

		ob_start();
		cariera_get_template(
			'backend/importer/popup-import-content-form.php',
			[
				'import_content_steps' => $import_content_steps,
				'demo_slug'            => $demo_slug,
			]
		);
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * Download media package.
	 *
	 * @since   1.7.3
	 * @version 1.8.9
	 */
	public function download_import_data() {
		global $wp_filesystem;

		// Initialize the WP Filesystem if not already done.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$this->verify_before_call_ajax( 'download_import_data' );

		// Check if the WP_CONTENT_DIR is writable.
		if ( defined( 'WP_CONTENT_DIR' ) && ! $wp_filesystem->is_writable( WP_CONTENT_DIR ) ) {
			// translators: %s: wp-content directory.
			wp_send_json_error( sprintf( esc_html__( 'Permission Error: %s directory is not writable.', 'cariera-core' ), WP_CONTENT_DIR ) );
		}

		// Validate the import data URL.
		if ( ! isset( $_POST['import_data_url'] ) || empty( $_POST['import_data_url'] ) ) { // phpcs:ignore
			wp_send_json_error( esc_html__( 'Could not download the import data package. The URL is not defined.', 'cariera-core' ) );
		}

		$import_data_url = sanitize_text_field( wp_unslash( $_POST['import_data_url'] ) ); // phpcs:ignore
		$import_package  = download_url( $import_data_url, 1800 );
		$import_path     = CARIERA_CORE_PATH . '/inc/importer/demo-data/';

		if ( is_wp_error( $import_package ) ) {
			wp_send_json_error(
				sprintf(
					// translators: %1$s: Error code, %2$s: Error message.
					__( 'ERROR %1$s: Could not download the import data package: %2$s.', 'cariera-core' ),
					$import_package->get_error_code(),
					$import_package->get_error_message()
				)
			);
		}

		// Create the import path if it doesn't exist.
		if ( ! wp_mkdir_p( $import_path ) ) {
			// translators: %s: Path to media package.
			wp_send_json_error( sprintf( esc_html__( 'Could not create %s directory.', 'cariera-core' ), $import_path ) );
		}

		if ( ! $wp_filesystem->is_writable( $import_path ) ) {
			// translators: %s: directory path.
			wp_send_json_error( sprintf( esc_html__( 'The directory %s is not writable. Please check permissions.', 'cariera-core' ), $import_path ) );
		}

		// Unzip the media package.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		$unzip = unzip_file( $import_package, $import_path );
		if ( is_wp_error( $unzip ) ) {
			$error_message = $unzip->get_error_message();
			// translators: %s: Path to media package.
			wp_send_json_error( sprintf( esc_html__( 'Could not unzip the import data package: %1$s. Error: %2$s', 'cariera-core' ), $import_package, $error_message ) );
		}

		// Clean up: delete the downloaded import package.
		wp_delete_file( $import_package );

		// Get demo steps for the import process.
		$demo_steps = $this->get_demo_steps( $this->demo_slug );

		// Capture output for the response.
		ob_start();
		cariera_get_template(
			'backend/importer/popup-demo-steps-form.php',
			[
				'demo_steps' => $demo_steps,
				'demo_slug'  => $this->demo_slug,
			]
		);
		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * Download media package.
	 *
	 * @since   1.7.3
	 * @version 1.7.9
	 */
	public function download_media_package() {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$this->verify_before_call_ajax( 'download_media_package' );

		if ( defined( 'WP_CONTENT_DIR' ) && ! $wp_filesystem->is_writable( WP_CONTENT_DIR ) ) {
			// translators: %s: wp-content directory.
			wp_send_json_error( sprintf( esc_html__( 'Could not write files into %s directory. Permission denied.', 'cariera-core' ), WP_CONTENT_DIR ) );
		}

		$demos      = self::get_import_demos();
		$theme_slug = $this->theme_slug;
		$demo_slug  = $this->demo_slug;

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['media_package_url'] ) || empty( $_POST['media_package_url'] ) ) {
			wp_send_json_error( esc_html__( 'Could not download the media package. The URL is not defined.', 'cariera-core' ) );
		}

		$media_package_url  = sanitize_text_field( wp_unslash( $_POST['media_package_url'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$selected_steps_str = isset( $_POST['selected_steps'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_steps'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$media_package      = download_url( $media_package_url, 1800 );
		$media_path         = WP_CONTENT_DIR . '/' . $theme_slug . '-' . $demo_slug;

		if ( ! is_wp_error( $media_package ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;

			// Delete the media folder if it already exists.
			if ( file_exists( $media_path ) && ! $wp_filesystem->rmdir( $media_path, true ) ) {
				// translators: %s: Path to media package.
				wp_send_json_error( sprintf( esc_html__( 'Could not create %s directory. It already exists but Cariera Core cannot delete it because of permission denied.', 'cariera-core' ), $media_path ) );
			}

			// Unzip the media package.
			if ( wp_mkdir_p( $media_path ) ) {
				$unzip = unzip_file( $media_package, $media_path );

				if ( is_wp_error( $unzip ) ) {
					// translators: %s: Path to media package.
					wp_send_json_error( sprintf( esc_html__( 'Could not unzip the media package: %s.', 'cariera-core' ), $media_package ) );
				}

				wp_delete_file( $media_package );
			} else {
				// translators: %s: Path to media package.
				wp_send_json_error( sprintf( esc_html__( 'Could not create %s directory.', 'cariera-core' ), $media_path ) );
			}
		} else {
			wp_send_json_error(
				sprintf(
					// translators: %1$s: Error code, %3$s: Error message, %2$s: Direct link of the media package.
					__( 'ERROR %1$s: Could not download the media package. %2$s. Please try to download it from <a href="%3$s" target="_blank">here</a> and try to import manually.', 'cariera-core' ),
					$media_package->get_error_code(),
					$media_package->get_error_message(),
					esc_url( $media_package_url )
				)
			);
		}

		// After downloading the media package, we have to copy images to wp-content/uploads.
		ob_start();

		cariera_get_template(
			'backend/importer/popup-copy-images-form.php',
			[
				'selected_steps_str' => $selected_steps_str,
				'demo_slug'          => $demo_slug,
			]
		);
		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * Copy images to wp-content/uploads
	 *
	 * @since   1.7.3
	 * @version 1.7.9
	 */
	public function copy_images() {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$this->verify_before_call_ajax( 'copy_images' );

		if ( defined( 'WP_CONTENT_DIR' ) && ! $wp_filesystem->is_writable( WP_CONTENT_DIR ) ) {
			// translators: %s: wp-content directory.
			wp_send_json_error( sprintf( esc_html__( 'Could not write files into %s directory. Permission denied.', 'cariera-core' ), WP_CONTENT_DIR ) );
		}

		$theme_slug = $this->theme_slug;
		$demo_slug  = $this->demo_slug;
		$media_path = WP_CONTENT_DIR . '/' . $theme_slug . '-' . $demo_slug;

		if ( isset( $_POST['media_package_local'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Unzip the local media package.
			$media_package_local = sanitize_text_field( wp_unslash( $_POST['media_package_local'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$unzip               = unzip_file( $media_package_local, $media_path );
		}

		if ( ! file_exists( $media_path ) ) {
			// translators: %s: Media package path.
			wp_send_json_error( sprintf( esc_html__( 'This directory could not be found: %s.', 'cariera-core' ), $media_path ) );
		}

		if ( ! is_dir( $media_path ) ) {
			// translators: %s: Media package path.
			wp_send_json_error( sprintf( esc_html__( '%s is not a directory.', 'cariera-core' ), $media_path ) );
		}

		$current_files = $this->list_files( WP_CONTENT_DIR . '/uploads' );
		$new_files     = $this->list_files( $media_path );

		foreach ( $current_files as $key => $value ) {
			// Remove all files already exist.
			if ( isset( $new_files[ $key ] ) ) {
				unset( $new_files[ $key ] );
			}
		}

		$uploads     = wp_upload_dir();
		$upload_path = WP_CONTENT_DIR . '/uploads/';

		if ( is_multisite() ) {
			$blog_id     = get_current_blog_id();
			$upload_path = WP_CONTENT_DIR . '/uploads/sites/' . $blog_id . '/';
		}

		// After copying image, delete the media directory.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		copy_dir(
			$media_path . '/',
			$upload_path
		);

		$wp_filesystem->rmdir( $media_path, true );

		ob_start();

		if ( isset( $_POST['selected_steps'] ) && ! empty( $_POST['selected_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Get import content form.
			$selected_steps_str   = sanitize_text_field( wp_unslash( $_POST['selected_steps'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$selected_steps       = explode( ',', $selected_steps_str ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$demo_steps           = $this->get_demo_steps( $demo_slug );
			$import_content_steps = $this->get_import_content_steps( $demo_steps, $selected_steps );

			cariera_get_template(
				'backend/importer/popup-import-content-form.php',
				[
					'import_content_steps' => $import_content_steps,
					'demo_slug'            => $demo_slug,
				]
			);
		} else {
			// If we don't have next steps, display success animation.
			cariera_get_template_part( 'backend/importer/popup-success' );
		}
		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * List all files in a directory
	 *
	 * @since   1.7.3
	 * @version 1.7.9
	 *
	 * @param string $folder Folder.
	 * @param string $parent_folder Parent folder.
	 */
	private function list_files( $folder, $parent_folder = null ) {
		if ( null === $parent_folder ) {
			$parent_folder = $folder;
		}

		$stack = [];

		if ( is_dir( $folder ) ) {
			$dir = opendir( $folder );

			// phpcs:ignore
			while ( false !== ( $file = readdir( $dir ) ) ) {
				$file_path = "{$folder}/{$file}";

				if ( substr( $file, 0, 1 ) === '.' && '.' !== $file && '..' !== $file ) {
					// Delete all hidden files.
					wp_delete_file( $file_path );
				} elseif ( is_file( $file_path ) ) {
					$stack[ rawurlencode( str_replace( "{$parent_folder}/", '', $file_path ) ) ] = 1;
				} elseif ( is_dir( $file_path ) && '.' !== $file && '..' !== $file ) {
					$stack[ rawurlencode( str_replace( "{$parent_folder}/", '', $file_path ) ) ] = 4;

					// Recursive.
					$stack = $stack + $this->list_files( $file_path, $parent_folder );
				}
			}
		}

		return $stack;
	}

	/**
	 * Get next step to import
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param string $current_step Key of an item in $steps array.
	 * @param array  $steps Import steps.
	 */
	public function get_next_step( $current_step, $steps ) {
		// Ensure steps is an array.
		if ( ! is_array( $steps ) || empty( $steps ) ) {
			return false;
		}

		// Find position of current step.
		$position = array_search( $current_step, $steps, true );

		// If current step not found in array.
		if ( false === $position ) {
			return false;
		}

		// Calculate next position.
		$next_position = $position + 1;

		// Check if next position exists in the array.
		if ( ! isset( $steps[ $next_position ] ) ) {
			return false;
		}

		return $steps[ $next_position ];
	}

	/**
	 * Get steps while importing content (pages, posts, widgets, ...etc)
	 *
	 * @since 1.7.3
	 *
	 * @param array $demo_steps All steps while importing demo.
	 * @param array $selected_steps Import steps that selected by user.
	 *
	 * @return array An key - value array, use when importing website content.
	 */
	private function get_import_content_steps( $demo_steps, $selected_steps ) {
		unset( $demo_steps['media_package_url'] );
		unset( $demo_steps['media_package_local'] );
		foreach ( $demo_steps as $key => $step ) {
			if ( ! in_array( $key, $selected_steps, true ) ) {
				unset( $demo_steps[ $key ] );
			}
		}

		return $demo_steps;
	}

	/**
	 * Set up the importer, after the theme has loaded and instantiate the importer.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function setup_content_importer() {
		$importer_options = apply_filters( 'cariera_importer_options', [ 'fetch_attachments' => true ] );
		$logger_options   = apply_filters( 'cariera_logger_options', [ 'logger_min_level' => 'warning' ] );

		$logger            = new \Cariera_Core\Importer\Import_Logger();
		$logger->min_level = $logger_options['logger_min_level'];

		$this->importer = new \Cariera_Core\Importer\Content_Importer( $importer_options, $logger );
	}

	/**
	 * Get content importer data, so we can continue the import with this new AJAX request.
	 *
	 * @since 1.7.3
	 */
	public function use_existing_importer_data() {
		$data = get_transient( 'cariera_importer_data' );
		if ( false !== $data ) {
			$this->frontend_error_messages = empty( $data['frontend_error_messages'] ) ? [] : $data['frontend_error_messages'];
			$this->log_file_path           = empty( $data['log_file_path'] ) ? '' : $data['log_file_path'];
			$this->importer->set_importer_data( $data );
			return true;
		}
		return false;
	}

	/**
	 * Get the current state of selected data.
	 *
	 * @since 1.7.3
	 */
	public function get_current_importer_data() {
		return [
			'frontend_error_messages' => $this->frontend_error_messages,
			'log_file_path'           => $this->log_file_path,
		];
	}

	/**
	 * Getter function to retrieve the private log_file_path value.
	 *
	 * @since 1.7.3
	 */
	public function get_log_file_path() {
		return $this->log_file_path;
	}

	/**
	 * Setter function to append additional value to the private frontend_error_messages value.
	 *
	 * @since 1.7.3
	 *
	 * @param string $text The additional value that will be appended to the existing frontend_error_messages.
	 */
	public function append_to_frontend_error_messages( $text ) {
		$lines = [];

		if ( ! empty( $text ) ) {
			$text  = str_replace( '<br>', PHP_EOL, $text );
			$lines = explode( PHP_EOL, $text );
		}

		foreach ( $lines as $line ) {
			if ( ! empty( $line ) && ! in_array( $line, $this->frontend_error_messages, true ) ) {
				$this->frontend_error_messages[] = $line;
			}
		}
	}

	/**
	 * Read import files
	 *
	 * @since 1.7.3
	 *
	 * @param string $file_name File name.
	 */
	public function read_import_file( $file_name ) {
		$file = CARIERA_CORE_PATH . '/inc/importer/demo-data/' . $this->demo_slug . '/' . $file_name;

		if ( ! file_exists( $file ) ) {
			// translators: %s: File name.
			return new \WP_Error( 'file_not_found', sprintf( esc_html__( 'The %s file does not exist.', 'cariera-core' ), $file_name ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$file_content = $wp_filesystem->get_contents( $file );

		return $file_content;
	}

	/**
	 * Send a JSON response with final report.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function send_final_response() {
		// Finalize the log via the Logger (handles performance data internally).
		\Cariera_Core\Importer\Import_Logger::finalize_log( $this->log_file_path, $this->demo_slug );

		// Clean up session data.
		delete_transient( 'cariera_importer_data' );
		$this->delete_download_import_package();

		// Mark as imported.
		update_option( $this->theme_slug . '_' . $this->demo_slug . '_imported', true );

		// Trigger import completed action.
		do_action( 'cariera_import_completed', $this->demo_slug );

		ob_start();
		cariera_get_template_part( 'backend/importer/popup-success' );
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * Cancel import if import content is online.
	 *
	 * @since   1.7.6
	 * @version 1.9.5
	 */
	public function cancel_importer_content() {
		do_action( 'cariera_before_cancel_importer' );

		// Get import demos.
		$demos = self::get_import_demos();

		// Verify and sanitize demo_slug.
		$demo_slug = isset( $_POST['demo_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['demo_slug'] ) ) : null; // phpcs:ignore

		if ( null === $demo_slug ) {
			wp_send_json_error( esc_html__( 'Demo slug is not defined.', 'cariera-core' ) );
		}

		// Check if import data URL is available.
		if ( empty( $demos[ $demo_slug ]['import_data_url'] ) ) {
			wp_send_json_error( esc_html__( 'No import data URL found for the specified demo slug.', 'cariera-core' ) );
		}

		// Delete downloaded package after import.
		$this->delete_download_import_package( $demo_slug );

		// Send success response.
		wp_send_json_success( [ 'status' => true ] );
	}

	/**
	 * Delete downloaded import data package
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param string $demo_slug
	 */
	private function delete_download_import_package( $demo_slug = '' ) {
		$demos = self::get_import_demos();

		// Use the default demo_slug if not provided.
		$demo_slug = empty( $demo_slug ) ? $this->demo_slug : $demo_slug;

		// Show download import data if URL exists.
		if ( empty( $demos[ $demo_slug ]['import_data_url'] ) ) {
			return;
		}

		$data_path = CARIERA_CORE_PATH . '/inc/importer/demo-data/' . $demo_slug;

		// Nothing to delete.
		if ( ! is_dir( $data_path ) ) {
			return;
		}

		// Initialize filesystem API.
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			\Cariera\write_log( 'WP_Filesystem initialization failed.' );
			return;
		}

		global $wp_filesystem;

		// Delete the directory and its contents.
		if ( ! $wp_filesystem->rmdir( $data_path, true ) ) {
			\Cariera\write_log( sprintf( 'Failed to delete directory: %s', $data_path ) );
		}

		// Trigger import package deleted action.
		do_action( 'cariera_import_package_deleted', $demo_slug, $data_path );
	}

	/**
	 * Import content.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function import_content_xml() {
		$this->verify_before_call_ajax( 'import_content_xml' );

		$demo_slug            = $this->demo_slug;
		$import_content_steps = [];

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['import_content_steps'] ) && ! empty( $_POST['import_content_steps'] ) ) {
			// Remove all empty item in steps.
			$import_content_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['import_content_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Define content.xml path.
		$import_dir  = $this->get_demo_path( $demo_slug );
		$content_xml = $import_dir . '/content.xml';

		if ( ! file_exists( $content_xml ) ) {
			wp_send_json_error( esc_html__( 'The content.xml file does not exist.', 'cariera-core' ) );
		}

		// Try to update PHP memory limit (so that it does not run out of it).
		ini_set( 'memory_limit', apply_filters( 'cariera_import_memory_limit', '350M' ) ); // phpcs:ignore

		// Mark as importing.
		self::$importing = true;

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $this->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			// Create a date and time string to use for demo and log file names.
			\Cariera_Core\Importer\Import_Logger::set_demo_import_start_time();

			// Define log file path.
			$this->log_file_path = \Cariera_Core\Importer\Import_Logger::get_log_path();

			\Cariera_Core\Importer\Import_Logger::append_to_file(
				'',
				$this->log_file_path,
				esc_html__( 'Importing content', 'cariera-core' )
			);
		}

		// Save current state as transient immediately to ensure continuity.
		$this->importer->save_importer_state();

		/**
		 * 1). Execute the actions hooked to the 'cariera_before_content_import_execution' action:
		 */
		do_action( 'cariera_before_content_import_execution' );

		/**
		 * 2). Import content
		 * Returns any errors greater then the "warning" logger level, that will be displayed on front page.
		 */
		$import_errors = $this->importer->import_content( $content_xml );
		$this->append_to_frontend_error_messages( $import_errors );

		/**
		 * 3). Execute the actions hooked to the 'cariera_after_content_import_execution' action.
		 */
		do_action( 'cariera_after_content_import_execution' );

		// Mark as not importing.
		self::$importing = false;

		// Request the after all import AJAX call.
		if ( ! empty( $import_content_steps ) ) {
			$next_step = $this->get_next_step( 'content_xml', $import_content_steps );

			if ( $next_step ) {
				// Save current mapping progress so the next step (widgets/menus) has access to new IDs.
				$this->importer->save_importer_state();

				wp_send_json(
					[
						'next_step' => $next_step,
						'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
					]
				);
			}
		}

		// Final state save before sending success response.
		$this->importer->save_importer_state();

		// Send a JSON response.
		$this->send_final_response();
	}

	/**
	 * Import widgets.
	 *
	 * @since 1.7.3
	 * @version 1.9.8
	 */
	public function import_widgets_json() {
		$this->verify_before_call_ajax( 'import_widgets_json' );

		$import_content_steps = [];

		if ( isset( $_POST['import_content_steps'] ) && ! empty( $_POST['import_content_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Remove all empty item in steps.
			$import_content_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['import_content_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $this->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			// Create a date and time string to use for demo and log file names.
			\Cariera_Core\Importer\Import_Logger::set_demo_import_start_time();

			// Define log file path.
			$this->log_file_path = \Cariera_Core\Importer\Import_Logger::get_log_path();
		}

		set_transient( 'cariera_importer_data', $this->get_current_importer_data(), 0.1 * HOUR_IN_SECONDS );

		$widgets_json = $this->read_import_file( 'widgets.json' );

		if ( ! is_wp_error( $widgets_json ) ) {
			$widgets_json = json_decode( $widgets_json );

			$widgets_importer = \Cariera_Core\Importer\Widgets_Importer::instance();
			$result           = $widgets_importer->import( $widgets_json );

			if ( ! is_wp_error( $result ) ) {
				$message = $widgets_importer->format_results_for_log( $result );

				// Add this message to log file.
				\Cariera_Core\Importer\Import_Logger::append_to_file( $message, $this->log_file_path, esc_html__( 'Importing widgets', 'cariera-core' ) );

				// Track imported sidebars.
				$sidebars_to_track = [];
				foreach ( $result as $sidebar_id => $sidebar_data ) {
					if ( 'wp_inactive_widgets' !== $sidebar_id && ! empty( $sidebar_data['widgets'] ) ) {
						$sidebars_to_track[] = $sidebar_id;
					}
				}

				if ( ! empty( $sidebars_to_track ) ) {
					$this->track_imported_data( 'widgets', $sidebars_to_track );
				}

				// Finish or go to next steps?
				if ( ! empty( $import_content_steps ) ) {
					$next_step = $this->get_next_step( 'widgets_json', $import_content_steps );

					if ( $next_step ) {
						wp_send_json(
							[
								'next_step' => $next_step,
								'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
							]
						);
					}
				}

				$this->send_final_response();
			} else {
				// Write error to log file.
				$error_message = $result->get_error_message();
				\Cariera_Core\Importer\Import_Logger::append_to_file( $error_message, $this->log_file_path, esc_html__( 'Importing widgets', 'cariera-core' ) );
				wp_send_json_error( $error_message );
			}
		} else {
			$error_message = $widgets_json->get_error_message();
			\Cariera_Core\Importer\Import_Logger::append_to_file( $error_message, $this->log_file_path, esc_html__( 'Importing widgets', 'cariera-core' ) );
			wp_send_json_error( $error_message );
		}
	}

	/**
	 * Import Customizer options
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function import_customizer_json() {
		$this->verify_before_call_ajax( 'import_customizer_json' );

		$import_content_steps = [];

		if ( isset( $_POST['import_content_steps'] ) && ! empty( $_POST['import_content_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Remove all empty item in steps.
			$import_content_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['import_content_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $this->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			// Create a date and time string to use for demo and log file names.
			\Cariera_Core\Importer\Import_Logger::set_demo_import_start_time();

			// Define log file path.
			$this->log_file_path = \Cariera_Core\Importer\Import_Logger::get_log_path();
		}

		set_transient( 'cariera_importer_data', $this->get_current_importer_data(), 0.1 * HOUR_IN_SECONDS );

		$data = $this->read_import_file( 'customizer.json' );

		if ( ! is_wp_error( $data ) && ! empty( $data ) ) {
			$data = json_decode( $data, true );

			// Have valid data? If no data or could not decode.
			if ( ! is_array( $data ) ) {
				\Cariera_Core\Importer\Import_Logger::append_to_file(
					esc_html__( 'Error: Customizer import data could not be read. Please try a different file.', 'cariera-core' ),
					$this->log_file_path,
					esc_html__( 'Importing customizer options', 'cariera-core' )
				);

				wp_send_json_error( esc_html__( 'Error: Customizer import data could not be read. Please try a different file.', 'cariera-core' ) );
			}

			$nav_menu_locations = get_theme_mod( 'nav_menu_locations' );
			remove_theme_mods(); // Reset customizer options.
			$data['nav_menu_locations'] = $nav_menu_locations;
			$message                    = '';

			// Track theme mods for deletion.
			$theme_mods_to_track = [];

			foreach ( $data as $name => $value ) {
				set_theme_mod( $name, $value );
				$message              .= $name . esc_html__( ' - Imported', 'cariera-core' ) . PHP_EOL;
				$theme_mods_to_track[] = $name;
			}

			\Cariera_Core\Importer\Import_Logger::append_to_file(
				$message,
				$this->log_file_path,
				esc_html__( 'Importing customizer options', 'cariera-core' )
			);

			// Track imported customizer options.
			if ( ! empty( $theme_mods_to_track ) ) {
				$this->track_imported_data( 'theme_mods', $theme_mods_to_track );
			}

			// Finish or go to next steps?
			if ( ! empty( $import_content_steps ) ) {
				$next_step = $this->get_next_step( 'customizer_json', $import_content_steps );

				if ( $next_step ) {
					wp_send_json(
						[
							'next_step' => $next_step,
							'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
						]
					);
				}
			}

			$this->send_final_response();
		} else {
			$error_message = $data->get_error_message();
			\Cariera_Core\Importer\Import_Logger::append_to_file( $error_message, $this->log_file_path, esc_html__( 'Importing customizer options', 'cariera-core' ) );
			wp_send_json_error( $error_message );
		}
	}

	/**
	 * Import Theme Option
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function import_theme_options_json() {
		$this->verify_before_call_ajax( 'import_theme_options_json' );

		$import_content_steps = [];

		if ( isset( $_POST['import_content_steps'] ) && ! empty( $_POST['import_content_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Remove all empty item in steps.
			$import_content_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['import_content_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $this->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			// Create a date and time string to use for demo and log file names.
			\Cariera_Core\Importer\Import_Logger::set_demo_import_start_time();

			// Define log file path.
			$this->log_file_path = \Cariera_Core\Importer\Import_Logger::get_log_path();
		}

		set_transient( 'cariera_importer_data', $this->get_current_importer_data(), 0.1 * HOUR_IN_SECONDS );

		$data = $this->read_import_file( 'theme-options.json' );

		if ( ! is_wp_error( $data ) && ! empty( $data ) ) {
			$data = json_decode( $data, true );

			// Have valid data? If no data or could not decode.
			if ( ! is_array( $data ) ) {
				\Cariera_Core\Importer\Import_Logger::append_to_file(
					esc_html__( 'Error: Theme options import data could not be read. Please try a different file.', 'cariera-core' ),
					$this->log_file_path,
					esc_html__( 'Importing theme options', 'cariera-core' )
				);

				wp_send_json_error( esc_html__( 'Error: Theme options import data could not be read. Please try a different file.', 'cariera-core' ) );
			}

			$message          = '';
			$options_to_track = [];

			// This should hold all theme options.
			update_option( 'cariera-core', $data );
			$options_to_track[] = 'cariera-core';

			foreach ( $data as $option => $value ) {
				if ( update_option( $option, $value ) ) {
					$message           .= $option . esc_html__( ' - Imported', 'cariera-core' ) . PHP_EOL;
					$options_to_track[] = $option;
				} else {
					$message .= $option . esc_html__( ' - Skipped', 'cariera-core' ) . PHP_EOL;
				}
			}

			\Cariera_Core\Importer\Import_Logger::append_to_file(
				$message,
				$this->log_file_path,
				esc_html__( 'Importing theme options', 'cariera-core' )
			);

			// Track imported theme options.
			if ( ! empty( $options_to_track ) ) {
				$this->track_imported_data( 'options', array_unique( $options_to_track ) );
			}

			// Finish or go to next steps?
			if ( ! empty( $import_content_steps ) ) {
				$next_step = $this->get_next_step( 'theme_options_json', $import_content_steps );

				if ( $next_step ) {
					wp_send_json(
						[
							'next_step' => $next_step,
							'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
						]
					);
				}
			}

			$this->send_final_response();
		} else {
			$error_message = $data->get_error_message();
			\Cariera_Core\Importer\Import_Logger::append_to_file( $error_message, $this->log_file_path, esc_html__( 'Importing theme options', 'cariera-core' ) );
			wp_send_json_error( $error_message );
		}
	}

	/**
	 * Import menu locations
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function import_menus_json() {
		$this->verify_before_call_ajax( 'import_menus_json' );

		// Get import steps from POST if provided.
		$import_content_steps = ! empty( $_POST['import_content_steps'] ) ? array_filter( explode( ',', sanitize_text_field( wp_unslash( $_POST['import_content_steps'] ) ) ) ) : []; // phpcs:ignore

		// Check if we are continuing an existing import.
		$use_existing_importer_data = $this->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			\Cariera_Core\Importer\Import_Logger::set_demo_import_start_time();
			$this->log_file_path = \Cariera_Core\Importer\Import_Logger::get_log_path();
		}

		set_transient( 'cariera_importer_data', $this->get_current_importer_data(), 0.1 * HOUR_IN_SECONDS );

		$data = $this->read_import_file( 'menus.json' );

		if ( is_wp_error( $data ) ) {
			\Cariera_Core\Importer\Import_Logger::append_to_file(
				$data->get_error_message(),
				$this->log_file_path,
				esc_html__( 'Importing menu', 'cariera-core' )
			);
			wp_send_json_error( $data->get_error_message() );
		}

		$menus = json_decode( $data, true );

		if ( ! is_array( $menus ) || empty( $menus ) ) {
			$error_msg = esc_html__( 'Error: Menu import data could not be read. Please try a different file.', 'cariera-core' );
			\Cariera_Core\Importer\Import_Logger::append_to_file( $error_msg, $this->log_file_path, esc_html__( 'Importing menu', 'cariera-core' ) );
			wp_send_json_error( $error_msg );
		}

		$current_locations = get_nav_menu_locations();
		$log_messages      = [];
		$menus_to_track    = [];

		foreach ( $menus as $location => $menu_slug ) {
			$menu_obj = wp_get_nav_menu_object( $menu_slug );

			if ( ! $menu_obj ) {
				$log_messages[] = sprintf(
					/* translators: %1$s menu location, %2$s menu slug */
					esc_html__( '%1$s - %2$s: Menu does not exist', 'cariera-core' ),
					esc_html( $location ),
					esc_html( $menu_slug )
				);
				continue;
			}

			if ( isset( $current_locations[ $location ] ) && absint( $current_locations[ $location ] ) === absint( $menu_obj->term_id ) ) {
				$log_messages[] = sprintf(
					/* translators: %1$s menu location, %2$s menu slug */
					esc_html__( '%1$s - %2$s: Menu already assigned', 'cariera-core' ),
					esc_html( $location ),
					esc_html( $menu_slug )
				);
			} else {
				$current_locations[ $location ] = absint( $menu_obj->term_id );
				$menus_to_track[]               = $location;
				$log_messages[]                 = sprintf(
					/* translators: %1$s menu location, %2$s menu slug */
					esc_html__( '%1$s - %2$s: Imported', 'cariera-core' ),
					esc_html( $location ),
					esc_html( $menu_slug )
				);
			}
		}

		// Update theme locations only if there is something to update.
		set_theme_mod( 'nav_menu_locations', $current_locations );

		\Cariera_Core\Importer\Import_Logger::append_to_file(
			implode( PHP_EOL, $log_messages ),
			$this->log_file_path,
			esc_html__( 'Importing menu', 'cariera-core' )
		);

		// Track imported menu locations.
		if ( ! empty( $menus_to_track ) ) {
			$this->track_imported_data( 'menus', $menus_to_track );
		}

		// Proceed to next step if applicable.
		if ( ! empty( $import_content_steps ) ) {
			// Only call get_next_step if current step is in the array and not last.
			$current_step_index = array_search( 'menus_json', $import_content_steps, true );

			if ( false !== $current_step_index && isset( $import_content_steps[ $current_step_index + 1 ] ) ) {
				$next_step = $this->get_next_step( 'menus_json', $import_content_steps );

				if ( ! empty( $next_step ) ) {
					wp_send_json(
						[
							'next_step' => $next_step,
							'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
						]
					);
				}
			}
		}

		$this->send_final_response();
	}

	/**
	 * Import page options
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function import_page_options_json() {
		$this->verify_before_call_ajax( 'import_page_options_json' );

		$import_content_steps = [];

		if ( isset( $_POST['import_content_steps'] ) && ! empty( $_POST['import_content_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Remove all empty item in steps.
			$import_content_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['import_content_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $this->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			// Create a date and time string to use for demo and log file names.
			\Cariera_Core\Importer\Import_Logger::set_demo_import_start_time();

			// Define log file path.
			$this->log_file_path = \Cariera_Core\Importer\Import_Logger::get_log_path();
		}

		set_transient( 'cariera_importer_data', $this->get_current_importer_data(), 0.1 * HOUR_IN_SECONDS );

		$data = $this->read_import_file( 'page-options.json' );

		if ( ! is_wp_error( $data ) && ! empty( $data ) ) {
			$data = json_decode( $data, true );

			// Have valid data? If no data or could not decode.
			if ( ! is_array( $data ) ) {
				\Cariera_Core\Importer\Import_Logger::append_to_file(
					esc_html__( 'Error: Page options import data could not be read. Please try a different file.', 'cariera-core' ),
					$this->log_file_path,
					esc_html__( 'Importing page options', 'cariera-core' )
				);

				wp_send_json_error( esc_html__( 'Error: Page options import data could not be read. Please try a different file.', 'cariera-core' ) );
			}

			$message = '';

			if ( isset( $data['show_on_front'] ) && ! empty( $data['show_on_front'] ) ) {
				if ( update_option( 'show_on_front', $data['show_on_front'] ) ) {
					$message .= 'show_on_front:' . $data['show_on_front'] . esc_html__( ' - Imported', 'cariera-core' ) . PHP_EOL;
				} else {
					$message .= 'show_on_front:' . $data['show_on_front'] . esc_html__( ' - Skipped', 'cariera-core' ) . PHP_EOL;
				}
			}

			if ( ! empty( $data['page_on_front'] ) ) {
				$page = cariera_get_page_by_title( $data['page_on_front'] );

				if ( update_option( 'page_on_front', $page->ID ) ) {
					$message .= 'page_on_front:' . $data['page_on_front'] . esc_html__( ' - Imported', 'cariera-core' ) . PHP_EOL;
				} else {
					$message .= 'page_on_front:' . $data['page_on_front'] . esc_html__( ' - Skipped', 'cariera-core' ) . PHP_EOL;
				}
			}

			if ( ! empty( $data['page_for_posts'] ) ) {
				$page = cariera_get_page_by_title( $data['page_for_posts'] );

				if ( update_option( 'page_for_posts', $page->ID ) ) {
					$message .= 'page_for_posts:' . $data['page_for_posts'] . esc_html__( ' - Imported', 'cariera-core' ) . PHP_EOL;
				} else {
					$message .= 'page_for_posts:' . $data['page_for_posts'] . esc_html__( ' - Skipped', 'cariera-core' ) . PHP_EOL;
				}
			}

			// Add extra theme options.
			$this->extra_theme_options();

			// Delete 'Hello World' post.
			wp_trash_post( 1 );

			// Delete 'Sample Page'.
			wp_trash_post( 2 );

			\Cariera_Core\Importer\Import_Logger::append_to_file(
				$message,
				$this->log_file_path,
				esc_html__( 'Importing page options', 'cariera-core' )
			);

			// Finish or go to next steps?
			if ( ! empty( $import_content_steps ) ) {
				$next_step = $this->get_next_step( 'page_options_json', $import_content_steps );

				if ( $next_step ) {
					wp_send_json(
						[
							'next_step' => $next_step,
							'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
						]
					);
				}
			}

			$this->send_final_response();
		} else {
			$error_message = $data->get_error_message();
			\Cariera_Core\Importer\Import_Logger::append_to_file( $error_message, $this->log_file_path, esc_html__( 'Importing page options', 'cariera-core' ) );
			wp_send_json_error( $error_message );
		}
	}

	/**
	 * Extra theme options for "After Import".
	 *
	 * @since 1.7.3
	 */
	private function extra_theme_options() {

		$pages = [
			// CARIERA CORE.
			'dashboard'         => [
				'option' => 'cariera_dashboard_page',
				'page'   => cariera_get_page_by_title( 'Dashboard' ),
			],
			'bookmarks'         => [
				'option' => 'cariera_bookmarks_page',
				'page'   => cariera_get_page_by_title( 'My Bookmarks' ),
			],
			'applied_jobs'      => [
				'option' => 'cariera_past_applications_page',
				'page'   => cariera_get_page_by_title( 'Past Applications' ),
			],
			'listing_reports'   => [
				'option' => 'cariera_listing_reports_page',
				'page'   => cariera_get_page_by_title( 'Reports' ),
			],
			'user_packages'     => [
				'option' => 'cariera_user_packages_page',
				'page'   => cariera_get_page_by_title( 'User Packages' ),
			],
			'my_profile'        => [
				'option' => 'cariera_dashboard_profile_page',
				'page'   => cariera_get_page_by_title( 'My Profile' ),
			],
			'approve_user'      => [
				'option' => 'cariera_moderate_new_user_page',
				'page'   => cariera_get_page_by_title( 'Approve User' ),
			],
			'privacy_policy'    => [
				'option' => 'cariera_register_privacy_policy_page',
				'page'   => cariera_get_page_by_title( 'Privacy Policy' ),
			],
			'employer_cta'      => [
				'option' => 'cariera_header_emp_cta_link',
				'page'   => cariera_get_page_by_title( 'Post Job' ),
			],
			'candidate_cta'     => [
				'option' => 'cariera_header_candidate_cta_link',
				'page'   => cariera_get_page_by_title( 'Submit Resume' ),
			],
			// WPJM.
			'job_submit'        => [
				'option' => 'job_manager_submit_job_form_page_id',
				'page'   => cariera_get_page_by_title( 'Post Job' ),
			],
			'job_dashboard'     => [
				'option' => 'job_manager_job_dashboard_page_id',
				'page'   => cariera_get_page_by_title( 'Job Dashboard' ),
			],
			'job_page'          => [
				'option' => 'job_manager_jobs_page_id',
				'page'   => cariera_get_page_by_title( 'Jobs' ),
			],
			'job_alerts'        => [
				'option' => 'job_manager_alerts_page_id',
				'page'   => cariera_get_page_by_title( 'Job Alerts' ),
			],
			// COMPANY MANAGER.
			'company_submit'    => [
				'option' => 'cariera_submit_company_page',
				'page'   => cariera_get_page_by_title( 'Submit Company' ),
			],
			'company_dashboard' => [
				'option' => 'cariera_company_dashboard_page',
				'page'   => cariera_get_page_by_title( 'Company Dashboard' ),
			],
			'company_page'      => [
				'option' => 'cariera_companies_page',
				'page'   => cariera_get_page_by_title( 'Companies' ),
			],
			// RESUME MANAGER.
			'resume_submit'     => [
				'option' => 'resume_manager_submit_resume_form_page_id',
				'page'   => cariera_get_page_by_title( 'Submit Resume' ),
			],
			'resume_dashboard'  => [
				'option' => 'resume_manager_candidate_dashboard_page_id',
				'page'   => cariera_get_page_by_title( 'Candidate Dashboard' ),
			],
			'resume_page'       => [
				'option' => 'resume_manager_resumes_page_id',
				'page'   => cariera_get_page_by_title( 'Resumes' ),
			],
			// WOOCOMMERCE.
			'shop_page'         => [
				'option' => 'woocommerce_shop_page_id',
				'page'   => cariera_get_page_by_title( 'Shop' ),
			],
			'shop_cart'         => [
				'option' => 'woocommerce_cart_page_id',
				'page'   => cariera_get_page_by_title( 'Cart' ),
			],
			'shop_checkout'     => [
				'option' => 'woocommerce_checkout_page_id',
				'page'   => cariera_get_page_by_title( 'Checkout' ),
			],
			'shop_account'      => [
				'option' => 'woocommerce_myaccount_page_id',
				'page'   => cariera_get_page_by_title( 'My Account' ),
			],
		];

		foreach ( $pages as $page ) {
			if ( isset( $page['page'] ) ) {
				update_option( $page['option'], $page['page']->ID );
			}
		}

		// WPJM RESUMES.
		update_option( 'resume_manager_enable_application', 0 );
		update_option( 'resume_manager_enable_application_for_url_method', 0 );

		// Remove setup notice in admin.
		if ( class_exists( 'WP_Job_Manager' ) ) {
			\WP_Job_Manager_Admin_Notices::remove_notice( \WP_Job_Manager_Admin_Notices::NOTICE_CORE_SETUP );
		}

		// Edit Premalink.
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		update_option( 'rewrite_rules', false );
		$wp_rewrite->flush_rules( true );

		// Refresh data.
		$this->refresh_data();

		// Recalculate term counts AFTER refreshing data.
		$this->recalculate_term_counts();
	}

	/**
	 * Refresh data to generate all taxonomy counts.
	 *
	 * @since   1.7.3
	 * @version 1.8.4
	 */
	private function refresh_data() {
		// Post types to refresh.
		$post_types = [
			'job_listing',
			'company',
			'resume',
			'elementor_library',
		];

		foreach ( $post_types as $post_type ) {
			$this->refresh_post_type( $post_type );
		}
	}

	/**
	 * Refresh posts of a given post type by updating their titles.
	 *
	 * @since 1.8.4
	 *
	 * @param string $post_type The post type to refresh.
	 */
	private function refresh_post_type( $post_type ) {
		$args = [
			'post_type'   => $post_type,
			'numberposts' => -1,
		];

		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$post->post_title = (string) $post->post_title; // Ensure the title is a string.
			wp_update_post( $post );
		}
	}

	/**
	 * Recalculate term counts for all taxonomies
	 *
	 * @since 1.9.8
	 */
	private function recalculate_term_counts() {
		// Defer term counting during import.
		wp_defer_term_counting( true );

		$taxonomies = get_taxonomies( [ 'public' => true ], 'names' );

		// Filter out built-in taxonomies that don't need recalculation.
		$excluded   = [ 'nav_menu', 'link_category', 'post_format' ];
		$taxonomies = array_diff( $taxonomies, $excluded );

		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_terms(
				[
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'fields'     => 'ids',
				]
			);

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				// Batch update in chunks of 100 terms.
				$chunks = array_chunk( $terms, 100 );
				foreach ( $chunks as $term_ids ) {
					wp_update_term_count_now( $term_ids, $taxonomy );
				}
			}
		}

		// Re-enable term counting.
		wp_defer_term_counting( false );
	}

	/**
	 * Import Elementor Settings
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public function import_elementor_json() {
		$this->verify_before_call_ajax( 'import_elementor_json' );

		if ( ! did_action( 'elementor/loaded' ) ) {
			wp_send_json_error( __( 'Could not export Elementor settings. The plugin Elementor is not installed.', 'cariera-core' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$import_content_steps = [];

		if ( isset( $_POST['import_content_steps'] ) && ! empty( $_POST['import_content_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Remove all empty item in steps.
			$import_content_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['import_content_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $this->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			// Create a date and time string to use for demo and log file names.
			\Cariera_Core\Importer\Import_Logger::set_demo_import_start_time();

			// Define log file path.
			$this->log_file_path = \Cariera_Core\Importer\Import_Logger::get_log_path();
		}

		set_transient( 'cariera_importer_data', $this->get_current_importer_data(), 0.1 * HOUR_IN_SECONDS );

		$data = $this->read_import_file( 'elementor.json' );

		if ( ! is_wp_error( $data ) && ! empty( $data ) ) {
			$data = json_decode( $data, true );

			// Have valid data? If no data or could not decode.
			if ( ! is_array( $data ) ) {
				\Cariera_Core\Importer\Import_Logger::append_to_file(
					esc_html__( 'Error: Elementor settings import data could not be read. Please try a different file.', 'cariera-core' ),
					$this->log_file_path,
					esc_html__( 'Importing Elementor settings', 'cariera-core' )
				);

				wp_send_json_error( esc_html__( 'Error: Elementor settings import data could not be read. Please try a different file.', 'cariera-core' ) );
			}

			$message          = '';
			$options_to_track = [];

			foreach ( $data as $option => $value ) {
				if ( update_option( $option, $value ) ) {
					$message           .= $option . esc_html__( ' - Imported', 'cariera-core' ) . PHP_EOL;
					$options_to_track[] = $option;
				} else {
					$message .= $option . esc_html__( ' - Skipped', 'cariera-core' ) . PHP_EOL;
				}
			}

			\Cariera_Core\Importer\Import_Logger::append_to_file(
				$message,
				$this->log_file_path,
				esc_html__( 'Importing Elementor settings', 'cariera-core' )
			);

			// Finish or go to next steps?
			if ( ! empty( $import_content_steps ) ) {
				$next_step = $this->get_next_step( 'elementor_json', $import_content_steps );

				if ( $next_step ) {
					wp_send_json(
						[
							'next_step' => $next_step,
							'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
						]
					);
				}
			}

			$this->send_final_response();
		} else {
			$error_message = $data->get_error_message();
			\Cariera_Core\Importer\Import_Logger::append_to_file( $error_message, $this->log_file_path, esc_html__( 'Importing Elementor settings', 'cariera-core' ) );
			wp_send_json_error( $error_message );
		}
	}

	/**
	 * Import Slider Revolution Data
	 *
	 * @since   1.7.3
	 * @version 1.8.8
	 */
	public function import_slider_data() {
		$this->verify_before_call_ajax( 'import_slider_data' );

		$slider = CARIERA_CORE_PATH . '/inc/importer/demo-data/' . $this->demo_slug . '/slider.zip';

		// Check if the slider file exists.
		if ( ! file_exists( $slider ) ) {
			wp_send_json_error( 'Slider file not found.' );
			return;
		}

		// Check if the RevSlider class is available.
		if ( ! class_exists( 'RevSliderSlider' ) ) {
			wp_send_json_error( 'RevSlider class not found.' );
			return;
		}

		// Retrieve and sanitize import content steps from the request.
		$import_content_steps = ! empty( $_POST['import_content_steps'] ) ? array_filter( array_map( 'sanitize_text_field', explode( ',', wp_unslash( $_POST['import_content_steps'] ) ) ) ) : []; // phpcs:ignore

		// Is this a new AJAX call to continue the previous import?
		if ( ! $this->use_existing_importer_data() ) {
			// Create a date and time string to use for demo and log file names.
			\Cariera_Core\Importer\Import_Logger::set_demo_import_start_time();

			// Define log file path.
			$this->log_file_path = \Cariera_Core\Importer\Import_Logger::get_log_path();
		}

		set_transient( 'cariera_importer_data', $this->get_current_importer_data(), 0.1 * HOUR_IN_SECONDS );

		// Initialize RevSlider and attempt to import the slider data.
		try {
			$revslider = new \RevSlider();
			$revslider->importSliderFromPost( true, true, $slider );

			// Log the successful import.
			\Cariera_Core\Importer\Import_Logger::append_to_file(
				'',
				$this->log_file_path,
				esc_html__( 'Importing Slider data', 'cariera-core' )
			);

			// Finish or go to next steps?
			if ( ! empty( $import_content_steps ) ) {
				$next_step = $this->get_next_step( 'slider_data', $import_content_steps );

				if ( $next_step ) {
					wp_send_json(
						[
							'next_step' => $next_step,
							'_wpnonce'  => wp_create_nonce( 'import_' . $next_step ),
						]
					);
					return;
				}
			}

			$this->send_final_response();
		} catch ( Exception $e ) {
			$error_message = $e->get_error_message();
			\Cariera_Core\Importer\Import_Logger::append_to_file( $error_message, $this->log_file_path, esc_html__( 'Importing Slider data', 'cariera-core' ) );
			wp_send_json_error( $error_message );
		}
	}

	/**
	 * Helper method to track imported data
	 *
	 * @since 1.9.8
	 *
	 * @param string $type Type of data (widgets, menus, options).
	 * @param array  $items Items to track.
	 */
	private function track_imported_data( $type, $items ) {
		$demo_slug   = $this->demo_slug;
		$theme_slug  = $this->theme_slug;
		$option_name = $theme_slug . '_' . $demo_slug . '_import_data';
		$import_data = get_option( $option_name, [] );

		if ( ! is_array( $import_data ) ) {
			$import_data = [];
		}

		if ( ! isset( $import_data[ $type ] ) ) {
			$import_data[ $type ] = [];
		}

		$import_data[ $type ] = array_merge( $import_data[ $type ], $items );
		$import_data[ $type ] = array_unique( $import_data[ $type ] );

		// Only update option if we have new items (reduces DB writes).
		update_option( $option_name, $import_data, false );
	}

	/**
	 * Check if we should show child theme generator
	 *
	 * @since 1.9.8
	 */
	public function should_show_child_theme_generator() {
		$child_theme_gen = \Cariera_Core\Importer\Child_Theme_Generator::instance();
		return ! $child_theme_gen->is_child_theme_active();
	}

	/**
	 * Generate child theme AJAX handler
	 *
	 * @since 1.9.8
	 */
	public function generate_child_theme() {
		$this->verify_before_call_ajax( 'generate_child_theme' );

		$demo_slug       = $this->demo_slug;
		$child_theme_gen = \Cariera_Core\Importer\Child_Theme_Generator::instance();
		$import_data_url = isset( $_POST['import_data_url'] ) ? sanitize_text_field( wp_unslash( $_POST['import_data_url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Check if child theme exists.
		if ( $child_theme_gen->child_theme_exists() ) {
			// Just activate it.
			$activation = $child_theme_gen->activate();

			if ( is_wp_error( $activation ) ) {
				wp_send_json_error( $activation->get_error_message() );
			}

			// Continue to next step.
			$this->continue_after_child_theme( $demo_slug, $import_data_url );
			return;
		}

		// Generate new child theme.
		$result = $child_theme_gen->generate();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Activate child theme.
		$activation = $child_theme_gen->activate();

		if ( is_wp_error( $activation ) ) {
			wp_send_json_error( $activation->get_error_message() );
		}

		// Continue to next step.
		$this->continue_after_child_theme( $demo_slug, $import_data_url );
	}

	/**
	 * Skip child theme generation
	 *
	 * @since 1.9.8
	 */
	public function skip_child_theme() {
		$this->verify_before_call_ajax( 'generate_child_theme' );

		if ( isset( $_POST['demo_slug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->demo_slug = sanitize_text_field( wp_unslash( $_POST['demo_slug'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			wp_send_json_error( esc_html__( 'Demo slug is not defined.', 'cariera-core' ) );
		}

		$demo_slug       = $this->demo_slug;
		$import_data_url = isset( $_POST['import_data_url'] ) ? sanitize_text_field( wp_unslash( $_POST['import_data_url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Continue to next step.
		$this->continue_after_child_theme( $demo_slug, $import_data_url );
	}

	/**
	 * Continue import after child theme step
	 *
	 * @since 1.9.8
	 *
	 * @param string $demo_slug Demo slug.
	 * @param string $import_data_url Import data URL.
	 */
	private function continue_after_child_theme( $demo_slug, $import_data_url ) {
		$demos = self::get_import_demos();

		// Show download import data if URL exists.
		if ( ! empty( $import_data_url ) ) {
			ob_start();
			cariera_get_template(
				'backend/importer/popup-download-import-form.php',
				[
					'import_data_url' => $import_data_url,
					'demo_slug'       => $demo_slug,
				]
			);
			wp_send_json_success( ob_get_clean() );
			return;
		}

		// If URL is empty, show demo steps form.
		$demo_steps = $this->get_demo_steps( $demo_slug );

		ob_start();
		cariera_get_template(
			'backend/importer/popup-demo-steps-form.php',
			[
				'demo_steps' => $demo_steps,
				'demo_slug'  => $demo_slug,
			]
		);
		wp_send_json_success( ob_get_clean() );
	}
}
