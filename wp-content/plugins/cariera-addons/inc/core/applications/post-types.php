<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Types {

	use \Cariera_Addons\Src\Traits\Singleton;

	const CPT_APPLICATION      = 'job_application';
	const CPT_APPLICATION_FORM = 'job_application_form';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_types' ], 20 );
		add_action( 'init', [ $this, 'register_meta_fields' ], 20 );

		// Output single job.
		add_filter( 'wpjm_the_job_title', [ $this, 'already_applied_title' ], 10, 2 );
		add_action( 'single_job_listing_meta_after', [ $this, 'already_applied_message' ] );

		// Delete applications.
		if ( get_option( 'job_application_delete_with_job', 0 ) ) {
			add_action( 'delete_post', [ $this, 'delete_post' ] );
			add_action( 'wp_trash_post', [ $this, 'trash_post' ] );
			add_action( 'untrash_post', [ $this, 'untrash_post' ] );
		}
		add_action( 'before_delete_post', [ $this, 'delete_application_files' ] );
		add_action( 'job_applications_purge', [ $this, 'job_applications_purge' ] );

		// Other.
		add_action( 'transition_post_status', [ $this, 'transition_post_status' ], 10, 3 );
		add_filter( 'post_class', [ $this, 'add_applied_post_class' ], 10, 3 );
	}

	/**
	 * Register post types function.
	 */
	public function register_post_types() {
		$this->register_application_post_type();
		$this->register_application_form_post_type();
	}

	/**
	 * Register Job Application post type and statuses.
	 *
	 * @since   0.9.3
	 * @version 0.9.4
	 */
	public function register_application_post_type() {
		if ( post_type_exists( self::CPT_APPLICATION ) ) {
			return;
		}

		$singular = esc_html__( 'Application', 'cariera-addons' );
		$plural   = esc_html__( 'Job Applications', 'cariera-addons' );

		register_post_type(
			self::CPT_APPLICATION,
			apply_filters(
				'cariera_addons_post_type_job_application',
				[
					'labels'              => \Cariera_Addons\Helpers::create_post_type_labels( $singular, $plural ),
					'description'         => esc_html__( 'This is where you can edit and view applications.', 'cariera-addons' ),
					'public'              => false,
					'show_ui'             => true,
					'menu_icon'           => 'dashicons-groups',
					'capability_type'     => 'job_application',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => [ 'title', 'custom-fields', 'editor' ],
					'has_archive'         => false,
					'show_in_nav_menus'   => false,
					'delete_with_user'    => true,
					'menu_position'       => 31,
				]
			)
		);

		$applicaton_statuses = get_job_application_statuses();

		foreach ( $applicaton_statuses as $name => $label ) {
			register_post_status(
				$name,
				apply_filters(
					'register_job_application_status',
					[
						'label'                     => $label,
						'public'                    => true,
						'exclude_from_search'       => 'archived' === $name ? true : false,
						'show_in_admin_all_list'    => 'archived' === $name ? false : true,
						'show_in_admin_status_list' => true,
						'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'cariera-addons' ), // phpcs:ignore
					],
					$name
				)
			);
		}
	}

	/**
	 * Register Application Form post type.
	 *
	 * @since   0.9.3
	 * @version 0.9.11
	 */
	public function register_application_form_post_type() {
		if ( post_type_exists( self::CPT_APPLICATION_FORM ) ) {
			return;
		}

		$singular = esc_html__( 'Application Form', 'cariera-addons' );
		$plural   = esc_html__( 'Application Forms', 'cariera-addons' );

		register_post_type(
			self::CPT_APPLICATION_FORM,
			apply_filters(
				'cariera_addons_post_type_job_application_form',
				[
					'labels'              => array_merge(
						\Cariera_Addons\Helpers::create_post_type_labels( $singular, $plural ),
						[
							'all_items' => $plural,
						]
					),
					'description'         => esc_html__( 'This is where you can edit and view application forms.', 'cariera-addons' ),
					'public'              => false,
					'show_ui'             => true,
					'capabilities'        => [
						'edit_post'              => 'manage_options',
						'read_post'              => 'manage_options',
						'delete_post'            => 'manage_options',
						'edit_posts'             => 'manage_options',
						'edit_others_posts'      => 'manage_options',
						'publish_posts'          => 'manage_options',
						'read_private_posts'     => 'manage_options',
						'delete_posts'           => 'manage_options',
						'delete_private_posts'   => 'manage_options',
						'delete_published_posts' => 'manage_options',
						'delete_others_posts'    => 'manage_options',
						'edit_private_posts'     => 'manage_options',
						'edit_published_posts'   => 'manage_options',
					],
					'map_meta_cap'        => false,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => [ 'title' ],
					'has_archive'         => false,
					'show_in_nav_menus'   => false,
					'delete_with_user'    => false,
					'menu_position'       => 32,
					'show_in_menu'        => 'edit.php?post_type=' . self::CPT_APPLICATION,
				]
			)
		);
	}

	/**
	 * Register application form meta fields.
	 *
	 * @since 0.9.3
	 */
	public function register_meta_fields() {
		$fields = self::get_application_form_meta_fields();

		foreach ( $fields as $meta_key => $field ) {
			register_post_meta(
				self::CPT_APPLICATION_FORM,
				$meta_key,
				[
					'type'              => $field['data_type'],
					'show_in_rest'      => $field['show_in_rest'],
					'description'       => $field['label'],
					'sanitize_callback' => $field['sanitize_callback'],
					'auth_callback'     => $field['auth_callback'],
					'single'            => true,
					'show_in_admin'     => $field['show_in_admin'],
				]
			);
		}
	}

	/**
	 * Returns configuration for custom fields on Application Form posts.
	 *
	 * @since 0.9.3
	 */
	public static function get_application_form_meta_fields() {
		$default_field = [
			'description'       => null,
			'default'           => null,
			'type'              => 'text',
			'show_in_rest'      => false,
			'auth_callback'     => [ __CLASS__, 'auth_check_can_edit_forms' ],
			'sanitize_callback' => [ __CLASS__, 'sanitize_meta_field_based_on_input_type' ],
		];

		$fields = [
			'_form_fields'             => [
				'label'             => esc_html__( 'Form Fields', 'cariera-addons' ),
				'placeholder'       => '',
				'description'       => '',
				'priority'          => 1,
				'data_type'         => 'array',
				'sanitize_callback' => null,
				'show_in_admin'     => true,
				'show_in_rest'      => false,
			],
			'_employer_email_subject'  => [
				'label'         => esc_html__( 'Employer Email Subject', 'cariera-addons' ),
				'placeholder'   => '',
				'description'   => '',
				'priority'      => 1,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => false,
			],
			'_employer_email_content'  => [
				'label'         => esc_html__( 'Employer Email Content', 'cariera-addons' ),
				'placeholder'   => '',
				'description'   => '',
				'priority'      => 1,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => false,
			],
			'_candidate_email_subject' => [
				'label'         => esc_html__( 'Candidate Email Subject', 'cariera-addons' ),
				'placeholder'   => '',
				'description'   => '',
				'priority'      => 1,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => false,
			],
			'_candidate_email_content' => [
				'label'         => esc_html__( 'Candidate Email Content', 'cariera-addons' ),
				'placeholder'   => '',
				'description'   => '',
				'priority'      => 1,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => false,
			],
		];

		// Set default fields.
		foreach ( $fields as $key => $field ) {
			$fields[ $key ] = array_merge( $default_field, $field );
		}

		return $fields;
	}

	/**
	 * Checks if user can edit application forms.
	 *
	 * @since 0.9.3
	 *
	 * @param bool   $allowed   Whether the user can edit the meta.
	 * @param string $meta_key  The meta key.
	 * @param int    $post_id   Post ID.
	 * @param int    $user_id   User ID.
	 */
	public static function auth_check_can_edit_forms( $allowed, $meta_key, $post_id, $user_id ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Sanitize meta fields based on input type.
	 *
	 * @since 0.9.3
	 *
	 * @param mixed  $meta_value Value of meta field that needs sanitization.
	 * @param string $meta_key   Meta key that is being sanitized.
	 */
	public static function sanitize_meta_field_based_on_input_type( $meta_value, $meta_key ) {
		$fields = self::get_application_form_meta_fields();

		if ( is_string( $meta_value ) ) {
			$meta_value = trim( $meta_value );
		}

		$type = 'text';
		if ( isset( $fields[ $meta_key ] ) ) {
			$type = $fields[ $meta_key ]['type'];
		}

		if (
			'textarea' === $type ||
			'wp_editor' === $type ||
			'_candidate_email_content' === $meta_key ||
			'_employer_email_content' === $meta_key
		) {
			return wp_kses_post( wp_unslash( $meta_value ) );
		}

		if ( 'checkbox' === $type ) {
			if ( $meta_value && '0' !== $meta_value ) {
				return 1;
			}

			return 0;
		}

		if ( is_array( $meta_value ) ) {
			return array_filter( array_map( 'sanitize_text_field', $meta_value ) );
		}

		return sanitize_text_field( $meta_value );
	}

	/**
	 * Append 'Applied' on jobs that have already been applied for.
	 *
	 * @since   0.9.3
	 * @version 0.9.10
	 *
	 * @param string      $title
	 * @param WP_Post|int $post
	 */
	public function already_applied_title( $title, $post = null ) {
		$post = get_post( $post );

		if ( ! is_admin()
			&& $post
			&& \WP_Job_Manager_Post_Types::PT_LISTING === get_post_type( $post )
			&& ! is_single()
			&& empty( $_POST['cariera_addons_resumes_apply_with_resume'] )
			&& empty( $_GET['download-csv'] )
			&& user_has_applied_for_job( get_current_user_id(), $post )
		) {
			$title .= ' <span class="job-manager-applications-applied-notice">' . esc_html__( 'Applied', 'cariera-addons' ) . '</span>';
		}
		return $title;
	}

	/**
	 * Show message if already applied
	 *
	 * @since   0.9.3
	 * @version 1.0.8
	 */
	public function already_applied_message() {
		global $post;

		if ( user_has_applied_for_job( get_current_user_id(), $post->ID ) ) {
			echo \Cariera_Addons\Helpers::get_template( 'applications/applied-notice.php', [] );
		}
	}

	/**
	 * Delete applications when deleting a job.
	 *
	 * @since   0.9.3
	 * @version 0.9.7
	 *
	 * @param int $id Post ID.
	 */
	public function delete_post( $id ) {
		if ( $id > 0 ) {

			$post_type = get_post_type( $id );

			if ( \WP_Job_Manager_Post_Types::PT_LISTING === $post_type ) {
				$applications = get_children( 'post_parent=' . $id . '&post_type=' . self::CPT_APPLICATION . '&post_status=trash,any' );

				if ( $applications ) {
					foreach ( $applications as $application ) {
						wp_delete_post( $application->ID, true );
					}
				}
			}
		}
	}

	/**
	 * Trashes applications when trashing the job.
	 *
	 * @since   0.9.3
	 * @version 0.9.7
	 *
	 * @param int $id Post ID.
	 */
	public function trash_post( $id ) {
		if ( $id > 0 ) {
			$post_type = get_post_type( $id );
			if ( \WP_Job_Manager_Post_Types::PT_LISTING === $post_type ) {
				$published_apps = [];
				$applications   = get_children( 'post_parent=' . $id . '&post_type=' . self::CPT_APPLICATION );
				if ( $applications ) {
					foreach ( $applications as $application ) {
						$published_apps[] = $application->ID;
						wp_trash_post( $application->ID );
					}
				}
				add_post_meta( $id, '_trashed_published_applications', $published_apps, true );
			}
		}
	}

	/**
	 * Restores applications when restoring the job.
	 *
	 * @since   0.9.3
	 * @version 0.9.7
	 *
	 * @param int $id Post ID.
	 */
	public function untrash_post( $id ) {
		if ( $id > 0 ) {
			$post_type = get_post_type( $id );

			if ( \WP_Job_Manager_Post_Types::PT_LISTING === $post_type ) {
				$applications = get_post_meta( $id, '_trashed_published_applications', true );
				if ( $applications ) {
					foreach ( $applications as $application_id ) {
						wp_untrash_post( $application_id );
					}
				}
				delete_post_meta( $id, '_trashed_published_applications' );
			}
		}
	}

	/**
	 * Recursive directory removal function
	 *
	 * @since 0.9.3
	 *
	 * @param mixed $directory
	 * TODO: improve this.
	 */
	public function recursive_rmdir( $directory ) {
		foreach ( glob( "{$directory}/*" ) as $file ) {
			if ( is_dir( $file ) ) {
				$this->recursive_rmdir( $file );
			} else {
				unlink( $file );
			}
		}
		rmdir( $directory );
	}

	/**
	 * Delete application files upon deletion.
	 *
	 * @since 0.9.3
	 *
	 * @param int $id Post ID.
	 */
	public function delete_application_files( $id ) {
		if ( $id > 0 ) {
			$post_type = get_post_type( $id );

			if ( self::CPT_APPLICATION === $post_type ) {
				$upload_dir = wp_upload_dir();
				$secret_dir = get_post_meta( $id, '_secret_dir', true );
				$dir_path   = trailingslashit( $upload_dir['basedir'] ) . 'job_applications/' . $secret_dir;
				if ( $secret_dir && is_dir( $dir_path ) ) {
					$this->recursive_rmdir( $dir_path );
				}
			}
		}
	}

	/**
	 * Purge applications after x days
	 *
	 * @since   0.9.3
	 * @version 0.9.10
	 */
	public function job_applications_purge() {
		$days = absint( get_option( 'job_application_purge_days' ) );

		if ( ! $days ) {
			return;
		}

		global $wpdb;

		$post_type = self::CPT_APPLICATION;

		$application_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT ID 
				FROM {$wpdb->posts} AS posts
				WHERE posts.post_type = %s
				AND DATEDIFF( NOW(), posts.post_date ) > %d
				",
				$post_type,
				$days
			)
		);

		if ( $application_ids ) {
			foreach ( $application_ids as $application_id ) {
				wp_delete_post( $application_id, true );
			}
		}
	}

	/**
	 * When the status changes
	 *
	 * @since 0.9.3
	 *
	 * @param mixed $new_status
	 * @param mixed $old_status
	 * @param mixed $post
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if ( self::CPT_APPLICATION !== $post->post_type ) {
			return;
		}

		$statuses = get_job_application_statuses();

		// Add a note.
		if ( $old_status !== $new_status && array_key_exists( $old_status, $statuses ) && array_key_exists( $new_status, $statuses ) ) {
			$user                 = get_user_by( 'id', get_current_user_id() );
			$comment_author       = $user->display_name;
			$comment_author_email = $user->user_email;
			$comment_post_id      = $post->ID;
			$comment_author_url   = '';
			$comment_content      = sprintf( __( 'Application status changed from "%1$s" to "%2$s"', 'cariera-addons' ), $statuses[ $old_status ], $statuses[ $new_status ] );
			$comment_agent        = 'WP Job Manager';
			$comment_type         = 'job_application_note';
			$comment_parent       = 0;
			$comment_approved     = 1;
			$commentdata          = apply_filters( 'job_application_note_data', compact( 'comment_post_id', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_agent', 'comment_type', 'comment_parent', 'comment_approved' ), $post->ID );
			$comment_id           = wp_insert_comment( $commentdata );
		}

		/**
		 * When hiring an applicant, mark the job as filled.
		 *
		 * @since 0.9.3
		 *
		 * @param bool Hiring an applicant fills a job. Default true.
		 */
		if ( 'hired' === $new_status && apply_filters( 'job_application_hired_fills_job', true ) ) {
			update_post_meta( wp_get_post_parent_id( $post->ID ), '_filled', 1 );
		}
	}

	/**
	 * Adds `job-applied` class to applied job listings.
	 *
	 * @since   0.9.3
	 * @version 1.0.8
	 *
	 * @param array $classes An array of post classes.
	 * @param array $class   An array of additional classes added to the post.
	 * @param int   $post_id The post ID.
	 */
	public function add_applied_post_class( $classes, $class, $post_id ) {
		if ( is_admin() ) {
			return $classes;
		}

		if ( \WP_Job_Manager_Post_Types::PT_LISTING !== get_post_type( $post_id ) || ! is_user_logged_in() ) {
			return $classes;
		}

		if ( user_has_applied_for_job( get_current_user_id(), $post_id ) ) {
			$classes[] = 'job-applied';
		}

		return $classes;
	}

	/**
	 * Add core capabilities for Job Applications post type.
	 *
	 * @since 1.0.7
	 */
	public static function add_user_capabilities() {
		if ( ! \Cariera_Addons\Helpers::core_feature_is_enabled( 'applications' ) ) {
			return;
		}

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		if ( ! is_object( $wp_roles ) ) {
			return;
		}

		$capability_type = 'job_application';

		$capabilities = [
			// Post type.
			"edit_{$capability_type}",
			"read_{$capability_type}",
			"delete_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"read_private_{$capability_type}s",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_published_{$capability_type}s",

			// Terms.
			"manage_{$capability_type}_terms",
			"edit_{$capability_type}_terms",
			"delete_{$capability_type}_terms",
			"assign_{$capability_type}_terms",
		];

		foreach ( $capabilities as $cap ) {
			$wp_roles->add_cap( 'administrator', $cap );
		}
	}
}
