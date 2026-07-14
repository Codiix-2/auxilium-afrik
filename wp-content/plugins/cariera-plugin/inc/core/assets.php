<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Register Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );

		// Enqueue Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ], 20 );
	}

	/**
	 * Register Core Plugin assets.
	 *
	 * @since   1.6.3
	 * @version 2.0.0
	 */
	public function register_assets() {
		$version = self::assets_version();
		$suffix  = is_rtl() ? '.rtl' : '';

		// Main Core Frontend.
		wp_register_script( 'cariera-core-main', CARIERA_URL . '/assets/dist/js/frontend.js', [ 'jquery' ], $version, true );

		$args = [
			'ajax_url'            => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
			'nonce'               => wp_create_nonce( '_cariera_core_nonce' ),
			'is_rtl'              => is_rtl() ? 1 : 0,
			'home_url'            => esc_url( home_url( '/' ) ),
			'upload_ajax'         => admin_url( 'admin-ajax.php?action=handle_uploaded_media' ),
			'delete_ajax'         => admin_url( 'admin-ajax.php?action=handle_deleted_media' ),
			'max_file_size'       => apply_filters( 'cariera_file_max_size', size_format( wp_max_upload_size() ) ),
			'map_provider'        => get_option( 'cariera_map_provider' ),
			'mapbox_access_token' => get_option( 'cariera_mapbox_access_token' ),
			'map_type'            => get_option( 'cariera_maps_type' ),
			'strings'             => [
				'uploading'           => esc_html__( 'Uploading...', 'cariera-core' ),
				'processing'          => esc_html__( 'Processing...', 'cariera-core' ),
				'max_file_size'       => esc_html__( 'Max file size:', 'cariera-core' ),
				'invalid_file_type'   => esc_html__( 'Please select a valid image file.', 'cariera-core' ),
				'delete_account_text' => esc_html__( 'Are you sure you want to delete your account?', 'cariera-core' ),
				'notification_loader' => esc_html__( 'No more notifications.', 'cariera-core' ),
			],
		];

		wp_localize_script( 'cariera-core-main', 'cariera_core_settings', $args );

		// WPJM Ajax Filters.
		if ( class_exists( 'WP_Job_Manager' ) && defined( 'JOB_MANAGER_VERSION' ) ) {
			wp_dequeue_script( 'wp-job-manager-ajax-filters' );
			wp_deregister_script( 'wp-job-manager-ajax-filters' );
			wp_register_script( 'wp-job-manager-ajax-filters', CARIERA_URL . '/assets/dist/js/jobs-ajax-filters.js', [ 'jquery', 'jquery-deserialize' ], $version, true );
			wp_localize_script(
				'wp-job-manager-ajax-filters',
				'job_manager_ajax_filters',
				[
					'ajax_url'                => \WP_Job_Manager_Ajax::get_endpoint(),
					'is_rtl'                  => is_rtl() ? 1 : 0,
					'i18n_load_prev_listings' => esc_html__( 'Load previous listings', 'cariera-core' ),
					'currency'                => \cariera_currency_symbol(),
				]
			);
		}

		// Resume AJAX Filters.
		if ( class_exists( 'WP_Job_Manager' ) && class_exists( 'WP_Resume_Manager' ) ) {
			wp_dequeue_script( 'wp-resume-manager-ajax-filters' );
			wp_deregister_script( 'wp-resume-manager-ajax-filters' );
			wp_register_script( 'wp-resume-manager-ajax-filters', CARIERA_URL . '/assets/dist/js/resumes-ajax-filters.js', [ 'jquery', 'jquery-deserialize' ], $version, true );
			wp_localize_script(
				'wp-resume-manager-ajax-filters',
				'resume_manager_ajax_filters',
				[
					'ajax_url'    => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
					'currency'    => \cariera_currency_symbol(),
					'showing_all' => esc_html__( 'Showing all resumes', 'cariera-core' ),
				]
			);
		}

		// Company Ajax Filters.
		if ( class_exists( 'WP_Job_Manager' ) ) {
			wp_register_script( 'cariera-company-ajax-filters', CARIERA_URL . '/assets/dist/js/company-ajax-filters.js', [ 'jquery', 'jquery-deserialize' ], $version, true );
			wp_localize_script(
				'cariera-company-ajax-filters',
				'cariera_company_ajax_filters',
				[
					'ajax_url' => \WP_Job_Manager_Ajax::get_endpoint(),
					'is_rtl'   => is_rtl() ? 1 : 0,
					'lang'     => apply_filters( 'wpjm_lang', null ),
				]
			);
		}

		// Company Submission.
		wp_register_script( 'cariera-company-manager-submission', CARIERA_URL . '/assets/dist/js/company-submission.js', [ 'jquery', 'jquery-ui-sortable' ], $version, true );
		wp_localize_script(
			'cariera-company-manager-submission',
			'cariera_company_manager_submission',
			[
				'ajax_url'       => admin_url( 'admin-ajax.php', 'relative' ),
				'select_company' => esc_html__( 'Select Company', 'cariera-core' ),
			]
		);

		// Company Dashboard.
		wp_register_script( 'cariera-company-manager-dashboard', CARIERA_URL . '/assets/dist/js/company-dashboard.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-company-manager-dashboard',
			'cariera_company_dashboard',
			[
				'i18n_confirm_delete' => esc_html__( 'Are you sure you want to delete this company listing?', 'cariera-core' ),
			]
		);

		// Cariera Companies List.
		wp_register_style( 'cariera-companies-list', CARIERA_URL . '/assets/dist/css/companies-list' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-companies-list', CARIERA_URL . '/assets/dist/js/companies-list.js', [], $version, true );

		// Cariera Company Backend.
		wp_register_style( 'cariera-core-admin-companies', CARIERA_URL . '/assets/dist/css/backend/companies' . $suffix . '.css', [ 'dashicons' ], $version );
		wp_register_script( 'cariera-core-admin-companies', CARIERA_URL . '/assets/dist/js/backend/companies.js', [ 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable' ], $version, true );

		// Location Autocomplete.
		wp_register_style( 'cariera-location-autocomplete', CARIERA_URL . '/assets/dist/css/location-autocomplete' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-location-autocomplete', CARIERA_URL . '/assets/dist/js/location-autocomplete.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-location-autocomplete',
			'cariera_location_autocomplete',
			[
				'map_provider' => get_option( 'cariera_map_provider' ),
				'autolocation' => get_option( 'cariera_location_autocomplete' ) ? true : false,
				'country'      => get_option( 'cariera_location_autocomplete_restriction' ),
				'strings'      => [
					'gelocation_error_denied'     => esc_html__( 'Location permission was denied. Please allow location access to use this feature.', 'cariera-core' ),
					'gelocation_error_unvailable' => esc_html__( 'Location information is unavailable.', 'cariera-core' ),
					'gelocation_error_timeout'    => esc_html__( 'The request to get user location timed out.', 'cariera-core' ),
				],
			]
		);

		// Maps.
		wp_register_style( 'cariera-maps', CARIERA_URL . '/assets/dist/css/maps' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-maps', CARIERA_URL . '/assets/dist/js/maps.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-maps',
			'cariera_maps',
			[
				'map_provider'        => get_option( 'cariera_map_provider' ),
				'map_autofit'         => get_option( 'cariera_map_autofit' ),
				'centerPoint'         => get_option( 'cariera_map_center' ),
				'mapbox_access_token' => get_option( 'cariera_mapbox_access_token' ),
				'map_type'            => get_option( 'cariera_maps_type' ),
			]
		);

		// Backend.
		wp_register_style( 'cariera-core-admin', CARIERA_URL . '/assets/dist/css/backend/admin' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-core-admin', CARIERA_URL . '/assets/dist/js/backend/admin.js', [], $version, true );
		wp_localize_script(
			'cariera-core-admin',
			'cariera_core_admin',
			[
				'ajax_url'     => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
				'nonce'        => wp_create_nonce( '_cariera_core_admin_nonce' ),
				'map_provider' => get_option( 'cariera_map_provider' ),
				'country'      => get_option( 'cariera_location_autocomplete_restriction' ),
				'strings'      => [
					'select_company' => esc_html__( 'No Company Selected', 'cariera-core' ),
					'loading_icons'  => esc_html__( 'Loading Icons...', 'cariera-core' ),
				],
			]
		);

		// Cariera Core Settings.
		wp_register_style( 'cariera-core-settings', CARIERA_URL . '/assets/dist/css/backend/settings' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-core-settings', CARIERA_URL . '/assets/dist/js/backend/settings.js', [], $version, true );

		// Cariera Core Migrations.
		wp_register_style( 'cariera-core-migrations', CARIERA_URL . '/assets/dist/css/backend/migrations' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-core-migrations', CARIERA_URL . '/assets/dist/js/backend/migrations.js', [], $version, true );

		// Admin location autocomplete.
		wp_register_style( 'cariera-core-admin-location-autocomplete', CARIERA_URL . '/assets/dist/css/backend/location-autocomplete' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-core-admin-location-autocomplete', CARIERA_URL . '/assets/dist/js/backend/location-autocomplete.js', [], $version, true );

		// Cariera My Profile.
		wp_register_style( 'cariera-core-my-profile', CARIERA_URL . '/assets/dist/css/my-profile' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-core-my-profile', CARIERA_URL . '/assets/dist/js/my-profile.js', [ 'jquery' ], $version, true );

		// reCaptcha.
		wp_register_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js', [], $version, true );

		// hCaptcha.
		wp_register_script( 'hcaptcha', 'https://js.hcaptcha.com/1/api.js', [], $version, true );

		// Cloudflare Turnstile.
		wp_register_script( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true ); // phpcs:ignore

		// Blog Elementor Element.
		wp_register_style( 'cariera-blog-element', CARIERA_URL . '/assets/dist/css/blog-element' . $suffix . '.css', [], $version );

		// Pricing Tables.
		wp_register_style( 'cariera-pricing-tables', CARIERA_URL . '/assets/dist/css/pricing-tables' . $suffix . '.css', [], $version );

		// Testimonials.
		wp_register_style( 'cariera-testimonials', CARIERA_URL . '/assets/dist/css/testimonials' . $suffix . '.css', [], $version );

		// Listing Categories.
		wp_register_style( 'cariera-listing-categories', CARIERA_URL . '/assets/dist/css/listing-categories' . $suffix . '.css', [], $version );

		// Listing Half Detail.
		wp_register_script( 'cariera-listing-split-view', CARIERA_URL . '/assets/dist/js/listing-split-view.js', [], $version, true );
		wp_register_style( 'cariera-listing-split-view', CARIERA_URL . '/assets/dist/css/listing-split-view' . $suffix . '.css', [], $version );

		// Listing Half Map.
		wp_register_script( 'cariera-listing-half-map', CARIERA_URL . '/assets/dist/js/listing-half-map.js', [], $version, true );
		wp_register_style( 'cariera-listing-half-map', CARIERA_URL . '/assets/dist/css/listing-half-map' . $suffix . '.css', [], $version );
	}

	/**
	 * Enqueue Core Plugin assets.
	 *
	 * @since   1.6.3
	 * @version 1.9.9
	 */
	public function enqueue_assets() {
		$version = self::assets_version();

		// Main JS File of the core plugin.
		wp_enqueue_script( 'cariera-core-main' );
	}

	/**
	 * Backend - Enqueue Core Plugin assets.
	 *
	 * @since   1.6.3
	 * @version 2.0.0
	 */
	public function enqueue_admin_assets() {
		global $wp_scripts, $post_type;
		$screen  = get_current_screen();
		$version = self::assets_version();

		// Main JS File of the core plugin.
		wp_enqueue_style( 'cariera-core-admin' );
		wp_enqueue_script( 'cariera-core-admin' );

		// Enqueue Google Maps only on specific CPT edit screens.
		if ( $screen && 'post' === $screen->base && in_array( $screen->post_type, [ 'job_listing', 'company', 'resume' ], true ) ) {
			self::enqueue_google_maps( true );

			// Location autocomplete.
			wp_enqueue_style( 'cariera-core-admin-location-autocomplete' );
			wp_enqueue_script( 'cariera-core-admin-location-autocomplete' );
		}

		// Companies Admin Pages.
		if ( 'company_page_cariera_company_manager_settings' === $screen->id || \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY === $post_type ) {
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : $version;
			$jquery_version = preg_replace( '/-wp/', '', $jquery_version );
			wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'cariera-core-admin-companies' );
			wp_enqueue_script( 'cariera-core-admin-companies' );
		}
	}

	/**
	 * Enqueue Maps script.
	 *
	 * @since 1.9.9
	 */
	public static function enqueue_maps() {
		$map_provider = get_option( 'cariera_map_provider' );

		if ( wp_script_is( 'cariera-maps', 'enqueued' ) ) {
			return;
		}

		if ( 'none' === $map_provider ) {
			return;
		}

		// Enqueue Google Maps if enabled.
		self::enqueue_google_maps( true );

		wp_enqueue_style( 'cariera-maps' );
		wp_enqueue_script( 'cariera-maps' );
	}

	/**
	 * Enqueue Location Autocomplete script.
	 *
	 * @since   1.9.9
	 * @version 2.0.0
	 */
	public static function enqueue_location_autocomplete() {
		$map_provider = get_option( 'cariera_map_provider' );
		$autocomplete = get_option( 'cariera_location_autocomplete' );
		$geolocate    = get_option( 'cariera_auto_geolocate' );

		if ( 'none' === $map_provider || ( ! $autocomplete && ! $geolocate ) ) {
			return;
		}

		// Enqueue Google Maps if enabled.
		self::enqueue_google_maps( true );

		if ( wp_script_is( 'cariera-location-autocomplete', 'enqueued' ) ) {
			return;
		}

		wp_enqueue_style( 'cariera-location-autocomplete' );
		wp_enqueue_script( 'cariera-location-autocomplete' );
	}

	/**
	 * Enqueue Google Maps JS.
	 *
	 * @since 1.9.9
	 *
	 * @param bool $async Load script asynchronously.
	 */
	public static function enqueue_google_maps( $async = false ) {
		if ( wp_script_is( 'google-maps', 'enqueued' ) ) {
			return;
		}

		if ( 'google' !== get_option( 'cariera_map_provider' ) ) {
			return;
		}

		$api_key = get_option( 'cariera_gmap_api_key' );
		if ( empty( $api_key ) ) {
			return;
		}

		$version = self::assets_version();

		$src = add_query_arg(
			[
				'key'       => $api_key,
				'libraries' => 'places',
				'language'  => get_option( 'cariera_gmap_language' ),
				'callback'  => 'Function.prototype',
				'loading'   => $async ? 'async' : null,
			],
			'https://maps.googleapis.com/maps/api/js'
		);

		wp_enqueue_script( 'google-maps', $src, [ 'jquery' ], $version, true );
	}

	/**
	 * Get asset version for cache busting.
	 *
	 * @since 1.9.9
	 *
	 * @return string Asset version.
	 */
	protected static function assets_version() {
		return function_exists( '\Cariera\is_dev_mode' ) && \Cariera\is_dev_mode() ? wp_rand( 1, 1e4 ) : CARIERA_CORE_VERSION;
	}
}
