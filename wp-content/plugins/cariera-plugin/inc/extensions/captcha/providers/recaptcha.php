<?php

namespace Cariera_Core\Extensions\Captcha\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Recaptcha implements \Cariera_Core\Extensions\Captcha\Providers {

	/**
	 * Render the reCAPTCHA v2 field HTML.
	 *
	 * @since 2.0.0
	 *
	 * @param string $form_context Form context identifier (e.g. 'login-form', 'register-form', 'forgot-pass-form').
	 */
	public static function render_field( string $form_context ): void {
		if ( ! self::is_configured() ) {
			return;
		}

		wp_enqueue_script( 'recaptcha' );

		$site_key = get_option( 'cariera_recaptcha_sitekey' );
		?>
		<div class="form-group">
			<div id="recaptcha-<?php echo esc_attr( $form_context ); ?>" class="g-recaptcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
		</div>
		<?php
	}

	/**
	 * Validate the reCAPTCHA response on form submission.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $result Current validation result.
	 * @return mixed|\WP_Error
	 */
	public static function validate_fields( $result ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- CAPTCHA validation does not rely on nonce checks.
		$token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

		if ( ! self::is_response_valid( $token ) ) {
			return new \WP_Error( 'validation-error', esc_html__( 'reCAPTCHA is a required field.', 'cariera-core' ) );
		}

		return $result;
	}

	/**
	 * Verify a reCAPTCHA token against Google's siteverify API.
	 *
	 * @since 2.0.0
	 *
	 * @param string $token The g-recaptcha-response token submitted with the form.
	 * @return bool True if the token is valid, false otherwise.
	 */
	public static function is_response_valid( string $token ): bool {
		if ( empty( $token ) ) {
			return false;
		}

		$response = wp_remote_get(
			add_query_arg(
				[
					'secret'   => get_option( 'cariera_recaptcha_secretkey' ),
					'response' => $token,
				],
				'https://www.google.com/recaptcha/api/siteverify'
			)
		);

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			return false;
		}

		$json = json_decode( $response['body'] );

		return $json && true === $json->success;
	}

	/**
	 * Whether this provider has both API keys configured.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if both the site key and secret key are set, false otherwise.
	 */
	public static function is_configured(): bool {
		return ! empty( get_option( 'cariera_recaptcha_sitekey' ) ) && ! empty( get_option( 'cariera_recaptcha_secretkey' ) );
	}
}
