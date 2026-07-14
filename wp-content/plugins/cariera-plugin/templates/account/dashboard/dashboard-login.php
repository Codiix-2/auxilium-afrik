<?php
/**
 * Cariera Dashboard Login Template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard-login.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.9.6
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration          = get_option( 'cariera_login_register_layout' );
$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_registration_page_url = get_permalink( $login_registration_page );
?>

<div id="cariera-dashboard-login">
	<p><?php esc_html_e( 'You need to be signed in to access your dashboard.', 'cariera-core' ); ?></p>
	<a class="btn btn-main btn-effect <?php echo 'popup' === $login_registration ? 'popup-with-zoom-anim' : ''; ?>" href="<?php echo 'popup' === $login_registration ? '#login-register-popup' : esc_url( $login_registration_page_url ); ?>">
		<?php esc_html_e( 'Sign in', 'cariera-core' ); ?>
	</a>
</div>
