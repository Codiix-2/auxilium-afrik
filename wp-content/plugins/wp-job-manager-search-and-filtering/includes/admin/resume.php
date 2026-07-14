<?php
namespace WPJMSF\Admin;
use WPJMSF\Admin as RootAdmin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Resume
 *
 * @package WPJMSF\Admin
 */
class Resume extends RootAdmin {

	/**
	 * Resume constructor.
	 *
	 * @param \WPJMSF\Resume $resume
	 */
	public function __construct( $resume ) {
		$this->type = $resume;
		$this->post_type = 'resume';
		$this->post_type_slug = 'resume';
		$this->capability = $resume->get_capability();
		parent::__construct();
	}

	/**
	 * Get Settings
	 *
	 * @return mixed|void
	 * @since 1.1.0
	 *
	 */
	public function get_settings(){
		$settings = array();
		return apply_filters( 'search_and_filtering_admin_resume_settings', $settings, $this );
	}
}
