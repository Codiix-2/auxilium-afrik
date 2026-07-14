<?php

namespace Cariera_Core\Core\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Approval {

	/**
	 * Constructor
	 */
	public function __construct() {
		// User Columns.
		add_filter( 'user_row_actions', [ __CLASS__, 'user_table_actions' ], 10, 2 );
		add_filter( 'manage_users_columns', [ __CLASS__, 'add_column' ] );
		add_filter( 'manage_users_custom_column', [ __CLASS__, 'status_column' ], 10, 3 );

		// User status sorting.
		add_action( 'restrict_manage_users', [ $this, 'add_account_status_filter' ], 10, 2 );
		add_action( 'pre_get_users', [ $this, 'filter_users_by_account_status' ] );

		// Status Action.
		add_action( 'load-users.php', [ __CLASS__, 'process_update_user_action' ] );
		add_filter( 'cariera_new_user_approve_validate_status_update', [ __CLASS__, 'validate_status_update' ], 10, 3 );
		add_action( 'cariera_new_user_approve_approve_user', [ __CLASS__, 'approve_user' ] );
		add_action( 'cariera_new_user_approve_deny_user', [ __CLASS__, 'deny_user' ] );

		// Resent Approval Mail.
		add_action( 'wp_ajax_cariera_resend_approval_mail', [ __CLASS__, 'resent_approval_mail' ] );
		add_action( 'wp_ajax_nopriv_cariera_resend_approval_mail', [ __CLASS__, 'resent_approval_mail' ] );

		// User Approval Frontend.
		add_action( 'wp', [ __CLASS__, 'frontend_approve_user' ] );
		add_shortcode( 'cariera_approve_user', [ __CLASS__, 'approve_user_shortcode' ] );

		// Pending user admin dashboard count.
		add_filter( 'admin_head', [ $this, 'pending_users' ] );
	}

	/**
	 * Add the "approve" or "deny" link.
	 *
	 * @since   1.4.8
	 * @version 1.9.7
	 *
	 * @param array $actions
	 * @param mixed $user
	 */
	public static function user_table_actions( $actions, $user ) {
		if ( get_current_user_id() === $user->ID ) {
			return $actions;
		}

		if ( is_super_admin( $user->ID ) ) {
			return $actions;
		}

		$user_status = self::get_user_status( $user->ID );

		$approve_link = add_query_arg(
			[
				'action' => 'approve',
				'user'   => $user->ID,
			]
		);
		$approve_link = remove_query_arg( [ 'new_role' ], $approve_link );
		$approve_link = wp_nonce_url( $approve_link, 'cariera-core' );

		$deny_link = add_query_arg(
			[
				'action' => 'deny',
				'user'   => $user->ID,
			]
		);
		$deny_link = remove_query_arg( [ 'new_role' ], $deny_link );
		$deny_link = wp_nonce_url( $deny_link, 'cariera-core' );

		$approve_action = '<a href="' . esc_url( $approve_link ) . '">' . esc_html__( 'Approve', 'cariera-core' ) . '</a>';
		$deny_action    = '<a href="' . esc_url( $deny_link ) . '">' . esc_html__( 'Deny', 'cariera-core' ) . '</a>';

		if ( 'pending' === $user_status ) {
			$actions[] = $approve_action;
			$actions[] = $deny_action;
		} elseif ( 'approved' === $user_status ) {
			$actions[] = $deny_action;
		} elseif ( 'denied' === $user_status ) {
			$actions[] = $approve_action;
		}

		return $actions;
	}

	/**
	 * Add the status column to the user table
	 *
	 * @since 1.4.8
	 *
	 * @param array $columns
	 */
	public static function add_column( $columns ) {
		$the_columns['user_status'] = esc_html__( 'Status', 'cariera-core' );

		$newcol  = array_slice( $columns, 0, -1 );
		$newcol  = array_merge( $newcol, $the_columns );
		$columns = array_merge( $newcol, array_slice( $columns, 1 ) );

		return $columns;
	}

