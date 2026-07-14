<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Application_Stats {

	/**
	 * Get application stats.
	 *
	 * @since 0.9.3
	 *
	 * @param int $job_id Job ID.
	 *
	 * @return mixed
	 */
	public static function get_application_stats( $job_id ) {
		global $wpdb;

		$cache_key = 'job_manager_job_' . $job_id . '_application_by_status';

		$cached = wp_cache_get( $cache_key, 'job_manager' );

		if ( false !== $cached ) {
			return $cached;
		}

		$post_type = \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$applications_by_status = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_status, COUNT(*) as count
				FROM {$wpdb->posts}
				WHERE post_parent = %d
				AND post_type = %s
				GROUP BY post_status",
				$job_id,
				$post_type
			),
			OBJECT_K
		);

		wp_cache_set( $cache_key, $applications_by_status, 'job_manager', HOUR_IN_SECONDS );

		return $applications_by_status;
	}
}
