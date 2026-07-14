<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Alert {

	/**
	 * Alert ID.
	 *
	 * @var int
	 */
	public int $ID;

	/**
	 * Alert post object.
	 *
	 * @var \WP_Post
	 */
	private \WP_Post $post;

	/**
	 * Frequency of the alert.
	 *
	 * @var string
	 */
	public string $frequency;

	/**
	 * Construct job alert model. Use Alert::load or Alert:create to create an instance.
	 *
	 * @param \WP_Post $alert The post for the alert.
	 */
	private function __construct( \WP_Post $alert ) {
		$this->post = $alert;
		$this->ID   = $alert->ID;

		$this->load_metadata();
	}

	/**
	 * Load the alert post model.
	 *
	 * @since 0.9.2
	 *
	 * @param int|\WP_Post|Alert $alert_id Alert post ID.
	 */
	public static function load( $alert_id ) {
		if ( empty( $alert_id ) ) {
			return false;
		}

		if ( $alert_id instanceof self ) {
			return $alert_id;
		}

		$post = get_post( $alert_id );

		if ( empty( $post ) || Post_Types::CPT_ALERT !== $post->post_type ) {
			return false;
		}

		return new self( $post );
	}

	/**
	 * Create and save a new alert.
	 *
	 * @since 0.9.2
	 *
	 * @param array                    $data User data. See self::update method.
	 * @param \WP_User|Guest_User|null $owner
	 *
	 * @return self Model object for the new alert.
	 */
	public static function create( $data, $owner = null ) {
		$alert_data = [
			'post_title'     => $data['alert_name'],
			'post_status'    => $data['post_status'] ?? 'publish',
			'post_type'      => Post_Types::CPT_ALERT,
			'comment_status' => 'closed',
		];

		if ( $owner instanceof \WP_Job_Manager\Guest_User ) {
			$alert_data['post_parent'] = $owner->ID;
		} else {
			$alert_data['post_author'] = $owner->ID ?? get_current_user_id();
		}

		$alert_id = wp_insert_post( $alert_data );

		$alert = self::load( $alert_id );

		$alert->update( $data );

		return $alert;
	}

	/**
	 * Get the name of the alert.
	 *
	 * @since 0.9.2
	 */
	public function get_name() {
		return $this->post->post_title;
	}

	/**
	 * Get the post object for the alert.
	 *
	 * @since 0.9.2
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * Get the alert's owner.
	 *
	 * @since 0.9.2
	 */
	public function get_user() {
		$guest_id = $this->post->post_parent;

		if ( ! empty( $guest_id ) ) {
			return \WP_Job_Manager\Guest_User::load( $guest_id );
		}

		if ( ! empty( $this->post->post_author ) ) {
			return get_user_by( 'id', $this->post->post_author );
		}

		return false;
	}

	/**
	 * Check if the alert ID is valid and the alert was found.
	 *
	 * @since 0.9.2
	 */
	public function check() {
		return ! empty( $this->post );
	}

	/**
	 * Check if the user can manage the alert.
	 *
	 * @since 0.9.2
	 *
	 * @param \WP_User|Guest_User|int|null $user User object, user ID, or null for current user or guest.
	 */
	public function check_ownership( $user = null ) {
		$alert = $this->post;

		if ( empty( $user ) ) {
			$user = get_current_user_id();
		}

		if ( empty( $user ) ) {
			$user = \WP_Job_Manager\Guest_Session::get_current_guest();
		}

		if ( $user instanceof \WP_User ) {
			return $user->ID === (int) $alert->post_author;
		}

		if ( $user instanceof \WP_Job_Manager\Guest_User ) {
			return $user->ID === $alert->post_parent;
		}

		if ( is_numeric( $user ) ) {
			return 0 !== $user && $user === (int) $alert->post_author;
		}

		return false;
	}

	/**
	 * Get the alert's search terms for display.
	 *
	 * @since 0.9.2
	 */
	public function get_search_terms() {
		return Post_Types::get_alert_search_term_names( $this->ID );
	}

	/**
	 * Get the schedule set for the alert.
	 *
	 * @since 0.9.2
	 */
	public function get_schedule() {
		$schedules = Notifier::get_alert_schedules();

		return $schedules[ $this->frequency ] ?? null;
	}

	/**
	 * Get the current user's alert.
	 *
	 * @since 0.9.2
	 *
	 * @param array $user_args The arguments to use when querying for the user alerts.
	 */
	public static function get_user_alerts( $user_args = null ) {
		if ( null === $user_args ) {
			$user_args = self::get_user_args();

			if ( ! $user_args ) {
				return [];
			}
		}

		$args = array_merge(
			[
				'post_type'           => Post_Types::CPT_ALERT,
				'post_status'         => [ 'publish', 'draft' ],
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => -1,
				'orderby'             => 'date',
				'order'               => 'desc',
			],
			$user_args
		);

		return get_posts( $args );
	}

	/**
	 * Get query args for the user or guest user association.
	 *
	 * @since 0.9.2
	 */
	private static function get_user_args() {
		$user = get_current_user_id();

		if ( ! empty( $user ) ) {
			return [
				'author'      => get_current_user_id(),
				'post_parent' => 0,
			];
		}

		$guest = \WP_Job_Manager\Guest_Session::get_current_guest();

		if ( ! empty( $guest ) ) {
			return [
				'post_parent' => $guest->ID,
			];
		}

		return false;
	}

	/**
	 * Update alert with new settings.
	 *
	 * @since 0.9.2
	 *
	 * @param array $data
	 */
	public function update( $data ) {
		$search_terms = [
			'categories' => $data['alert_cats'] ?? null,
			'regions'    => $data['alert_regions'] ?? null,
			'tags'       => $data['alert_tags'] ?? null,
			'types'      => $data['alert_job_type'] ?? null,
		];

		$meta = [
			'alert_search_terms' => $search_terms,
			'alert_frequency'    => $data['alert_frequency'] ?? null,
			'alert_keyword'      => $data['alert_keyword'] ?? null,
			'alert_location'     => $data['alert_location'] ?? null,
		];

		if ( ! empty( $data['alert_permission'] ) ) {
			$meta['_alert_permission_approved'] = true;
		}

		wp_update_post(
			[
				'ID'         => $this->ID,
				'post_title' => $data['alert_name'],
				'meta_input' => $meta,
			]
		);

		$frequency_was_changed = $this->frequency !== $data['alert_frequency'];

		$this->reload();

		if ( $frequency_was_changed ) {
			$this->update_schedule();
		}
	}

	/**
	 * Check if the alert is enabled.
	 *
	 * @since 0.9.2
	 */
	public function is_enabled() {
		return 'publish' === $this->post->post_status;
	}

	/**
	 * Enable and schedule the alert emails.
	 *
	 * @since 0.9.2
	 */
	public function enable() {

		wp_update_post(
			[
				'ID'          => $this->ID,
				'post_status' => 'publish',
			]
		);

		$this->reload();
	}

	/**
	 * Disable the alert emails.
	 *
	 * @since 0.9.2
	 */
	public function disable() {
		wp_update_post(
			[
				'ID'          => $this->ID,
				'post_status' => 'draft',
			]
		);

		$this->reload();
	}

	/**
	 * Delete the alert.
	 *
	 * @since 0.9.2
	 */
	public function delete() {
		$this->disable();

		return wp_trash_post( $this->ID );
	}

	/**
	 * Send an alert email now, with all matching jobs.
	 *
	 * @since 0.9.2
	 */
	public function send_now() {
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Left as it is for backward compatibility.
		do_action( 'job-manager-alert', $this->ID, true );
		\WP_Job_Manager_Email_Notifications::send_deferred_notifications();
	}

	/**
	 * Update send count.
	 *
	 * @since 0.9.2
	 */
	public function increase_send_count() {
		$count = absint( get_post_meta( $this->ID, 'send_count', true ) );
		update_post_meta( $this->ID, 'send_count', 1 + $count );
	}

	/**
	 * Get jobs matching the alert.
	 *
	 * @since 0.9.2
	 *
	 * @param bool $force Ignore alert frequency and cache.
	 */
	public function get_matching_jobs( $force = false ) {
		return Alert_Jobs_Query::get_matching_jobs( $this, $force );
	}

	/**
	 * Get time of next email scheduled.
	 *
	 * @param bool $format Whether to return a formatted date or just a timestamp.
	 *
	 * @return string|int|false Time of next email, or false if no email is scheduled.
	 */
	public function get_next_scheduled( $format = true ) {
		$date = Notifier::instance()->get_next_scheduled( $this );

		if ( ! $format || ! $date ) {
			return $date;
		}

		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		return date_i18n( $date_format, $date );
	}

	/**
	 * Get the expiration date for the alert if there is one.
	 *
	 * @since 0.9.2
	 *
	 * @param bool $format Whether to return a formatted date or just a timestamp.
	 */
	public function get_expiration_date( $format = true ): string {
		$expire_after = get_option( 'job_manager_alerts_auto_disable' );

		if ( $expire_after <= 0 ) {
			return false;
		}

		$post_modified = get_post_modified_time( 'U', false, $this->ID, false );

		$expiration_date = strtotime( '+' . absint( get_option( 'job_manager_alerts_auto_disable' ) ) . ' days', $post_modified );

		if ( ! $expiration_date ) {
			return false;
		}

		if ( ! $format ) {
			return $expiration_date;
		}

		$date_format = get_option( 'date_format' );

		return date_i18n( $date_format, $expiration_date );
	}

	/**
	 * Reload post data after updating.
	 *
	 * @since 0.9.2
	 */
	private function reload() {
		$this->post = get_post( $this->ID );
		$this->load_metadata();
	}

	/**
	 * Load meta.
	 *
	 * @since 0.9.2
	 */
	private function load_metadata() {
		$this->frequency = get_post_meta( $this->ID, Post_Types::META_FREQUENCY, true );
	}

	/**
	 * Update the cron schedule for the alert emails.
	 */
	public function update_schedule() {
		Notifier::instance()->update_schedule( $this );
	}
}
