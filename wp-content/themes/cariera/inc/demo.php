<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Demo {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		if ( ! \Cariera\is_demo_mode() ) {
			return;
		}

		add_action( 'wp_footer', [ $this, 'theme_demo_btns' ] );
		add_action( 'admin_init', [ $this, 'block_backend_access' ] );

		// Check demo handlings.
		add_action( 'cariera_change_user_details_before', [ $this, 'check_demo_account' ] );
		add_action( 'cariera_change_user_password_before', [ $this, 'check_demo_account' ] );
		add_action( 'cariera_delete_account_before', [ $this, 'check_demo_account' ] );
		add_action( 'cariera_before_message_media_upload', [ $this, 'private_message_media_upload' ] );

		// Demo login credentials.
		add_action( 'cariera_login_form_before', [ $this, 'demo_login_accounts' ] );

		// Demo Settings.
		add_filter( 'cariera_settings', [ $this, 'demo_settings' ] );

		// Delete demo data AJAX function.
		add_action( 'wp_ajax_cariera_delete_demo_data', [ $this, 'delete_demo_data' ] );
	}

	/**
	 * Theme Demo actions (doc, support, purchase )
	 *
	 * @since   1.7.0
	 * @version 1.8.5
	 */
	public function theme_demo_btns() {
		get_template_part( 'templates/demo/theme-demo' );
	}

	/**
	 * Block access to backend if user is not admin.
	 *
	 * @since 1.7.0
	 */
	public function block_backend_access() {
		if ( is_admin() && ! current_user_can( 'manage_options' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Check demo handlings
	 *
	 * @since   1.7.1
	 * @version 1.8.4
	 */
	public function check_demo_account() {
		$user = wp_get_current_user();

		if ( in_array( strtolower( $user->user_login ), [ 'employer', 'candidate' ], true ) ) {
			$args = [
				'status' => false,
				'msg'    => esc_html__( 'You can not take this action on a demo account.', 'cariera' ),
			];

			echo wp_json_encode( $args );
			exit;
		}
	}

	/**
	 * Restrict media upload on demo for private messages
	 *
	 * @since   1.9.0
	 * @version 1.9.2
	 */
	public function private_message_media_upload() {
		wp_send_json_error( esc_html__( 'File uploads are disabled on the demo site.', 'cariera' ) );
		exit;
	}

	/**
	 * Demo account credentials
	 *
	 * @since 1.7.1
	 */
	public function demo_login_accounts() {
		get_template_part( 'templates/demo/login-accounts' );
	}

	/**
	 * Add demo settings to Cariera Settings page
	 *
	 * @since   1.7.1
	 * @version 1.8.1
	 *
	 * @param array $settings
	 */
	public function demo_settings( $settings = [] ) {
		$settings['demo_handling'] = [
			esc_html__( 'Demo Handling', 'cariera' ),
			[
				[
					'id'            => 'cariera_delete_demo_data',
					'label'         => esc_html__( 'Delete All Demo Data', 'cariera' ),
					'description'   => esc_html__( 'This will delete all unneeded demo data. Pending listings, applications, media etc.', 'cariera' ),
					'type'          => 'ajax',
					'ajax_action'   => 'cariera_delete_demo_data',
					'btn_label'     => esc_html__( 'Delete all demo ', 'cariera' ),
					'input_field'   => false,
					'class_wrapper' => 'cariera-demo',
					'attributes'    => [],
				],
			],
		];

		return $settings;
	}

	/**
	 * Delete all demo data via AJAX
	 *
	 * @since   1.7.1
	 * @version 1.7.3
	 */
	public function delete_demo_data() {
		$posts            = [];
		$post_types       = [ 'job_listing', 'resume', 'company' ];
		$other_post_types = [ 'job_application', 'job_alert', 'shop_order' ];

		// Get listings.
		$posts['listings'] = get_posts(
			[
				'post_type'   => $post_types,
				'post_status' => [ 'pending', 'pending_payment', 'preview', 'expired', 'trash', 'draft' ],
				'numberposts' => -1,
				'orderby'     => 'post_date ID',
				'order'       => 'ASC',
			]
		);

		// Get applications, alerts and orders.
		$posts['other'] = get_posts(
			[
				'post_type'   => $other_post_types,
				'post_status' => 'any',
				'numberposts' => -1,
				'orderby'     => 'post_date ID',
				'order'       => 'ASC',
			]
		);

		// Delete all listings.
		foreach ( $posts['listings'] as $post ) {
			\Cariera\write_log( $post->ID );
			wp_delete_post( $post->ID, true );
		}

		// Delete all other data.
		foreach ( $posts['other'] as $post ) {
			\Cariera\write_log( $post->ID );
			wp_delete_post( $post->ID, true );
		}

		// Delete Users.
		$this->delete_users();

		// Delete Messages.
		$this->delete_all_messages();

		// Delete Notifications.
		$this->delete_notifications();

		wp_send_json_success( esc_html__( 'All data except the demo data has been deleted.', 'cariera' ) );
	}

	/**
	 * Delete users that are not part of the demo.
	 *
	 * @since   1.8.1
	 * @version 1.9.6
	 */
	private function delete_users() {
		// Include the user file with the user administration API.
		require_once ABSPATH . 'wp-admin/includes/user.php';

		$users      = get_users();
		$demo_users = [ 'admin', 'gnodesign', 'employer', 'candidate' ];

		foreach ( $users as $user ) {
			if ( in_array( $user->user_login, $demo_users, true ) ) {
				continue;
			}

			wp_delete_user( $user->ID );
		}
	}

	/**
	 * Delete all messages and conversations from the DB Table via AJAX
	 *
	 * @since 1.7.2
	 */
	private function delete_all_messages() {
		global $wpdb;

		$message_table      = $wpdb->prefix . 'cariera_messages';
		$conversation_table = $wpdb->prefix . 'cariera_conversations';

		$wpdb->query( "TRUNCATE TABLE {$message_table}" ); // phpcs:ignore
		$wpdb->query( "TRUNCATE TABLE {$conversation_table}" ); // phpcs:ignore
	}

	/**
	 * Delete old notifications
	 *
	 * @since 1.7.2
	 */
	private function delete_notifications() {
		global $wpdb;

		$notifications_table = $wpdb->prefix . 'cariera_notifications';

		$wpdb->query( "TRUNCATE TABLE {$notifications_table}" ); // phpcs:ignore
	}
}
