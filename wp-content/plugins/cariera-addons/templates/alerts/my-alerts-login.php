<?php
/**
 * Lists job listing alerts content if user is not logged in.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/alerts/my-alerts-login.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.2
 * @version     0.9.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration          = get_option( 'cariera_login_register_layout' );
$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_registration_page_url = get_permalink( $login_registration_page );
?>

<div id="cariera-addons-job-alerts">
	<p class="account-sign-in"><?php esc_html_e( 'Sign in or create an account to manage your alerts.', 'cariera-addons' ); ?></p>
	<a class="btn btn-main btn-effect <?php echo 'popup' === $login_registration ? 'popup-with-zoom-anim' : ''; ?>" href="<?php echo 'popup' === $login_registration ? '#login-register-popup' : esc_url( $login_registration_page_url ); ?>"><?php esc_html_e( 'Sign in', 'cariera-addons' ); ?></a>
</div>