	/**
	 * Show the status of the user in the status column
	 *
	 * @since   1.4.8
	 * @version 1.9.7
	 *
	 * @param string $val
	 * @param string $column_name
	 * @param int    $user_id
	 */
	public static function status_column( $val, $column_name, $user_id ) {
		if ( 'user_status' !== $column_name ) {
			return $val;
		}

		$status = self::get_user_status( $user_id );

		$statuses_i18n = [
			'approved' => esc_html__( 'approved', 'cariera-core' ),
			'denied'   => esc_html__( 'denied', 'cariera-core' ),
			'pending'  => esc_html__( 'pending', 'cariera-core' ),
		];

		return isset( $statuses_i18n[ $status ] ) ? $statuses_i18n[ $status ] : $val;
	}

	/**
	 * Render the account-status filter dropdown above the users table.
	 *
	 * @since 2.0.0
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	public function add_account_status_filter( $which ) {
		// Render only once (top bar).
		if ( 'top' !== $which ) {
			return;
		}

		$selected = isset( $_GET['account_status'] ) ? sanitize_key( $_GET['account_status'] ) : '';

		$statuses = [
			''         => __( 'All statuses', 'cariera-core' ),
			'approved' => __( 'Approved', 'cariera-core' ),
			'pending'  => __( 'Pending', 'cariera-core' ),
			'denied'   => __( 'Denied', 'cariera-core' ),
		];
		?>
		<label for="cariera-account-status-filter" class="screen-reader-text">
			<?php esc_html_e( 'Filter by account status', 'cariera-core' ); ?>
		</label>
		<select id="cariera-account-status-filter" name="account_status" style="float: none; margin-left: 10px;">
			<?php foreach ( $statuses as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
		submit_button( esc_html__( 'Filter', 'cariera-core' ), 'button', false, false );
	}

	/**
	 * Modify the WP_User_Query when an account-status filter is active.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_User_Query $query
	 */
	public function filter_users_by_account_status( $query ) {
		global $pagenow;

		if ( ! is_admin() || 'users.php' !== $pagenow ) {
			return;
		}

		$status = isset( $_GET['account_status'] ) ? sanitize_key( $_GET['account_status'] ) : '';

		if ( '' === $status ) {
			return;
		}

		if ( 'approved' === $status ) {
			$query->set(
				'meta_query',
				[
					'relation' => 'OR',
					[
						'key'     => 'user_account_status',
						'value'   => 'approved',
						'compare' => '=',
					],
					[
						'key'     => 'user_account_status',
						'compare' => 'NOT EXISTS',
					],
				]
			);
		} else {
			$query->set(
				'meta_query',
				[
					[
						'key'     => 'user_account_status',
						'value'   => $status,
						'compare' => '=',
					],
				]
			);
		}
	}

	/**
	 * Get user status
	 *
	 * @since 1.4.8
	 *
	 * @param int $user_id
	 */
	public static function get_user_status( $user_id ) {
		$user_status = get_user_meta( $user_id, 'user_account_status', true );

		if ( empty( $user_status ) ) {
			$user_status = 'approved';
		}

		return $user_status;
	}

	/**
	 * Validate status update
	 *
	 * @since 1.4.8
	 *
	 * @param bool   $do_update
	 * @param int    $user_id
	 * @param string $status
	 */
	public static function validate_status_update( $do_update, $user_id, $status ) {
		$current_status = self::get_user_status( $user_id );

		if ( 'approve' === $status ) {
			$new_status = 'approved';
		} else {
			$new_status = 'denied';
		}

		if ( $current_status == $new_status ) {
			$do_update = false;
		}

		return $do_update;
	}

	/**
	 * Update user status
	 *
	 * @since 1.4.8
	 *
	 * @param mixed  $user
	 * @param string $status
	 */
	public static function update_user_status( $user, $status ) {
		$user_id = absint( $user );
		if ( ! $user_id ) {
			return false;
		}

		if ( ! in_array( $status, [ 'approve', 'deny' ], true ) ) {
			return false;
		}

		$do_update = apply_filters( 'cariera_new_user_approve_validate_status_update', true, $user_id, $status );
		if ( ! $do_update ) {
			return false;
		}

		// Where it all happens!
		do_action( 'cariera_new_user_approve_' . $status . '_user', $user_id );
		do_action( 'cariera_new_user_approve_user_status_update', $user_id, $status );

		return true;
	}

