<?php

namespace Cariera_Addons\Core\Job_Alerts\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Confirmation_Email extends Email_Base {

	/**
	 * Identifier for this email.
	 */
	const KEY = 'alert_confirmation';

	/**
	 * Send a confirmation email.
	 *
	 * @since 0.9.2
	 *
	 * @param array $args {
	 *    Arguments used in generation of email.
	 *
	 * @type string     $email Email address to send to.
	 * @type \WP_Post   $alert Alert.
	 * @type Guest_User $guest Guest user.
	 * }
	 */
	public static function send( $args ) {
		do_action( 'job_manager_send_notification', self::get_key(), $args );
	}

	/**
	 * Get the unique email notification key.
	 *
	 * @since 0.9.2
	 */
	public static function get_key() {
		return self::KEY;
	}

	/**
	 * Get the context for where this email notification is used. Used to direct which admin settings to show.
	 *
	 * @since 0.9.2
	 */
	public static function get_context() {
		return self::CONTEXT;
	}

	/**
	 * Get the friendly name for this email notification.
	 *
	 * @since 0.9.2
	 */
	public static function get_name() {
		return esc_html__( 'Alert Confirmation E-mail', 'cariera-addons' );
	}

	/**
	 * Get the description for this email notification.
	 *
	 * @since 0.9.2
	 */
	public static function get_description() {
		return esc_html__( 'Send an e-mail to confirm the e-mail address when not using an account.', 'cariera-addons' );
	}

	/**
	 * Get the email subject.
	 *
	 * @since 0.9.2
	 */
	public function get_subject() {
		return apply_filters( 'job_manager_alert_confirmation_subject', __( 'Confirm your Job Alert', 'cariera-addons' ) );
	}

	/**
	 * Get array or comma-separated list of email addresses to send message.
	 *
	 * @since 0.9.2
	 */
	public function get_to() {
		$args = $this->get_args();

		$email_to = $args['email'] ?? '';

		if ( ! is_email( $email_to ) ) {
			return false;
		}

		return $email_to;
	}

	/**
	 * Checks the arguments and returns whether the email notification is properly set up.
	 *
	 * @since 0.9.2
	 */
	public function is_valid() {
		$args = $this->get_args();

		return isset( $args['email'] ) && ! empty( $args['alert'] ) && ! empty( $args['guest'] ) && ! empty( $args['token'] );
	}

	/**
	 * Force the email notification to be enabled.
	 *
	 * @since 0.9.2
	 */
	public static function get_enabled_force_value() {
		return true;
	}

	/**
	 * Get the rich text version of the email content.
	 *
	 * @since   0.9.2
	 * @version 0.9.4
	 */
	public function get_rich_content() {
		$alert = $this->get_args()['alert'];

		$args = [
			'site_url'          => get_site_url(),
			'site_name'         => get_bloginfo( 'name' ),
			'alert'             => $alert,
			'search_terms'      => \Cariera_Addons\Core\Job_Alerts\Post_Types::get_alert_search_term_names( $alert->ID ),
			'alert_confirm_url' => $this->get_confirm_url(),
		];

		return \Cariera_Addons\Helpers::get_template( 'alerts/emails/email-confirmation.php', $args );
	}

	/**
	 * Get the link to confirm the alert.
	 *
	 * @since 0.9.2
	 */
	private function get_confirm_url() {
		$args = $this->get_args();

		return add_query_arg(
			[
				'action'                                 => 'confirm',
				'alert_id'                               => $args['alert']->ID,
				\WP_Job_Manager\Guest_Session::QUERY_VAR => $args['token'],
			],
			get_permalink( get_option( 'job_manager_alerts_page_id' ) )
		);
	}
}
