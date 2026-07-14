<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Alert_Stats {

	use \Cariera_Addons\Src\Traits\Singleton;

	const ALERT_IMPRESSION = 'job_alert_impression';
	const MAIL_ICON        = 'url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' fill=\'none\' viewBox=\'0 0 24 24\'%3e%3cpath fill=\'black\' fill-rule=\'evenodd\' d=\'M3 7c0-1.1.9-2 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Zm2-.5h14c.28 0 .5.22.5.5v.94L12 13.56 4.5 7.94V7c0-.28.22-.5.5-.5Zm-.5 3.31V17c0 .28.22.5.5.5h14a.5.5 0 0 0 .5-.5V9.81L12 15.44 4.5 9.8Z\' clip-rule=\'evenodd\'/%3e%3c/svg%3e")';

	/**
	 * Constructor
	 */
	private function __construct() {
		add_filter( 'job_manager_job_stats_summary', [ $this, 'job_stats_summary' ], 10, 2 );
	}

	/**
	 * Log the stats for the jobs sent in the alert.
	 *
	 * @since 0.9.2
	 *
	 * @param \WP_Query $jobs
	 */
	public static function log_jobs_sent( $jobs ) {
		if ( ! class_exists( 'WP_Job_Manager\Stats' ) || ! \WP_Job_Manager\Stats::is_enabled() ) {
			return;
		}

		$stats = array_map(
			fn( $job ) => [
				'post_id' => $job->ID,
				'name'    => self::ALERT_IMPRESSION,
			],
			$jobs->posts
		);

		\WP_Job_Manager\Stats::instance()->batch_log_stats( $stats );
	}

	/**
	 * Add alert e-mail impressions to job listing overlay stats section.
	 *
	 * @since 0.9.2
	 *
	 * @param array    $stats
	 * @param \WP_Post $job
	 */
	public function job_stats_summary( $stats, $job ) {
		if ( ! empty( $stats['impressions']['stats'] ) && ! empty( $job->ID ) ) {
			$stats['impressions']['stats'][] =
				[
					'icon'  => self::MAIL_ICON,
					'label' => esc_html__( 'Alerts sent', 'cariera-addons' ),
					'value' => self::get_alert_impression_count( $job->ID ),
				];
			$stats['impressions']['help']    = esc_html__( 'How many times the listing was seen in search results or sent out in job alert e-mails.', 'cariera-addons' );
		}

		return $stats;
	}

	/**
	 * Get the count of alert impressions for a job.
	 *
	 * @since 0.9.2
	 *
	 * @param int $job_id
	 */
	public static function get_alert_impression_count( $job_id ) {
		$stats = new \WP_Job_Manager\Job_Listing_Stats( $job_id );
		return $stats->get_event_total( self::ALERT_IMPRESSION );
	}
}
