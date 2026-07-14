<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Settings page.
	 *
	 * @var \Cariera_Addons\Core\Applications\Admin\Settings
	 */
	private $settings_page;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings_page = new \Cariera_Addons\Core\Applications\Admin\Settings();

		new \Cariera_Addons\Core\Applications\Admin\Form_Editor();
		new \Cariera_Addons\Core\Applications\Admin\Writepanels();

		// Hooks.
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 12 );
		add_filter( 'job_manager_admin_screen_ids', [ $this, 'screen_ids' ] );
		add_filter( 'manage_edit-job_listing_columns', [ $this, 'job_columns' ], 12 );
		add_action( 'manage_job_listing_posts_custom_column', [ $this, 'job_custom_columns' ], 2, 2 );
		add_filter( 'enter_title_here', [ $this, 'enter_title_here' ], 1, 2 );
		add_filter( 'manage_edit-job_application_columns', [ $this, 'columns' ] );
		add_action( 'manage_job_application_posts_custom_column', [ $this, 'custom_columns' ], 2 );
		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );
		add_action( 'restrict_manage_posts', [ $this, 'restrict_manage_posts' ] );
		add_action( 'parse_query', [ $this, 'search_meta' ] );
		add_filter( 'get_search_query', [ $this, 'search_meta_label' ] );
		add_filter( 'request', [ $this, 'request' ] );
		add_filter( 'manage_edit-job_application_sortable_columns', [ $this, 'sortable_columns' ] );
		add_action( 'admin_footer-edit.php', [ $this, 'add_custom_statuses' ] );
	}

	/**
	 * Admin menu function.
	 *
	 * @since   0.9.3
	 * @version 0.9.11
	 */
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION,
			esc_html__( 'Settings', 'cariera-addons' ),
			esc_html__( 'Settings', 'cariera-addons' ),
			'manage_options',
			'job-applications-settings',
			[ $this->settings_page, 'output' ]
		);
	}

	/**
	 * Add screen ids to JM
	 *
	 * @since   0.9.3
	 * @version 0.9.10
	 *
	 * @param array $ids
	 */
	public function screen_ids( $ids ) {
		$post_type = \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION;

		$ids[] = 'edit-' . $post_type;
		$ids[] = $post_type;
		$ids[] = $post_type . '_page_job-applications-settings';

		return $ids;
	}

	/**
	 * Add applications column
	 *
	 * @since 0.9.3
	 *
	 * @param  array $columns
	 */
	public function job_columns( $columns ) {
		$new_columns = [];

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;

			if ( 'filled' === $key ) {
				$new_columns['job_applications'] = esc_html__( 'Applications', 'cariera-addons' );
			}
		}

		return $new_columns;
	}

	/**
	 * Custom application column on job_listing.
	 *
	 * @since   0.9.3
	 * @version 1.0.8
	 *
	 * @param mixed $column
	 * @param mixed $post_id
	 */
	public function job_custom_columns( $column, $post_id ) {
		if ( 'job_applications' !== $column ) {
			return;
		}

		$count = get_job_application_count( $post_id );

		if ( $count ) {
			$url = admin_url(
				add_query_arg(
					[
						's'            => '',
						'post_status'  => 'all',
						'post_type'    => \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION,
						'_job_listing' => $post_id,
					],
					'edit.php'
				)
			);

			echo '<a href="' . esc_url( $url ) . '">' . absint( $count ) . '</a>';
		} else {
			echo '&ndash;';
		}
	}

	/**
	 * Enter title here function.
	 *
	 * @since 0.9.3
	 *
	 * @param mixed $text
	 * @param mixed $post
	 */
	public function enter_title_here( $text, $post ) {
		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION === $post->post_type ) {
			return esc_html__( 'Candidate name', 'cariera-addons' );
		}
		return $text;
	}

	/**
	 * Post updated messages function.
	 *
	 * @since 0.9.3
	 *
	 * @param array $messages
	 */
	public function post_updated_messages( $messages ) {
		$messages['job_application'] = [
			0  => '',
			1  => esc_html__( 'Job application updated.', 'cariera-addons' ),
			2  => esc_html__( 'Custom field updated.', 'cariera-addons' ),
			3  => esc_html__( 'Custom field deleted.', 'cariera-addons' ),
			4  => esc_html__( 'Job application updated.', 'cariera-addons' ),
			5  => '',
			6  => esc_html__( 'Job application published.', 'cariera-addons' ),
			7  => esc_html__( 'Job application saved.', 'cariera-addons' ),
			8  => esc_html__( 'Job application submitted.', 'cariera-addons' ),
			9  => '',
			10 => esc_html__( 'Job application draft updated.', 'cariera-addons' ),
		];

		return $messages;
	}

	/**
	 * Columns function.
	 *
	 * @since 0.9.3
	 *
	 * @param mixed $columns
	 */
	public function columns( $columns ) {
		if ( ! is_array( $columns ) ) {
			$columns = [];
		}

		unset( $columns['title'], $columns['date'] );

		$columns['application_status'] = esc_html__( 'Status', 'cariera-addons' );
		$columns['candidate']          = esc_html__( 'Candidate', 'cariera-addons' );
		$columns['job']                = esc_html__( 'Job applied for', 'cariera-addons' );
		$columns['application_rating'] = esc_html__( 'Rating', 'cariera-addons' );
		$columns['application_notes']  = '<span class="application_notes_head tips" data-tip="' . esc_attr__( 'Notes', 'cariera-addons' ) . '">' . esc_attr__( 'Notes', 'cariera-addons' ) . '</span>';
		$columns['attachment']         = esc_html__( 'Attachment(s)', 'cariera-addons' );

		if ( function_exists( 'get_resume_share_link' ) ) {
			$columns['online_resume'] = esc_html__( 'Resume', 'cariera-addons' );
		}

		$columns['job_application_posted']  = esc_html__( 'Posted', 'cariera-addons' );
		$columns['job_application_actions'] = esc_html__( 'Actions', 'cariera-addons' );

		return $columns;
	}

	/**
	 * Custom columns function.
	 *
	 * @since   0.9.3
	 * @version 1.0.4
	 *
	 * @param mixed $column
	 */
	public function custom_columns( $column ) {
		global $post;

		switch ( $column ) {
			case 'application_status':
				$status = get_post_status_object( $post->post_status );

				echo '<span class="status">' . esc_html( $status ? $status->label : $post->post_status ) . '</span>';
				break;
			case 'candidate':
				$title               = $post->post_title ?: '#' . $post->ID;
				$edit_url            = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );
				$application_id_text = sprintf( __( 'Application ID: %d', 'cariera-addons' ), $post->ID );

				echo '<a href="' . esc_url( $edit_url ) . '" class="tips candidate_name" data-tip="' . esc_attr( $application_id_text ) . '">' . esc_html( $title ) . '</a>';

				$email = get_post_meta( $post->ID, '_candidate_email', true );
				if ( $email ) {
					echo '<br/><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
					echo get_avatar( $email, 42 );
				}

				echo '<div class="hidden" id="inline_' . esc_attr( $post->ID ) . '"><div class="post_title">' . esc_html( $post->post_title ) . '</div></div>';
				break;
			case 'job':
				$job = get_post( $post->post_parent );

				if ( $job && \WP_Job_Manager_Post_Types::PT_LISTING === $job->post_type ) {
					echo '<a href="' . esc_url( get_permalink( $job->ID ) ) . '" target="_blank">' . esc_html( get_the_title( $job->ID ) ) . '</a>';
				} else {
					$job_meta = get_post_meta( $post->ID, '_job_applied_for', true );
					if ( $job_meta ) {
						echo esc_html( $job_meta );
					} else {
						echo '<span class="na">&ndash;</span>';
					}
				}
				break;

			case 'attachment':
				$attachments = get_job_application_attachments( $post->ID );
				if ( ! empty( $attachments ) ) {
					foreach ( $attachments as $attachment ) {
						echo '<a href="' . esc_url( $attachment ) . '">' . esc_html( get_job_application_attachment_name( $attachment, 20 ) ) . '</a><br>';
					}
				} else {
					echo '<span class="na">&ndash;</span>';
				}
				break;

			case 'online_resume':
				$resume_id  = get_job_application_resume_id( $post->ID );
				$share_link = '';

				if ( $resume_id && function_exists( 'get_resume_share_link' ) ) {
					$share_link = get_resume_share_link( $resume_id );
				}

				if ( ! empty( $share_link ) ) {
					echo '<a href="' . esc_url( $share_link ) . '" target="_blank" rel="noopener noreferrer" class="job-application-resume">' . esc_html( get_the_title( $resume_id ) ) . '</a>';
				} else {
					echo '<span class="na">&ndash;</span>';
				}
				break;
			case 'application_rating':
				$rating     = (float) get_job_application_rating( $post->ID );
				$percentage = min( 100, max( 0, ( $rating / 5 ) * 100 ) );

				echo '<span class="job-application-rating"><span style="width: ' . esc_attr( $percentage ) . '%;"></span></span>';
				break;
			case 'application_notes':
				// translators: %d is the number of notes.
				printf( _n( '%d note', '%d notes', $post->comment_count, 'cariera-addons' ), $post->comment_count );
				break;
			case 'job_application_posted':
				echo '<strong>' . esc_html( date_i18n( get_option( 'date_format' ), get_post_time( 'U' ) ) ) . '</strong><span>';
				echo ( empty( $post->post_author ) ? esc_html__( 'by a guest', 'cariera-addons' ) : sprintf( __( 'by %s', 'cariera-addons' ), '<a href="' . esc_url( get_edit_user_link( $post->post_author ) ) . '">' . get_the_author() . '</a>' ) ) . '</span>';
				break;
			case 'job_application_actions':
				echo '<div class="actions">';
				$admin_actions = [];
				if ( 'trash' !== $post->post_status ) {
					$admin_actions['view']   = [
						'action' => 'view',
						'name'   => esc_html__( 'View', 'cariera-addons' ),
						'url'    => get_edit_post_link( $post->ID ),
					];
					$admin_actions['delete'] = [
						'action' => 'delete',
						'name'   => esc_html__( 'Delete', 'cariera-addons' ),
						'url'    => get_delete_post_link( $post->ID ),
					];
				}

				$admin_actions = apply_filters( 'job_manager_job_applications_admin_actions', $admin_actions, $post );

				foreach ( $admin_actions as $action ) {
					printf( '<a class="icon-%s button tips" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
				}

				echo '</div>';

				break;
		}
	}

	/**
	 * Filter applications
	 *
	 * @since   0.9.3
	 * @version 0.9.4
	 */
	public function restrict_manage_posts() {
		global $typenow, $wpdb;

		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION !== $typenow ) {
			return;
		}

		$post_type = esc_sql( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION );
		$current   = isset( $_GET['_job_listing'] ) ? absint( $_GET['_job_listing'] ) : 0;

		$jobs_with_applications = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type = %s AND post_parent > 0;",
				$post_type
			)
		);
		?>

		<select id="dropdown_job_listings" name="_job_listing">
			<option value=""><?php esc_html_e( 'Applications for all jobs', 'cariera-addons' ); ?></option>
			<?php
			foreach ( $jobs_with_applications as $job_id ) {
				$title = get_the_title( $job_id );
				if ( $job_id && $title ) {
					printf(
						'<option value="%d" %s>%s</option>',
						esc_attr( $job_id ),
						selected( $current, $job_id, false ),
						esc_html( $title )
					);
				}
			}
			?>
		</select>
		<?php
	}

	/**
	 * Modifies the query parameters for displaying a list of job applications.
	 *
	 * @since   0.9.3
	 * @version 1.0.2
	 *
	 * @param array $vars
	 */
	public function request( $vars ) {
		global $typenow;

		$job_listing_id = ! empty( $_GET['_job_listing'] ) ? absint( $_GET['_job_listing'] ) : 0; // phpcs:ignore

		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION === $typenow && $job_listing_id > 0 ) {
			$vars['post_parent'] = $job_listing_id;
		}

		// Sorting by rating (meta key).
		if ( isset( $vars['orderby'] ) && 'rating' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				[
					'meta_key' => '_rating',
					'orderby'  => 'meta_value_num',
				]
			);
		}

		return $vars;
	}

	/**
	 * Sorting
	 *
	 * @since 0.9.3
	 *
	 * @param array $columns
	 */
	public function sortable_columns( $columns ) {
		$custom = [
			'application_rating'     => 'rating',
			'candidate'              => 'post_title',
			'job_application_posted' => 'date',
			'job'                    => 'post_parent',
		];
		unset( $columns['comments'] );

		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Search custom fields as well as content.
	 *
	 * @since   0.9.3
	 * @version 0.9.10
	 *
	 * @param WP_Query $wp
	 */
	public function search_meta( $wp ) {
		global $wpdb;

		$post_type = \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION;

		// Bail early if not in admin, no search term, or incorrect post type.
		if ( ! is_admin() || empty( $wp->query_vars['s'] ) || ! isset( $wp->query_vars['post_type'] ) || $wp->query_vars['post_type'] !== $post_type ) {
			return;
		}

		$search_term = '%' . $wpdb->esc_like( $wp->query_vars['s'] ) . '%';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// Using raw SQL because WP_Query doesn't support optional meta_value LIKE + OR with title/content search.
		$sql = $wpdb->prepare(
			"
			SELECT DISTINCT posts.ID
			FROM {$wpdb->posts} AS posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			WHERE (
				meta.meta_value LIKE %s
				OR posts.post_title LIKE %s
				OR posts.post_content LIKE %s
			)
			AND posts.post_type = %s
			",
			$search_term,
			$search_term,
			$search_term,
			$post_type
		);

		$post_ids = array_unique(
			array_merge(
				$wpdb->get_col( $sql ),
				[ 0 ] // Prevent empty results from showing all posts.
			)
		);

		// Override the default search behavior for this post type.
		unset( $wp->query_vars['s'] );
		$wp->query_vars['job_application_search'] = true;
		$wp->query_vars['post__in']               = $post_ids;
	}

	/**
	 * Change the label when searching meta.
	 *
	 * @since   0.9.3
	 * @version 0.9.4
	 *
	 * @param string $query
	 */
	public function search_meta_label( $query ) {
		global $pagenow, $typenow;

		if ( 'edit.php' !== $pagenow
			|| \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION !== $typenow
			|| ! get_query_var( 'job_application_search' )
		) {
			return $query;
		}

		return sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
	}

	/**
	 * Add statuses to admin
	 *
	 * @since 0.9.3
	 */
	public function add_custom_statuses() {
		global $typenow;

		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION === $typenow ) {
			echo '<script>jQuery(document).ready( function() {';
			echo "jQuery( 'select[name=\"_status\"]' ).find('option[value!=\"-1\"]').remove();";
			foreach ( get_job_application_statuses() as $key => $value ) {
				echo "jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"" . esc_attr( $key ) . '">' . esc_attr( $value ) . "</option>' );";
			}
			echo '});</script>';
		}
	}
}
