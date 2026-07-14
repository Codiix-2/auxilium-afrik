<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Alert_Migrator {

	use \Cariera_Addons\Src\Traits\Singleton;

	private const UPDATE_PER_REQUEST = 100;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'user_register', [ $this, 'migrate_alerts' ], 10, 2 );
		add_action( 'job_manager_migrate_alerts', [ $this, 'migrate_alerts' ], 10, 2 );
		add_filter( 'wp_insert_post_data', [ $this, 'do_not_update_author_guest_alerts' ], 10, 4 );
	}

	/**
	 * Migrates the users alerts. It links the alerts to the newly created account and deletes the guest user when finished.
	 *
	 * @since 0.9.2
	 *
	 * @param int   $user_id  User ID.
	 * @param array $userdata The raw array of data passed to wp_insert_user().
	 */
	public function migrate_alerts( $user_id, $userdata ) {
		if ( empty( $userdata['user_email'] ) ) {
			return;
		}

		$guest_user = \WP_Job_Manager\Guest_User::load( $userdata['user_email'] );

		if ( false === $guest_user ) {
			return;
		}

		$user_alerts = Alert::get_user_alerts(
			[
				'post_parent'    => $guest_user->ID,
				'posts_per_page' => self::UPDATE_PER_REQUEST,
			]
		);

		foreach ( $user_alerts as $user_alert ) {
			wp_update_post(
				[
					'ID'          => $user_alert->ID,
					'post_parent' => 0,
					'post_author' => $user_id,
				]
			);
		}

		if ( count( $user_alerts ) < self::UPDATE_PER_REQUEST ) {
			wp_delete_post( $guest_user->ID, true );
		} else {
			wp_schedule_single_event( time(), 'job_manager_migrate_alerts', [ $user_id, $userdata ] );
		}
	}

	/**
	 * For alerts from guest users set the post_author to 0.
	 *
	 * @since 0.9.2
	 *
	 * @param array $data                An array of slashed, sanitized, and processed post data.
	 * @param array $postarr             An array of sanitized (and slashed) but otherwise unmodified post data.
	 * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed post data as
	 *                                   originally passed to wp_insert_post().
	 * @param bool  $update              Whether this is an existing post being updated.
	 */
	public function do_not_update_author_guest_alerts( $data, $postarr, $unsanitized_postarr, $update ) {
		if ( false === $update || Post_Types::CPT_ALERT !== $data['post_type'] ) {
			return $data;
		}

		if ( ! empty( $data['post_parent'] ) && $data['post_parent'] > 0 ) {
			$data['post_author'] = 0;
		}

		return $data;
	}
}
