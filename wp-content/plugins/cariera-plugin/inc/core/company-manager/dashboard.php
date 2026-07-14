<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Dashboard message.
	 *
	 * @access private
	 * @var string
	 */
	private $company_dashboard_message = '';

	/**
	 * Cache of company post IDs currently displayed on company dashboard.
	 *
	 * @var int[]
	 */
	private $company_dashboard_company_ids;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'handle_actions' ] );
		add_shortcode( 'company_dashboard', [ $this, 'output_company_dashboard' ] );
	}

	/**
	 * Companies Dashboard shortcode
	 *
	 * @since   1.4.4
	 * @version 1.9.3
	 *
	 * @param array $atts
	 */
	public function output_company_dashboard( $atts ) {
		global $cariera_company_manager;

		if ( ! is_user_logged_in() ) {
			ob_start();
			get_job_manager_template( 'company-dashboard-login.php', [], 'wp-job-manager-companies' );
			return ob_get_clean();
		}

		$posts_per_page = isset( $atts['posts_per_page'] ) ? intval( $atts['posts_per_page'] ) : 25;

		wp_enqueue_script( 'cariera-company-manager-dashboard' );

		ob_start();

		// If doing an action, show conditional content if needed....
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		$action = isset( $_REQUEST['action'] ) ? sanitize_title( wp_unslash( $_REQUEST['action'] ) ) : false;
		if ( ! empty( $action ) ) {
			// phpcs:ignore
			$company_id = isset( $_REQUEST['company_id'] ) ? absint( $_REQUEST['company_id'] ) : '';

			switch ( $action ) {
				case 'edit':
					return $cariera_company_manager->forms->get_form( 'edit-company' );
			}
		}

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

		// ....If not show the company dashboard.
		$companies = new \WP_Query(
			$this->get_company_dashboard_query_args(
				[
					'posts_per_page' => $posts_per_page,
					's'              => $search,
				]
			),
		);

		// Cache IDs for access check later on.
		$this->company_dashboard_company_ids = wp_list_pluck( $companies->posts, 'ID' );

		$message = \WP_Job_Manager\UI\Redirect_Message::get_message( 'updated' );

		if ( ! empty( $message ) ) {
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the notice class.
			echo '<div class="alignwide">' . $message . '</div>';
		}

		$updated_message = \WP_Job_Manager\UI\Redirect_Message::get_message( 'company_updated' );
		if ( ! empty( $updated_message ) ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the notice class.
			echo '<div class="job-manager-message">' . $updated_message . '</div>';
		}

		$company_dashboard_columns = apply_filters(
			'cariera_company_dashboard_columns',
			[
				'company-logo'  => esc_html__( 'Logo', 'cariera-core' ),
				'listing-title' => esc_html__( 'Name', 'cariera-core' ),
				'date'          => esc_html__( 'Date Posted', 'cariera-core' ),
				// translators: %s is the "job post type label".
				'company-jobs'  => sprintf( esc_html__( 'Active %s', 'cariera-core' ), cariera_get_job_post_label( true ) ),
			]
		);

		$company_actions = [];
		foreach ( $companies->posts as $company ) {
			$company_actions[ $company->ID ] = $this->get_company_actions( $company );
		}

		/**
		 * Output content before the company dashboard.
		 */
		do_action( 'cariera_company_dashboard_before', $companies );

		get_job_manager_template(
			'company-dashboard.php',
			[
				'companies'                 => $companies->posts,
				'company_actions'           => $company_actions,
				'max_num_pages'             => $companies->max_num_pages,
				'company_dashboard_columns' => $company_dashboard_columns,
				'search_input'              => $search,
			],
			'wp-job-manager-companies'
		);

		/**
		 * Output content after the company dashboard.
		 */
		do_action( 'cariera_company_dashboard_after', $companies );

		return ob_get_clean();
	}

	/**
	 * Get the actions available to the user for a company listing on the company dashboard page.
	 *
	 * @since   1.7.0
	 * @version 1.9.3
	 *
	 * @param WP_POST $company
	 */
	public function get_company_actions( $company ) {
		if ( ! $this->can_manage_company( $company ) ) {
			return [];
		}

		$base_url = self::get_company_dashboard_page_url();

		$base_nonce_action_name = 'cariera_my_company_actions';

		$actions = [];
		switch ( $company->post_status ) {
			case 'publish':
				if ( \Cariera_Core\Core\Company_Manager\CPT::company_is_editable( $company->ID ) ) {
					$actions['edit'] = [
						'label' => esc_html__( 'Edit', 'cariera-core' ),
						'nonce' => false,
					];
				}
				$actions['hide'] = [
					'label' => esc_html__( 'Hide', 'cariera-core' ),
					'nonce' => $base_nonce_action_name,
				];
				break;
			case 'private':
			case 'hidden':
				if ( \Cariera_Core\Core\Company_Manager\CPT::company_is_editable( $company->ID ) ) {
					$actions['edit'] = [
						'label' => esc_html__( 'Edit', 'cariera-core' ),
						'nonce' => false,
					];
				}
				$actions['publish'] = [
					'label' => esc_html__( 'Publish', 'cariera-core' ),
					'nonce' => $base_nonce_action_name,
				];
				break;
			case 'pending_payment':
			case 'pending':
				if ( \Cariera_Core\Core\Company_Manager\CPT::company_is_editable( $company->ID ) ) {
					$actions['edit'] = [
						'label' => esc_html__( 'Edit', 'cariera-core' ),
						'nonce' => false,
					];
				}
				break;
		}

		$actions['delete'] = [
			'label' => esc_html__( 'Delete', 'cariera-core' ),
			'nonce' => $base_nonce_action_name,
		];

		/**
		 * Filter the actions available to the current user for a company on the company dashboard page.
		 *
		 * @since 1.7.0
		 *
		 * @param array   $actions Actions to filter.
		 * @param WP_Post $company     Company post object.
		 */
		$actions = apply_filters( 'cariera_my_company_actions', $actions, $company );

		// For backwards compatibility, convert `nonce => true` to the nonce action name.
		foreach ( $actions as $key => $action ) {
			if ( isset( $action['nonce'] ) && true === $action['nonce'] ) {
				$action['nonce'] = $base_nonce_action_name;
			}

			$action_url = add_query_arg(
				[
					'action'     => $key,
					'company_id' => $company->ID,
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
	 * Helper function used to check if page is company dashboard page.
	 *
	 * @since 1.9.3
	 */
	private function is_company_dashboard_page() {
		global $post;

		if ( is_page() && has_shortcode( $post->post_content, 'company_dashboard' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handles actions on company dashboard.
	 *
	 * @since 1.9.3
	 *
	 * @throws \Exception On action handling error.
	 */
	public function handle_actions() {
		$should_run_handler = apply_filters( 'cariera_company_should_run_shortcode_action_handler', $this->is_company_dashboard_page() );

		if ( ! $should_run_handler
			|| empty( $_REQUEST['action'] )
			|| empty( $_REQUEST['company_id'] )
			|| empty( $_REQUEST['_wpnonce'] )
		) {
			return;
		}

		$company_id = isset( $_REQUEST['company_id'] ) ? absint( $_REQUEST['company_id'] ) : 0;
		$action     = sanitize_title( wp_unslash( $_REQUEST['action'] ) );

		$company         = get_post( $company_id );
		$company_actions = $this->get_company_actions( $company );

		if (
			! isset( $company_actions[ $action ] )
			|| empty( $company_actions[ $action ]['nonce'] )
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce should not be modified.
			|| ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), $company_actions[ $action ]['nonce'] )
		) {
			return;
		}

		try {
			// Check ownership.
			if ( empty( $company ) || \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY !== $company->post_type || ! cariera_user_can_edit_company( $company_id ) ) {
				throw new \Exception( esc_html__( 'Invalid Company ID', 'cariera-core' ) );
			}

			switch ( $action ) {
				case 'delete':
					// Trash it.
					wp_trash_post( $company_id );

					// Message.
					// translators: Placeholder %s is the job listing title.
					$this->company_dashboard_message = \WP_Job_Manager\UI\Notice::success( sprintf( __( '%s has been deleted', 'cariera-core' ), $company->post_title ) );

					break;
				case 'hide':
					if ( 'publish' === $company->post_status ) {
						$update_company = [
							'ID'          => $company_id,
							'post_status' => 'hidden',
						];
						wp_update_post( $update_company );
						// translators: %s is the company title.
						$this->company_dashboard_message = \WP_Job_Manager\UI\Notice::success( sprintf( __( '%s has been hidden', 'cariera-core' ), $company->post_title ) );
					}
					break;
				case 'publish':
					if ( 'hidden' === $company->post_status ) {
						$update_company = [
							'ID'          => $company_id,
							'post_status' => 'publish',
						];
						wp_update_post( $update_company );
						// translators: %s is the company title.
						$this->company_dashboard_message = \WP_Job_Manager\UI\Notice::success( sprintf( __( '%s has been published', 'cariera-core' ), $company->post_title ) );
					}
					break;
				default:
					do_action( 'cariera_company_dashboard_do_action_' . $action, $company_id );
					break;
			}

			do_action( 'cariera_my_company_do_action', $action, $company_id );

			/**
			 * Set a success message for a custom dashboard action handler.
			 */
			$success_message = apply_filters( 'cariera_company_dashboard_success_message', '', $action, $company_id );
			if ( $success_message ) {
				$this->company_dashboard_message = \WP_Job_Manager\UI\Notice::success( $success_message );
			}
		} catch ( \Exception $e ) {
			$this->company_dashboard_message = \WP_Job_Manager\UI\Notice::error( $e->getMessage() );
		}

		\WP_Job_Manager\UI\Redirect_Message::redirect( remove_query_arg( [ 'action', 'company_id', '_wpnonce' ] ), $this->company_dashboard_message, 'updated' );
	}

	/**
	 * Get the URL of the [company_dashboard] page.
	 *
	 * @since 1.9.3
	 */
	public static function get_company_dashboard_page_url() {
		$page_id = get_option( 'cariera_company_dashboard_page' );
		if ( $page_id ) {
			return (string) get_permalink( $page_id );
		} else {
			return home_url( '/' );
		}
	}

	/**
	 * Check if the current user can manage this company listing.
	 *
	 * @since 1.9.3
	 *
	 * @param \WP_Post|null $company
	 */
	public function can_manage_company( $company ) {

		if ( ! get_current_user_id()
			|| empty( $company )
			|| ! $company instanceof \WP_Post
			|| \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY !== $company->post_type ) {
			return false;
		}

		return is_admin() ? current_user_can( 'edit_posts', $company->ID ) : $this->is_company_available_on_dashboard( $company );
	}

	/**
	 * Check if a company is listed on the current user's company dashboard page.
	 *
	 * @since 1.7.0
	 *
	 * @param WP_Post $company Company post object.
	 */
	private function is_company_available_on_dashboard( \WP_Post $company ) {
		// Check cache of currently displayed company dashboard IDs first to avoid lots of queries.
		if ( isset( $this->company_dashboard_company_ids ) && in_array( (int) $company->ID, $this->company_dashboard_company_ids, true ) ) {
			return true;
		}

		$args           = $this->get_company_dashboard_query_args();
		$args['p']      = $company->ID;
		$args['fields'] = 'ids';

		$query = new \WP_Query( $args );

		return (int) $query->post_count > 0;
	}

	/**
	 * Helper that generates the company dashboard query args.
	 *
	 * @since   1.7.0
	 * @version 1.9.3
	 *
	 * @param int $args
	 */
	private function get_company_dashboard_query_args( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'post_type'           => \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY,
				'post_status'         => [ 'publish', 'expired', 'pending', 'draft', 'preview', 'private', 'hidden' ],
				'orderby'             => 'date',
				'order'               => 'desc',
				'author'              => get_current_user_id(),
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => -1,
			]
		);

		if ( ! empty( $args['posts_per_page'] ) && $args['posts_per_page'] > 0 ) {
			$args['offset'] = ( max( 1, get_query_var( 'paged' ) ) - 1 ) * $args['posts_per_page'];
		}

		/**
		 * Customize the query that is used to get jobs on the company dashboard.
		 */
		return apply_filters( 'cariera_company_manager_get_dashboard_companies_args', $args );
	}

	/**
	 * Add a flash message to display on a company dashboard.
	 *
	 * @since 1.4.7
	 *
	 * @param string $message
	 * @param bool   $is_error
	 */
	public static function add_company_dashboard_message( $message, $is_error = false ) {
		$company_dashboard_page_id = get_option( 'cariera_company_dashboard_page' );
		if ( ! wp_get_session_token() || ! $company_dashboard_page_id ) {
			// We only handle flash messages when the company dashboard page ID is set and user has valid session token.
			return false;
		}
		$messages_key = self::get_company_dashboard_message_key();
		$messages     = self::get_company_dashboard_messages( false );

		$messages[] = [
			'message'  => $message,
			'is_error' => $is_error,
		];

		set_transient( $messages_key, wp_json_encode( $messages ), HOUR_IN_SECONDS );

		return true;
	}

	/**
	 * Gets the current flash messages for the listing dashboard.
	 *
	 * @since 1.4.7
	 *
	 * @param mixed $clear
	 */
	private static function get_company_dashboard_messages( $clear ) {
		$messages_key = self::get_company_dashboard_message_key();
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
	 * Get the transient key to use to store listing dashboard messages.
	 *
	 * @since 1.4.7
	 */
	private static function get_company_dashboard_message_key() {
		return 'company_dashboard_messages_' . md5( wp_get_session_token() );
	}
}