	/**
	 * Process the user status update
	 *
	 * @since   1.4.8
	 * @version 1.9.7
	 */
	public static function process_update_user_action() {
		$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
		$user   = isset( $_GET['user'] ) ? absint( $_GET['user'] ) : 0;

		if ( ! empty( $action ) && in_array( $action, [ 'approve', 'deny' ], true ) && ! isset( $_GET['new_role'] ) ) {
			check_admin_referer( 'cariera-core' );

			$sendback = remove_query_arg( [ 'approved', 'denied', 'deleted', 'ids', 'cariera-status-query-submit', 'new_role' ], wp_get_referer() );
			if ( ! $sendback ) {
				$sendback = admin_url( 'users.php' );
			}

			$wp_list_table = _get_list_table( 'WP_Users_List_Table' );
			$pagenum       = $wp_list_table->get_pagenum();
			$sendback      = add_query_arg( 'paged', $pagenum, $sendback );

			if ( $user > 0 ) {
				self::update_user_status( $user, $action );
			}

			if ( 'approve' === $action ) {
				$sendback = add_query_arg(
					[
						'approved' => 1,
						'ids'      => $user,
					],
					$sendback
				);
			} else {
				$sendback = add_query_arg(
					[
						'denied' => 1,
						'ids'    => $user,
					],
					$sendback
				);
			}

			wp_safe_redirect( $sendback );
			exit;
		}
	}

	/**
	 * Approve User
	 *
	 * @since 1.4.8
	 *
	 * @param int $user_id
	 */
	public static function approve_user( $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		wp_cache_delete( $user->ID, 'users' );
		wp_cache_delete( $user->data->user_login, 'userlogins' );

		// Send mail when user gets approved.
		$mail_args = [
			'email'        => stripslashes( $user->data->user_email ),
			'display_name' => $user->data->user_login,
			'site_url'     => home_url(),
		];
		do_action( 'cariera_new_user_approved_notification', $mail_args );

		// Change usermeta tag in database to approved.
		update_user_meta( $user->ID, 'user_account_status', 'approved' );
		update_user_meta( $user->ID, 'account_approve_key', '' );

		do_action( 'cariera_new_user_approve_user_approved', $user );
	}

	/**
	 * Deny User
	 *
	 * @since 1.4.8
	 *
	 * @param int $user_id
	 */
	public static function deny_user( $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		// Send mail when user gets denied.
		$mail_args = [
			'email'        => stripslashes( $user->data->user_email ),
			'display_name' => $user->data->user_login,
			'site_url'     => home_url(),
		];
		do_action( 'cariera_new_user_denied_notification', $mail_args );

		update_user_meta( $user->ID, 'user_account_status', 'denied' );

		do_action( 'cariera_new_user_approve_user_denied', $user );
	}

	/**
	 * Resent Approval Mail
	 *
	 * @since   1.4.8
	 * @version 1.9.7
	 */
	public static function resent_approval_mail() {
		$user_login = isset( $_POST['login'] ) ? sanitize_text_field( wp_unslash( $_POST['login'] ) ) : ''; // phpcs:ignore

		if ( empty( $user_login ) ) {
			wp_send_json(
				[
					'status'  => false,
					'message' => '<span class="job-manager-message error">' . esc_html__( 'Username or Email not correct.', 'cariera-core' ) . '</span>',
				]
			);
		}

		// Get user by email or username.
		$user_obj = filter_var( $user_login, FILTER_VALIDATE_EMAIL ) ? get_user_by( 'email', $user_login ) : get_user_by( 'login', $user_login );

		if ( ! empty( $user_obj->ID ) ) {
			$user_login_auth = self::get_user_status( $user_obj->ID );

			if ( 'pending' === $user_login_auth ) {
				if ( get_option( 'cariera_moderate_new_user' ) === 'email' ) {
					$recipent_mail = stripslashes( $user_obj->data->user_email );
				} else {
					$recipent_mail = get_option( 'admin_email' );
				}

				$approval_url = get_permalink( get_option( 'cariera_moderate_new_user_page' ) );
				$code         = get_user_meta( $user_obj->data->ID, 'account_approve_key', true );
				$approval_url = add_query_arg(
					[
						'user_id'     => $user_obj->data->ID,
						'approve-key' => $code,
					],
					$approval_url
				);

				// Send Email.
				$mail_args = [
					'send_to'      => $recipent_mail,
					'email'        => $user_obj->data->user_email,
					'display_name' => $user_obj->data->user_login,
					'approval_url' => $approval_url,
				];

				do_action( 'cariera_new_user_approval_notification', $mail_args );

				echo wp_json_encode(
					[
						'status'  => true,
						'message' => '<span class="job-manager-message success">' . esc_html__( 'Email has been sent successfully.', 'cariera-core' ) . '</span>',
					]
				);

				die();
			}
		}

		echo wp_json_encode(
			[
				'status'  => false,
				'message' => '<span class="job-manager-message error">' . esc_html__( 'Your account is not available.', 'cariera-core' ) . '</span>',
			]
		);

		die();
	}

