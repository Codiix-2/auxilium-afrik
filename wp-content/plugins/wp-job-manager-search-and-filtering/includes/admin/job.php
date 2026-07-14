<?php

namespace WPJMSF\Admin;
use WPJMSF\Admin as RootAdmin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Job
 *
 * @package WPJMSF\Admin
 */
class Job extends RootAdmin {

	/**
	 * Job constructor.
	 *
	 * @param \WPJMSF\Job $job
	 */
	public function __construct( $job ) {
		$this->type = $job;
		$this->post_type = 'job_listing';
		$this->post_type_slug = 'job';
//		$this->capability = 'manage_job_fields';
		$this->capability = $job->get_capability();
		parent::__construct();
	}
}
