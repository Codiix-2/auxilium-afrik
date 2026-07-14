<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Alert feedback message set as a result of an action.
	 *
	 * @var string|null
	 */
	private $alert_message = null;

	/**
	 * Alert action being handled.
	 *
	 * @var string
	 */
	private $action = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'shortcode_action_handler' ] );

		// Shortcode.
		add_shortcode( 'job_alerts', [ $this, 'job_alerts' ] );

		$this->action = isset( $_REQUEST['action'] ) ? sanitize_title( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Input is used safely.
	}

	/**
	 * Handle actions which need to be run before the shortcode e.g. post actions
	 *
	 * @since 0.9.2
	 */
	public function shortcode_action_handler() {
		global $post;

		if ( ! empty( $post->post_content ) && str_contains( $post->post_content, '[job_alerts' ) ) {
			$this->job_alerts_handler();
		}
	}

	/**
	 * Handles actions for an alert.
	 *
	 * @since   0.9.2
	 * @version 1.0.2
	 *
	 * @throws \Exception When an action fails.
	 */
	public function job_alerts_handler() {
		$this->action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended -- Input used for comparison.

		if ( empty( $this->action ) && Job_Alerts::instance()->can_user_add_alert() ) {
			$guest_user = \WP_Job_Manager\Guest_Session::get_current_guest();

			if ( ! is_user_logged_in() && empty( $guest_user ) && empty( $_REQUEST['updated'] ) ) {
				wp_safe_redirect( add_query_arg( [ 'action' => 'add_alert' ] ) );
				exit;
			}
		}

		try {
			/**
			 * Actions without nonce check, allowed to come from external links. (E-mails)
			 */
			switch ( $this->action ) {
				case 'unsubscribe':
					$this->handle_unsubscribe();
					break;

				case 'confirm':
					$alert_id = empty( $_REQUEST['alert_id'] ) ? 0 : absint( $_REQUEST['alert_id'] );
					$alert    = $this->get_alert( $alert_id );
					$this->handle_confirm( $alert );
					break;
			}

			/**
			 * Actions with nonce check.
			 */
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce check.
			if ( ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'cariera_addons_alert_actions' ) ) {
				if ( ! Job_Alerts::instance()->can_user_add_alert() ) {
					throw new \Exception( __( 'You need to be logged in to create alerts.', 'cariera-addons' ) );
				}

				$alert_id = empty( $_REQUEST['alert_id'] ) ? 0 : absint( $_REQUEST['alert_id'] );

				$alert = null;

				if ( 'add_alert' !== $this->action ) {
					$alert = $this->get_alert( $alert_id );
				}

				switch ( $this->action ) {
					case 'add_alert':
						if ( isset( $_POST['submit-job-alert'] ) ) {
							$this->handle_add_alert();
						}
						break;

					case 'edit':
						if ( isset( $_POST['submit-job-alert'] ) ) {
							$this->handle_edit_alert( $alert );
						}
						break;
					case 'toggle_status':
						$this->handle_toggle_status( $alert );
						break;
					case 'delete':
						$this->handle_delete( $alert );
						break;
					case 'email':
						$this->handle_send_now( $alert );
						break;
					default:
						break;
				}
			}
		} catch ( \Exception $e ) {
			$this->alert_message = \WP_Job_Manager\UI\Notice::error( $e->getMessage() );
		}
	}

	/**
	 * Check permissions and load the alert.
	 *
	 * @since   0.9.2
	 * @version 1.0.2
	 *
	 * @param int|null $alert_id Alert ID.
	 * @param int|null $user_id Owner user to check. Defaults to current user or guest.
	 * @throws \Exception When the alert is invalid or the user cannot manage it.
	 */
	protected function get_alert( $alert_id, $user_id = null ) {
		$alert = Alert::load( $alert_id );

		if ( ! $alert || ! $alert->check_ownership( $user_id ) ) {
			throw new \Exception( esc_html__( 'Invalid Alert', 'cariera-addons' ) );
		}

		return $alert;
	}

	/**
	 * Delete alert.
	 *
	 * @since 0.9.2
	 *
	 * @param Alert $alert Current alert.
	 */
	private function handle_delete( Alert $alert ): void {
		$alert->delete();

		// translators: %s is the alert name.
		$alert_message = sprintf( __( '%s: Alert deleted.', 'cariera-addons' ), $alert->get_name() );

		$this->redirect( $alert_message );
	}

	/**
	 * Shortcode for the alerts page
	 *
	 * @since 0.9.2
	 */
	public function job_alerts() {
		$alert_message = $this->get_alert_message();

		ob_start();

		if ( ! empty( $alert_message ) ) {
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $alert_message;
		}

		if ( ! Job_Alerts::instance()->can_user_add_alert() ) {
			get_job_manager_template( 'alerts/my-alerts-login.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
			return ob_get_clean();
		}

		wp_enqueue_script( 'cariera-addons-job-alerts' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		$alert_id = isset( $_REQUEST['alert_id'] ) ? absint( $_REQUEST['alert_id'] ) : '';

		switch ( $this->action ) {
			case 'add_alert':
				$this->add_alert();
				break;
			case 'edit':
				$this->edit_alert( $alert_id );
				break;
			case 'view':
				$this->view_results( $alert_id );
				break;
			case 'unsubscribe':
				break;
			case 'confirm':
				if ( empty( $this->alert_message ) ) {
					$this->view_alerts();
				}
				break;
			default:
				$this->view_alerts();

		}

		return ob_get_clean();
	}

	/**
	 * List the current user's alerts.
	 *
	 * @since 0.9.2
	 */
	public function view_alerts() {
		$user = wp_get_current_user();

		if ( ! $user->ID ) {
			$user = \WP_Job_Manager\Guest_Session::get_current_guest();
		}

		if ( empty( $user ) ) {
			return;
		}

		$alerts = Alert::get_user_alerts();

		get_job_manager_template(
			'alerts/my-alerts.php',
			[
				'alerts' => $alerts,
				'user'   => $user,
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}

	/**
	 * Add alert form
	 *
	 * @since 0.9.2
	 */
	public function add_alert() {
		$form_data  = $this->get_alert_form_data();
		$user_email = self::get_user_email();

		if ( ! empty( $user_email ) ) {
			$form_data['alert_email'] = $user_email;
		}

		get_job_manager_template(
			'alerts/alert-form.php',
			array_merge(
				[
					'alert_id'        => null,
					'show_alert_name' => false,
				],
				$form_data
			),
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}

	/**
	 * Display the edit alert form.
	 *
	 * @since 0.9.2
	 *
	 * @param int $alert_id Alert ID.
	 */
	public function edit_alert( $alert_id ) {
		try {
			$alert = $this->get_alert( $alert_id );
			$user  = $alert->get_user();

			$search_terms = Post_Types::get_alert_search_terms( $alert_id );

			$form_data = $this->get_alert_form_data();

			$post = $alert->get_post();

			get_job_manager_template(
				'alerts/alert-form.php',
				[
					'alert_id'        => $alert_id,
					'alert_name'      => $form_data['alert_name'] ?? $alert->get_name(),
					'alert_keyword'   => $form_data['alert_keyword'] ?? $post->alert_keyword,
					'alert_location'  => $form_data['alert_location'] ?? $post->alert_location,
					'alert_frequency' => $form_data['alert_frequency'] ?? $post->alert_frequency,
					'alert_cats'      => $form_data['alert_cats'] ?? $search_terms['categories'],
					'alert_regions'   => $form_data['alert_regions'] ?? $search_terms['regions'],
					'alert_tags'      => $form_data['alert_tags'] ?? $search_terms['tags'],
					'alert_job_type'  => $form_data['alert_job_type'] ?? $search_terms['types'],
					'alert_email'     => $user->user_email ?? '',
					'show_alert_name' => true,
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);
		} catch ( \Exception $e ) {
			$this->alert_message = '<div class="job-manager-error">' . $e->getMessage() . '</div>';
		}
	}

	/**
	 * Display the alert results.
	 *
	 * @since 0.9.2
	 *
	 * @param int $alert_id Alert ID.
	 */
	public function view_results( $alert_id ) {
		try {
			$alert = $this->get_alert( $alert_id );

			$jobs = $alert->get_matching_jobs( true );

			// Translators: placeholder is the alert name.
			echo wp_kses_post( wpautop( sprintf( __( 'Jobs matching your "%s" alert:', 'cariera-addons' ), $alert->get_name() ) ) );

			if ( $jobs->have_posts() ) {
				?>
				<ul class="job_listings">
					<?php
					while ( $jobs->have_posts() ) :
						$jobs->the_post();
						get_job_manager_template_part( 'content', 'job_listing' );
					endwhile;
					?>
				</ul>
				<?php
			} else {
				echo wp_kses_post( wpautop( __( 'No jobs found', 'cariera-addons' ) ) );
			}

			wp_reset_postdata();
		} catch ( \Exception $e ) {
			echo '<div class="job-manager-error">' . esc_html( $e->getMessage() ) . '</div>';
		}
	}

	/**
	 * Handle a user clicking the unsubscribe button in the email.
	 *
	 * @since   0.9.2
	 * @version 1.0.2
	 *
	 * @throws \Exception When the alert is invalid or the user cannot manage it.
	 */
	private function handle_unsubscribe(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		$alert_id = empty( $_REQUEST['alert_id'] ) ? '' : absint( $_REQUEST['alert_id'] );
		$user_id  = empty( $_REQUEST['user_id'] ) ? '' : absint( $_REQUEST['user_id'] );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is used for comparison.
		$token = empty( $_REQUEST['token'] ) ? '' : wp_unslash( $_REQUEST['token'] );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$guest_user = empty( $user_id ) ? \WP_Job_Manager\Guest_Session::get_current_guest() : false;

		$token_valid = ! empty( $user_id ) && ! empty( $alert_id ) && Job_Alerts::instance()->verify_alert_token( $token, $alert_id, $user_id );

		if ( ! $guest_user && ! $token_valid ) {
			throw new \Exception( esc_html__( 'Invalid Alert', 'cariera-addons' ) );
		}

		$alert = $this->get_alert( $alert_id, $user_id ?? $guest_user->ID );

		$alert->delete();

		$alert_message = [
			'title'   => esc_html__( 'Alert deleted', 'cariera-addons' ),
			'message' => esc_html__( 'You will no longer receive e-mails for this search.', 'cariera-addons' ),
			'buttons' => [
				[
					'url'   => remove_query_arg(
						[
							'action',
							'alert_id',
							'user_id',
							'token',
						]
					),
					'label' => esc_html__( 'Manage Alerts', 'cariera-addons' ),
				],
			],
		];

		$this->alert_message = \WP_Job_Manager\UI\Notice::success( $alert_message );
	}

	/**
	 * Handles changes the status of an alert.
	 *
	 * @since 0.9.2
	 *
	 * @param Alert $alert Current alert.
	 */
	private function handle_toggle_status( Alert $alert ): void {
		if ( $alert->is_enabled() ) {
			$alert->disable();
		} else {
			$alert->enable();
		}

		// translators: %1$ is the alert title, %2$s is Enabled or Disabled.
		$alert_message = sprintf( __( '%1$s: Alert %2$s.', 'cariera-addons' ), $alert->get_name(), $alert->is_enabled() ? __( 'enabled', 'cariera-addons' ) : __( 'disabled', 'cariera-addons' ) );

		$this->redirect( $alert_message );
	}

	/**
	 * Handles changes the status of an alert.
	 *
	 * @since 0.9.2
	 *
	 * @param Alert $alert Current alert.
	 */
	private function handle_confirm( Alert $alert ): void {
		if ( $alert->is_enabled() ) {
			return;
		}

		$alert->enable();

		$alert_message = [
			'title'   => esc_html__( 'Alert confirmed', 'cariera-addons' ),
			'message' => esc_html__( 'You will start receiving new job listings matching your search.', 'cariera-addons' ),
			'links'   => [
				[
					'url'   => remove_query_arg(
						[
							'action',
							'alert_id',
						]
					),
					'label' => esc_html__( 'Manage Alerts', 'cariera-addons' ),
				],
			],
		];

		$this->alert_message = \WP_Job_Manager\UI\Notice::success( $alert_message );
	}

	/**
	 * Handles the action to trigger an alert.
	 *
	 * @since 0.9.2
	 *
	 * @param Alert $alert Current alert.
	 */
	private function handle_send_now( Alert $alert ): void {
		$alert->send_now();

		// translators: %s is the alert name.
		$alert_message = sprintf( __( '%s: Alert e-mail sent.', 'cariera-addons' ), $alert->get_name() );

		$this->redirect( $alert_message );
	}

	/**
	 * Handles the action to add an alert.
	 *
	 * @since   0.9.2
	 * @version 1.0.2
	 *
	 * @throws \Exception When an action fails.
	 */
	private function handle_add_alert() {
		$alert_data = $this->get_alert_form_data();

		$alerts_form_fields           = get_option( 'job_manager_alerts_form_fields', [] );
		$permission_checkbox_required = isset( $alerts_form_fields['fields']['permission_checkbox'] );

		if ( $permission_checkbox_required && empty( $alert_data['alert_permission'] ) ) {
			throw new \Exception( esc_html__( 'You need to approve receiving emails for this alert.', 'cariera-addons' ) );
		}

		if ( empty( $alert_data['alert_name'] ) ) {
			$alert_data['alert_name'] = self::generate_alert_name( $alert_data );
		}

		$current_user = wp_get_current_user();

		if ( ! $current_user->exists() ) {
			$current_user = \WP_Job_Manager\Guest_Session::get_current_guest();
		}

		if ( false === $current_user ) {
			$this->create_new_guest_alert( $alert_data );
		} else {
			Alert::create( $alert_data, $current_user );

			$alert_message = [
				'title'   => __( 'Alert created', 'cariera-addons' ),
				'message' => __( 'You will start receiving new job listings matching your search.', 'cariera-addons' ),
			];

			$this->redirect( $alert_message );
		}
	}

	/**
	 * Create an alert for a new guest user, and set up guest account and confirmation.
	 *
	 * @since   0.9.2
	 * @version 1.0.2
	 *
	 * @param array $alert_data Alert data.
	 * @throws \Exception When an action fails.
	 */
	private function create_new_guest_alert( $alert_data ) {
		$email = sanitize_email( $alert_data['alert_email'] );

		// Determine login/register page directly.
		$login_registration      = get_option( 'cariera_login_register_layout' );
		$login_registration_page = ( 'popup' === $login_registration ) ? get_option( 'woocommerce_myaccount_page_id' ) : get_option( 'cariera_login_register_page' );
		$login_url               = get_permalink( $login_registration_page );

		if ( get_user_by( 'email', $email ) ) {
			$this->alert_message = \WP_Job_Manager\UI\Notice::error(
				[
					'classes' => [ 'actions-right' ],
					'message' => esc_html__( 'A user account already exists for this e-mail.', 'cariera-addons' ),
					'buttons' => [
						[
							'url'   => apply_filters( 'cariera_addons_alerts_login_url', $login_url ),
							'label' => esc_html__( 'Sign in', 'cariera-addons' ),
						],
					],
				]
			);

			return;
		}

		$owner = \WP_Job_Manager\Guest_User::create( $email );

		if ( ! $owner ) {
			throw new \Exception( esc_html__( 'Invalid email address.', 'cariera-addons' ) );
		}

		$alert_data['post_status'] = 'draft';

		$alert = Alert::create( $alert_data, $owner );

		Emails\Confirmation_Email::send(
			[
				'email' => $owner->user_email,
				'alert' => $alert->get_post(),
				'guest' => $owner,
				'token' => $owner->create_token(),
			]
		);

		list( , $domain ) = explode( '@', $email, 2 );

		$alert_message = [
			'title'   => esc_html__( 'Alert created', 'cariera-addons' ),
			'message' => esc_html__( 'A confirmation e-mail has been sent to your e-mail address.', 'cariera-addons' ),
			'buttons' => [
				[
					'url'   => 'https://' . $domain,
					// Translators: %s is the user's e-mail domain.
					'label' => sprintf( __( 'Open %s', 'cariera-addons' ), $domain ),
				],
			],
		];

		$this->redirect( $alert_message );
	}

	/**
	 * Handles the action to edit an alert.
	 *
	 * @since 0.9.2
	 *
	 * @param Alert $alert Current alert.
	 */
	private function handle_edit_alert( Alert $alert ) {
		$alert_data = $this->get_alert_form_data();

		if ( empty( $alert_data['alert_name'] ) ) {
			$alert_data['alert_name'] = self::generate_alert_name( $alert_data );
		}

		$alert->update( $alert_data );

		// translators: %s is the alert name.
		$alert_message = sprintf( __( '%s: Alert updated.', 'cariera-addons' ), $alert->get_name() );

		$this->redirect( $alert_message );
	}

	/**
	 * Redirect after action is successful.
	 *
	 * @since 0.9.2
	 *
	 * @param string $message Feedback message.
	 */
	private function redirect( $message = null ) {
		$url = remove_query_arg(
			[
				'action',
				'alert_id',
				'_wpnonce',
				'alert_job_type',
				'alert_location',
				'alert_cats',
				'alert_keyword',
				'alert_regions',
				'token',
			]
		);

		$alert_notice = \WP_Job_Manager\UI\Notice::success( $message );

		\WP_Job_Manager\UI\Redirect_Message::redirect( $url, $alert_notice, 'updated' );
	}

	/**
	 * Get alert message set by the action handler.
	 *
	 * @since 0.9.2
	 */
	private function get_alert_message(): ?string {
		$alert_message = $this->alert_message;
		if ( ! $alert_message ) {
			$alert_message = \WP_Job_Manager\UI\Redirect_Message::get_message( 'updated' );
		}

		return $alert_message;
	}

	/**
	 * Get the input data for an alert from an add alert or edit alert form.
	 *
	 * @since 0.9.2
	 */
	private function get_alert_form_data() {
		return [
			'alert_name'       => self::get_text_field( 'alert_name' ),
			'alert_email'      => self::get_text_field( 'alert_email' ),
			'alert_keyword'    => self::get_text_field( 'alert_keyword' ),
			'alert_location'   => self::get_text_field( 'alert_location' ),
			'alert_frequency'  => self::get_text_field( 'alert_frequency' ),
			'alert_cats'       => self::get_array_field( 'alert_cats' ),
			'alert_regions'    => self::get_array_field( 'alert_regions' ),
			'alert_tags'       => self::get_array_field( 'alert_tags' ),
			'alert_job_type'   => self::get_array_field( 'alert_job_type' ),
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Check nonce in action handler.
			'alert_permission' => isset( $_REQUEST['alert_permission'] ),
		];

		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get sanitized text input.
	 *
	 * @since 0.9.2
	 *
	 * @param string $key Input name.
	 */
	private static function get_text_field( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Check nonce in action handler.
		return isset( $_REQUEST[ $key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) : null;
	}

	/**
	 * Get sanitized array input.
	 *
	 * @since 0.9.2
	 *
	 * @param string $key Input name.
	 */
	private static function get_array_field( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Check nonce in action handler.
		return isset( $_REQUEST[ $key ] ) ? array_filter( array_map( 'absint', (array) $_REQUEST[ $key ] ) ) : null;
	}

	/**
	 * Get the management actions for an alert.
	 *
	 * @since 0.9.2
	 *
	 * @param Alert $alert
	 */
	public static function get_alert_actions( Alert $alert ) {
		/**
		 * Filters the management actions available for an alert.
		 *
		 * @param array    $actions The actions.
		 * @param \WP_Post $post The alert post.
		 * @param Alert    $alert The alert post model.
		 */
		$actions = apply_filters(
			'cariera_addons_alert_actions',
			[
				'view'          => [
					'label' => esc_html__( 'Results', 'cariera-addons' ),
					'nonce' => false,
				],
				'email'         => [
					'label' => esc_html__( 'Send&nbsp;Now', 'cariera-addons' ),
					'nonce' => true,
				],
				'edit'          => [
					'label' => esc_html__( 'Edit', 'cariera-addons' ),
					'nonce' => false,
				],
				'toggle_status' => [
					'label' => $alert->is_enabled() ? esc_html__( 'Disable', 'cariera-addons' ) : esc_html__( 'Enable', 'cariera-addons' ),
					'nonce' => true,
				],
				'delete'        => [
					'label' => esc_html__( 'Delete', 'cariera-addons' ),
					'nonce' => true,
				],
			],
			$alert->get_post(),
			$alert
		);

		foreach ( $actions as $key => &$action ) {
			$action_url = add_query_arg(
				[
					'action'   => $key,
					'alert_id' => $alert->ID,
					'updated'  => null,
				]
			);

			if ( $action['nonce'] ) {
				$action_url = wp_nonce_url( $action_url, 'cariera_addons_alert_actions' );
			}

			$action['url'] = $action_url;
		}

		return $actions;
	}

	/**
	 * Generate alert name from keyword, location and search terms.
	 *
	 * @since   0.9.2
	 * @version 1.1.0
	 *
	 * @param array $alert_data Alert input data.
	 */
	public static function generate_alert_name( array $alert_data ): string {
		$alert_name = [];
		if ( ! empty( $alert_data['alert_keyword'] ) ) {
			$alert_name[] = trim( $alert_data['alert_keyword'] );
		}
		if ( ! empty( $alert_data['alert_location'] ) ) {
			$alert_name[] = trim( $alert_data['alert_location'] );
		}
		if ( ! empty( $alert_data['alert_cats'] ) ) {
			if ( is_numeric( $alert_data['alert_cats'][0] ) ) {
				$categories = Post_Types::get_terms( array_slice( $alert_data['alert_cats'], 0, 3 ) );
			} else {
				$categories = get_terms(
					[
						'fields'     => 'names',
						'slug'       => array_slice( $alert_data['alert_cats'], 0, 3 ),
						'hide_empty' => false,
						'taxonomy'   => 'job_listing_category',
					]
				);
			}
			$alert_name[] = implode( ', ', $categories );
		}
		if ( ! empty( $alert_data['alert_tags'] ) ) {
			$tags         = Post_Types::get_terms( array_slice( $alert_data['alert_tags'], 0, 3 ) );
			$alert_name[] = implode( ', ', $tags );
		}
		if ( empty( $alert_name ) ) {
			$alert_name[] = esc_html__( 'All Jobs', 'cariera-addons' );
		}

		$alert_name = array_slice( $alert_name, 0, 3 );
		$alert_name = implode( ', ', $alert_name );
		$alert_name = html_entity_decode( $alert_name, ENT_QUOTES | ENT_HTML5 );
		$alert_name = mb_convert_case( $alert_name, MB_CASE_TITLE );

		return $alert_name;
	}

	/**
	 * Get the email of the current user or guest.
	 *
	 * @since 0.9.2
	 */
	public static function get_user_email() {
		$user = wp_get_current_user();
		if ( ! empty( $user->ID ) ) {
			return $user->user_email;
		} else {
			$guest_user = \WP_Job_Manager\Guest_Session::get_current_guest();

			if ( false !== $guest_user ) {
				return $guest_user->user_email;
			}
		}

		return null;
	}

	/**
	 * Get the URL of the alerts page.
	 *
	 * @since   0.9.2
	 * @version 0.9.11
	 */
	public static function get_page_url() {
		return get_permalink( Settings::get_alerts_page() );
	}
}
