<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'the_title', [ $this, 'add_breadcrumb_to_the_title' ] );
		add_action( 'wp_loaded', [ $this, 'delete_handler' ] );
		add_action( 'wp_loaded', [ $this, 'edit_handler' ] );
		add_action( 'wp_loaded', [ $this, 'csv_handler' ] );
		add_filter( 'job_manager_job_dashboard_columns', [ $this, 'add_applications_columns' ] );
		add_action( 'job_manager_job_dashboard_column_applications', [ $this, 'applications_column' ] );
		add_action( 'job_manager_job_dashboard_content_show_applications', [ $this, 'show_applications' ] );
		add_filter( 'job_manager_job_stats_summary', [ $this, 'application_stats' ], 10, 2 );

		// Ajax.
		add_action( 'wp_ajax_add_job_application_note', [ $this, 'add_job_application_note' ] );
		add_action( 'wp_ajax_delete_job_application_note', [ $this, 'delete_job_application_note' ] );

		// Secure order notes.
		add_filter( 'comments_clauses', [ __CLASS__, 'exclude_application_comments' ], 10, 1 );
		add_action( 'comment_feed_join', [ $this, 'exclude_application_comments_from_feed_join' ] );
		add_action( 'comment_feed_where', [ $this, 'exclude_application_comments_from_feed_where' ] );
	}

	/**
	 * Change page titles
	 *
	 * @since   0.9.3
	 * @version 0.9.7
	 *
	 * @param string $post_title
	 */
	public function add_breadcrumb_to_the_title( $post_title ) {
		global $post;

		if ( is_main_query() && is_page() && strstr( $post->post_content, '[job_dashboard' ) && in_the_loop() ) {
			remove_filter( 'the_title', [ $this, 'add_breadcrumb_to_the_title' ] );
			if ( ! empty( $_GET['action'] ) && 'show_applications' === $_GET['action'] ) {
				$job_id = isset( $_GET['job_id'] ) ? absint( $_GET['job_id'] ) : 0;
				if ( \WP_Job_Manager_Post_Types::PT_LISTING === get_post_type( $job_id ) ) {
					$post_title = esc_html__( 'Job Applications', 'cariera-addons' ) . ' &laquo; <a href="' . get_permalink( $post->ID ) . '">' . $post_title . '</a>';
				}
			}
		}

		return $post_title;
	}

	/**
	 * See if user can edit the application
	 *
	 * @since 0.9.3
	 *
	 * @param int $application_id
	 */
	public function can_edit_application( $application_id ) {
		$application = get_post( $application_id );

		if ( ! $application ) {
			return false;
		}

		$job = get_post( $application->post_parent );

		// Permissions.
		if ( ! $job || ! $application || \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION !== $application->post_type || \WP_Job_Manager_Post_Types::PT_LISTING !== $job->post_type || ! job_manager_user_can_edit_job( $job->ID ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Edit an application
	 *
	 * @since   0.9.3
	 * @version 1.0.2
	 */
	public function edit_handler() {
		// phpcs:ignore
		if ( ! empty( $_POST['wp_job_manager_edit_application'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'edit_job_application' ) ) {
			global $wp_post_statuses;

			$application_id = isset( $_POST['application_id'] ) ? absint( $_POST['application_id'] ) : 0;

			if ( ! $this->can_edit_application( $application_id ) ) {
				return;
			}

			$application_status = isset( $_POST['application_status'] ) ? sanitize_text_field( wp_unslash( $_POST['application_status'] ) ) : '';
			$application_rating = isset( $_POST['application_rating'] ) ? floatval( wp_unslash( $_POST['application_rating'] ) ) : 0;
			$application_rating = $application_rating < 0 ? 0 : $application_rating;
			$application_rating = $application_rating > 5 ? 5 : $application_rating;

			update_post_meta( $application_id, '_rating', $application_rating );

			if ( array_key_exists( $application_status, $wp_post_statuses ) ) {
				wp_update_post(
					[
						'ID'          => $application_id,
						'post_status' => $application_status,
					]
				);
			}
		}
	}

	/**
	 * Delete an application
	 *
	 * @since   0.9.3
	 * @version 1.0.2
	 */
	public function delete_handler() {
		// phpcs:ignore
		if ( ! empty( $_GET['delete_job_application'] ) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'delete_job_application' ) ) {
			$application_id = absint( $_GET['delete_job_application'] );

			if ( ! $this->can_edit_application( $application_id ) ) {
				return;
			}

			wp_delete_post( $application_id, true );
		}
	}

	/**
	 * Download a CSV
	 *
	 * @since   0.9.3
	 * @version 1.1.0
	 */
	public function csv_handler() {
		// phpcs:ignore
		if ( ! empty( $_GET['download-csv'] ) ) {
			$job_id = isset( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0; // phpcs:ignore
			$job    = get_post( $job_id );

			// Permissions.
			if ( ! job_manager_user_can_edit_job( $job ) ) {
				return;
			}

			$args = apply_filters(
				'job_manager_job_applications_args',
				[
					'post_type'           => \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION,
					'post_status'         => array_merge( array_keys( get_job_application_statuses() ), [ 'publish' ] ),
					'ignore_sticky_posts' => 1,
					'posts_per_page'      => -1,
					'post_parent'         => $job_id,
				]
			);

			// Filters.
			$application_status  = ! empty( $_GET['application_status'] ) ? sanitize_text_field( wp_unslash( $_GET['application_status'] ) ) : ''; // phpcs:ignore
			$application_orderby = ! empty( $_GET['application_orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['application_orderby'] ) ) : ''; // phpcs:ignore

			if ( $application_status ) {
				$args['post_status'] = $application_status;
			}

			switch ( $application_orderby ) {
				case 'name':
					$args['order']   = 'ASC';
					$args['orderby'] = 'post_title';
					break;
				case 'rating':
					$args['order']    = 'DESC';
					$args['orderby']  = 'meta_value';
					$args['meta_key'] = '_rating'; // phpcs:ignore
					break;
				default:
					$args['order']   = 'DESC';
					$args['orderby'] = 'date';
					break;
			}

			$applications = get_posts( $args );

			@set_time_limit( 0 );
			if ( function_exists( 'apache_setenv' ) ) {
				@apache_setenv( 'no-gzip', 1 );
			}
			@ini_set( 'zlib.output_compression', 0 );

			header( 'Content-Type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=' . __( 'applications', 'cariera-addons' ) . '.csv' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			$fp = fopen( 'php://output', 'w' );

			// Excel-friendly UTF-8 BOM.
			fprintf( $fp, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

			// CSV settings.
			$delimiter = ',';
			$enclosure = '"';
			$escape    = '\\';

			$row = [
				esc_html__( 'Application date', 'cariera-addons' ),
				esc_html__( 'Application status', 'cariera-addons' ),
				esc_html__( 'Applicant name', 'cariera-addons' ),
				esc_html__( 'Applicant email', 'cariera-addons' ),
				esc_html__( 'Job applied for', 'cariera-addons' ),
				esc_html__( 'Attachment', 'cariera-addons' ),
				esc_html__( 'Applicant message', 'cariera-addons' ),
				esc_html__( 'Rating', 'cariera-addons' ),
			];

			// Other custom fields.
			$custom_fields = [];

			foreach ( $applications as $application ) {
				$custom_fields = array_merge( $custom_fields, array_keys( get_post_custom( $application->ID ) ) );
			}

			$custom_fields = array_unique( $custom_fields );
			$custom_fields = array_diff(
				$custom_fields,
				[
					'_edit_lock',
					'_attachment',
					'_attachment_file',
					'_job_applied_for',
					'_candidate_email',
					'_candidate_user_id',
					'_rating',
					'_application_source',
					'_secret_dir',
				]
			);

			foreach ( $custom_fields as $custom_field ) {
				$row[] = $custom_field;
			}

			fputcsv( $fp, $row, $delimiter, $enclosure, $escape );

			foreach ( $applications as $application ) {
				$row   = [];
				$row[] = date_i18n( get_option( 'date_format' ), strtotime( $application->post_date ) );
				$row[] = $this->convert_encoding_to_utf8( $application->post_status );
				$row[] = $this->convert_encoding_to_utf8( $application->post_title );
				$row[] = $this->convert_encoding_to_utf8( get_job_application_email( $application->ID ) );
				$row[] = $this->convert_encoding_to_utf8( get_the_title( $application->post_parent ) );
				$row[] = $this->convert_encoding_to_utf8( implode( '; ', get_job_application_attachments( $application->ID ) ) );
				$row[] = $this->convert_encoding_to_utf8( $application->post_content );
				$row[] = get_job_application_rating( $application->ID );

				foreach ( $custom_fields as $custom_field ) {
					$custom_field_value = get_post_meta( $application->ID, $custom_field, true );

					if ( is_array( $custom_field_value ) ) {
						$custom_field_value = wp_json_encode( $custom_field_value );
					}
					$row[] = $this->convert_encoding_to_utf8( $custom_field_value );
				}

				fputcsv( $fp, $row, $delimiter, $enclosure, $escape );
			}

			fclose( $fp );
			exit;
		}
	}

	/**
	 * Convert encoding to UTF-8
	 *
	 * @since 0.9.3
	 *
	 * @param string $string
	 */
	private function convert_encoding_to_utf8( $string ) {
		static $has_mb = null;
		if ( null === $has_mb ) {
			$has_mb = function_exists( 'mb_convert_encoding' ) && function_exists( 'mb_detect_encoding' );
		}

		if ( ! $has_mb ) {
			return $string;
		}

		$encoding = strtoupper( mb_detect_encoding( $string, null, true ) ?? '' );

		if ( empty( $encoding ) || 'UTF-8' === $encoding ) {
			return $string;
		}
		return mb_convert_encoding( $string, 'UTF-8', $encoding );
	}

	/**
	 * Add a new column to the job dashboard
	 *
	 * @since 0.9.3
	 *
	 * @param array $columns
	 */
	public function add_applications_columns( $columns ) {
		$columns['applications'] = esc_html__( 'Applications', 'cariera-addons' );
		return $columns;
	}

	/**
	 * Show the count of applications in the job dashboard
	 *
	 * @since 0.9.3
	 *
	 * @param object $job
	 */
	public function applications_column( $job ) {
		global $post;

		$count = get_job_application_count( $job->ID );

		$link = add_query_arg(
			[
				'action' => 'show_applications',
				'job_id' => $job->ID,
			],
			get_permalink( $post->ID )
		);

		$new_applications = $count ? get_job_application_count( $job->ID, 'new' ) : 0;

		if ( $count ) {
			// translators: Placeholder is the number of applications.
			echo '<a href="' . esc_attr( $link ) . '">' . esc_html( sprintf( _n( '%s application', '%s applications', $count, 'cariera-addons' ), $count ) ) . '</a>';
		}

		if ( $new_applications ) {
			echo '<div class="jm-ui-row">'
					. '<small>'
					// translators: Placeholder is the number of new applications.
					. esc_html( sprintf( _n( '%s new', '%s new', $new_applications, 'cariera-addons' ), $new_applications ) )
					. '</small>'
					. '<a class="jm-ui-marker-dot"></a>'
				. '</div>';
		}
	}

	/**
	 * Show applications on the job dashboard
	 *
	 * @since   0.9.3
	 * @version 1.0.2
	 *
	 * @param array $atts
	 */
	public function show_applications( $atts ) {
		// Get and validate job ID from GET request.
		$job_id = isset( $_GET['job_id'] ) ? absint( $_GET['job_id'] ) : 0; // phpcs:ignore
		if ( ! $job_id ) {
			return;
		}

		// Ensure the job exists and is of the correct post type.
		$job = get_post( $job_id );
		if ( ! $job || \WP_Job_Manager_Post_Types::PT_LISTING !== $job->post_type ) {
			return;
		}

		// Parse shortcode attributes.
		$atts           = shortcode_atts(
			[
				'posts_per_page' => '20',
			],
			$atts
		);
		$posts_per_page = absint( $atts['posts_per_page'] );

		// Optional: remove previously added filters if needed.
		remove_filter( 'the_title', [ $this, 'add_breadcrumb_to_the_title' ] );

		// Permissions check.
		if ( ! job_manager_user_can_edit_job( $job_id ) ) {
			esc_html_e( 'You do not have permission to view this job.', 'cariera-addons' );
			return;
		}

		// Enqueue necessary scripts.
		wp_enqueue_script( 'cariera-addons-applications-dashboard' );

		// Handle application filters.
		$application_status  = ! empty( $_GET['application_status'] ) ? sanitize_text_field( wp_unslash( $_GET['application_status'] ) ) : ''; // phpcs:ignore
		$application_orderby = ! empty( $_GET['application_orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['application_orderby'] ) ) : ''; // phpcs:ignore

		// Validate application status if needed.
		$valid_statuses = array_keys( get_job_application_statuses() );
		if ( $application_status && ! in_array( $application_status, $valid_statuses, true ) ) {
			$application_status = '';
		}

		// Build query arguments.
		$args = apply_filters(
			'job_manager_job_applications_args',
			[
				'post_type'           => \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION,
				'post_status'         => array_diff( array_merge( $valid_statuses, [ 'publish' ] ), [ 'archived' ] ),
				'ignore_sticky_posts' => true,
				'posts_per_page'      => $posts_per_page,
				'offset'              => ( max( 1, get_query_var( 'paged' ) ) - 1 ) * $posts_per_page,
				'post_parent'         => $job_id,
			]
		);

		if ( $application_status ) {
			$args['post_status'] = $application_status;
		}

		switch ( $application_orderby ) {
			case 'name':
				$args['order']   = 'ASC';
				$args['orderby'] = 'post_title';
				break;
			case 'rating':
				$args['order']    = 'DESC';
				$args['orderby']  = 'meta_value';
				$args['meta_key'] = '_rating';
				break;
			default:
				$args['order']   = 'DESC';
				$args['orderby'] = 'date';
				break;
		}

		// Run query.
		$applications_query = new \WP_Query( $args );

		// Define columns.
		$columns = apply_filters(
			'job_manager_job_applications_columns',
			[
				'name'  => esc_html__( 'Name', 'cariera-addons' ),
				'email' => esc_html__( 'Email', 'cariera-addons' ),
				'date'  => esc_html__( 'Date Received', 'cariera-addons' ),
			]
		);

		// Load the applications template.
		get_job_manager_template(
			'applications/job-applications.php',
			[
				'applications'        => $applications_query->posts,
				'job_id'              => $job_id,
				'max_num_pages'       => $applications_query->max_num_pages,
				'columns'             => $columns,
				'application_status'  => $application_status,
				'application_orderby' => $application_orderby,
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}

	/**
	 * Add note via ajax
	 *
	 * @since   0.9.3
	 * @version 0.9.10
	 */
	public function add_job_application_note() {
		check_ajax_referer( 'job-application-notes', 'security' );

		$application_id = isset( $_POST['application_id'] ) ? absint( $_POST['application_id'] ) : 0;
		$application    = get_post( $application_id );
		$note           = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );

		if ( $application_id > 0 && $this->can_edit_application( $application_id ) ) {
			$user                 = get_user_by( 'id', get_current_user_id() );
			$comment_author       = $user->display_name;
			$comment_author_email = $user->user_email;
			$comment_post_id      = $application_id;
			$comment_author_url   = '';
			$comment_content      = $note;
			$comment_agent        = 'WP Job Manager';
			$comment_type         = 'job_application_note';
			$comment_parent       = 0;
			$comment_approved     = 1;
			$commentdata          = apply_filters( 'job_application_note_data', compact( 'comment_post_id', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_agent', 'comment_type', 'comment_parent', 'comment_approved' ), $application_id );
			$comment_id           = wp_insert_comment( $commentdata );

			echo '<li rel="' . esc_attr( $comment_id ) . '" class="job-application-note"><div class="job-application-note-content">';
			echo wpautop( wptexturize( $note ) );
			echo '</div><p class="job-application-note-meta"><a href="#" class="delete_note">' . esc_html__( 'Delete note', 'cariera-addons' ) . '</a></p>';
			echo '</li>';
		}

		die();
	}

	/**
	 * Delete note via ajax
	 *
	 * @since   0.9.3
	 * @version 0.9.4
	 */
	public function delete_job_application_note() {
		check_ajax_referer( 'job-application-notes', 'security' );

		$note_id = isset( $_POST['note_id'] ) ? absint( $_POST['note_id'] ) : 0;
		if ( $note_id ) {
			$note           = get_comment( $note_id );
			$application_id = absint( $note->comment_post_id );
			$application    = get_post( $application_id );
			if ( $application_id > 0 && $this->can_edit_application( $application_id ) ) {
				wp_delete_comment( $note_id );
			}
		}
		die();
	}

	/**
	 * Exclude application comments from queries and RSS
	 *
	 * This code should exclude comments from queries. Some queries (like the recent comments widget on the dashboard) are hardcoded
	 * and are not filtered, however, the code current_user_can( 'read_post', $comment->comment_post_id ) should keep them safe.
	 *
	 * The frontend view order pages get around this filter by using remove_filter.
	 *
	 * @since   0.9.3
	 * @version 0.9.4
	 *
	 * @param array $clauses
	 */
	public static function exclude_application_comments( $clauses ) {
		global $wpdb, $typenow, $pagenow;

		// Avoid altering queries in admin list screen for job applications.
		if ( is_admin() && \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION === $typenow ) {
			return $clauses;
		}

		$post_type = esc_sql( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION );

		// Add join only if not already present.
		if ( strpos( $clauses['join'], 'JOIN ' . $wpdb->posts . ' AS cariera_post_filter' ) === false ) {
			$clauses['join'] .= " LEFT JOIN {$wpdb->posts} AS cariera_post_filter ON comment_post_id = cariera_post_filter.ID ";
		}

		// Append to WHERE.
		if ( ! empty( $clauses['where'] ) ) {
			$clauses['where'] .= ' AND ';
		}

		$clauses['where'] .= " cariera_post_filter.post_type NOT IN ('$post_type') ";

		return $clauses;
	}


	/**
	 * Exclude comments from queries and RSS
	 *
	 * @since 0.9.3
	 *
	 * @param string $join
	 */
	public function exclude_application_comments_from_feed_join( $join ) {
		global $wpdb;

		if ( ! strstr( $join, $wpdb->posts ) ) {
			$join = " LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_id = $wpdb->posts.ID ";
		}

		return $join;
	}

	/**
	 * Exclude order comments from queries and RSS
	 *
	 * @since   0.9.3
	 * @version 0.9.10
	 *
	 * @param string $where
	 */
	public function exclude_application_comments_from_feed_where( $where ) {
		global $wpdb;

		if ( $where ) {
			$where .= ' AND ';
		}

		$post_type = \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION;

		$where .= $wpdb->prepare(
			" $wpdb->posts.post_type NOT IN ( %s ) ",
			$post_type
		);

		return $where;
	}

	/**
	 * Add application stats to the job overlay.
	 *
	 * @since 0.9.3
	 *
	 * @param array  $stats
	 * @param object $job
	 */
	public function application_stats( $stats, $job ) {
		$application_by_status = Application_Stats::get_application_stats( $job->ID );
		$statuses              = get_job_application_statuses();

		$counts = [];
		$total  = 0;

		foreach ( $statuses as $status => $label ) {
			$value    = $application_by_status[ $status ]->count ?? 0;
			$counts[] = [
				'label' => $label,
				'value' => $value,
			];
			$total   += $value;
		}

		if ( $total > 0 ) {
			foreach ( $counts as &$count ) {
				$count['background'] = round( $count['value'] / $total * 100 );
			}
		}

		array_unshift(
			$counts,
			[
				'label'      => esc_html__( 'Total', 'cariera-addons' ),
				'value'      => $total,
				'background' => $total > 0 ? 0 : null,
			]
		);

		$stats['applications'] = [
			'title'  => esc_html__( 'Applications', 'cariera-addons' ),
			'stats'  => $counts,
			'column' => 3,
		];

		return $stats;
	}
}
