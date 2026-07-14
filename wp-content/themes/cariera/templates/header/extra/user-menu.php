<?php
/**
 * Header - User Menu template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/extra/user-menu.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.4
 * @version     1.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();
$user_id      = get_current_user_id();
$user_img     = get_avatar( $user_id, 40, '', esc_attr( $current_user->display_name ) );
$roles        = (array) $current_user->roles;
$role         = ! empty( $roles ) ? array_shift( $roles ) : 'user';

// Menu nav if the user is an Employer.
if ( in_array( 'employer', (array) $current_user->roles, true ) ) {
	$menu_nav = 'employer-menu';
}

// Menu nav if the user is a Candidate.
if ( in_array( 'candidate', (array) $current_user->roles, true ) ) {
	$menu_nav = 'candidate-menu';
}
?>

<div class="extra-menu-item extra-user">
	<a href="#" id="user-account-extra" aria-label="<?php esc_attr_e( 'Header user account', 'cariera' ); ?>">
		<div class="login-status"></div>
		<span class="avatar-img">
			<?php echo wp_kses_post( $user_img ); ?>
		</span>
	</a>

	<!-- Header Account Widget -->
	<div class="header-account-widget header-account-widget-<?php echo esc_attr( $role ); ?>">
		<div class="title-bar">
			<h2 class="title"><?php echo esc_html( $current_user->first_name ) . ' ' . esc_html( $current_user->last_name ); ?></h2>
			<small><?php echo esc_html( $current_user->user_email ); ?></small>
		</div>

		<!-- Main Content -->
		<div class="main-content">
			<?php
			if ( ! empty( $menu_nav ) && has_nav_menu( $menu_nav ) ) {
				$args = [
					'theme_location' => $menu_nav,
					'container'      => false,
					'menu_class'     => 'account-nav',
					'menu_id'        => '',
					'fallback_cb'    => '__return_false',
					'walker'         => new \Cariera\Mega_Menu(),
				];

				wp_nav_menu( $args );
			} else {
				get_template_part( 'templates/header/extra/user-menu-items' );
			}
			?>
		</div>

		<!-- Logout Footer -->
		<div class="logout-footer">
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><i class="las la-power-off"></i><?php esc_html_e( 'Logout', 'cariera' ); ?></a>
		</div>
	</div>
</div>
