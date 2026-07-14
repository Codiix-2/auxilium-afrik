<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 10 );

		// Enqueue admin assets.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
	}

	/**
	 * Registers and enqueues scripts and CSS.
	 *
	 * @since   0.9.0
	 * @version 0.9.10
	 */
	public function register_assets() {
		$ajax_filter_deps = [ 'jquery', 'jquery-deserialize' ];
		$version          = function_exists( '\Cariera\is_dev_mode' ) && \Cariera\is_dev_mode() ? wp_rand( 1, 1e4 ) : CARIERA_PACKAGES_VERSION;
		$suffix           = is_rtl() ? '.rtl' : '';

		// Packages.
		wp_register_style( 'cariera-packages', CARIERA_PACKAGES_URL . '/assets/dist/css/packages' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-packages', CARIERA_PACKAGES_URL . 'assets/dist/js/packages.js', [ 'jquery' ], $version, true );

		// Promotions.
		wp_register_style( 'cariera-packages-promotions', CARIERA_PACKAGES_URL . 'assets/dist/css/promotions' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-packages-promotions', CARIERA_PACKAGES_URL . 'assets/dist/js/promotions.js', [ 'jquery' ], $version, true );
	}

	/**
	 * Enqueues necessary admin scripts.
	 *
	 * @since   0.9.0
	 * @version 0.9.21
	 */
	public function admin_assets() {
		$screen  = get_current_screen();
		$version = function_exists( '\Cariera\is_dev_mode' ) && \Cariera\is_dev_mode() ? wp_rand( 1, 1e4 ) : CARIERA_PACKAGES_VERSION;
		$suffix  = is_rtl() ? '.rtl' : '';

		// Register assets.
		wp_register_style( 'cariera-packages-admin', CARIERA_PACKAGES_URL . 'assets/dist/css/admin' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-packages-admin', CARIERA_PACKAGES_URL . 'assets/dist/js/admin.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-packages-admin',
			'cariera_packages_admin',
			[
				'strings' => [
					'choose_package_select2_placeholder' => esc_html__( 'Choose package type', 'cariera-packages' ),
				],
			]
		);

		// Backend Edit listings.
		wp_register_script( 'cariera-packages-backend-edit-listings', CARIERA_PACKAGES_URL . 'assets/dist/js/backend/edit-listings.js', [ 'jquery' ], $version, true );

		// Enqueue assets.
		if ( $screen && \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE === $screen->post_type ) {
			wp_enqueue_style( 'cariera-packages-admin' );
			wp_enqueue_script( 'cariera-packages-admin' );
		}

		$allowed_post_types = [ 'job_listing', 'resume', 'company' ];
		if ( $screen && 'post' === $screen->base && in_array( $screen->post_type, $allowed_post_types, true ) ) {
			wp_enqueue_script( 'cariera-packages-backend-edit-listings' );
		}
	}
}
