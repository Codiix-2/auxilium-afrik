<?php

namespace Cariera_Core\Core\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Registration {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Shortcodes.
		add_shortcode( 'cariera_registration_form', [ $this, 'registration_form' ] );

		// Register AJAX Functions.
		add_action( 'wp_ajax_nopriv_cariera_ajax_register', [ $this, 'register_process' ] );
	}

	/**
	 * Registration Form Shortcode
	 *
	 * @since   1.4.8
	 * @version 1.7.2
	 */
	public function registration_form() {
		if ( is_user_logged_in() ) {
			return;
		}

		$registration = get_option( 'cariera_registration' );

		if ( 1 === intval( $registration ) ) {
			cariera_get_template_part( 'account/register-form' );
		} else {
			cariera_get_template_part( 'account/register-form-disabled' );
		}
	}

	/**
	 * Registration validation
	 *
	 * @since   1.4.8
	 * @version 2.0.0
	 *
	 * @param string $username
	 * @param string $email
	 * @param string $password
	 * @param bool   $privacy_policy
	 * @param string $user_role
	 */
	private function registration_validation( $username, $email, $password, $privacy_policy, $user_role ) {
		global $reg_errors;

		$reg_errors      = new \WP_Error();
		$password_length = get_option( 'cariera_register_password_length' );

		$registration_captcha = get_option( 'cariera_captcha_register' );
		if ( \Cariera_Core\Extensions\Captcha\Captcha::is_enabled() && $registration_captcha ) {
			$result = \Cariera_Core\Extensions\Captcha\Captcha::validate_fields( true );
			if ( is_wp_error( $result ) ) {
				$reg_errors->add( 'field', esc_html__( 'CAPTCHA is a required field.', 'cariera-core' ) );
			}
		}

		if ( empty( $username ) || empty( $password ) || empty( $email ) || empty( $privacy_policy ) ) {
			$reg_errors->add( 'field', esc_html__( 'Required form field is missing', 'cariera-core' ) );
		}

		if ( 4 > strlen( $username ) ) {
			$reg_errors->add( 'username_length', esc_html__( 'Username too short, it should be at least 4 characters.', 'cariera-core' ) );
		}

		if ( username_exists( $username ) ) {
			$reg_errors->add( 'user_name', esc_html__( 'This Username already exists', 'cariera-core' ) );
		}

		if ( ! validate_username( $username ) ) {
			$reg_errors->add( 'username_invalid', esc_html__( 'The Username you entered is not valid', 'cariera-core' ) );
		}

		if ( $password_length > strlen( $password ) ) {
			/* translators: %s is the minimum password length required */
			$reg_errors->add( 'password', sprintf( esc_html__( 'Password length must be greater than %s', 'cariera-core' ), $password_length ) );
		}

		if ( ! is_email( $email ) ) {
			$reg_errors->add( 'email_invalid', esc_html__( 'Email is not valid, please provide a correct email address.', 'cariera-core' ) );
		}

		if ( email_exists( $email ) ) {
			$reg_errors->add( 'email', esc_html__( 'This Email already exists.', 'cariera-core' ) );
		}

		if ( empty( $privacy_policy ) ) {
			$reg_errors->add( 'privacy_policy', esc_html__( 'Please accept our Privacy Policy.', 'cariera-core' ) );
		}

		if ( 'administrator' === $user_role ) {
			$reg_errors->add( 'user_role_security', esc_html__( 'Nice try!', 'cariera-core' ) );
		}

		if ( ( get_option( 'cariera_user_role_candidate' ) || get_option( 'cariera_user_role_employer' ) ) && empty( $user_role ) ) {
			$reg_errors->add( 'user_role_check', esc_html__( 'Please select a user role!', 'cariera-core' ) );
		}
	}

	/**
	 * Complete the registration and add the user in the DB
	 *
	 * @since 1.4.8
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @param string $user_role
	 */
	private function registration_complete( $username, $password, $email, $user_role ) {
		$userdata = [
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => $password,
			'role'       => $user_role,
		];

		return wp_insert_user( $userdata );
	}

	/**
	 * AJAX Register function
	 *
	 * @since   1.4.8
	 * @version 1.9.7
	 */
	public function register_process() {
		global $reg_errors;

		// First check the nonce, if it fails the function will break.
		if ( ! isset( $_POST['register_security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['register_security'] ) ), 'cariera-ajax-register-nonce' ) ) {
			wp_send_json(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Your session has expired. Please reload the page and try again.', 'cariera-core' ) . '</span>',
				]
			);
		}

		// Determine username.
		if ( get_option( 'cariera_register_hide_username' ) ) {
			$register_email = isset( $_POST['register_email'] ) ? wp_unslash( $_POST['register_email'] ) : ''; // phpcs:ignore
			$reg_username   = '';

			if ( ! empty( $register_email ) ) {
				$reg_email_parts = explode( '@', $register_email );
				$reg_username    = sanitize_user( trim( $reg_email_parts[0] ), true );

				// Ensure unique and minimum length.
				if ( username_exists( $reg_username ) || strlen( $reg_username ) < 4 ) {
					$reg_username .= '_' . wp_rand( 1000, 99999 );

					if ( username_exists( $reg_username ) ) {
						$reg_username .= '_' . wp_rand( 10000, 99999 );
					}
				}
			}

			// Fallback if still empty.
			if ( empty( $reg_username ) ) {
				$reg_username = 'user_' . wp_rand( 1000, 99999 );
			}
		} else {
			$register_username = isset( $_POST['register_username'] ) ? wp_unslash( $_POST['register_username'] ) : ''; // phpcs:ignore
			$reg_username      = sanitize_user( $register_username, true );
		}

		// Email and password.
		$register_email    = isset( $_POST['register_email'] ) ? sanitize_email( wp_unslash( $_POST['register_email'] ) ) : '';
		$register_password = isset( $_POST['register_password'] ) ? sanitize_text_field( wp_unslash( $_POST['register_password'] ) ) : '';

		// Check Privacy Policy if enabled.
		$privacy_policy = 1;
		if ( 1 === intval( get_option( 'cariera_register_privacy_policy' ) ) ) {
			$privacy_policy = isset( $_POST['privacy_policy'] ) ? sanitize_text_field( wp_unslash( $_POST['privacy_policy'] ) ) : '';
		}

		if ( ! get_option( 'cariera_user_role_candidate' ) && ! get_option( 'cariera_user_role_employer' ) ) {
			$user_role = get_option( 'default_role' );
		} else {
			$user_role = isset( $_POST['cariera_user_role'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_user_role'] ) ) : '';
		}

		// Validate Registration fields.
		$this->registration_validation( $reg_username, $register_email, $register_password, $privacy_policy, $user_role );

		// Check for errors.
		if ( count( $reg_errors->get_error_messages() ) > 0 ) {
			wp_send_json(
				[
					'register' => false,
					'message'  => '<span class="job-manager-message error"><ul><li>' . implode( '</li><li>', $reg_errors->get_error_messages() ) . '</li></ul></span>',
				]
			);
		}

		// Complete registration.
		$user_id = $this->registration_complete( $reg_username, $register_password, $register_email, $user_role );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json(
				[
					'register' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Registration Error!', 'cariera-core' ) . '</span>',
				]
			);
		}

		$user_obj = get_userdata( $user_id );

		// Handle account approval.
		if ( get_option( 'cariera_moderate_new_user' ) !== 'auto' ) {
			$code = cariera_random_key();
			update_user_meta( $user_id, 'account_approve_key', $code );
			update_user_meta( $user_id, 'user_account_status', 'pending' );

			$approval_url = get_permalink( get_option( 'cariera_moderate_new_user_page' ) );
			$approval_url = add_query_arg(
				[
					'user_id'     => $user_id,
					'approve-key' => $code,
				],
				$approval_url
			);

			$recipient_email = ( 'email' === get_option( 'cariera_moderate_new_user' ) ) ? $user_obj->user_email : get_option( 'admin_email' );

			$mail_args = [
				'send_to'      => $recipient_email,
				'email'        => $user_obj->user_email,
				'display_name' => $user_obj->user_login,
				'password'     => $register_password,
				'approval_url' => $approval_url,
			];

			do_action( 'cariera_new_user_approval_notification', $mail_args );

			$final = [
				'register' => true,
				'status'   => true,
				'message'  => '<span class="job-manager-message success">' . $this->register_message( $user_obj ) . '</span>',
			];
		} else {
			// Auto-login if enabled.
			if ( get_option( 'cariera_auto_login' ) ) {
				wp_signon(
					[
						'user_login'    => $reg_username,
						'user_password' => $register_password,
						'remember'      => true,
					],
					is_ssl()
				);
				$note = esc_html__( 'You have been successfully registered, you will be logged in shortly.', 'cariera-core' );
			} else {
				$note = esc_html__( 'You have been successfully registered, you can login now.', 'cariera-core' );
			}

			$mail_args = [
				'email'        => $user_obj->user_email,
				'display_name' => $user_obj->user_login,
				'password'     => $register_password,
			];

			do_action( 'cariera_new_user_notification', $mail_args );

			$final = [
				'register' => true,
				'message'  => '<span class="job-manager-message success">' . $note . '</span>',
				'role'     => $user_obj->roles[0] ?? '',
			];
		}

		do_action( 'cariera_core_register_process_completed', $final, $user_id );

		wp_send_json( $final );
	}

	/**
	 * Message regarding the user status after registration
	 *
	 * @since 1.4.8
	 *
	 * @param mixed $user
	 */
	private function register_message( $user ) {
		$approval = get_option( 'cariera_moderate_new_user' );

		if ( 'email' === $approval ) {
			return esc_html__( 'Registration complete! Before you can login you must activate your account via the email sent to you.', 'cariera-core' );
		} elseif ( 'admin' === $approval ) {
			return esc_html__( 'Registration complete! Your account has to be activated by an admin before you can login.', 'cariera-core' );
		} else {
			return esc_html__( 'Your account has to be activated.', 'cariera-core' );
		}
	}
}
