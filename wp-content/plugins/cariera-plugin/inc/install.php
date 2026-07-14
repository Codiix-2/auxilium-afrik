<?php

namespace Cariera_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Install {

	const DB_VERSION            = '1.3.0';
	const DB_OPTION_NAME        = 'cariera_db_version';
	const INSTALLED_OPTION_NAME = 'cariera_core_installed';
	const VERSION_OPTION_NAME   = 'cariera_core_version';

	/**
	 * Constructor
	 */
	public function __construct() {
		register_activation_hook( CARIERA_CORE, [ $this, 'activate' ] );
		register_deactivation_hook( CARIERA_CORE, [ $this, 'deactivate' ] );

		// Check and update database on plugin load or update.
		add_action( 'plugins_loaded', [ $this, 'check_db_version_and_update' ] );
	}

	/**
	 * Check database version and update if necessary
	 *
	 * @since   1.9.0
	 * @version 1.9.9
	 */
	public function check_db_version_and_update() {
		$current_version = get_option( self::DB_OPTION_NAME );
		if ( version_compare( $current_version, self::DB_VERSION, '<' ) ) {
			$this->install();
		}
	}

	/**
	 * Plugin activation init
	 *
	 * @since  1.5.3
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
				restore_current_blog();
			}
		} else {
			$this->install();
		}
	}

	/**
	 * Plugin deactivation init
	 *
	 * @since  1.5.3
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
	 * @since   1.4.8
	 * @version 1.7.2
	 */
	public function install() {
		$this->activate_options();
		$this->create_table_views();
		$this->create_table_notifications();
		$this->create_table_conversations();
		$this->create_table_messages();
		$this->update_db_version();
		$this->schedule_cron_jobs();
	}

	/**
	 * Run when plugin gets deactivated
	 *
	 * @since   1.5.0
	 * @version 1.7.2
	 */
	public function uninstall() {
		$this->deactivate_options();
		$this->unschedule_events();
	}

	/**
	 * Run function when plugin get's activated
	 *
	 * @since 1.7.2
	 * @version 1.9.9
	 */
	private function activate_options() {
		$installed = get_option( self::INSTALLED_OPTION_NAME );

		if ( ! $installed ) {
			update_option( self::INSTALLED_OPTION_NAME, time() );
		}

		update_option( self::VERSION_OPTION_NAME, CARIERA_CORE_VERSION );
	}

	/**
	 * Run function when plugin get's deactivated
	 *
	 * @since 1.7.2
	 * @version 1.9.9
	 */
	private function deactivate_options() {
		delete_option( self::INSTALLED_OPTION_NAME );
		delete_option( self::VERSION_OPTION_NAME );
	}

	/**
	 * Scheduled events to clear the db tables
	 *
	 * @since   1.5.0
	 * @version 2.0.0
	 */
	public function schedule_cron_jobs() {
		// Check for expired companies.
		if ( ! wp_next_scheduled( 'cariera_company_check_for_expired_companies' ) ) {
			wp_schedule_event( time(), 'hourly', 'cariera_company_check_for_expired_companies' );
		}

		// Check for expired promotions.
		if ( ! wp_next_scheduled( 'cariera_check_expired_promotions' ) ) {
			wp_schedule_event( time(), 'hourly', 'cariera_check_expired_promotions' );
		}

		// Check for theme updates.
		if ( ! wp_next_scheduled( 'cariera_check_theme_update_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'cariera_check_theme_update_cron' );
		}

		// Delete Notifications.
		if ( ! wp_next_scheduled( 'cariera_delete_notifications' ) ) {
			wp_schedule_event( time(), 'monthly', 'cariera_delete_notifications' );
		}

		// Check if theme license is activated.
		if ( ! wp_next_scheduled( 'cariera_check_license_activated' ) ) {
			wp_schedule_event( time(), 'daily', 'cariera_check_license_activated' );
		}

		// Check if theme license is activated.
		if ( ! wp_next_scheduled( 'cariera_check_core_plugin' ) ) {
			wp_schedule_event( time(), 'daily', 'cariera_check_core_plugin' );
		}
	}

	/**
	 * Unscheduled events to avoid issues after plugin deactivation
	 *
	 * @since   1.5.0
	 * @version 2.0.0
	 */
	public function unschedule_events() {
		wp_clear_scheduled_hook( 'cariera_company_check_for_expired_companies' );
		wp_clear_scheduled_hook( 'cariera_check_expired_promotions' );
		wp_clear_scheduled_hook( 'cariera_check_theme_update_cron' );
		wp_clear_scheduled_hook( 'cariera_delete_notifications' );
		wp_clear_scheduled_hook( 'cariera_check_license_activated' );
		wp_clear_scheduled_hook( 'cariera_check_core_plugin' );
	}

	/**
	 * Creating the Database table for the views
	 *
	 * @since   1.3.4
	 * @version 1.4.8
	 */
	public function create_table_views() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'cariera_listing_stats_views';
		$collate    = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		$sql = "
            CREATE TABLE $table_name (
                main_id bigint(20) NOT NULL auto_increment,
                user_id varchar(255) default NULL,
                listing_id varchar(255) default NULL,
                listing_title varchar(255) default NULL,
                post_type varchar(255) default NULL,
                action_type varchar(255) default NULL,
                month LONGTEXT default NULL,
                count varchar(255) default NULL,
                PRIMARY KEY  (`main_id`)
            ) $collate;
        ";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Create Notifications Database Table
	 *
	 * @since  1.5.0
	 */
	public function create_table_notifications() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'cariera_notifications';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
            CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                owner_id bigint(20) NOT NULL,
                user_id bigint(20) NOT NULL,
                post_id bigint(20) NOT NULL,
                action varchar(255) NOT NULL,
                meta longtext NULL,
                active boolean DEFAULT 1 NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;
        ";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Create Conversations Database Table
	 *
	 * @since   1.6.0
	 * @version 1.9.0
	 */
	public function create_table_conversations() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'cariera_conversations';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE IF NOT EXISTS $table_name (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				user_id VARCHAR(255) NOT NULL,
				friend_id VARCHAR(255) NULL DEFAULT NULL,
				status INT(11) NULL DEFAULT '0',
				listing_id BIGINT(20) NULL DEFAULT '0',
				created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY ( id ),
                INDEX idx_users_listing (user_id, friend_id, listing_id),
                INDEX idx_created_at (created_at)
			) $charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Create Messages Database Table
	 *
	 * @since   1.6.0
	 * @version 1.9.0
	 */
	public function create_table_messages() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'cariera_messages';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE IF NOT EXISTS $table_name (
				id BIGINT(20) NOT NULL AUTO_INCREMENT,
				message text NOT NULL,
				from_id VARCHAR(255) NULL DEFAULT NULL,
				to_id VARCHAR(225) NULL DEFAULT NULL,
				listing_id BIGINT(20) NULL DEFAULT '0',
				media_id BIGINT(20) NULL DEFAULT '0',
				created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				seen TINYINT(11) NOT NULL DEFAULT '0',
				PRIMARY KEY ( id ),
                INDEX idx_from_to_listing (from_id, to_id, listing_id),
                INDEX idx_created_at (created_at)
			) $charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Updating Database Version if something changes to update the Tables
	 *
	 * @since   1.4.8
	 * @version 1.9.9
	 */
	public function update_db_version() {
		update_option( self::DB_OPTION_NAME, self::DB_VERSION );
	}
}
