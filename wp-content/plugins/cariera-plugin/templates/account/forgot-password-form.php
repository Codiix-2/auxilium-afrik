<?php
/**
 * Cariera Forgot Password Form template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/forgot-password-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'cariera-user-ajax' );
?>

<form id="cariera_forget_pass" method="post">
	<p class="status"></p>

	<div class="form-group">
		<label for="forgot_pass"><?php esc_html_e( 'Username or Email Address *', 'cariera-core' ); ?></label>
		<input id="forgot_pass" type="text" name="forgot_pass" class="form-control" placeholder="<?php esc_html_e( 'Your Username or Email Address', 'cariera-core' ); ?>" />
	</div>

	<?php
	$forgotpass_captcha = get_option( 'cariera_captcha_forgotpass' );
	if ( $forgotpass_captcha ) {
		\Cariera_Core\Extensions\Captcha\Captcha::render_field( 'forgot-pass-form' );
	}
	?>

	<div class="form-group">
		<input type="submit" name="submit" value="<?php esc_html_e( 'Reset Password', 'cariera-core' ); ?>" class="btn btn-main btn-effect" />
	</div>

	<?php wp_nonce_field( 'cariera-ajax-forgetpass-nonce', 'forgetpass_security' ); ?>
</form>
