<?php
/**
 * Cariera My Profile Login template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/my-profile-login.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.9.8
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration          = get_option( 'cariera_login_register_layout' );
$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_registration_page_url = get_permalink( $login_registration_page );
?>

<p><?php esc_html_e( 'You must be logged in to edit your profile.', 'cariera-core' ); ?></p>
<a class="btn btn-main btn-effect <?php echo 'popup' === $login_registration ? 'popup-with-zoom-anim' : ''; ?>" href="<?php echo 'popup' === $login_registration ? '#login-register-popup' : esc_url( $login_registration_page_url ); ?>"><?php esc_html_e( 'Sign in', 'cariera-core' ); ?></a>
