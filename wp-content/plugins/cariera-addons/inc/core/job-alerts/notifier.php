<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notifier {

	use \Cariera_Addons\Src\Traits\Singleton;

	public const SCHEDULE_HOOK_NAME = 'job-manager-alert';

	/**
	 * Store current alert frequency for queries
	 *
	 * @var string
	 */
	private static $current_alert_frequency = 'daily';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'job-manager-alert', [ $this, 'job_manager_alert' ], 10, 2 );
		add_filter( 'cron_schedules', [ $this, 'add_cron_schedules' ] );
		add_filter( 'job_manager_email_notifications', [ $this, 'register_emails' ] );
		add_action( 'job-manager-alert-check-reschedule', [ $this, 'check_reschedule_events' ] );
		add_action( 'transition_post_status', [ $this, 'maybe_update_schedule' ], 10, 3 );
		add_action( 'delete_post', [ $this, 'clear_schedule' ] );

		if ( false === wp_next_scheduled( 'job-manager-alert-check-reschedule' ) ) {
			wp_schedule_event( time(), 'daily', 'job-manager-alert-check-reschedule' );
		}
	}

	/**
	 * Apply the brand color for emails.
	 *
	 * @since 0.9.2
	 *
	 * @param array $style_vars Variables used in email style generation.
	 */
	public function apply_brand_color( $style_vars ) {
		$brand_color = Settings::instance()->get_the_brand_color();
		if ( ! empty( $brand_color ) ) {
			$style_vars['color_link']        = $brand_color;
			$style_vars['color_button']      = $brand_color;
			$style_vars['color_button_text'] = '#FFF';
		}

		return $style_vars;
	}

	/**
	 * Register e-mails.
	 *
	 * @since 0.9.2
	 *
	 * @param array $emails List of registered e-mail notifications.
	 */
	public function register_emails( $emails ) {
		$emails[] = Emails\Job_Alert_Email::class;
		$emails[] = Emails\Confirmation_Email::class;

		return $emails;
	}

	/**
	 * Get alert schedules.
	 *
	 * @since 0.9.2
	 */
	public static function get_alert_schedules() {
		$schedules = [];

		$schedules['daily'] = [
			'interval' => DAY_IN_SECONDS,
			'display'  => esc_html__( 'Daily', 'cariera-addons' ),
		];

		$schedules['weekly'] = [
			'interval' => WEEK_IN_SECONDS,
			'display'  => esc_html__( 'Weekly', 'cariera-addons' ),
		];

		$schedules['fortnightly'] = [
			'interval' => WEEK_IN_SECONDS * 2,
			'display'  => esc_html__( 'Fortnightly', 'cariera-addons' ),
		];

		$schedules['monthly'] = [
			'interval' => MONTH_IN_SECONDS,
			'display'  => esc_html__( 'Monthly', 'cariera-addons' ),
		];

		return apply_filters( 'cariera_addons_alerts_alert_schedules', $schedules );
	}

	/**
	 * Add custom cron schedules
	 *
	 * @since 0.9.2
	 *
	 * @param array $schedules
	 */
	public function add_cron_schedules( array $schedules ) {
		return array_merge( $schedules, self::get_alert_schedules() );
	}

	/**
	 * Send and update an alert.
	 *
	 * @since 0.9.2
	 *
	 * @param int  $alert_id Alert ID.
	 * @param bool $force Ignore alert frequency and disabled status.
	 */
	public function job_manager_alert( $alert_id, $force = false ) {
		$alert = Alert::load( $alert_id );

		if ( ! $alert || ( ! $alert->is_enabled() && ! $force ) ) {
			return;
		}

		$this->send_alert_email( $alert, $force );

		$this->maybe_expire_alert( $alert );

		$alert->increase_send_count();
	}

	/**
	 * Format and send the job alert e-mail.
	 *
	 * @since 0.9.2
	 *
	 * @param Alert $alert Job Alert.
	 * @param bool  $force Ignore alert frequency and disabled status.
	 */
	private function send_alert_email( Alert $alert, bool $force = false ) {
		add_filter( 'job_manager_email_style_vars', [ $this, 'apply_brand_color' ] );

		$user = $alert->get_user();
		$jobs = $alert->get_matching_jobs( $force );

		if ( ! $jobs->found_posts && get_option( 'job_manager_alerts_matches_only' ) ) {
			return;
		}

		Emails\Job_Alert_Email::send( $alert, $jobs, $user );

		Alert_Stats::log_jobs_sent( $jobs );
	}

	/**
	 * Disable the alert if it's expiration date has passed.
	 *
	 * @since 0.9.2
	 *
	 * @param Alert $alert Job alert.
	 */
	private function maybe_expire_alert( $alert ) {
		$expiration = $alert->get_expiration_date( false );

		if ( ! empty( $expiration ) && time() > $expiration ) {
			$alert->disable();
		}
	}

	/**
	 * Checks alerts for their corresponding scheduled event and reschedules if missing.
	 *
	 * @since 0.9.2
	 */
	public function check_reschedule_events() {
		$alert_posts = new \WP_Query(
			[
				'post_type'      => Post_Types::CPT_ALERT,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			]
		);

		foreach ( $alert_posts->posts as $post ) {
			if ( false === wp_next_scheduled( 'job-manager-alert', [ $post->ID ] ) ) {
				$alert_frequency = get_post_meta( $post->ID, 'alert_frequency', true );

				// Use the created time to distribute the events again, starting tomorrow.
				$created = strtotime( $post->post_date );
				$next    = strtotime( gmdate( 'Y-m-d', strtotime( '+1 day' ) ) . ' ' . gmdate( 'G:i:s', $created ) );

				wp_schedule_event( $next, $alert_frequency, 'job-manager-alert', [ $post->ID ] );
			}
		}
	}

	/**
	 * Update schedule when alert status is changed.
	 *
	 * @since 0.9.2
	 *
	 * @param string   $new_status
	 * @param string   $old_status
	 * @param \WP_Post $post
	 */
	public function maybe_update_schedule( $new_status, $old_status, $post ) {
		$alert = Alert::load( $post->ID );

		if ( $alert && $new_status !== $old_status ) {
			$this->update_schedule( $alert );
		}
	}

	/**
	 * Schedule the alert e-mails based on the alert frequency.
	 *
	 * @since 0.9.2
	 *
	 * @param int|\WP_Post|Alert $alert The alert.
	 */
	public function update_schedule( $alert ) {
		$alert = Alert::load( $alert );

		if ( ! $alert ) {
			return;
		}

		$this->clear_schedule( $alert );

		if ( ! $alert->is_enabled() ) {
			return;
		}

		// Schedule new alert.
		$schedule = $alert->get_schedule();

		if ( ! empty( $schedule ) ) {
			$next = strtotime( '+' . $schedule['interval'] . ' seconds' );
		} else {
			$next = strtotime( '+1 day' );
		}

		wp_schedule_event( $next, $alert->frequency, self::SCHEDULE_HOOK_NAME, [ $alert->ID ] );
	}

	/**
	 * Clear the schedule for this alert.
	 *
	 * @since 0.9.2
	 *
	 * @param int|\WP_Post|Alert $alert The alert.
	 */
	public function clear_schedule( $alert ) {
		$alert = Alert::load( $alert );

		if ( ! $alert ) {
			return;
		}

		wp_clear_scheduled_hook( self::SCHEDULE_HOOK_NAME, [ $alert->ID ] );
	}

	/**
	 * Get time of next email scheduled.
	 *
	 * @since 0.9.2
	 *
	 * @param int|\WP_Post|Alert $alert The alert.
	 */
	public function get_next_scheduled( $alert ) {
		$alert = Alert::load( $alert );

		if ( ! $alert ) {
			return false;
		}

		$scheduled = wp_next_scheduled( self::SCHEDULE_HOOK_NAME, [ $alert->ID ] );

		$date = $scheduled ? $scheduled + (int) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS : false;

		return $date;
	}
}
