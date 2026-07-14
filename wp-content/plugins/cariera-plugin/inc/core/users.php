<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Users {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Handles user approval logic.
	 *
	 * @var \Cariera_Core\Core\Users\Approval
	 */
	private $approval;

	/**
	 * Handles user avatar functionality.
	 *
	 * @var \Cariera_Core\Core\Users\Avatar
	 */
	private $avatar;

	/**
	 * Handles the user dashboard.
	 *
	 * @var \Cariera_Core\Core\Users\Dashboard
	 */
	private $dashboard;

	/**
	 * Handles password reset functionality.
	 *
	 * @var \Cariera_Core\Core\Users\Forget_Password
	 */
	private $forget_password;

	/**
	 * Handles third-party integrations (e.g., social login).
	 *
	 * @var \Cariera_Core\Core\Users\Integrations
	 */
	private $integrations;

	/**
	 * Handles user login functionality.
	 *
	 * @var \Cariera_Core\Core\Users\Login
	 */
	private $login;

	/**
	 * Handles user profile management.
	 *
	 * @var \Cariera_Core\Core\Users\Profile
	 */
	private $profile;

	/**
	 * Handles user registration functionality.
	 *
	 * @var \Cariera_Core\Core\Users\Registration
	 */
	private $registration;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_classes();

		// Register Login & Register script.
		add_action( 'wp_enqueue_scripts', [ $this, 'user_script' ] );

		// User contact methods.
		add_action( 'user_contactmethods', [ $this, 'modify_user_contact_methods' ], 10 );
	}

	/**
	 * Initialize sub-handlers
	 *
	 * @since 1.9.7
	 */
	private function init_classes() {
		$this->approval        = new Users\Approval();
		$this->avatar          = new Users\Avatar();
		$this->dashboard       = new Users\Dashboard();
		$this->forget_password = new Users\Forget_Password();
		$this->integrations    = new Users\Integrations();
		$this->login           = new Users\Login();
		$this->profile         = new Users\Profile();
		$this->registration    = new Users\Registration();
	}

	/**
	 * Login & Register script
	 *
	 * @since   1.4.8
	 * @version 1.9.8
	 *
	 * TODO: Check this function for improvements.
	 */
	public function user_script() {
		if ( is_user_logged_in() ) {
			return;
		}

		wp_register_script( 'cariera-user-ajax', CARIERA_URL . '/assets/dist/js/login-register.js', [ 'jquery' ], CARIERA_CORE_VERSION, true );

		// Redirection Settings.
		$login_redirect       = get_option( 'cariera_login_redirection' );
		$login_redirect_candi = get_option( 'cariera_login_redirection_candidate' );
		$dashboard_title      = cariera_get_page_by_title( 'Dashboard' );
		$dashboard_page       = apply_filters( 'cariera_dashboard_page', get_option( 'cariera_dashboard_page' ) );

		if ( $dashboard_title ) {
			$dashboard = get_permalink( $dashboard_title );
		} else {
			$dashboard = get_permalink( $dashboard_page );
		}

		// Redirection after login for all users.
		if ( 'dashboard' === $login_redirect ) {
			$redirect = $dashboard;
		} elseif ( 'home' === $login_redirect ) {
			$redirect = home_url( '/' );
		} elseif ( 'custom_page' === $login_redirect ) {
			$redirect = get_permalink( apply_filters( 'cariera_login_redirection_page', get_option( 'cariera_login_redirection_page' ) ) );
		} else {
			$redirect = isset( $_SERVER['REQUEST_URI'] ) ? home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';
		}

		// Redirection after login for candidates.
		if ( 'dashboard' === $login_redirect_candi ) {
			$redirect_candi = $dashboard;
		} elseif ( 'home' === $login_redirect_candi ) {
			$redirect_candi = home_url( '/' );
		} elseif ( 'custom_page' === $login_redirect_candi ) {
			$redirect_candi = get_permalink( apply_filters( 'cariera_login_candi_redirection_page', get_option( 'cariera_login_candi_redirection_page' ) ) );
		} else {
			$redirect_candi = isset( $_SERVER['REQUEST_URI'] ) ? home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';
		}

		wp_localize_script(
			'cariera-user-ajax',
			'cariera_user_ajax',
			[
				'ajaxurl'           => admin_url( 'admin-ajax.php', 'relative' ),
				'loadingmessage'    => '<span class="job-manager-message generic loading"><i></i>' . esc_html__( 'Please wait...', 'cariera-core' ) . '</span>',
				'moderate'          => get_option( 'cariera_moderate_new_user' ),
				'auto_login'        => get_option( 'cariera_auto_login' ),
				'redirection'       => $redirect,
				'redirection_candi' => $redirect_candi,
			]
		);
	}

	/**
	 * Add new fields in the user contact method
	 *
	 * @since  1.5.3
	 *
	 * @param array $profile_fields
	 */
	public function modify_user_contact_methods( $profile_fields ) {
		// Add new fields.
		$profile_fields['phone'] = esc_html__( 'Phone', 'cariera-core' );

		return $profile_fields;
	}
}
