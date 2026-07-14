<?php

namespace Cariera_Core\Extensions\Captcha;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface that all CAPTCHA providers must implement.
 *
 * @since 2.0.0
 */
interface Providers {

	/**
	 * Render the CAPTCHA field HTML.
	 *
	 * @since 2.0.0
	 *
	 * @param string $form_context Form context identifier (e.g. 'login-form', 'register-form', etc).
	 */
	public static function render_field( string $form_context ): void;

	/**
	 * Validate the CAPTCHA field on form submission.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $result Current validation result.
	 */
	public static function validate_fields( $result );
}
