<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notifications {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Mark Notifications as read.
		add_action( 'wp_ajax_cariera_notification_marked_read', [ $this, 'mark_read' ] );

		// Load more notifications.
		add_action( 'wp_ajax_cariera_load_more_notifications', [ $this, 'load_more_notifications' ] );

		// Delete Notifications.
		add_action( 'cariera_delete_notifications', [ $this, 'delete_notifications' ] );

		// Notifications.
		add_action( 'transition_post_status', [ $this, 'listing_post_status' ], 10, 3 );
		add_action( 'job_manager_applications_new_job_application', [ $this, 'application_notification' ], 10 );
		add_action( 'cariera_listing_promotion_started', [ $this, 'listing_promoted_notification' ], 10 );
		add_action( 'cariera_listing_promotion_ended', [ $this, 'promotion_expired_notification' ], 10 );

		// Webhook Test Trigger AJAX.
		add_action( 'wp_ajax_cariera_webhook_trigger', [ $this, 'webhook_test_trigger' ] );

		// Clear notification DB Table.
		add_action( 'wp_ajax_cariera_delete_all_notifications', [ $this, 'delete_all_notifications' ] );
	}

	/**
	 * Insert data to database
	 *
	 * @since   1.5.0
	 * @version 1.8.3
	 *
	 * @param array $args
	 */
	public function insert_notification( $args ) {
		global $wpdb;

		// Validate and sanitize input arguments.
		$args = wp_parse_args(
			$args,
			[
				'user_id'  => 0,          // Default to 0 if no user ID is provided.
				'action'   => '',         // Default action name.
				'owner_id' => 0,          // Default owner ID.
				'post_id'  => 0,          // Default post ID.
			]
		);

		$args['user_id']  = absint( $args['user_id'] );
		$args['action']   = sanitize_text_field( $args['action'] );
		$args['owner_id'] = absint( $args['owner_id'] );
		$args['post_id']  = absint( $args['post_id'] );

		// Get Current User ID.
		$current_user_id = get_current_user_id();
		if ( $current_user_id && empty( $args['user_id'] ) ) {
			$args['user_id'] = $current_user_id;
		}

		// Duplication check.
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}cariera_notifications
				WHERE action = %s
					AND owner_id = %d
					AND user_id = %d
					AND post_id = %d
					AND active = %d;",
				$args['action'],
				$args['owner_id'],
				$args['user_id'],
				$args['post_id'],
				1 // active flag.
			)
		);

		// Return if the insert already exists.
		if ( $exists ) {
			return false;
		}

		// Insert into the database.
		// phpcs:ignore
		$inserted = $wpdb->insert(
			$wpdb->prefix . 'cariera_notifications',
			[
				'action'   => $args['action'],     // Action name.
				'owner_id' => $args['owner_id'],   // The ID of the owner.
				'user_id'  => $args['user_id'],    // The ID of the user that did the action.
				'post_id'  => $args['post_id'],    // Post ID.
				'active'   => 1,                  // Active flag (integer).
			],
			[
				'%s', // action.
				'%d', // owner_id.
				'%d', // user_id.
				'%d', // post_id.
				'%d', // active.
			]
		);

		// Check for errors.
		if ( false === $inserted ) {
			\Cariera\write_log( 'Failed to insert notification into the database: ' . $wpdb->last_error );
			return false;
		}

		do_action( 'cariera_notification_inserted', $args );

		return true;
	}

	/**
	 * Active notifications that haven't been read
	 *
	 * @since   1.5.0
	 * @version 1.8.3
	 *
	 * @param int $user_id
	 */
	public function active( $user_id = null ) {
		global $wpdb;

		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Sanitize and validate user ID.
		$user_id = absint( $user_id );

		// Check if user exists.
		if ( ! $user_id || ! get_userdata( $user_id ) ) {
			return 0; // Return 0 if user does not exist.
		}

		// Query to count active notifications.
		$query = $wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$wpdb->prefix}cariera_notifications
			WHERE owner_id = %d
			AND active = %d",
			$user_id,
			1 // active flag.
		);

		// Execute the query and return the result.
		$results = $wpdb->get_var($query); // phpcs:ignore

		return $results;
	}

	/**
	 * Get latest notifications
	 *
	 * @since 1.5.0
	 *
	 * @param int|null $user_id User ID for whom to fetch notifications. Defaults to the current user.
	 * @param int      $num Number of notifications to retrieve. Defaults to 10.
	 * @param int      $offset Offset for pagination. Defaults to 0.
	 * @param bool     $active Whether to filter notifications by active status. Defaults to false.
	 * @return array List of notifications.
	 */
	public function get_latest_notifications( $user_id = null, $num = 10, $offset = 0, $active = false ) {
		global $wpdb;

		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Check if user exists.
		if ( get_userdata( $user_id ) === false ) {
			return [];
		}

		$active_sql = $active ? 'AND active = 1' : '';

		$sql = "
            SELECT *
            FROM {$wpdb->prefix}cariera_notifications
            WHERE owner_id = {$user_id}
            $active_sql
            ORDER BY created_at DESC
            LIMIT $num OFFSET $offset
        ";

		$results = $wpdb->get_results( $sql, OBJECT ); // phpcs:ignore

		return $results;
	}

	/**
	 * Mark active notifications as read
	 *
	 * @since   1.5.0
	 * @version 1.8.4
	 */
	public function mark_read() {
		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_nonce', 'nonce' );

		global $wpdb;

		$user_id = get_current_user_id();

		// Check if user exists.
		if ( ! $user_id || ! get_userdata( $user_id ) ) {
			wp_send_json_error(
				[
					'success' => false,
					'message' => 'User not found.',
				]
			);
			wp_die();
		}

		// Prepare the query.
		$query = "
			UPDATE {$wpdb->prefix}cariera_notifications
			SET active = 0
			WHERE owner_id = %d
		";

		// Execute the query.
		$result = $wpdb->query( $wpdb->prepare( $query, $user_id ) ); // phpcs:ignore

		// Check if the query was successful.
		if ( false === $result ) {
			wp_send_json_error(
				[
					'success' => false,
					'message' => 'Failed to mark notifications as read.',
				]
			);
			wp_die();
		}

		// Send success response.
		wp_send_json_success(
			[
				'success' => true,
			]
		);

		wp_die();
	}

	/**
	 * Delete old notifications
	 *
	 * @since 1.5.0
	 */
	public function delete_notifications() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}cariera_notifications WHERE active = 0" );
	}

	/**
	 * Create the notifications output
	 *
	 * @since   1.5.0
	 * @version 1.9.8
	 *
	 * @param int $page
	 */
	public function output( $page = 1 ) {
		$job_dashboard_page_id  = get_option( 'job_manager_job_dashboard_page_id' );
		$notifications_per_page = 10; // Number of notifications per page.
		$offset                 = ( $page - 1 ) * $notifications_per_page;

		$notifications = $this->get_latest_notifications( null, $notifications_per_page, $offset );

		if ( ! $notifications ) {
			return '';
		}

		// Prime post cache - loads all posts in 1 query instead of 10.
		$post_ids = array_column( $notifications, 'post_id' );
		_prime_post_caches( $post_ids, false, true );

		ob_start();
		echo '<ul class="cariera-notifications">';

		foreach ( $notifications as $notification ) {
			do_action( 'cariera_before_notification_output', $notification );

			$post = get_post( $notification->post_id );

			if ( ! $post ) {
				continue; // Skip if post is invalid.
			}

			// Can be moved in different function if it gets to much.
			$action = $notification->action;
			switch ( $action ) {
				case 'job_application':
					$job_id     = wp_get_post_parent_id( $notification->post_id );
					$action_url = $job_dashboard_page_id ? htmlspecialchars_decode(
						add_query_arg(
							[
								'action' => 'show_applications',
								'job_id' => $job_id,
							],
							get_permalink( $job_dashboard_page_id )
						)
					) : '';

					break;

				default:
					$action_url = ( 'publish' === $post->post_status ) ? get_permalink( $post ) : '#';
					break;
			}

			// Prepare notification details.
			$args = [
				'post'       => $post,
				'action'     => $action,
				'post_title' => get_the_title( $notification->post_id ),
				'post_url'   => $action_url,
				'user_url'   => get_author_posts_url( $notification->user_id ),
				'active'     => $notification->active ? 'active' : '',
				'time'       => date_i18n( get_option( 'date_format' ), strtotime( $notification->created_at ) ),
			];

			// Render the notification item with arguments passed.
			$this->render_notification_item( $args );

			do_action( 'cariera_after_notification_output', $notification );
		}

		echo '</ul>';

		// Add the "Load More" button.
		if ( count( $notifications ) === $notifications_per_page ) {
			?>
			<div class="loader-wrapper">
				<button id="load-more" class="btn btn-main notification-loader" data-page="<?php echo esc_attr( $page + 1 ); ?>"><?php esc_html_e( 'Load More', 'cariera-core' ); ?></button>
			</div>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Renders a notification item as an HTML list item.
	 *
	 * @since   1.8.3
	 * @version 1.8.4
	 *
	 * @param array $args The notification details: action, post_url, post_title, time, and active state.
	 */
	private function render_notification_item( $args ) {
		$icon    = $this->get_notification_icon( $args['action'] );
		$message = $this->get_notification_message( $args['post'], $args['action'], $args['post_title'] );

		$notification_classes = [ 'notification', 'notification-' . $args['action'], $args['active'] ];

		?>
		<li class="<?php echo esc_attr( join( ' ', $notification_classes ) ); ?>">
			<a href="<?php echo esc_url( $args['post_url'] ); ?>">
				<div class="notification-icon">
					<i class="<?php echo esc_attr( $icon ); ?>"></i>
				</div>
				<div class="notification-content">
					<span class="action"><?php echo wp_kses_post( $message ); ?></span>
					<span class="time"><?php echo esc_html( $args['time'] ); ?></span>
				</div>
			</a>
		</li>
		<?php
	}

	/**
	 * Retrieves the icon class for a given notification action.
	 *
	 * @since 1.8.3
	 *
	 * @param string $action The notification action.
	 * @return string The icon class for the notification.
	 */
	private function get_notification_icon( $action ) {
		// Define default icons for each action.
		$icons = apply_filters(
			'cariera_notification_icons',
			[
				'listing_created'         => 'las la-layer-group',
				'listing_pending'         => 'las la-layer-group',
				'listing_pending_payment' => 'las la-layer-group',
				'listing_approved'        => 'las la-check-circle',
				'listing_expired'         => 'las la-clock',
				'listing_relisted'        => 'las la-sync',
				'listing_deleted'         => 'las la-trash',
				'job_application'         => 'las la-pencil-alt',
				'listing_promoted'        => 'las la-bolt',
				'promotion_expired'       => 'las la-clock',
			]
		);

		return isset( $icons[ $action ] ) ? $icons[ $action ] : 'las la-info-circle';
	}

	/**
	 * Retrieves the message for a given notification action.
	 *
	 * @since 1.8.3
	 *
	 * @param WP_POST $post
	 * @param string  $action     The notification action.
	 * @param string  $post_title The title of the associated post.
	 * @return string The message associated with the notification action.
	 */
	private function get_notification_message( $post, $action, $post_title ) {
		$messages = apply_filters(
			'cariera_notification_messages',
			[
				'listing_created'         => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Listing %s has been published.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
				'listing_pending'         => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Your listing %s is pending for approval.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
				'listing_pending_payment' => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Your listing %s has been created, payment approval might be required.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
				'listing_approved'        => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Your listing %s has been approved.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
				'listing_expired'         => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Your listing %s has expired.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
				'listing_relisted'        => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Your listing %s has been relisted.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
				'listing_deleted'         => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Your listing %s has been deleted.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
				'job_application'         => sprintf(
					/* translators: %1$s is the title of the job, %2$s is the title of the job application */
					esc_html__( '%1$s applied to your job %2$s.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>',
					'<strong>' . esc_html( get_the_title( $post->post_parent ) ) . '</strong>'
				),
				'listing_promoted'        => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Your listing %s has been promoted.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
				'promotion_expired'       => sprintf(
					/* translators: %s is the title of the listing */
					esc_html__( 'Your promotion for %s has expired.', 'cariera-core' ),
					'<strong>' . esc_html( $post_title ) . '</strong>'
				),
			],
			$action,
			$post_title
		);

		return isset( $messages[ $action ] ) ? $messages[ $action ] : esc_html__( 'Notification message not defined.', 'cariera-core' );
	}

	/**
	 * Load more notifcation ajax function
	 *
	 * @since   1.8.3
	 * @version 1.9.8
	 */
	public function load_more_notifications() {
		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_nonce', 'nonce' );

		// Get the page number from AJAX request.
		$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

		// Adjust the number of notifications per page and offset.
		$notifications_per_page = 10;
		$offset                 = ( $page - 1 ) * $notifications_per_page;

		// Fetch notifications with the specified page and offset.
		$notifications = $this->get_latest_notifications( get_current_user_id(), $notifications_per_page, $offset );

		if ( ! empty( $notifications ) ) {
			$post_ids = array_column( $notifications, 'post_id' );
			_prime_post_caches( $post_ids, false, true );

			ob_start();

			foreach ( $notifications as $notification ) {
				$post = get_post( $notification->post_id );

				if ( ! $post ) {
					continue;
				}

				$args = [
					'post'       => $post,
					'action'     => $notification->action,
					'post_title' => get_the_title( $notification->post_id ),
					'post_url'   => get_permalink( $notification->post_id ),
					'user_url'   => get_author_posts_url( $notification->user_id ),
					'active'     => $notification->active ? 'active' : '',
					'time'       => date_i18n( get_option( 'date_format' ), strtotime( $notification->created_at ) ),
				];

				$this->render_notification_item( $args );
			}

			// Output only new notifications HTML.
			$new_notifications_html = ob_get_clean();

			// Check if more notifications are available.
			$more_notifications = count( $notifications ) === $notifications_per_page;

			// Prepare response.
			$response = [
				'html' => $new_notifications_html,
				'more' => $more_notifications,
				'page' => $page + 1, // Next page number.
			];

			// Return JSON response.
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( [ 'message' => 'No more notifications available.' ] );
		}

		wp_die();
	}

	/**
	 * Listing Statuses
	 *
	 * @since   1.5.0
	 * @version 1.8.3
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param mixed  $post
	 */
	public function listing_post_status( $new_status, $old_status, $post ) {
		// Check if notifications are enabled.
		if ( ! get_option( 'cariera_notifications' ) || ! get_option( 'cariera_notification_listing_status' ) ) {
			return;
		}

		$post_types = apply_filters( 'cariera_notification_post_types', [ 'job_listing', 'company', 'resume' ] );

		// Return if the post type is not in the allowed list.
		if ( ! in_array( get_post_type( $post->ID ), $post_types, true ) ) {
			return;
		}

		$action = '';

		// Determine the action based on the old and new post statuses.
		$status_actions = [
			// Old listing status.
			'publish'         => [
				'pending_payment' => 'listing_pending_payment',
				'pending'         => 'listing_pending',
				'expired'         => 'listing_expired',
				'trash'           => 'listing_deleted',
			],
			'preview'         => [
				'publish'         => 'listing_created',
				'pending_payment' => 'listing_pending_payment',
				'pending'         => 'listing_pending',
				'expired'         => 'listing_expired',
			],
			'pending'         => [
				'publish' => 'listing_approved',
				'expired' => 'listing_expired',
				'pending' => 'listing_pending',
				'trash'   => 'listing_deleted',
			],
			'pending_payment' => [
				'publish' => 'listing_approved',
				'expired' => 'listing_expired',
				'pending' => 'listing_pending',
				'trash'   => 'listing_deleted',
			],
			'expired'         => [
				'publish' => 'listing_relisted',
				'expired' => 'listing_expired',
				'trash'   => 'listing_deleted',
			],
			'trash'           => [
				'publish' => 'listing_relisted',
			],
			'draft'           => [
				'publish' => 'listing_relisted',
			],
		];

		// Apply filter to allow modification of status actions.
		$status_actions = apply_filters( 'cariera_notification_status_actions', $status_actions );

		// Lookup the action.
		$action = isset( $status_actions[ $old_status ][ $new_status ] ) ? $status_actions[ $old_status ][ $new_status ] : '';

		// Exit if no action is determined or if the post is a revision.
		if ( empty( $action ) || wp_is_post_revision( $post->ID ) ) {
			return;
		}

		$owner = get_post_field( 'post_author', $post->ID );

		// Insert the notification into the database.
		$this->insert_notification(
			[
				'action'   => $action,
				'owner_id' => $owner,
				'user_id'  => '', // Assuming user_id can be empty; adjust if necessary.
				'post_id'  => $post->ID,
			]
		);

		// Check if webhooks are enabled and send if necessary.
		if ( get_option( 'cariera_notification_listing_status_webhook' ) ) {
			$this->send_webhook( $action, $owner, $post->ID );
		}
	}

	/**
	 * Add Application Notification
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 *
	 * @param int $post_id
	 */
	public function application_notification( $post_id ) {
		if ( ! get_option( 'cariera_notifications' ) ) {
			return;
		}

		if ( ! get_option( 'cariera_notification_application' ) ) {
			return;
		}

		$owner = get_post_field( 'post_author', $post_id );

		$this->insert_notification(
			[
				'action'   => 'job_application',
				'owner_id' => $owner,
				'user_id'  => get_current_user_id(),
				'post_id'  => $post_id,
			]
		);

		// Check if webhooks are enabled.
		if ( get_option( 'cariera_notification_application_webhook' ) ) {
			$this->send_webhook( 'job_application', $owner, $post_id );
		}
	}

	/**
	 * Add Promotion Notification
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 *
	 * @param int $post_id
	 */
	public function listing_promoted_notification( $post_id ) {
		if ( ! get_option( 'cariera_notifications' ) ) {
			return;
		}

		if ( ! get_option( 'cariera_notification_listing_promotion' ) ) {
			return;
		}

		$this->insert_notification(
			[
				'action'   => 'listing_promoted',
				'owner_id' => get_post_field( 'post_author', $post_id ),
				'user_id'  => '',
				'post_id'  => $post_id,
			]
		);

		// $this->send_webhook( 'listing_promoted', $post_id );
	}

	/**
	 * Add Promotion Notification
	 *
	 * @since   1.5.0
	 * @version 1.6.0
	 *
	 * @param int $post_id
	 */
	public function promotion_expired_notification( $post_id ) {
		if ( ! get_option( 'cariera_notifications' ) ) {
			return;
		}

		if ( ! get_option( 'cariera_notification_listing_promotion_ended' ) ) {
			return;
		}

		$owner = get_post_field( 'post_author', $post_id );

		$this->insert_notification(
			[
				'action'   => 'promotion_expired',
				'owner_id' => $owner,
				'user_id'  => '',
				'post_id'  => $post_id,
			]
		);

		// Check if webhooks are enabled.
		if ( get_option( 'cariera_notification_listing_promotion_webhook' ) ) {
			$this->send_webhook( 'promotion_expired', $owner, $post_id );
		}
	}

	/**
	 * Send webhook
	 *
	 * @since   1.5.2
	 * @version 1.8.4
	 *
	 * @param array $action
	 * @param int   $user_id
	 * @param int   $post_id
	 */
	public function send_webhook( $action, $user_id = 0, $post_id = 0 ) {

		// Get the webhook URL from the options.
		$webhook_url = get_option( sprintf( 'cariera_notification_webhook_url_%s', sanitize_text_field( $action ) ) );

		// Validate webhook URL.
		if ( empty( $webhook_url ) || ! filter_var( $webhook_url, FILTER_VALIDATE_URL ) ) {
			return; // Exit early if the webhook URL is invalid.
		}

		// Get user data.
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		// Get listing details (if available).
		$listing_title = ! empty( $post_id ) ? get_the_title( $post_id ) : '';
		$listing_url   = ! empty( $post_id ) ? get_permalink( $post_id ) : '';

		$data = [
			// User Data.
			'email'              => $user->user_email,
			'name'               => $user->display_name,
			'first_name'         => $user->first_name ?? '',
			'last_name'          => $user->last_name ?? '',
			'phone'              => $user->phone ?? '',
			'billing_first_name' => $user->billing_first_name ?? '',
			'billing_last_name'  => $user->billing_last_name ?? '',
			'billing_email'      => $user->billing_email ?? '',
			'billing_phone'      => $user->billing_phone ?? '',
			'billing_country'    => $user->billing_country ?? '',
			'billing_city'       => $user->billing_city ?? '',
			'billing_postcode'   => $user->billing_postcode ?? '',
			// Listing.
			'listing_id'         => $post_id,
			'listing_title'      => $listing_title,
			'listing_url'        => $listing_url,
		];

		// Send the request using wp_remote_post.
		$response = wp_remote_post(
			$webhook_url,
			[
				'body'    => wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'timeout' => 15, // Timeout after 15 seconds.
			]
		);

		// Handle response errors.
		if ( is_wp_error( $response ) ) {
			\Cariera\write_log( 'Webhook request failed: ' . $response->get_error_message() );
			return;
		}

		// Optionally, log the response or take further action.
		$response_body = wp_remote_retrieve_body( $response );
	}

	/**
	 * Webhook test trigger AJAX
	 *
	 * @since   1.5.2
	 * @version 1.8.4
	 */
	public function webhook_test_trigger() {
		$webhook_id  = sanitize_text_field( wp_unslash( $_POST['webhook_id'] ?? '' ) ); // phpcs:ignore
		$webhook_url = esc_url_raw( wp_unslash( $_POST['webhook_url'] ?? '' ) ); // phpcs:ignore

		// Validate webhook URL.
		if ( empty( $webhook_url ) || ! filter_var( $webhook_url, FILTER_VALIDATE_URL ) ) {
			wp_send_json_error( 'Invalid webhook URL.' );
			return;
		}

		// Test Data.
		$data = [
			// User Data.
			'email'              => 'example@cariera.cc',
			'name'               => 'John Doe',
			'first_name'         => 'John',
			'last_name'          => 'Doe',
			'phone'              => '999 999 999',
			'billing_first_name' => 'John',
			'billing_last_name'  => 'Doe',
			'billing_email'      => 'example@cariera.cc',
			'billing_phone'      => '999 999 999',
			'billing_country'    => 'DE',
			'billing_city'       => 'Berlin',
			'billing_postcode'   => '10115',
			// Listing.
			'listing_id'         => '1',
			'listing_title'      => 'Web Designer Listing',
			'listing_url'        => 'https://example.com',
		];

		// Make a POST request to the webhook URL.
		$response = wp_remote_post(
			$webhook_url,
			[
				'body'    => wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'timeout' => 15, // Adjust the timeout as necessary.
			]
		);

		// Check for errors in the response.
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( 'Request failed: ' . $response->get_error_message() );
			return;
		}

		// Parse the response body.
		$response_body = wp_remote_retrieve_body( $response );

		// Send the successful response.
		wp_send_json_success(
			[
				'message' => esc_html__( 'Request has been sent successfully!', 'cariera-core' ),
				'output'  => $response_body,
			]
		);
	}

	/**
	 * Delete all notifications from the DB Table via AJAX
	 *
	 * @since   1.5.6
	 * @version 1.8.4
	 */
	public function delete_all_notifications() {
		global $wpdb;

		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}cariera_notifications" );

		wp_send_json_success( esc_html__( 'All notifications have been deleted!', 'cariera-core' ) );
	}
}
