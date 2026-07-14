<?php
/**
 * Message to show above resume submit form when submitting a new resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/candidate-dashboard-login.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration          = get_option( 'cariera_login_register_layout' );
$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_registration_page_url = get_permalink( $login_registration_page );
$plural_label                = cariera_addons_resume_cpt_plural_label();
?>

<div id="resume-manager-candidate-dashboard">
	<p class="account-sign-in">
		<?php
		printf(
			/* translators: %s: plural label (e.g. Resumes) */
			esc_html__( 'You need to be signed in to manage your %s.', 'cariera-addons' ),
			esc_html( $plural_label )
		);
		?>
	</p>
	<a class="btn btn-main btn-effect <?php echo 'popup' === $login_registration ? 'popup-with-zoom-anim' : ''; ?>" href="<?php echo 'popup' === $login_registration ? '#login-register-popup' : esc_url( $login_registration_page_url ); ?>"><?php esc_html_e( 'Sign in', 'cariera-addons' ); ?></a>
</div>
