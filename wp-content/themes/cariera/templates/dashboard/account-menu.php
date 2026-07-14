<?php
/**
 * Account menu of the dashboard menu.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/account-menu.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

// Pages for the Dashboard Listing Menu.
$profile = apply_filters( 'cariera_dashboard_user_profile_page', get_option( 'cariera_dashboard_profile_page' ) );
?>

<ul class="dashboard-nav-account" data-submenu-title="<?php esc_attr_e( 'Account', 'cariera' ); ?>">
	<li class="dashboard-menu-item_my-profile <?php echo esc_attr( $post->ID == $profile ? 'active' : '' ); ?>">
		<a href="<?php echo esc_url( get_permalink( $profile ) ); ?>">
			<i class="las la-user-cog"></i><span><?php esc_html_e( 'My Profile', 'cariera' ); ?></span>
		</a>
	</li>

	<li>
		<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><i class="las la-power-off"></i><span><?php esc_html_e( 'Logout', 'cariera' ); ?></span></a>
	</li>
</ul>
