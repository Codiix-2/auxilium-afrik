<?php
/**
 * Header Extra: Account template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/extra/account.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! cariera_get_option( 'header_account' ) ) {
	return;
}

$login_registration = get_option( 'cariera_login_register_layout' );

if ( ! is_user_logged_in() ) {
	?>
	<div class="extra-menu-item extra-user">

		<?php if ( 'popup' === $login_registration ) { ?>
			<a href="#login-register-popup" class="popup-with-zoom-anim" aria-label="<?php esc_attr_e( 'User login & register trigger.', 'cariera' ); ?>">
			<?php
		} else {
			$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
			$login_registration_page_url = get_permalink( $login_registration_page );
			?>

			<a href="<?php echo esc_url( $login_registration_page_url ); ?>" aria-label="<?php esc_attr_e( 'User login & register trigger.', 'cariera' ); ?>">
		<?php } ?>
			<i class="las la-user"></i>
		</a>
	</div>
	<?php
} else {
	get_template_part( 'templates/header/extra/messages' );
	get_template_part( 'templates/header/extra/notifications' );
	get_template_part( 'templates/header/extra/user-menu' );
}
