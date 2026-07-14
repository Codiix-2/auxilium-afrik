<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notices {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Add notices.
		add_action( 'admin_init', [ $this, 'add_notices' ] );

		// Dismiss action.
		add_action( 'wp_ajax_cariera_dismiss_notice', [ $this, 'handle_dismiss_action' ] );

		// Display notices.
		add_action( 'admin_notices', [ __CLASS__, 'display_notices' ] );
	}

	/**
	 * Admin Notices
	 *
	 * @since   1.4.8
	 * @version 1.9.1
	 */
	public function add_notices() {
		$this->php_version_notice();
	}

	/**
	 * PHP Version notice
	 *
	 * @since   1.8.8
	 * @version 1.9.9
	 */
	public function php_version_notice() {
		// Define maximum supported PHP version.
		$max_supported_version = CARIERA_MAX_PHP_VERSION;

		// If current PHP version is lower than max supported, remove notice.
		if ( version_compare( PHP_VERSION, $max_supported_version, '<' ) ) {
			self::remove_notice( 'cariera_php_version_warning' );
			return;
		}

		// Add notice if PHP version is equal or higher than max supported.
		self::add_notice(
			'cariera_php_version_warning',
			esc_html__( 'PHP Compatibility Issue', 'cariera' ),
			sprintf(
				/* translators: %s: maximum supported PHP version */
				esc_html__(
					'Your current PHP version (%1$s) is not fully supported. This version of Cariera supports PHP up to %2$s.',
					'cariera'
				),
				PHP_VERSION,
				$max_supported_version
			),
			'error',
			false
		);
	}

	/**
	 * Add notice to Cariera notices
	 *
	 * @since   1.8.8
	 * @version 1.8.9
	 *
	 * @param string  $id
	 * @param string  $title
	 * @param string  $message
	 * @param string  $type
	 * @param boolean $dismissible
	 * @param string  $btn_url
	 * @param string  $btn_label
	 */
	public static function add_notice( $id, $title = '', $message = '', $type = 'info', $dismissible = true, $btn_url = '', $btn_label = '' ) {
		$notices = get_option( 'cariera_notices', [] );

		// Ensure $notices is always an array.
		// if ( ! is_array( $notices ) ) {
		// $notices = [];
		// }

		// Check if a notice with the same unique_id already exists.
		foreach ( $notices as $notice ) {
			if ( isset( $notice['id'] ) && $notice['id'] === $id ) {
				return; // Exit if the notice already exists.
			}
		}

		$notices[] = [
			'id'          => $id,
			'title'       => $title,
			'message'     => $message,
			'type'        => $type,
			'dismissible' => $dismissible,
			'btn_url'     => $btn_url,
			'btn_label'   => $btn_label,
		];

		update_option( 'cariera_notices', $notices );
	}

	/**
	 * Remove notice
	 *
	 * @since   1.8.8
	 * @version 1.8.9
	 *
	 * @param string $id
	 */
	public static function remove_notice( $id ) {
		// Get the current list of notices.
		$notices = get_option( 'cariera_notices', [] );

		// Ensure $notices is always an array.
		if ( ! is_array( $notices ) ) {
			$notices = [];
		}

		// Filter out the notice with the specified ID.
		$notices = array_filter(
			$notices,
			function ( $notice ) use ( $id ) {
				return isset( $notice['id'] ) && $notice['id'] !== $id;
			}
		);

		// Reset array keys to avoid potential issues.
		$notices = array_values( $notices );

		// Update the notices option.
		update_option( 'cariera_notices', $notices );
	}

	/**
	 * Display Cariera Notices
	 *
	 * @since 1.8.8
	 */
	public static function display_notices() {
		$notices = get_option( 'cariera_notices', [] );
		$user_id = get_current_user_id();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $index => $notice ) {
				$dismiss_class = $notice['dismissible'] ? 'is-dismissible' : '';

				// Check if the notice is dismissible and if the user has dismissed it recently.
				if ( $notice['dismissible'] ) {
					$transient_key       = 'cariera_notice_dismissed_' . $user_id . '_' . $notice['id']; // Unique key for each user and notice.
					$dismissed_timestamp = get_transient( $transient_key );

					// If the user has dismissed the notice and it hasn't expired, skip this notice.
					if ( $dismissed_timestamp ) {
						continue;
					}
				}

				echo '<div class="cariera-notice notice notice-' . esc_attr( $notice['type'] ) . ' ' . esc_attr( $dismiss_class ) . '" data-notice-id="' . esc_attr( $notice['id'] ) . '">';

				if ( ! empty( $notice['title'] ) ) {
					echo '<h2 class="title">' . esc_html( $notice['title'] ) . '</h2>';
				}

				echo '<p>' . wp_kses_post( $notice['message'] ) . '</p>';

				// Show the button if it's provided.
				if ( ! empty( $notice['btn_label'] ) && ! empty( $notice['btn_url'] ) ) {
					echo '<a href="' . esc_url( $notice['btn_url'] ) . '" class="cariera-btn" target="_blank">' . esc_html( $notice['btn_label'] ) . '</a>';
				}

				echo '</div>';

				if ( $notice['dismissible'] ) {
					unset( $notices[ $index ] ); // Remove notice after displaying.
				}
			}

			update_option( 'cariera_notices', $notices );
		}
	}

	/**
	 * Handle notice dismiss action via AJAX
	 *
	 * @since 1.8.8
	 */
	/**
	 * Handle notice dismiss action via AJAX
	 *
	 * @since 1.8.8
	 */
	public function handle_dismiss_action() {
		$user_id = get_current_user_id();

		// Sanitize and validate the notice id.
		$notice_action = isset( $_POST['notice_id'] ) ? sanitize_text_field( $_POST['notice_id'] ) : false;

		if ( ! $notice_action ) {
			wp_send_json_error( [ 'message' => 'Invalid notice index' ] );
		}

		// Construct the transient key.
		$transient_key = 'cariera_notice_dismissed_' . $user_id . '_' . $notice_action;

		// Attempt to set the transient.
		$success = set_transient( $transient_key, time(), 30 * DAY_IN_SECONDS );

		if ( ! $success ) {
			wp_send_json_error( [ 'message' => 'Failed to set transient' ] );
		}

		wp_send_json_success( [ 'message' => 'Notice dismissed' ] );
	}
}
