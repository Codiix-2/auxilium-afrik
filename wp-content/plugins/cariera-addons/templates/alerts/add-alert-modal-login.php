<?php
/**
 * Job search page modal dialog when login is required.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/alerts/add-alert-modal-login.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.2
 * @version     1.0.4
 */

use WP_Job_Manager\UI\Notice;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_registration      = get_option( 'cariera_login_register_layout' );
$login_registration_page = get_option( 'cariera_login_register_page' );
$login_url               = ( 'popup' === $login_registration ) ? '#login-register-popup' : get_permalink( $login_registration_page );

echo Notice::render(
	[
		'title'   => esc_html__( 'Add Alert', 'cariera-addons' ),
		'message' => esc_html__( 'Sign in or create an account to continue.', 'cariera-addons' ),
		'buttons' => [
			[
				'url'   => apply_filters( 'cariera_addons_alerts_login_url', $login_url ),
				'class' => ( 'popup' === $login_registration ) ? 'popup-with-zoom-anim' : '',
				'label' => esc_html__( 'Sign in', 'cariera-addons' ),
			],
			[
				'url'   => apply_filters( 'cariera_addons_alerts_register_url', $login_url ),
				'class' => ( 'popup' === $login_registration ) ? 'popup-with-zoom-anim register-popup' : '',
				'label' => esc_html__( 'Create Account', 'cariera-addons' ),
			],
		],
	]
);
