<?php

namespace Cariera_Core\Core\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Login {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Shortcodes.
		add_shortcode( 'cariera_login_form', [ $this, 'login_form' ] );

		// Login AJAX Functions.
		add_action( 'wp_ajax_nopriv_cariera_ajax_login', [ $this, 'login_process' ] );
	}

	/**
	 * Login Form Shortcode
	 *
	 * @since   1.0.0
	 * @version 1.7.2
	 */
	public function login_form() {
		if ( is_user_logged_in() ) {
			return;
		}

		cariera_get_template_part( 'account/login-form' );
	}

	/**
	 * AJAX Login function
	 *
	 * @since   1.4.8
	 * @version 2.0.0
	 */
	public function login_process() {
		$login_captcha = get_option( 'cariera_captcha_login' );
		if ( \Cariera_Core\Extensions\Captcha\Captcha::is_enabled() && $login_captcha ) {
			$result = \Cariera_Core\Extensions\Captcha\Captcha::validate_fields( true );
			if ( is_wp_error( $result ) ) {
				wp_send_json(
					[
						'loggedin' => false,
						'message'  => '<span class="job-manager-message error">' . esc_html__( 'Captcha is not valid.', 'cariera-core' ) . '</span>',
					]
				);
			}
		}

		// First check the nonce, if it fails the function will break.
		if ( ! isset( $_POST['login_security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['login_security'] ) ), 'cariera-ajax-login-nonce' ) ) {
			wp_send_json(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Your session has expired. Please reload the page and try again.', 'cariera-core' ) . '</span>',
				]
			);
		}

		// Collect credentials.
		$creds = [
			'user_login'    => isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '',
			'user_password' => isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '',
			'remember'      => isset( $_POST['remember'] ) ? true : false,
		];

		// Get user object.
		$user_obj = filter_var( $creds['user_login'], FILTER_VALIDATE_EMAIL ) ? get_user_by( 'email', $creds['user_login'] ) : get_user_by( 'login', $creds['user_login'] );
		$user_id  = isset( $user_obj->ID ) ? $user_obj->ID : '0';

		// Check user approval status.
		if ( isset( $user_obj->ID ) ) {
			$user_status = Approval::get_user_status( $user_id );

			if ( 'pending' === $user_status ) {
				wp_send_json(
					[
						'loggedin' => false,
						'message'  => '<span class="job-manager-message error">' . $this->login_message( $user_obj ) . '</span>',
					]
				);
			}

			if ( 'denied' === $user_status ) {
				wp_send_json(
					[
						'loggedin' => false,
						'message'  => '<span class="job-manager-message error">' . esc_html__( 'Your account has been denied and you can not login.', 'cariera-core' ) . '</span>',
					]
				);
			}
		}

		// Sign user in with the given credentials.
		$user_signon = wp_signon( $creds, is_ssl() );

		if ( is_wp_error( $user_signon ) ) {
			wp_send_json(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Wrong username or password.', 'cariera-core' ) . '</span>',
				]
			);
		}

		// Successful login.
		wp_set_current_user( $user_signon->ID );
		$user_meta = get_userdata( $user_id );
		$role      = $user_meta->roles[0] ?? '';

		wp_send_json(
			[
				'loggedin' => true,
				'message'  => '<span class="job-manager-message success">' . esc_html__( 'Login successful, redirecting...', 'cariera-core' ) . '</span>',
				'role'     => $role,
			]
		);
	}

	/**
	 * Login Message regarding the user's status
	 *
	 * @since   1.4.8
	 * @version 1.9.7
	 *
	 * @param mixed $user
	 */
	private function login_message( $user ) {
		$approval = get_option( 'cariera_moderate_new_user' );

		if ( 'email' === $approval ) {
			/* translators: %s is the user's login/email */
			$message = sprintf(
				/* translators: Link included to resend activation email */
				__( 'Your account has not been verified yet. Please activate your account using the link sent to your email address. If you did not receive the email, check your spam/junk folder or <a href="javascript:void(0);" class="cariera-resend-approval-mail" data-login="%s">click here</a> to resend the activation email.', 'cariera-core' ),
				esc_attr( $user->user_login )
			);
			return wp_kses_post( $message );
		}

		if ( 'admin' === $approval ) {
			return esc_html__( 'Your account has not been activated yet. Please wait until an admin activates your account.', 'cariera-core' );
		}

		return esc_html__( 'Your account needs to be activated before you can log in.', 'cariera-core' );
	}
}
