<?php

namespace Cariera_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Core {

	/**
	 * App Rest Class for mobile handling.
	 *
	 * @var \Cariera_Core\Core\App
	 */
	protected $app;

	/**
	 * Ajax functions of the core plugin.
	 *
	 * @var \Cariera_Core\Core\Ajax
	 */
	protected $ajax;

	/**
	 * Core plugin assets.
	 *
	 * @var \Cariera_Core\Core\Assets
	 */
	protected $assets;

	/**
	 * Cariera Notifications
	 *
	 * @var \Cariera_Core\Core\Notifications
	 */
	protected $notifications;

	/**
	 * Cariera Admin Settings
	 *
	 * @var \Cariera_Core\Core\Admin
	 */
	protected $admin;

	/**
	 * Cariera Email Handling
	 *
	 * @var \Cariera_Core\Core\Emails
	 */
	protected $emails;

	/**
	 * Cariera Users
	 *
	 * @var \Cariera_Core\Core\Users
	 */
	protected $users;

	/**
	 * Cariera Metaboxes
	 *
	 * @var \Cariera_Core\Core\Metabox
	 */
	protected $metabox;

	/**
	 * Cariera Messages
	 *
	 * @var \Cariera_Core\Core\Messages
	 */
	protected $messages;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Init Classes.
		$this->app           = \Cariera_Core\Core\App::instance();
		$this->ajax          = \Cariera_Core\Core\Ajax::instance();
		$this->assets        = \Cariera_Core\Core\Assets::instance();
		$this->notifications = \Cariera_Core\Core\Notifications::instance();
		$this->admin         = \Cariera_Core\Core\Admin::instance();
		$this->emails        = \Cariera_Core\Core\Emails::instance();
		$this->users         = \Cariera_Core\Core\Users::instance();
		$this->metabox       = \Cariera_Core\Core\Metabox::instance();
		$this->messages      = \Cariera_Core\Core\Messages::instance();

		\Cariera_Core\Core\Migrations::instance();

		// Actions.
		add_action( 'widgets_init', [ $this, 'widgets_init' ] );

		// Custom Cron Schedules.
		add_filter( 'cron_schedules', [ $this, 'cron_schedules' ] );

		// Remove WPJM Actions.
		add_action( 'admin_init', [ $this, 'remove_wpjm_actions' ] );

		// Core plugin.
		add_action( 'cariera_check_core_plugin', [ $this, 'check_core_plugin' ] );

		// Disable Gutenberg for this CPT.
		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
	}

	/**
	 * Registering Widgets
	 *
	 * @since   1.2.2
	 * @version 1.9.3
	 */
	public function widgets_init() {
		require_once CARIERA_CORE_PATH . '/inc/widgets/social-media-widget.php';
		require_once CARIERA_CORE_PATH . '/inc/widgets/recent-posts-widget.php';
		require_once CARIERA_CORE_PATH . '/inc/widgets/job-search-widget.php';
		require_once CARIERA_CORE_PATH . '/inc/widgets/resume-search-widget.php';
		require_once CARIERA_CORE_PATH . '/inc/widgets/company-search-widget.php';
	}

	/**
	 * Add schedule to use for cron job. Should not be called externally.
	 *
	 * @since 1.5.0
	 *
	 * @param array $schedules
	 */
	public function cron_schedules( $schedules ) {
		if ( ! isset( $schedules['30min'] ) ) {
			$schedules['30min'] = [
				'interval' => 30 * 60,
				'display'  => esc_html__( 'Once every 30 minutes', 'cariera-core' ),
			];
		}

		if ( ! isset( $schedules['cariera_two_weeks'] ) ) {
			$schedules['two_weeks'] = [
				'interval' => 15 * DAY_IN_SECONDS,
				'display'  => esc_html__( 'Every Two Weeks', 'cariera-core' ),
			];
		}

		if ( ! isset( $schedules['cariera_monthly'] ) ) {
			$schedules['monthly'] = [
				'interval' => 2635200,
				'display'  => esc_html__( 'Every Month', 'cariera-core' ),
			];
		}

		return $schedules;
	}

	/**
	 * Remove WPJM Actions
	 *
	 * @since   1.3.8
	 * @version 1.7.4
	 */
	public function remove_wpjm_actions() {
		if ( ! class_exists( 'WP_Job_Manager_Helper' ) || ! class_exists( 'WP_Job_Manager_Admin_Notices' ) ) {
			return;
		}

		// Remove Admin Notices.
		remove_action( 'admin_notices', [ \WP_Job_Manager_Helper::instance(), 'licence_error_notices' ] );
		remove_action( 'admin_notices', [ 'WP_Job_Manager_Admin_Notices', 'display_notices' ] );

		// Remove "check for update" function. This causes the plugin updates to fail.
		remove_action( 'pre_set_site_transient_update_plugins', [ \WP_Job_Manager_Helper::instance(), 'check_for_updates' ] );
	}

	/**
	 * Check core plugin.
	 *
	 * @since   1.8.4
	 * @version 2.0.0
	 *
	 * TODO: Check why the write_log used to happen some times. I need to fix this correctly.
	 */
	public function check_core_plugin() {
		// Retrieve license key and email from options.
		$license_status  = get_option( 'cariera_license_activated' );
		$license_key     = get_option( 'Cariera_lic_Key' );
		$plugin_basename = 'cariera-plugin/cariera-core.php';

		// Ensure the function is available.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check if the license is inactive or the license key does not exist.
		if ( ! empty( $license_status ) && ! empty( $license_key ) ) {
			\Cariera\write_log( 'License status & key are not empty.' );
			return;
		}

		if ( ! is_plugin_active( $plugin_basename ) ) {
			\Cariera\write_log( 'Plugin does not exist.' );
			return;
		}

		// Deactivate the plugin if it's currently active.
		$result = deactivate_plugins( $plugin_basename, true );

		if ( is_wp_error( $result ) ) {
			\Cariera\write_log( sprintf( 'Failed to deactivate plugin %s: %s', $plugin_basename, $result->get_error_message() ) );
			return;
		}

		// Log the deactivation.
		$deactivation_count = get_option( 'cariera_core_force_deactivation_count', 0 );

		// Increment the count.
		++$deactivation_count;

		// Update the option with the new count.
		update_option( 'cariera_core_force_deactivation_count', $deactivation_count );

		// Log the new count for debugging.
		\Cariera\write_log( sprintf( 'License deactivation count updated to: %d', $deactivation_count ) );

		// Clear any cached data related to the plugin.
		wp_cache_delete( $plugin_basename, 'plugins' );
	}

	/**
	 * Disable Gutenberg for CPTs
	 *
	 * @since   1.9.5
	 * @version 1.9.7
	 *
	 * @param bool   $use_block_editor
	 * @param string $post_type
	 */
	public function disable_gutenberg( $use_block_editor, $post_type ) {
		if ( get_option( 'cariera_block_editor' ) ) {
			return $use_block_editor;
		}

		$cpts_to_disable = [ 'page', 'post', 'job_listing', 'resume', 'company', 'cariera_event' ];

		// Filter the list of CPTs to disable Gutenberg for.
		$cpts_to_disable = apply_filters( 'cariera_core_disable_gutenberg_cpts', $cpts_to_disable );

		if ( in_array( $post_type, $cpts_to_disable, true ) ) {
			return false;
		}

		return $use_block_editor;
	}
}
