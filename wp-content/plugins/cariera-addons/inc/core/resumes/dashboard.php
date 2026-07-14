<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Dashboard message.
	 *
	 * @var string
	 */
	private $resume_dashboard_message = '';

	/**
	 * Cache of resume post IDs currently displayed on resume dashboard.
	 *
	 * @var int[]
	 */
	private $resume_dashboard_resume_ids;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'handle_actions' ] );
		add_shortcode( 'candidate_dashboard', [ $this, 'output_candidate_dashboard' ] );
		add_action( 'cariera_addons_resume_dashboard_content_edit', [ $this, 'edit_resume' ] );
	}

	/**
	 * Shortcode which lists the logged in user's resumes
	 *
	 * @since   0.9.5
	 * @version 0.9.7
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public function output_candidate_dashboard( $atts ) {
		if ( ! is_user_logged_in() ) {
			ob_start();
			get_job_manager_template( 'resumes/candidate-dashboard-login.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
			return ob_get_clean();
		}

		$atts = shortcode_atts(
			[
				'posts_per_page' => '25',
			],
			$atts
		);

		$posts_per_page = $atts['posts_per_page'];

		wp_enqueue_script( 'cariera-addons-resume-dashboard' );

		ob_start();

		// If doing an action, show conditional content if needed....
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		$action = isset( $_REQUEST['action'] ) ? sanitize_title( wp_unslash( $_REQUEST['action'] ) ) : false;
		if ( ! empty( $action ) ) {
			// Show alternative content if a plugin wants to.
			if ( has_action( 'cariera_addons_resume_dashboard_content_' . $action ) ) {
				do_action( 'cariera_addons_resume_dashboard_content_' . $action, $atts );

				return ob_get_clean();
			}
		}

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

		// ....If not show the resume dashboard.
		$resumes = new \WP_Query(
			$this->get_resume_dashboard_query_args(
				[
					'posts_per_page' => $posts_per_page,
					's'              => $search,
				]
			),
		);

		// Cache IDs for access check later on.
		$this->resume_dashboard_resume_ids = wp_list_pluck( $resumes->posts, 'ID' );

		$message = \WP_Job_Manager\UI\Redirect_Message::get_message( 'updated' );

		if ( ! empty( $message ) ) {
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the notice class.
			echo '<div class="alignwide">' . $message . '</div>';
		}

		$updated_message = \WP_Job_Manager\UI\Redirect_Message::get_message( 'resume_updated' );
		if ( ! empty( $updated_message ) ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the notice class.
			echo '<div class="job-manager-message">' . $updated_message . '</div>';
		}

		$candidate_dashboard_columns = apply_filters(
			'cariera_addons_candidate_dashboard_columns',
			[
				'resume-image'    => esc_html__( 'Candidate Image', 'cariera-addons' ),
				'resume-title'    => esc_html__( 'Name', 'cariera-addons' ),
				'candidate-title' => esc_html__( 'Title', 'cariera-addons' ),
				'date'            => esc_html__( 'Date Posted', 'cariera-addons' ),
			]
		);

		$resume_actions = [];
		foreach ( $resumes->posts as $resume ) {
			$resume_actions[ $resume->ID ] = $this->get_resume_actions( $resume );
		}

		/**
		 * Output content before the resume dashboard.
		 */
		do_action( 'cariera_addons_resume_dashboard_before', $resumes );

		get_job_manager_template(
			'resumes/candidate-dashboard.php',
			[
				'resumes'                     => $resumes->posts,
				'resume_actions'              => $resume_actions,
				'max_num_pages'               => $resumes->max_num_pages,
				'candidate_dashboard_columns' => $candidate_dashboard_columns,
				'search_input'                => $search,
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);

		/**
		 * Output content after the resume dashboard.
		 */
		do_action( 'cariera_addons_resume_dashboard_after', $resumes );

		return ob_get_clean();
	}

	/**
	 * Get the actions available to the user for a resume listing on the resume dashboard page.
	 *
	 * @since 0.9.7
	 *
	 * @param \WP_Post $resume The resume post object.
	 */
	public function get_resume_actions( $resume ) {
		if ( ! $this->can_manage_resume( $resume ) ) {
			return [];
		}

		$base_url = self::get_resume_dashboard_page_url();

		$base_nonce_action_name = 'resume_manager_my_resume_actions';

		$actions = [];

		switch ( $resume->post_status ) {
			case 'publish':
				if ( \Cariera_Addons\Core\Resumes\Post_Types::resume_is_editable( $resume->ID ) ) {
					$actions['edit'] = [
						'label' => esc_html__( 'Edit', 'cariera-addons' ),
						'nonce' => false,
					];
				}
				$actions['hide'] = [
					'label' => esc_html__( 'Hide', 'cariera-addons' ),
					'nonce' => $base_nonce_action_name,
				];
				break;
			case 'hidden':
				if ( \Cariera_Addons\Core\Resumes\Post_Types::resume_is_editable( $resume->ID ) ) {
					$actions['edit'] = [
						'label' => esc_html__( 'Edit', 'cariera-addons' ),
						'nonce' => false,
					];
				}
				$actions['publish'] = [
					'label' => esc_html__( 'Publish', 'cariera-addons' ),
					'nonce' => $base_nonce_action_name,
				];
				break;
			case 'pending_payment':
			case 'pending':
				if ( resume_manager_user_can_edit_pending_submissions( $resume->ID ) ) {
					$actions['edit'] = [
						'label' => esc_html__( 'Edit', 'cariera-addons' ),
						'nonce' => false,
					];
				}
				break;
			// case 'expired':
			// if ( get_option( 'resume_manager_submit_resume_form_page_id' ) ) {
			// $actions['relist'] = [
			// 'label' => esc_html__( 'Relist', 'cariera-addons' ),
			// 'nonce' => $base_nonce_action_name,
			// ];
			// }
			// break;
		}

		$actions['delete'] = [
			'label' => esc_html__( 'Delete', 'cariera-addons' ),
			'nonce' => $base_nonce_action_name,
		];

		/**
		 * Filter the actions available to the current user for a resume on the resume dashboard page.
		 */
		$actions = apply_filters( 'resume_manager_my_resume_actions', $actions, $resume );

		// For backwards compatibility, convert `nonce => true` to the nonce action name.
		foreach ( $actions as $key => &$action ) {
			if ( isset( $action['nonce'] ) && true === $action['nonce'] ) {
				$action['nonce'] = $base_nonce_action_name;
			}

			$action_url = add_query_arg(
				[
					'action'    => $key,
					'resume_id' => $resume->ID,
				],
				'?'
			);

			if ( $action['nonce'] ) {
				$action_url = wp_nonce_url( $action_url, $action['nonce'] );
			}

			$action['name'] = $key;
			$action['url']  = $action_url;
		}

		return $actions;
	}

	/**
	 * Displays edit resume form.
	 *
	 * @since 0.9.7
	 */
	public function edit_resume() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output should be appropriately escaped in the form generator.
		echo \Cariera_Addons\Core\Resumes\Resumes::instance()->forms->get_form( 'edit-resume' );
	}

	/**
	 * Helper function used to check if page is resume dashboard page.
	 *
	 * @since 0.9.7
	 */
	private function is_resume_dashboard_page() {
		global $post;

		if ( is_page() && has_shortcode( $post->post_content, 'candidate_dashboard' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handles actions on resume dashboard.
	 *
	 * @since   0.9.7
	 * @version 1.1.0
	 *
	 * @throws \Exception On action handling error.
	 */
	public function handle_actions() {
		$should_run_handler = apply_filters( 'resume_manager_should_run_shortcode_action_handler', $this->is_resume_dashboard_page() );

		if ( ! $should_run_handler
			|| empty( $_REQUEST['action'] )
			|| empty( $_REQUEST['resume_id'] )
			|| empty( $_REQUEST['_wpnonce'] )
		) {
			return;
		}

		$action    = sanitize_title( wp_unslash( $_REQUEST['action'] ) );
		$resume_id = isset( $_REQUEST['resume_id'] ) ? absint( $_REQUEST['resume_id'] ) : 0;

		$resume         = get_post( $resume_id );
		$resume_actions = $this->get_resume_actions( $resume );

		if ( ! isset( $resume_actions[ $action ] )
			|| empty( $resume_actions[ $action ]['nonce'] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce should not be modified.
			|| ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), $resume_actions[ $action ]['nonce'] )
		) {
			return;
		}

		try {
			if ( empty( $resume ) || \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $resume->post_type || ! resume_manager_user_can_edit_resume( $resume_id ) ) {
				throw new \Exception( esc_html__( 'Invalid ID', 'cariera-addons' ) );
			}

			switch ( $action ) {
				case 'delete':
					// Trash it.
					wp_trash_post( $resume_id );

					// Message.
					// translators: Placeholder %s is the resume title.
					$this->resume_dashboard_message = \WP_Job_Manager\UI\Notice::success( sprintf( __( '%s has been deleted', 'cariera-addons' ), $resume->post_title ) );

					break;
				case 'hide':
					if ( 'publish' === $resume->post_status ) {
						$update_resume = [
							'ID'          => $resume_id,
							'post_status' => 'hidden',
						];
						wp_update_post( $update_resume );
						// translators: Placeholder %s is the resume title.
						$this->resume_dashboard_message = \WP_Job_Manager\UI\Notice::success( sprintf( __( '%s has been hidden', 'cariera-addons' ), $resume->post_title ) );
					}
					break;
				case 'publish':
					if ( 'hidden' === $resume->post_status ) {
						$update_resume = [
							'ID'          => $resume_id,
							'post_status' => 'publish',
						];
						wp_update_post( $update_resume );
						// translators: Placeholder %s is the resume title.
						$this->resume_dashboard_message = \WP_Job_Manager\UI\Notice::success( sprintf( __( '%s has been published', 'cariera-addons' ), $resume->post_title ) );
					}
					break;
				case 'relist':
					if ( ! job_manager_get_permalink( 'submit_resume_form' ) ) {
						throw new \Exception( __( 'Missing submission page.', 'cariera-addons' ) );
					}
					wp_safe_redirect( add_query_arg( $query_args, job_manager_get_permalink( 'submit_resume_form' ) ) );
					exit;
				default:
					do_action( 'cariera_addons_resume_dashboard_do_action_' . $action, $resume_id );
					break;
			}

			do_action( 'cariera_addons_resume_dashboard_do_action', $action, $resume_id );

			/**
			 * Set a success message for a custom dashboard action handler.
			 */
			$success_message = apply_filters( 'cariera_addons_resume_dashboard_success_message', '', $action, $resume_id );
			if ( $success_message ) {
				$this->resume_dashboard_message = \WP_Job_Manager\UI\Notice::success( $success_message );
			}
		} catch ( \Exception $e ) {
			$this->resume_dashboard_message = \WP_Job_Manager\UI\Notice::error( $e->getMessage() );
		}

		\WP_Job_Manager\UI\Redirect_Message::redirect( remove_query_arg( [ 'action', 'resume_id', '_wpnonce' ] ), $this->resume_dashboard_message, 'updated' );
	}

	/**
	 * Get the URL of the [resume_dashboard] page.
	 *
	 * @since 0.9.7
	 */
	public static function get_resume_dashboard_page_url() {
		$page_id = get_option( 'resume_manager_candidate_dashboard_page_id' );
		if ( $page_id ) {
			return (string) get_permalink( $page_id );
		} else {
			return home_url( '/' );
		}
	}

	/**
	 * Check if the current user can manage this resume listing.
	 *
	 * @since 0.9.7
	 *
	 * @param \WP_Post|null $resume
	 */
	public function can_manage_resume( $resume ) {
		if ( ! get_current_user_id()
			|| empty( $resume )
			|| ! $resume instanceof \WP_Post
			|| \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $resume->post_type ) {
			return false;
		}

		return is_admin() ? current_user_can( 'edit_posts', $resume->ID ) : $this->is_resume_available_on_dashboard( $resume );
	}

	/**
	 * Check if a resume is listed on the current user's resume dashboard page.
	 *
	 * @since 0.9.7
	 *
	 * @param \WP_Post $resume
	 */
	public function is_resume_available_on_dashboard( \WP_Post $resume ) {
		// Check cache of currently displayed resume dashboard IDs first to avoid lots of queries.
		if ( ! empty( $this->resume_dashboard_resume_ids ) && in_array( (int) $resume->ID, $this->resume_dashboard_resume_ids, true ) ) {
			return true;
		}

		$args           = $this->get_resume_dashboard_query_args();
		$args['p']      = $resume->ID;
		$args['fields'] = 'ids';

		$query = new \WP_Query( $args );

		return (int) $query->post_count > 0;
	}

	/**
	 * Helper that generates the resume dashboard query args.
	 *
	 * @since 0.9.7
	 *
	 * @param array $args Additional query args.
	 */
	private function get_resume_dashboard_query_args( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'post_type'           => \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME,
				'post_status'         => [ 'publish', 'expired', 'pending', 'draft', 'preview', 'hidden' ],
				'ignore_sticky_posts' => 1,
				'orderby'             => 'date',
				'order'               => 'desc',
				'author'              => get_current_user_id(),
				'posts_per_page'      => -1,
			]
		);

		if ( ! empty( $args['posts_per_page'] ) && $args['posts_per_page'] > 0 ) {
			$args['offset'] = ( max( 1, get_query_var( 'paged' ) ) - 1 ) * $args['posts_per_page'];
		}

		/**
		 * Customize the query that is used to get resumes on the resume dashboard.
		 */
		return apply_filters( 'cariera_addons_get_dashboard_resumes_args', $args );
	}

	/**
	 * Add a flash message to display on a candidate dashboard.
	 *
	 * @since 0.9.5
	 *
	 * @param string $message Flash message to show on candidate dashboard.
	 * @param bool   $is_error True this message is an error.
	 */
	public static function add_candidate_dashboard_message( $message, $is_error = false ) {
		$candidate_dashboard_page_id = get_option( 'resume_manager_candidate_dashboard_page_id' );
		if ( ! wp_get_session_token() || ! $candidate_dashboard_page_id ) {
			// We only handle flash messages when the candidate dashboard page ID is set and user has valid session token.
			return false;
		}
		$messages_key = self::get_candidate_dashboard_message_key();
		$messages     = self::get_candidate_dashboard_messages( false );

		$messages[] = [
			'message'  => $message,
			'is_error' => $is_error,
		];

		set_transient( $messages_key, wp_json_encode( $messages ), HOUR_IN_SECONDS );

		return true;
	}

	/**
	 * Gets the current flash messages for the candidate dashboard.
	 *
	 * @since 0.9.5
	 *
	 * @param bool $clear Flush messages after retrieval.
	 */
	private static function get_candidate_dashboard_messages( $clear ) {
		$messages_key = self::get_candidate_dashboard_message_key();
		$messages     = get_transient( $messages_key );

		if ( empty( $messages ) ) {
			$messages = [];
		} else {
			$messages = json_decode( $messages, true );
		}

		if ( $clear ) {
			delete_transient( $messages_key );
		}

		return $messages;
	}

	/**
	 * Get the transient key to use to store candidate dashboard messages.
	 *
	 * @since 0.9.5
	 */
	private static function get_candidate_dashboard_message_key() {
		return 'candidate_dashboard_messages_' . md5( wp_get_session_token() );
	}
}
