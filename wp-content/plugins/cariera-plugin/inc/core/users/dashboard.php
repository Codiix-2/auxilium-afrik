<?php

namespace Cariera_Core\Core\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	/**
	 * Constructor
	 */
	public function __construct() {
		// User Dashboard.
		add_shortcode( 'cariera_dashboard', [ $this, 'user_dashboard' ] );
	}

	/**
	 * User Dashboard shortcode
	 * Usage: [cariera_dashboard]
	 *
	 * @since   1.5.2
	 * @version 1.9.6
	 */
	public function user_dashboard() {
		if ( ! is_user_logged_in() ) {
			cariera_get_template_part( 'account/dashboard/dashboard-login' );
		} else {
			wp_enqueue_script( 'cariera-dashboard-charts' );
			cariera_get_template_part( 'account/dashboard/dashboard' );
		}
	}
}
