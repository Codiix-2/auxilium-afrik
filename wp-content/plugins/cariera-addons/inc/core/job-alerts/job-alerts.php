<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_Alerts {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Post types class.
	 *
	 * @var \Cariera_Addons\Core\Job_Alerts\Post_Types
	 */
	public $post_types;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		if ( class_exists( 'WP_Job_Manager_Alerts' ) ) {
			return;
		}

		// Init main functions when plugin loads.
		$this->init_plugin();
	}

	/**
	 * Required notices when WPJM Alerts is enabled.
	 *
	 * @since 0.9.4
	 */
	public function required_notices() {
		// If WP Job Manager Alerts is installed and activated.
		if ( class_exists( 'WP_Job_Manager_Alerts' ) ) {
			echo '<div class="error">';
			echo '<p>' . wp_kses_post( __( 'Please deactivate <strong>WP Job Manager Alerts</strong> to enable the <strong>Cariera Addons Alerts</strong> feature.', 'cariera-addons' ) ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Init plugin
	 *
	 * @since 0.9.2
	 */
	public function init_plugin() {
		$this->post_types = Post_Types::instance();

		Admin::instance();
		Notifier::instance();
		Shortcodes::instance();
		Add_Alert::instance();
		Alert_Stats::instance();
	}

	/**
	 * Check if there is a user logged in or if account creation is not required.
	 *
	 * @since   0.9.2
	 * @version 0.9.11
	 */
	public function can_user_add_alert(): bool {
		// Logged-in users are always allowed to add alerts.
		if ( is_user_logged_in() ) {
			return true;
		}

		// If an account is required, guest users cannot add an alert.
		if ( Settings::is_account_required() ) {
			return false;
		}

		// Do not allow users that have an account to add alert as guests.
		return ! \WP_Job_Manager\Guest_Session::current_guest_has_account();
	}

	/**
	 * Verify alert token.
	 *
	 * @since 0.9.2
	 *
	 * @param string $token    Token to verify.
	 * @param int    $alert_id Alert ID.
	 * @param int    $user_id  User ID.
	 */
	public function verify_alert_token( $token, $alert_id, $user_id ) {
		if ( ( new \WP_Job_Manager\Access_Token( [ $alert_id, $user_id ] ) )->verify( $token ) ) {
			return true;
		}

		// Check if the token was created with get_alert_token.
		$correct_token = wp_json_encode( [ $user_id, $alert_id ] );
		$correct_token = crypt( $correct_token, $this->get_user_secret_key( $user_id ) );

		return hash_equals(
			$correct_token,
			$token
		);
	}
}
