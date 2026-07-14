<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * The settings page.
	 *
	 * @var \Cariera_Addons\Core\Resumes\Admin\Settings
	 */
	private $settings_page;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		$this->settings_page = Admin\Settings::instance();
		Admin\CPT::instance();
		Admin\Writepanels::instance();

		// Actions & Filters.
		add_filter( 'job_manager_admin_screen_ids', [ $this, 'add_screen_ids' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 12 );
	}

	/**
	 * Add screen ids
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 *
	 * @param array $screen_ids
	 */
	public function add_screen_ids( $screen_ids ) {
		$screen_ids[] = 'edit-resume';
		$screen_ids[] = 'resume';
		$screen_ids[] = 'resume_page_cariera_addons_resumes_settings';
		return $screen_ids;
	}

	/**
	 * Add submenu under the resume post type settings.
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 */
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME,
			esc_html__( 'Settings', 'cariera-addons' ),
			esc_html__( 'Settings', 'cariera-addons' ),
			'manage_options',
			'cariera_addons_resumes_settings',
			[ $this->settings_page, 'output' ]
		);
	}
}
