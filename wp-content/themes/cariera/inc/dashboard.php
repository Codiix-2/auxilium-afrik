<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		add_action( 'cariera_dashboard_nav_inner_start', [ $this, 'dashboard_profile' ], 10 );
		add_action( 'cariera_dashboard_menu', [ $this, 'dashboard_main_menu' ], 10 );
		add_action( 'cariera_dashboard_content_start', [ $this, 'dashboard_titlebar' ], 10 );
		add_action( 'cariera_dashboard_content_end', [ $this, 'dashboard_copyright' ], 10 );
		add_action( 'wp', [ $this, 'remove_wc_nav_on_dash' ] );
	}

	/**
	 * Dashboard Navigation - Profile Box
	 *
	 * @since   1.4.0
	 * @version 1.7.0
	 */
	public function dashboard_profile() {
		get_template_part( 'templates/dashboard/profile' );
	}

	/**
	 * Dashboard Main Menu
	 *
	 * @since   1.3.4
	 * @version 1.8.4
	 */
	public function dashboard_main_menu() {
		$user        = wp_get_current_user();
		$roles_menus = [
			'administrator' => 'dashboard-admin',
			'employer'      => 'dashboard-employer',
			'candidate'     => 'dashboard-candidate',
		];

		// Loop through defined roles and corresponding menu locations.
		foreach ( $roles_menus as $role => $menu_location ) {
			if ( in_array( $role, (array) $user->roles, true ) ) {
				$menu_args = [
					'theme_location' => $menu_location,
					'container'      => false,
					'menu_class'     => 'dashboard-nav-' . $role,
					'fallback_cb'    => '__return_false',
				];

				// Add walker if Mega Menu class exists.
				if ( has_nav_menu( $menu_location ) && class_exists( '\Cariera\Mega_Menu' ) ) {
					$menu_args['walker'] = new \Cariera\Mega_Menu();
				}

				// Display the menu or fallback to template parts.
				if ( has_nav_menu( $menu_location ) ) {
					wp_nav_menu( $menu_args );
				} else {
					get_template_part( 'templates/dashboard/main-menu' );
					get_template_part( 'templates/dashboard/listing-menu' );
					get_template_part( 'templates/dashboard/account-menu' );
				}

				break; // Exit loop once the correct role is found.
			}
		}
	}

	/**
	 * Dashboard Title Bar
	 *
	 * @since   1.3.4
	 * @version 1.7.0
	 */
	public function dashboard_titlebar() {
		get_template_part( 'templates/dashboard/titlebar' );
	}

	/**
	 * Dashboard Copyright Footer
	 *
	 * @since   1.3.4
	 * @version 1.7.0
	 */
	public function dashboard_copyright() {
		get_template_part( 'templates/dashboard/copyright' );
	}

	/**
	 * Remove WooCommerce Nav on User Dashboard Template
	 *
	 * @since   1.3.5
	 * @version 1.7.0
	 */
	public function remove_wc_nav_on_dash() {
		if ( is_page_template( 'templates/user-dashboard.php' ) ) {
			remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );
		}
	}
}
