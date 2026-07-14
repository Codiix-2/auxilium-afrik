<?php

namespace Cariera_Core\Core\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Forget_Password {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Shortcodes.
		add_shortcode( 'cariera_forgetpass_form', [ $this, 'forgetpass_form' ] );

		// Forgot Password AJAX Functions.
		add_action( 'wp_ajax_nopriv_cariera_ajax_forgotpass', [ $this, 'forgot_pass_process' ] );
	}

	/**
	 * Forget Password Form Shortcode
	 *
	 * @since   1.4.8
	 * @version 1.7.2
	 */
	public function forgetpass_form() {
		if ( is_user_logged_in() ) {
			return;
		}

		cariera_get_template_part( 'account/forgot-password-form' );
	}

	/**
	 * AJAX Forgot Password function
	 *
	 * @since   1.4.8
	 * @version 2.0.0
	 */
	public function forgot_pass_process() {
		$nonce = isset( $_POST['forgetpass_security'] ) ? wp_unslash( $_POST['forgetpass_security'] ) : ''; // phpcs:ignore
		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'cariera-ajax-forgetpass-nonce' ) ) {
			wp_send_json(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Your session has expired. Please reload the page and try again.', 'cariera-core' ) . '</span>',
				]
			);
		}

		// Check Recaptcha if enabled.
		$forgotpass_captcha = get_option( 'cariera_captcha_forgotpass' );
		if ( \Cariera_Core\Extensions\Captcha\Captcha::is_enabled() && $forgotpass_captcha ) {
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

		// Get account input.
		$account = isset( $_POST['forgot_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['forgot_pass'] ) ) : '';
		$error   = '';
		$success = '';

		// Validate account.
		if ( empty( $account ) ) {
			$error = esc_html__( 'Enter a Username or Email address.', 'cariera-core' );
		} elseif ( is_email( $account ) ) {
			if ( ! email_exists( $account ) ) {
				$error = esc_html__( 'There is no user registered with that Email address.', 'cariera-core' );
			} else {
				$get_by = 'email';
			}
		} elseif ( validate_username( $account ) ) {
			if ( ! username_exists( $account ) ) {
				$error = esc_html__( 'There is no user registered with that Username.', 'cariera-core' );
			} else {
				$get_by = 'login';
			}
		} else {
			$error = esc_html__( 'Invalid username or e-mail address.', 'cariera-core' );
		}

		if ( ! empty( $error ) ) {
			wp_send_json(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . $error . '</span>',
				]
			);
		}

		// Get user object.
		$user_obj = filter_var( $account, FILTER_VALIDATE_EMAIL ) ? get_user_by( 'email', $account ) : get_user_by( 'login', $account );
		$user_id  = $user_obj ? $user_obj->ID : 0;

		// Check account approval status.
		$user_status = Approval::get_user_status( $user_id );
		if ( 'pending' === $user_status ) {
			wp_send_json(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . $this->login_message_for_pending( $user_obj ) . '</span>',
				]
			);
		}

		if ( 'denied' === $user_status ) {
			wp_send_json(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Your account has been denied.', 'cariera-core' ) . '</span>',
				]
			);
		}

		// Generate new password.
		$random_password = wp_generate_password();
		$update_user     = wp_update_user(
			[
				'ID'        => $user_id,
				'user_pass' => $random_password,
			]
		);

		if ( is_wp_error( $update_user ) ) {
			wp_send_json(
				[
					'loggedin' => false,
					'message'  => '<span class="job-manager-message error">' . esc_html__( 'Something went wrong while updating your account.', 'cariera-core' ) . '</span>',
				]
			);
		}

		// Prepare email.
		$from_name  = get_option( 'cariera_emails_name', get_bloginfo( 'name' ) );
		$from_email = get_option( 'cariera_emails_from_email', get_bloginfo( 'admin_email' ) );
		$headers    = sprintf( "From: %s <%s>\r\nContent-Type: text/html", $from_name, $from_email );
		$subject    = esc_html__( 'Password Reset', 'cariera-core' );

		ob_start();
		get_template_part( '/templates/emails/header' );
		?>
		<tr><td class="h2"><?php printf( esc_html__( 'Hello %s,', 'cariera-core' ), esc_html( $user_obj->user_login ) ); ?></td></tr>
		<tr><td><?php esc_html_e( 'Your password has been reset successfully. You can log in with the new password below.', 'cariera-core' ); ?></td></tr>
		<tr><td style="padding-top: 15px;"><?php printf( esc_html__( 'Your new password is: %s', 'cariera-core' ), esc_html( $random_password ) ); ?></td></tr>
		<?php
		get_template_part( '/templates/emails/footer' );
		$content = ob_get_clean();

		wp_mail( $user_obj->user_email, $subject, $content, $headers );

		$success = esc_html__( 'Your password has been reset. Please check your inbox or spam/junk folder for the new password.', 'cariera-core' );

		wp_send_json(
			[
				'loggedin' => true,
				'message'  => '<span class="job-manager-message success">' . $success . '</span>',
			]
		);
	}

	/**
	 * Login message for pending users (helper method)
	 *
	 * @since 1.9.7
	 *
	 * @param mixed $user
	 */
	private function login_message_for_pending( $user ) {
		$approval = get_option( 'cariera_moderate_new_user' );

		if ( 'email' === $approval ) {
			/* translators: Message shown to a user who must verify their account via email */
			return wp_kses_post(
				sprintf(
					__( 'Your account has not been verified yet. Please activate your account using the link sent to your email address. If you did not receive the email, check your spam/junk folder.', 'cariera-core' ),
					esc_html( $user->user_login )
				)
			);
		}

		if ( 'admin' === $approval ) {
			return esc_html__( 'Your account has not been approved yet. Please be patient while an administrator approves your account.', 'cariera-core' );
		}

		return esc_html__( 'Your account must be approved before you can log in.', 'cariera-core' );
	}
}
