<?php

namespace Cariera_Addons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Install {

	const DB_VERSION            = '1.0.0';
	const DB_OPTION_NAME        = 'cariera_addons_db_version';
	const INSTALLED_OPTION_NAME = 'cariera_addons_installed';
	const VERSION_OPTION_NAME   = 'cariera_addons_version';

	/**
	 * Version of the DB install.
	 *
	 * @var $version
	 */
	public $version = '1.0.0';

	/**
	 * Constructor
	 */
	public function __construct() {
		register_activation_hook( CARIERA_ADDONS_PLUGIN, [ $this, 'activate' ] );
		register_deactivation_hook( CARIERA_ADDONS_PLUGIN, [ $this, 'deactivate' ] );

		// Check and update database on plugin load or update.
		add_action( 'plugins_loaded', [ $this, 'check_db_version_and_update' ] );
	}

	/**
	 * Check database version and update if necessary
	 *
	 * @since   0.9.0
	 * @version 1.0.8
	 */
	public function check_db_version_and_update() {
		$current_version = get_option( self::DB_OPTION_NAME );

		// Only run install if version is outdated or doesn't exist.
		if ( version_compare( $current_version, self::DB_VERSION, '<' ) ) {
			$this->install();
			$this->update_db_version();
		}
	}

	/**
	 * Plugin activation init
	 *
	 * @since   0.9.0
	 * @version 1.0.8
	 *
	 * @param mixed $network_wide
	 */
	public function activate( $network_wide ) {

		// If multisite.
		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				[
					'fields' => 'ids',
				]
			);

			foreach ( $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->install();
				$this->update_db_version();
				restore_current_blog();
			}
		} else {
			$this->install();
			$this->update_db_version();
		}
	}

	/**
	 * Plugin deactivation init
	 *
	 * @since 0.9.0
	 *
	 * @param mixed $network_wide
	 */
	public function deactivate( $network_wide ) {

		// If multisite.
		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				[
					'fields' => 'ids',
				]
			);

			foreach ( $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->uninstall();
				restore_current_blog();
			}
		} else {
			$this->uninstall();
		}
	}

	/**
	 * Run when plugin gets activated
	 *
	 * @since   0.9.0
	 * @version 1.0.7
	 */
	public function install() {
		$this->activate_options();
		$this->create_table_bookmarks();
		$this->schedule_cron_jobs();

		// Add Job Application user capabilities.
		\Cariera_Addons\Core\Applications\Post_Types::add_user_capabilities();
	}

	/**
	 * Run when plugin gets deactivated
	 *
	 * @since 0.9.0
	 */
	public function uninstall() {
		$this->deactivate_options();
		$this->unschedule_events();
	}

	/**
	 * Run function when plugin get's activated
	 *
	 * @since 0.9.0
	 * @version 1.0.8
	 */
	private function activate_options() {
		$installed = get_option( self::INSTALLED_OPTION_NAME );

		if ( ! $installed ) {
			update_option( self::INSTALLED_OPTION_NAME, time() );
		}

		update_option( self::VERSION_OPTION_NAME, CARIERA_ADDONS_VERSION );
	}

	/**
	 * Run function when plugin get's deactivated
	 *
	 * @since   0.9.0
	 * @version 1.0.8
	 */
	private function deactivate_options() {
		delete_option( self::INSTALLED_OPTION_NAME );
		delete_option( self::VERSION_OPTION_NAME );
	}

	/**
	 * Scheduled events to clear the db tables
	 *
	 * @since   0.9.0
	 * @version 1.0.1
	 */
	public function schedule_cron_jobs() {
		if ( ! wp_next_scheduled( 'cariera_addons_check_plugin' ) ) {
			wp_schedule_event( time(), 'daily', 'cariera_addons_check_plugin' );
		}

		if ( ! wp_next_scheduled( 'cariera_addons_check_for_expired_resumes' ) ) {
			wp_schedule_event( time(), 'hourly', 'cariera_addons_check_for_expired_resumes' );
		}
	}

	/**
	 * Unscheduled events to avoid issues after plugin deactivation
	 *
	 * @since 0.9.0
	 */
	public function unschedule_events() {
		wp_clear_scheduled_hook( 'check_application_deadlines' );
		wp_clear_scheduled_hook( 'cariera_addons_check_for_expired_resumes' );
	}

	/**
	 * Creating the Database table for the bookmarks
	 *
	 * @since   0.9.0
	 * @version 1.0.8
	 */
	public function create_table_bookmarks() {
		global $wpdb;

		$table_name = $wpdb->prefix . \Cariera_Addons\Core\Bookmarks\Bookmarks::TABLE;

		// Check if table already exists before running dbDelta.
		// This prevents unnecessary DESCRIBE and SHOW INDEX queries.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		if ( $table_exists === $table_name ) {
			return; // Table already exists, skip creation.
		}

		$wpdb->hide_errors();

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "
        CREATE TABLE $table_name (
        id bigint(20) NOT NULL auto_increment,
        user_id bigint(20) NOT NULL,
        post_id bigint(20) NOT NULL,
        bookmark_note longtext NULL,
        date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (id)
        ) $collate;
        ";
		dbDelta( $sql );
	}

	/**
	 * Updating Database Version if something changes to update the Tables
	 *
	 * @since   0.9.0
	 * @version 1.0.8
	 */
	public function update_db_version() {
		update_option( self::DB_OPTION_NAME, self::DB_VERSION );
	}
}