	/**
	 * Approve user via the frontend
	 *
	 * @since 1.4.8
	 *
	 * TODO: rewrite and improve this method handling.
	 */
	public static function frontend_approve_user() {
		$post = get_post();

		if ( is_object( $post ) ) {
			if ( strpos( $post->post_content, '[cariera_approve_user]' ) !== false ) {
				$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( wp_unslash( $_GET['user_id'] ) ) : 0; // phpcs:ignore
				$code    = isset( $_GET['approve-key'] ) ? sanitize_text_field( wp_unslash( $_GET['approve-key'] ) ) : 0; // phpcs:ignore

				if ( ! $user_id ) {
					$error = [
						'error'   => true,
						'message' => esc_html__( 'The user does not exist.', 'cariera-core' ),
					];
				}

				$user = get_user_by( 'ID', $user_id );
				if ( empty( $user ) ) {
					$error = [
						'error'   => true,
						'message' => esc_html__( 'The user does not exist.', 'cariera-core' ),
					];
				} else {
					$user_code = get_user_meta( $user_id, 'account_approve_key', true );
					if ( $code != $user_code ) {
						$error = [
							'error'   => true,
							'message' => esc_html__( 'Activation code is not the same.', 'cariera-core' ),
						];
					}
				}

				if ( empty( $error ) ) {
					$return                       = self::update_user_status( $user_id, 'approve' );
					$error                        = [
						'error'   => false,
						'message' => esc_html__( 'Congratulations, your account has been approved!', 'cariera-core' ),
					];
					$_SESSION['approve_user_msg'] = $error;
				} else {
					$_SESSION['approve_user_msg'] = $error;
				}
			}
		}
	}

	/**
	 * Approve user shortcode
	 *
	 * @since 1.4.8
	 *
	 * TODO: Improve this function.
	 *
	 * @param array $atts
	 */
	public static function approve_user_shortcode( $atts ) {
		?>
		<div class="approve-user-wrapper">
			<?php if ( isset( $_SESSION['approve_user_msg'] ) ) { ?>
				<div class="job-manager-message <?php echo esc_attr( $_SESSION['approve_user_msg']['error'] ? 'error' : 'success' ); ?>">
					<h3><?php echo trim( $_SESSION['approve_user_msg']['message'] ); ?></h3>
				</div>
				<?php
				unset( $_SESSION['approve_user_msg'] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Adding a pending number of users in the admin dashboard
	 *
	 * @since   1.5.2
	 * @version 1.9.8
	 */
	public function pending_users() {
		global $menu;

		$plural = esc_html__( 'Users', 'cariera-core' );

		$args = [
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'     => 'user_account_status',
					'value'   => 'pending',
					'compare' => '=',
				],
			],
			'count_total' => true,
		];

		$users       = new \WP_User_Query( $args );
		$count_users = $users->get_total();

		foreach ( $menu as $key => $menu_item ) {
			if ( strpos( $menu_item[0], $plural ) === 0 ) {
				if ( $count_users ) {
					$menu[ $key ][0] .= " <span class='awaiting-mod update-plugins count-$count_users'><span class='pending-count'>" . number_format_i18n( $count_users ) . '</span></span>';
				}
				break;
			}
		}
	}
}
