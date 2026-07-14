<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Packages_Dependency_Checker {
	const MINIMUM_PHP_VERSION = '7.0';
	const MINIMUM_WP_VERSION  = '4.7.0';

	/**
	 * Check if dependencies have been met.
	 *
	 * @since 0.9.3
	 */
	public static function check_dependencies() {
		if ( ! self::check_cariera_license() ) {
			add_action( 'admin_notices', [ 'Cariera_Packages_Dependency_Checker', 'add_cariera_notice' ] );
		}

		if ( ! self::check_php() ) {
			add_action( 'admin_notices', [ 'Cariera_Packages_Dependency_Checker', 'add_php_notice' ] );
		}

		if ( ! self::check_wp() ) {
			add_action( 'admin_notices', [ 'Cariera_Packages_Dependency_Checker', 'add_wp_notice' ] );
			add_filter( 'plugin_action_links_' . CARIERA_PACKAGES_BASENAME, [ 'Cariera_Packages_Dependency_Checker', 'wp_version_plugin_action_notice' ] );
		}

		return true;
	}

	/**
	 * Checks if Cariera is license activated.
	 *
	 * @since 0.9.3
	 */
	private static function check_cariera_license() {
		return get_option( 'cariera_license_activated' );
	}

	/**
	 * Adds notice in WP Admin if Cariera license is not activated.
	 *
	 * @since 0.9.3
	 */
	public static function add_cariera_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		// Activate theme URL.
		$activate_url = '';

		// Message.
		$message = sprintf(
			'<strong>%s</strong> %s <strong>%s</strong> %s <a href="%s">%s</a>.',
			esc_html__( 'Cariera', 'cariera-packages' ),
			esc_html__( 'has no valid license and is not active. ', 'cariera-packages' ),
			esc_html__( 'Cariera Packages', 'cariera-packages' ),
			esc_html__( 'will not work unless the theme has been activated. Please activate the theme', 'cariera-packages' ),
			esc_url( admin_url( 'admin.php?page=cariera_theme' ) ),
			esc_html__( 'here', 'cariera-packages' )
		);

		echo '<div class="error">';
		echo '<p>' . wp_kses_post( $message ) . '</p>';
		echo '</div>';
	}

	/**
	 * Checks for our PHP version requirement.
	 *
	 * @since 0.9.3
	 */
	private static function check_php() {
		return version_compare( phpversion(), self::MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of PHP is not met.
	 *
	 * @since 0.9.3
	 */
	public static function add_php_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		// translators: %1$s is version of PHP that Cariera Packages requires; %2$s is the version of PHP WordPress is running on.
		$message = sprintf( __( '<strong>Cariera Packages</strong> requires a minimum PHP version of %1$s, but you are running %2$s. Please update PHP to continue using this plugin.', 'cariera-packages' ), self::MINIMUM_PHP_VERSION, phpversion() );

		echo '<div class="error"><p>';
		echo wp_kses( $message, [ 'strong' => [] ] );
		$php_update_url = 'https://wordpress.org/support/update-php/';
		if ( function_exists( 'wp_get_update_php_url' ) ) {
			$php_update_url = wp_get_update_php_url();
		}
		printf(
			'<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( $php_update_url ),
			esc_html__( 'Learn more about updating PHP', 'cariera-packages' ),
			/* translators: accessibility text */
			esc_html__( '(opens in a new tab)', 'cariera-packages' )
		);
		echo '</p></div>';
	}

	/**
	 * Checks for our WordPress version requirement.
	 *
	 * @since 0.9.3
	 */
	private static function check_wp() {
		global $wp_version;
		return version_compare( $wp_version, self::MINIMUM_WP_VERSION, '>=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of WordPress is not met.
	 *
	 * @since 0.9.3
	 */
	public static function add_wp_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		$update_action_link = '';
		if ( current_user_can( 'update_core' ) ) {
			// translators: %s is the URL for the page where users can go to update WordPress.
			$update_action_link = ' ' . sprintf( __( 'Please <a href="%s">update WordPress</a> to avoid issues.', 'cariera-packages' ), esc_url( self_admin_url( 'update-core.php' ) ) );
		}

		echo '<div class="error">';
		echo '<p>' . wp_kses_post( __( '<strong>Cariera Packages</strong> requires a more recent version of WordPress.', 'cariera-packages' ) . $update_action_link ) . '</p>';
		echo '</div>';
	}

	/**
	 * Add admin notice when WP upgrade is required.
	 *
	 * @since 0.9.3
	 *
	 * @param array $actions Actions to show in WordPress admin's plugin list.
	 * @return array
	 */
	public static function wp_version_plugin_action_notice( $actions ) {
		if ( ! current_user_can( 'update_core' ) ) {
			$actions[] = '<strong style="color: red">' . esc_html__( 'WordPress Update Required', 'cariera-packages' ) . '</strong>';
		} else {
			$actions[] = '<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '" style="color: red">' . esc_html__( 'WordPress Update Required', 'cariera-packages' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Returns the screen IDs where dependency notices should be displayed.
	 *
	 * @since 0.9.3
	 *
	 * @return array
	 */
	private static function get_critical_screen_ids() {
		return [ 'dashboard', 'plugins', 'plugins-network', 'themes', 'toplevel_page_cariera_theme' ];
	}
}
