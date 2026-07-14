<?php

namespace Cariera_Core\Extensions\Captcha\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hcaptcha implements \Cariera_Core\Extensions\Captcha\Providers {

	/**
	 * Render the hCaptcha field HTML.
	 *
	 * @since 2.0.0
	 *
	 * @param string $form_context Form context identifier (e.g. 'login-form', 'register-form', 'forgot-pass-form').
	 */
	public static function render_field( string $form_context ): void {
		if ( ! self::is_configured() ) {
			return;
		}

		wp_enqueue_script( 'hcaptcha' );

		$site_key = get_option( 'cariera_hcaptcha_sitekey' );
		?>
		<div class="form-group">
			<div id="hcaptcha-<?php echo esc_attr( $form_context ); ?>" class="h-captcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
		</div>
		<?php
	}

	/**
	 * Validate the hCaptcha response on form submission.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $result Current validation result.
	 * @return mixed|\WP_Error
	 */
	public static function validate_fields( $result ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- CAPTCHA validation does not rely on nonce checks.
		$token = isset( $_POST['h-captcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['h-captcha-response'] ) ) : '';

		if ( ! self::is_response_valid( $token ) ) {
			return new \WP_Error( 'validation-error', esc_html__( 'hCaptcha verification failed. Please try again.', 'cariera-core' ) );
		}

		return $result;
	}

	/**
	 * Verify an hCaptcha token against hCaptcha's siteverify API.
	 *
	 * @since 2.0.0
	 *
	 * @param string $token The h-captcha-response token submitted with the form.
	 * @return bool True if the token is valid, false otherwise.
	 */
	public static function is_response_valid( string $token ): bool {
		if ( empty( $token ) ) {
			return false;
		}

		$response = wp_remote_post(
			'https://hcaptcha.com/siteverify',
			[
				'body' => [
					'secret'   => get_option( 'cariera_hcaptcha_secretkey' ),
					'response' => $token,
				],
			]
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
		return ! empty( get_option( 'cariera_hcaptcha_sitekey' ) ) && ! empty( get_option( 'cariera_hcaptcha_secretkey' ) );
	}
}