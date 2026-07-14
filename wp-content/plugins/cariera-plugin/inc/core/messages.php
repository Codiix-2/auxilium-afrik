<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Messages {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Only trigger email notifications after a set time has
	 * passed since the last message.
	 *
	 * @var integer delay in seconds
	 */
	protected $notification_send_delay = 15 * MINUTE_IN_SECONDS;

	/**
	 * Number of conversations to load per lazy load request.
	 *
	 * @var int
	 */
	protected $lazy_load_conversations_limit = 5;

	/**
	 * Post types that can be messaged.
	 *
	 * @var array
	 */
	protected $post_types = [ 'job_listing', 'resume', 'company' ];

	/**
	 * Message Database Table Name
	 *
	 * @var string
	 */
	private $db_message_table = null;

	/**
	 * Conversation Database Table Name
	 *
	 * @var string
	 */
	private $db_conversation_table = null;

	/**
	 * Allowed file types for media uploading
	 *
	 * @var array
	 */
	private $allowed_file_types = [
		'image' => [ 'jpg', 'jpeg', 'png', 'gif' ],
		'doc'   => [ 'pdf', 'doc', 'docx' ],
	];

	/**
	 * Max file size for media uploading
	 *
	 * @var int
	 */
	private $max_file_size = 5242880; // 5MB in bytes

	/**
	 * Constructor
	 */
	public function __construct() {
		// Do nothing if the message system is disabled.
		if ( ! get_option( 'cariera_private_messages' ) ) {
			return;
		}

		global $wpdb;

		// Set Database table names.
		$this->db_message_table      = $GLOBALS['wpdb']->prefix . 'cariera_messages';
		$this->db_conversation_table = $GLOBALS['wpdb']->prefix . 'cariera_conversations';

		// Ajax functions.
		add_action( 'wp_ajax_cariera_get_unread_count', [ $this, 'get_unread_count' ] );
		add_action( 'wp_ajax_cariera_get_conversations', [ $this, 'get_conversations' ] );
		add_action( 'wp_ajax_cariera_get_user_message', [ $this, 'get_messages' ] );
		add_action( 'wp_ajax_cariera_send_message', [ $this, 'send_message' ] );
		add_action( 'wp_ajax_cariera_delete_conversation', [ $this, 'delete_conversation' ] );
		add_action( 'wp_ajax_cariera_block_user', [ $this, 'block_user' ] );
		add_action( 'wp_ajax_cariera_unblock_user', [ $this, 'unblock_user' ] );
		add_action( 'wp_ajax_cariera_check_block_status', [ $this, 'check_user_block_status' ] );
		add_action( 'wp_ajax_cariera_user_search', [ $this, 'user_search' ] );
		add_action( 'wp_ajax_cariera_upload_message_media', [ $this, 'upload_message_media' ] );

		// Send alert when message has been successfully sent.
		add_action( 'cariera_private_message_sent_successfully', [ $this, '_send_alert' ] );

		// Clear messages & conversations DB Table.
		add_action( 'wp_ajax_cariera_delete_all_messages', [ $this, 'delete_all_messages' ] );

		// On user account delete.
		add_action( 'delete_user', [ $this, 'user_data_account_delete' ] );

		// Enqueue assets & load templates.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_footer', [ $this, 'load_templates' ] );
	}

	/**
	 * Load Messages assets.
	 *
	 * @since   1.6.0
	 * @version 1.9.0
	 */
	public function enqueue_scripts() {
		// Do not load script files if the user is not logged in.
		if ( ! is_user_logged_in() ) {
			return;
		}

		$suffix = is_rtl() ? '.rtl' : '';
		wp_enqueue_style( 'cariera-core-messages', CARIERA_URL . '/assets/dist/css/messages' . $suffix . '.css', [], CARIERA_CORE_VERSION );
		wp_enqueue_script( 'cariera-core-messages', CARIERA_URL . '/assets/dist/js/messages.js', [ 'jquery' ], CARIERA_CORE_VERSION, [ 'strategy' => 'defer' ] );

		$args = [
			'ajax_url'                      => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
			'nonce'                         => wp_create_nonce( 'cariera_messages_nonce' ),
			'login_user_id'                 => get_current_user_id(),
			'avatar_url'                    => get_avatar_url( get_current_user_id() ),
			'default_avatar'                => get_avatar_url( 0 ),
			'autoload_interval'             => ! empty( get_option( 'cariera_private_messages_autoload_interval' ) ) ? absint( get_option( 'cariera_private_messages_autoload_interval' ) ) : '10000',
			'lazy_load_conversations_limit' => $this->lazy_load_conversations_limit,
			'max_file_size'                 => $this->max_file_size,
			'allowed_types'                 => $this->allowed_file_types,
			'strings'                       => [
				'loading'             => esc_html__( 'Loading...', 'cariera-core' ),
				'sending_time'        => esc_html__( 'a few seconds ago', 'cariera-core' ),
				'load_conversations'  => esc_html__( 'Load more conversations', 'cariera-core' ),
				'delete_conversation' => esc_html__( 'Are you sure want to delete this conversation?', 'cariera-core' ),
				'yes'                 => esc_html__( 'Yes', 'cariera-core' ),
				'no'                  => esc_html__( 'No', 'cariera-core' ),
				'please_wait'         => esc_html__( 'Please wait...', 'cariera-core' ),
				'block_msg'           => esc_html__( 'Are you sure you want to block this user?', 'cariera-core' ),
				'unblock_msg'         => esc_html__( 'Are you sure you want to unblock this user?', 'cariera-core' ),
				'user_block_msg'      => esc_html__( 'This user has been blocked!', 'cariera-core' ),
				'user_blocked_msg'    => esc_html__( 'You have been blocked and can not message this user anymore!', 'cariera-core' ),
				'user_not_found'      => esc_html__( 'No users found!', 'cariera-core' ),
				'no_more_messages'    => esc_html__( 'No more messages to load!', 'cariera-core' ),
				'file_too_large'      => esc_html__( 'File size exceeds maximum limit of 5MB', 'cariera-core' ),
				'invalid_file_type'   => esc_html__( 'Invalid file type. Allowed: images and documents', 'cariera-core' ),
				'uploading'           => esc_html__( 'Uploading attachment...', 'cariera-core' ),
				'uploading_wait'      => esc_html__( 'Please wait for upload to complete.', 'cariera-core' ),
				'upload_failed'       => esc_html__( 'Failed to upload file.', 'cariera-core' ),
				'upload_success'      => esc_html__( 'File uploaded successfully.', 'cariera-core' ),
				'msg_seen'            => esc_html__( '✓✓ Seen', 'cariera-core' ),
			],
		];

		wp_localize_script( 'cariera-core-messages', 'cariera_messages', $args );
	}

	/**
	 * Load Messages template.
	 *
	 * @since   1.6.0
	 * @version 1.7.2
	 */
	public function load_templates() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		cariera_get_template_part( 'private-messages' );
	}

	/**
	 * Get the unread message count for a user via AJAX
	 *
	 * @since   1.9.0
	 * @version 1.9.5
	 */
	public function get_unread_count() {
		$this->verify_nonce_or_die();

		// Check for required user_id parameter.
		if ( ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( 'Missing user_id' );
			return;
		}

		global $wpdb;

		$user_id = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );

		// Query to count distinct conversations with unread messages.
		$unread_conversations = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT COUNT(DISTINCT from_id, listing_id)
				FROM {$this->db_message_table}
				WHERE to_id = %d AND seen = 0
				",
				$user_id
			)
		);

		// Return success response with unread count.
		wp_send_json_success( [ 'unread_count' => (int) $unread_conversations ] );
	}

	/**
	 * Get Conversations
	 *
	 * @since   1.6.0
	 * @version 1.9.5
	 */
	public function get_conversations() {
		$this->verify_nonce_or_die();

		if ( ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( 'Missing user_id' );
			return;
		}

		global $wpdb;

		$user_id  = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
		$start_id = isset( $_POST['start_id'] ) ? absint( wp_unslash( $_POST['start_id'] ) ) : 0;
		$last_id  = isset( $_POST['last_id'] ) ? absint( wp_unslash( $_POST['last_id'] ) ) : 0;

		// Assume status 1 represents deleted conversations.
		$deleted_status = 1;

		// Modify the query to use proper pagination.
		$query = "SELECT id, user_id, friend_id, listing_id, created_at 
				FROM {$this->db_conversation_table} 
				WHERE ((user_id = %d OR friend_id = %d) AND status != %d)";

		if ( $start_id > 0 ) {
			$query .= ' AND id < %d';
		}
		$query .= ' ORDER BY id DESC LIMIT %d';

		// Prepare parameters.
		$params = [ $user_id, $user_id, $deleted_status ];
		if ( $start_id > 0 ) {
			$params[] = $start_id;
		}
		$params[] = $this->lazy_load_conversations_limit;

		// Execute query.
		$friend_list = $wpdb->get_results( $wpdb->prepare( $query, $params ) );

		$newarr = [];
		foreach ( $friend_list as $friend ) {
			$other_user_id = ( $friend->user_id == $user_id ) ? $friend->friend_id : $friend->user_id;
			$other_user    = get_user_by( 'ID', $other_user_id );
			if ( ! $other_user ) {
				continue;
			}

			// Fetch the last message with last_id filter.
			$last_message_query = "SELECT id, message, from_id, to_id, seen, created_at 
                       FROM {$this->db_message_table} 
                       WHERE ((from_id = %d AND to_id = %d) OR (from_id = %d AND to_id = %d)) 
                       AND listing_id = %d";
			if ( $last_id > 0 ) {
				$last_message_query .= ' AND id > %d';
			}
			$last_message_query .= ' ORDER BY id DESC LIMIT 1';
			$params              = [ $other_user_id, $user_id, $user_id, $other_user_id, $friend->listing_id ];
			if ( $last_id > 0 ) {
				$params[] = $last_id;
			}
			$last_message = $wpdb->get_results( $wpdb->prepare( $last_message_query, ...$params ) );

			$listing_name   = $friend->listing_id ? get_post( $friend->listing_id )->post_title : '';
			$listing_url    = $friend->listing_id ? get_permalink( $friend->listing_id ) : '';
			$listing_avatar = $friend->listing_id ? $this->listing_avatar( $friend->listing_id ) : '';

			foreach ( $last_message as $last ) {
				$newarr[] = [
					'id'                 => $friend->id,
					'message_id'         => $last->id,
					'message'            => $last->message,
					'from_id'            => $last->from_id,
					'user_id'            => $other_user->ID,
					'display_name'       => $other_user->display_name,
					'seen'               => $last->seen,
					'created_at'         => human_time_diff( strtotime( $last->created_at ), current_time( 'timestamp' ) ) . esc_html__( ' ago', 'cariera-core' ),
					'avatar_url'         => get_avatar_url( $other_user->ID ),
					'listing_id'         => $friend->listing_id,
					'listing_name'       => $listing_name,
					'listing_url'        => $listing_url,
					'listing_avatar_url' => $listing_avatar,
					'sender_id'          => $friend->user_id,
					'last_message_time'  => $last->created_at,
				];
			}
		}

		wp_send_json( $newarr );
	}

	/**
	 * Get Messages
	 *
	 * @since   1.6.0
	 * @version 1.9.5
	 */
	public function get_messages() {
		$this->verify_nonce_or_die();

		if ( ! isset( $_POST['from'] ) || ! isset( $_POST['to'] ) ) {
			wp_send_json_error( 'Missing from or to parameter' );
			return;
		}

		global $wpdb;

		$from       = sanitize_text_field( wp_unslash( $_POST['from'] ) );
		$to         = sanitize_text_field( wp_unslash( $_POST['to'] ) );
		$listing_id = sanitize_text_field( wp_unslash( $_POST['listing_id'] ) );
		$start_id   = isset( $_POST['start_id'] ) ? absint( wp_unslash( $_POST['start_id'] ) ) : 0;
		$last_id    = isset( $_POST['last_id'] ) ? absint( wp_unslash( $_POST['last_id'] ) ) : 0;

		// Mark messages as seen.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->db_message_table} SET seen = 1 WHERE to_id = %d AND from_id = %d AND listing_id = %d",
				$from,
				$to,
				$listing_id
			)
		);

		$from_avatar = get_avatar_url( $from );
		$to_avatar   = get_avatar_url( $to );

		$query_base = "SELECT * FROM {$this->db_message_table} 
               WHERE ((from_id = %d AND to_id = %d) OR (from_id = %d AND to_id = %d)) AND listing_id = %d";

		if ( $last_id > 0 ) {
			// Fetch newer messages (for auto-load).
			$query    = $wpdb->prepare(
				"$query_base AND id > %d ORDER BY id ASC",
				$from,
				$to,
				$to,
				$from,
				$listing_id,
				$last_id
			);
			$messages = $wpdb->get_results( $query );
		} elseif ( $start_id > 0 ) {
			// Fetch older messages (for scroll-to-load).
			$query    = $wpdb->prepare(
				"SELECT * FROM (
					SELECT * FROM {$this->db_message_table} 
					WHERE ((from_id = %d AND to_id = %d) OR (from_id = %d AND to_id = %d)) AND listing_id = %d AND id < %d 
					ORDER BY id DESC LIMIT 10
				) AS sub ORDER BY id ASC",
				$from,
				$to,
				$to,
				$from,
				$listing_id,
				$start_id
			);
			$messages = $wpdb->get_results( $query );
		} else {
			// Initial load: Fetch the 10 most recent messages.
			$query    = $wpdb->prepare(
				"SELECT * FROM (
					SELECT * FROM {$this->db_message_table} 
					WHERE ((from_id = %d AND to_id = %d) OR (from_id = %d AND to_id = %d)) AND listing_id = %d 
					ORDER BY id DESC LIMIT 10
				) AS sub ORDER BY id ASC",
				$from,
				$to,
				$to,
				$from,
				$listing_id
			);
			$messages = $wpdb->get_results( $query );
		}

		foreach ( $messages as $key => $message ) {
			$messages[ $key ]->created_at = human_time_diff( strtotime( $message->created_at ), current_time( 'timestamp' ) ) . esc_html__( ' ago', 'cariera-core' );
			$messages[ $key ]->avatar_url = ( $message->from_id == $from ) ? $from_avatar : $to_avatar;

			if ( $message->media_id ) {
				$messages[ $key ]->media_url  = wp_get_attachment_url( $message->media_id );
				$messages[ $key ]->media_type = wp_check_filetype( get_attached_file( $message->media_id ) )['ext'];
			}
		}

		wp_send_json( $messages );
	}

	/**
	 * Send Message function
	 *
	 * @since   1.6.0
	 * @version 1.9.5
	 */
	public function send_message() {
		$this->verify_nonce_or_die();

		if ( ! isset( $_POST['from'] ) || ! isset( $_POST['to'] ) || ! isset( $_POST['message'] ) || empty( $_POST['message'] ) ) {
			wp_send_json_error( 'Missing required parameters' );
			return;
		}

		global $wpdb;

		$from       = sanitize_text_field( wp_unslash( $_POST['from'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$to         = sanitize_text_field( wp_unslash( $_POST['to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$listing_id = sanitize_text_field( wp_unslash( $_POST['listing_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$message    = sanitize_text_field( wp_unslash( $_POST['message'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$msg_uid    = sanitize_text_field( wp_unslash( $_POST['msg_uid'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$media_id   = isset( $_POST['media_id'] ) ? absint( $_POST['media_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $listing_id && get_post_field( 'post_author', $listing_id ) == $from && $from == $to ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => esc_html__( 'You cannot send a message to yourself!', 'cariera-core' ),
				]
			);
			return;
		}
		// Manage conversation.
		$conv_query = "SELECT * FROM {$this->db_conversation_table} WHERE (user_id = %d AND friend_id = %d AND listing_id = %d) OR (user_id = %d AND friend_id = %d AND listing_id = %d)";
		$conv       = $wpdb->get_results( $wpdb->prepare( $conv_query, $from, $to, $listing_id, $to, $from, $listing_id ) );
		if ( empty( $conv ) ) {
			$wpdb->insert(
				$this->db_conversation_table,
				[
					'user_id'    => $from,
					'friend_id'  => $to,
					'listing_id' => $listing_id,
					'status'     => 0,
					'created_at' => current_time( 'mysql' ),
				]
			);
		}

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->db_conversation_table} SET status = 0, created_at = %s WHERE (user_id = %d AND friend_id = %d AND listing_id = %d) OR (user_id = %d AND friend_id = %d AND listing_id = %d)",
				current_time( 'mysql' ),
				$from,
				$to,
				$listing_id,
				$to,
				$from,
				$listing_id
			)
		);

		// Check block status.
		$sender_blocks   = get_user_meta( $from, 'block_user', true );
		$receiver_blocks = get_user_meta( $to, 'block_user', true );
		if ( ( $sender_blocks && in_array( $to, explode( ',', $sender_blocks ), true ) ) ||
			( $receiver_blocks && in_array( $from, explode( ',', $receiver_blocks ), true ) ) ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => esc_html__( 'You are blocked from sending messages to this user.', 'cariera-core' ),
				]
			);
			return;
		}

		// Insert message.
		$args = [
			'from_id'    => $from,
			'to_id'      => $to,
			'listing_id' => $listing_id,
			'message'    => $message,
			'media_id'   => $media_id,
			'created_at' => current_time( 'mysql' ),
		];

		$insert_id = $wpdb->insert( $this->db_message_table, $args );

		if ( false === $insert_id ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => esc_html__( 'Failed to send message.', 'cariera-core' ),
				]
			);
			return;
		}

		$response = [
			'status'            => 'success',
			'message_unique_id' => $msg_uid,
			'message_id'        => $wpdb->insert_id,
		];

		if ( $media_id ) {
			$response['media_url']  = wp_get_attachment_url( $media_id );
			$response['media_type'] = wp_check_filetype( get_attached_file( $media_id ) )['ext'];
		}

		do_action( 'cariera_private_message_sent_successfully', $args );
		wp_send_json( $response );
	}

	/**
	 * Upload media message
	 *
	 * @since   1.9.0
	 * @version 1.9.5
	 */
	public function upload_message_media() {
		$this->verify_nonce_or_die();

		do_action( 'cariera_before_message_media_upload' );

		if ( ! isset( $_FILES['media_file'] ) ) {
			wp_send_json_error( 'No file uploaded' );
			return;
		}

		if ( $_FILES['media_file']['size'] > $this->max_file_size ) {
			wp_send_json_error( 'File too large' );
			return;
		}

		$file_type = wp_check_filetype( $_FILES['media_file']['name'] );
		$allowed   = false;
		foreach ( $this->allowed_file_types as $type => $exts ) {
			if ( in_array( strtolower( $file_type['ext'] ), $exts ) ) {
				$allowed = true;
				break;
			}
		}

		if ( ! $allowed ) {
			wp_send_json_error( 'Invalid file type' );
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$uploaded_file = wp_handle_upload(
			$_FILES['media_file'],
			[
				'test_form' => false,
				'mimes'     => [
					'jpg'  => 'image/jpeg',
					'jpeg' => 'image/jpeg',
					'png'  => 'image/png',
					'gif'  => 'image/gif',
					'pdf'  => 'application/pdf',
					'doc'  => 'application/msword',
					'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				],
			]
		);

		if ( isset( $uploaded_file['error'] ) ) {
			wp_send_json_error( $uploaded_file['error'] );
			return;
		}

		$attachment = [
			'guid'           => $uploaded_file['url'],
			'post_mime_type' => $uploaded_file['type'],
			'post_title'     => sanitize_file_name( $_FILES['media_file']['name'] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		$attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );

		if ( is_wp_error( $attach_id ) ) {
			wp_send_json_error( 'Failed to save attachment' );
			return;
		}

		if ( in_array( $file_type['ext'], $this->allowed_file_types['image'], true ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] ) );
		}

		wp_send_json_success(
			[
				'media_id'  => $attach_id,
				'media_url' => wp_get_attachment_url( $attach_id ),
				'file_type' => $file_type['ext'],
			]
		);
	}

	/**
	 * Delete conversation ajax function
	 *
	 * @since   1.6.0
	 * @version 1.9.5
	 */
	public function delete_conversation() {
		$this->verify_nonce_or_die();

		if ( ! isset( $_POST['user_id'] ) || ! isset( $_POST['delete_user_id'] ) ) {
			wp_send_json_error( 'Missing user_id or delete_user_id' );
			return;
		}

		global $wpdb;

		$user_id        = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
		$delete_user_id = sanitize_text_field( wp_unslash( $_POST['delete_user_id'] ) );
		$listing_id     = sanitize_text_field( wp_unslash( $_POST['listing_id'] ) );

		// Fetch media IDs before deleting messages.
		$media_messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT media_id FROM {$this->db_message_table} 
				WHERE (from_id = %d AND to_id = %d AND listing_id = %d)
				OR (from_id = %d AND to_id = %d AND listing_id = %d)",
				$user_id,
				$delete_user_id,
				$listing_id,
				$delete_user_id,
				$user_id,
				$listing_id
			)
		);

		// Delete media attachments.
		foreach ( $media_messages as $message ) {
			if ( $message->media_id ) {
				// Force delete the attachment.
				wp_delete_attachment( $message->media_id, true );
			}
		}

		// Delete conversation.
		$conv = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->db_conversation_table} 
				WHERE (user_id = %d AND friend_id = %d AND listing_id = %d) 
				OR (user_id = %d AND friend_id = %d AND listing_id = %d)",
				$user_id,
				$delete_user_id,
				$listing_id,
				$delete_user_id,
				$user_id,
				$listing_id
			)
		);

		if ( $conv && $conv->status == 0 ) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$this->db_conversation_table} SET status = %d 
					WHERE (user_id = %d AND friend_id = %d AND listing_id = %d) 
					OR (user_id = %d AND friend_id = %d AND listing_id = %d)",
					$user_id,
					$user_id,
					$delete_user_id,
					$listing_id,
					$delete_user_id,
					$user_id,
					$listing_id
				)
			);
		} elseif ( $conv && $conv->status != $user_id ) {
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$this->db_conversation_table} 
					WHERE (user_id = %d AND friend_id = %d AND listing_id = %d) 
					OR (user_id = %d AND friend_id = %d AND listing_id = %d)",
					$user_id,
					$delete_user_id,
					$listing_id,
					$delete_user_id,
					$user_id,
					$listing_id
				)
			);
		}

		// Delete messages.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->db_message_table} 
				WHERE (from_id = %d AND to_id = %d AND listing_id = %d) 
				OR (from_id = %d AND to_id = %d AND listing_id = %d)",
				$user_id,
				$delete_user_id,
				$listing_id,
				$delete_user_id,
				$user_id,
				$listing_id
			)
		);

		wp_send_json( [ 'status' => 'success' ] );
	}

	/**
	 * Block user ajax function
	 *
	 * @since   1.6.0
	 * @version 1.9.5
	 */
	public function block_user() {
		$this->verify_nonce_or_die();

		if ( ! isset( $_POST['user_id'] ) || ! isset( $_POST['block_user_id'] ) ) {
			wp_send_json_error( 'Missing user_id or block_user_id' );
			return;
		}

		$user_id       = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
		$block_user_id = sanitize_text_field( wp_unslash( $_POST['block_user_id'] ) );
		$blocked_users = get_user_meta( $user_id, 'block_user', true );

		if ( empty( $blocked_users ) ) {
			update_user_meta( $user_id, 'block_user', $block_user_id );
		} else {
			$blocked_ids = explode( ',', $blocked_users );
			if ( ! in_array( $block_user_id, $blocked_ids, true ) ) {
				$blocked_ids[] = $block_user_id;
				update_user_meta( $user_id, 'block_user', implode( ',', $blocked_ids ) );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Unblock user ajax function
	 *
	 * @since   1.6.0
	 * @version 1.9.5
	 */
	public function unblock_user() {
		$this->verify_nonce_or_die();

		if ( ! isset( $_POST['user_id'] ) || ! isset( $_POST['unblock_user_id'] ) ) {
			wp_send_json_error( 'Missing user_id or unblock_user_id' );
			return;
		}

		$user_id         = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
		$unblock_user_id = sanitize_text_field( wp_unslash( $_POST['unblock_user_id'] ) );
		$blocked_users   = get_user_meta( $user_id, 'block_user', true );

		if ( ! empty( $blocked_users ) ) {
			$blocked_ids = explode( ',', $blocked_users );
			if ( ( $key = array_search( $unblock_user_id, $blocked_ids, true ) ) !== false ) {
				unset( $blocked_ids[ $key ] );
				update_user_meta( $user_id, 'block_user', implode( ',', $blocked_ids ) );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Check user block status ajax function
	 *
	 * @since   1.6.0
	 * @version 1.9.5
	 */
	public function check_user_block_status() {
		$this->verify_nonce_or_die();

		if ( ! isset( $_POST['login_user'] ) || ! isset( $_POST['selected_user'] ) ) {
			wp_send_json_error( 'Missing login_user or selected_user' );
			return;
		}

		$login_user    = sanitize_text_field( wp_unslash( $_POST['login_user'] ) );
		$selected_user = sanitize_text_field( wp_unslash( $_POST['selected_user'] ) );

		$sender_blocks   = get_user_meta( $login_user, 'block_user', true );
		$receiver_blocks = get_user_meta( $selected_user, 'block_user', true );

		if ( $sender_blocks && in_array( $selected_user, explode( ',', $sender_blocks ), true ) ) {
			wp_send_json(
				[
					'status' => 'true',
					'id'     => $selected_user,
				]
			);
		} elseif ( $receiver_blocks && in_array( $login_user, explode( ',', $receiver_blocks ), true ) ) {
			wp_send_json(
				[
					'status' => 'true',
					'id'     => $login_user,
				]
			);
		}

		wp_send_json( [ 'status' => 'false' ] );
	}

	/**
	 * Check users ajax function
	 *
	 * @since   1.6.0
	 * @version 1.9.5
	 */
	public function user_search() {
		$this->verify_nonce_or_die();

		if ( ! isset( $_POST['user_search'] ) ) {
			wp_send_json_error( 'Missing search term' );
			return;
		}

		global $wpdb;

		$search = sanitize_text_field( wp_unslash( $_POST['user_search'] ) );
		$users  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, user_login FROM {$wpdb->prefix}users WHERE display_name LIKE %s AND ID != %d",
				'%' . $wpdb->esc_like( $search ) . '%',
				get_current_user_id()
			)
		);

		if ( empty( $users ) ) {
			wp_send_json( [ 'status' => 'empty' ] );
			return;
		}

		$return_list = [];
		foreach ( $users as $user ) {
			$return_list[ $user->ID ] = [
				'id'     => $user->ID,
				'login'  => $user->user_login,
				'avatar' => get_avatar_url( $user->ID ) ?: '',
			];
		}

		wp_send_json( $return_list );
	}

	/**
	 * Delete all user related messages & conversations when user deletes account
	 *
	 * @since   1.6.0
	 * @version 1.6.1
	 *
	 * @param int $user_id
	 */
	public function user_data_account_delete( $user_id ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->db_message_table} WHERE from_id = %d OR to_id = %d",
				$user_id,
				$user_id
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->db_conversation_table} WHERE user_id = %d OR friend_id = %d",
				$user_id,
				$user_id
			)
		);
	}

	/**
	 * Delete all messages and conversations from the DB Table via AJAX
	 *
	 * @since   1.6.0
	 * @version 1.8.4
	 */
	public function delete_all_messages() {
		global $wpdb;

		$wpdb->query( "TRUNCATE TABLE {$this->db_message_table}" );
		$wpdb->query( "TRUNCATE TABLE {$this->db_conversation_table}" );

		wp_send_json_success( esc_html__( 'All messages have been deleted from the database!', 'cariera-core' ) );
	}

	/**
	 * Generating the listings avatar url
	 *
	 * @since   1.6.0
	 * @version 1.7.0
	 *
	 * @param int $post_id
	 */
	private function listing_avatar( $post_id = null ) {
		if ( empty( $post_id ) ) {
			return '';
		}

		$post_type = get_post_type( $post_id );
		$logo      = '';

		if ( function_exists( 'get_the_candidate_photo' ) && 'resume' === $post_type ) {
			$logo = get_the_candidate_photo( $post_id, 'thumbnail' );
		} elseif ( function_exists( 'get_the_company_logo' ) && 'job_listing' === $post_type ) {
			if ( get_option( 'cariera_company_manager_integration', false ) ) {
				$company = cariera_get_the_company( $post_id );
				$logo    = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
			} else {
				$logo = get_the_company_logo( $post_id, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
			}
		} elseif ( 'company' === $post_type ) {
			$logo = get_the_company_logo( $post_id, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
		}

		if ( ! empty( $logo ) ) {
			$logo_img = $logo;
		} elseif ( 'job_listing' === $post_type || 'company' === $post_type ) {
			$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
		} elseif ( 'resume' === $post_type ) {
			$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
		}

		return $logo_img;
	}

	/**
	 * Init action to use for email notification
	 *
	 * @since   1.6.0
	 * @version 1.7.0
	 */
	public function _send_alert( $args ) {
		// Return if email notification has been disabled.
		if ( ! get_option( 'cariera_private_messages_email_notification' ) ) {
			return;
		}

		do_action( 'cariera_private_messages_email_notification', $args );
	}

	/**
	 * Verify nonce
	 *
	 * @since 1.9.5
	 *
	 * @param string $action
	 */
	private function verify_nonce_or_die( $action = 'cariera_messages_nonce' ) {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Security check failed. Please refresh the page.', 'cariera-core' ),
				]
			);
		}
	}
}
