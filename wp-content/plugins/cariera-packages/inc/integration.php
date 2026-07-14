<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Integration {

	use \Cariera_Packages\Src\Traits\Singleton;

	private const STATUS_PENDING_PAYMENT = 'pending_payment';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Job Packages.
		\Cariera_Packages\Integration\Job::instance();

		// Company Packages.
		if ( \Cariera_Packages::cariera_company_manager_active() ) {
			\Cariera_Packages\Integration\Company::instance();
		}

		// Resume Packages.
		if ( \Cariera_Packages::wprm_active() ) {
			\Cariera_Packages\Integration\Resume::instance();
		}

		// Event Packages.
		if ( class_exists( 'Cariera_Events' ) ) {
			\Cariera_Packages\Integration\Event::instance();
		}

		// Register custom post status.
		add_action( 'init', [ $this, 'register_post_status' ], 12 );
	}

	/**
	 * Registers custom post status.
	 *
	 * @since   0.9.12
	 * @version 0.9.15
	 */
	public function register_post_status() {
		global $job_manager;

		register_post_status(
			'pending_payment',
			[
				'label'                     => _x( 'Pending Payment', 'Post status label', 'cariera-packages' ),
				'protected'                 => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// translators: %s placeholder is the plural lable of listings cpt.
				'label_count'               => _n_noop(
					'Pending Payment <span class="count">(%s)</span>',
					'Pending Payment <span class="count">(%s)</span>',
					'cariera-packages'
				),
			]
		);

		// Ensure expiry is set when moving to publish.
		if ( isset( $job_manager->post_types ) ) {
			add_action( self::STATUS_PENDING_PAYMENT . '_to_publish', [ $job_manager->post_types, 'set_expiry' ] );
		}
	}
}
