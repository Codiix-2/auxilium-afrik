<?php

namespace Cariera_Core\Extensions\Captcha;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Captcha {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Registered CAPTCHA providers.
	 *
	 * @var array
	 */
	protected static array $providers = [
		'recaptcha' => \Cariera_Core\Extensions\Captcha\Providers\Recaptcha::class,
		'hcaptcha'  => \Cariera_Core\Extensions\Captcha\Providers\Hcaptcha::class,
		'turnstile' => \Cariera_Core\Extensions\Captcha\Providers\Turnstile::class,
	];

	/**
	 * Active provider class name.
	 *
	 * @var class-string<\Cariera_Core\Extensions\Captcha\Providers>|null
	 */
	protected static ?string $active_provider = null;

	/**
	 * Initialize the CAPTCHA system.
	 *
	 * Sets the active provider based on plugin settings.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public static function init(): void {
		$method = get_option( 'cariera_captcha_provider', '' );

		if ( empty( $method ) || ! isset( static::$providers[ $method ] ) ) {
			return;
		}

		static::$active_provider = static::$providers[ $method ];
	}

	/**
	 * Get the active provider class.
	 *
	 * @since 2.0.0
	 */
	public static function get_provider(): ?string {
		return static::$active_provider;
	}

	/**
	 * Check whether CAPTCHA is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @return bool True if a provider is active, false otherwise.
	 */
	public static function is_enabled(): bool {
		return null !== static::$active_provider;
	}

	/**
	 * Render the CAPTCHA field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $form_context Form context identifier (e.g. 'login-form', 'register-form', 'forgot-pass-form').
	 */
	public static function render_field( string $form_context ): void {
		if ( empty( static::$active_provider ) || ! is_callable( [ static::$active_provider, 'render_field' ] ) ) {
			return;
		}

		$provider = static::$active_provider;
		$provider::render_field( $form_context );
	}

	/**
	 * Validate CAPTCHA input.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $result Current validation result.
	 * @return mixed|\WP_Error Validation result or WP_Error on failure.
	 */
	public static function validate_fields( $result ) {
		if ( ! static::is_enabled() ) {
			return $result;
		}

		$provider = static::$active_provider;

		if ( ! is_callable( [ $provider, 'validate_fields' ] ) ) {
			return $result;
		}

		return $provider::validate_fields( $result );
	}

	/**
	 * Register a custom CAPTCHA provider.
	 *
	 * @since 2.0.0
	 *
	 * @param string                                                   $slug Provider identifier.
	 * @param class-string<\Cariera_Core\Extensions\Captcha\Providers> $provider_class Provider class name.
	 */
	public static function register_provider( string $slug, string $provider_class ): void {
		static::$providers[ $slug ] = $provider_class;
	}
}
