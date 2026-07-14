<?php

namespace Cariera_Addons\Core\Resumes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPT {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Customize the placeholder text for the resume title input.
		add_filter( 'enter_title_here', [ $this, 'enter_title_here' ], 1, 2 );

		// Add, manage, and sort custom columns for the Resume post type.
		add_filter( 'manage_edit-resume_columns', [ $this, 'columns' ] );
		add_action( 'manage_resume_posts_custom_column', [ $this, 'custom_columns' ], 2 );
		add_filter( 'manage_edit-resume_sortable_columns', [ $this, 'sortable_columns' ] );

		// Add custom filters for the Resume list table.
		add_action( 'parse_query', [ $this, 'search_meta' ] );
		add_filter( 'get_search_query', [ $this, 'search_meta_label' ] );

		// Handle sorting logic for custom columns.
		add_filter( 'request', [ $this, 'sort_columns' ] );

		// Customize post updated messages shown after editing resumes.
		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );

		// Add and process custom bulk actions in the Resume list table.
		add_action( 'admin_footer-edit.php', [ $this, 'add_bulk_actions' ] );
		add_action( 'load-edit.php', [ $this, 'do_bulk_actions' ] );

		// Resume approval logic and admin notice.
		add_action( 'admin_init', [ $this, 'approve_resume' ] );
		add_action( 'admin_notices', [ $this, 'approved_notice' ] );

		// Conditionally add dropdown filter by category in admin if enabled in settings.
		if ( get_option( 'resume_manager_enable_categories' ) ) {
			add_action( 'restrict_manage_posts', [ $this, 'resumes_by_category' ] );
		}

		// Add custom post status options to the Publish metabox on post edit screens.
		foreach ( [ 'post', 'post-new' ] as $hook ) {
			add_action( "admin_footer-{$hook}.php", [ $this, 'extend_submitdiv_post_status' ] );
		}
	}

	/**
	 * Change the title placeholder text for the resume post type.
	 *
	 * @since 0.9.5
	 *
	 * @param string  $text
	 * @param WP_Post $post
	 */
	public function enter_title_here( $text, $post ) {
		if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME === $post->post_type ) {
			return esc_html__( 'Candidate name', 'cariera-addons' );
		}
		return $text;
	}

	/**
	 * Columns function.
	 *
	 * @since 0.9.5
	 *
	 * @param mixed $columns
	 */
	public function columns( $columns ) {
		if ( ! is_array( $columns ) ) {
			$columns = [];
		}

		unset( $columns['title'], $columns['date'], $columns['author'] );

		$columns['candidate']          = esc_html__( 'Candidate', 'cariera-addons' );
		$columns['candidate_location'] = esc_html__( 'Location', 'cariera-addons' );
		$columns['resume_status']      = '<span class="tips" data-tip="' . esc_html__( 'Status', 'cariera-addons' ) . '">' . esc_html__( 'Status', 'cariera-addons' ) . '</span>';
		$columns['resume_posted']      = esc_html__( 'Posted', 'cariera-addons' );
		$columns['resume_expires']     = esc_html__( 'Expires', 'cariera-addons' );

		if ( get_option( 'resume_manager_enable_skills' ) ) {
			$columns['resume_skills'] = esc_html__( 'Skills', 'cariera-addons' );
		}

		if ( get_option( 'resume_manager_enable_categories' ) ) {
			$columns['resume_category'] = esc_html__( 'Categories', 'cariera-addons' );
		}

		$columns['featured_resume'] = '<span class="tips" data-tip="' . esc_html__( 'Featured?', 'cariera-addons' ) . '">' . esc_html__( 'Featured?', 'cariera-addons' ) . '</span>';
		$columns['resume_actions']  = esc_html__( 'Actions', 'cariera-addons' );

		return $columns;
	}

	/**
	 * Custom columns function.
	 *
	 * @since 0.9.5
	 *
	 * @param string $column
	 */
	public function custom_columns( $column ) {
		global $post;

		switch ( $column ) {
			case 'candidate':
				$edit_url = admin_url( 'post.php?post=' . intval( $post->ID ) . '&action=edit' );
				$tooltip  = sprintf( __( 'Resume ID: %d', 'cariera-addons' ), intval( $post->ID ) );
				echo '<a href="' . esc_url( $edit_url ) . '" class="tips candidate_name" data-tip="' . esc_attr( $tooltip ) . '">' . esc_html( get_the_title( $post ) ) . '</a>';

				echo '<div class="candidate_title">';
				the_candidate_title();
				echo '</div>';

				the_candidate_photo();
				break;
			case 'candidate_location':
				if ( empty( get_the_candidate_location( $post ) ) ) {
					echo '<span class="na">&ndash;</span>';
				} else {
					the_candidate_location( true, $post );
				}

				break;
			case 'resume_skills':
				$terms = get_the_term_list( $post->ID, \Cariera_Addons\Core\Resumes\Post_Types::TAX_SKILL, '', ', ', '' );
				if ( empty( $terms ) ) {
					echo '<span class="na">&ndash;</span>';
				} else {
					echo wp_kses_post( $terms );
				}
				break;
			case 'resume_category':
				$terms = get_the_term_list( $post->ID, $column, '', ', ', '' );
				if ( empty( $terms ) ) {
					echo '<span class="na">&ndash;</span>';
				} else {
					echo wp_kses_post( $terms );
				}
				break;
			case 'resume_posted':
				echo '<strong>' . date_i18n( __( 'M j, Y', 'cariera-addons' ), strtotime( $post->post_date ) ) . '</strong><span>';
				echo ( empty( $post->post_author ) ? esc_html__( 'by a guest', 'cariera-addons' ) : sprintf( __( 'by %s', 'cariera-addons' ), '<a href="' . get_edit_user_link( $post->post_author ) . '">' . get_the_author() . '</a>' ) ) . '</span>';
				break;
			case 'resume_expires':
				if ( $post->_resume_expires ) {
					echo '<strong>' . date_i18n( __( 'M j, Y', 'cariera-addons' ), strtotime( $post->_resume_expires ) ) . '</strong>';
				} else {
					echo '&ndash;';
				}
				break;
			case 'featured_resume':
				if ( is_resume_featured( $post ) ) {
					echo '&#10004;';
				} else {
					echo '&ndash;';
				}
				break;
			case 'resume_status':
				echo '<span data-tip="' . esc_attr( get_the_resume_status( $post ) ) . '" class="tips status-' . esc_attr( $post->post_status ) . '">' . get_the_resume_status( $post ) . '</span>';
				break;
			case 'resume_actions':
				echo '<div class="actions">';
				$admin_actions = [];

				if ( 'pending' === $post->post_status ) {
					$admin_actions['approve'] = [
						'action' => 'approve',
						'name'   => esc_html__( 'Approve', 'cariera-addons' ),
						'url'    => wp_nonce_url( add_query_arg( 'approve_resume', $post->ID ), 'approve_resume' ),
					];
				}

				if ( 'trash' !== $post->post_status ) {
					$admin_actions['view'] = [
						'action' => 'view',
						'name'   => esc_html__( 'View', 'cariera-addons' ),
						'url'    => get_permalink( $post->ID ),
					];

					$email = get_post_meta( $post->ID, '_candidate_email', true );
					if ( $email ) {
						$admin_actions['email'] = [
							'action' => 'email',
							'name'   => esc_html__( 'Email Candidate', 'cariera-addons' ),
							'url'    => 'mailto:' . esc_attr( $email ),
						];
					}
					$admin_actions['edit']   = [
						'action' => 'edit',
						'name'   => esc_html__( 'Edit', 'cariera-addons' ),
						'url'    => get_edit_post_link( $post->ID ),
					];
					$admin_actions['delete'] = [
						'action' => 'delete',
						'name'   => esc_html__( 'Delete', 'cariera-addons' ),
						'url'    => get_delete_post_link( $post->ID ),
					];
				}

				$admin_actions = apply_filters( 'resume_manager_admin_actions', $admin_actions, $post );

				foreach ( $admin_actions as $action ) {
					printf( '<a class="icon-%s button tips" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
				}

				echo '</div>';

				break;
		}
	}

	/**
	 * Sortable columns function.
	 *
	 * @since 0.9.5
	 *
	 * @param array $columns
	 */
	public function sortable_columns( $columns ) {
		$custom = [
			'resume_posted'      => 'date',
			'candidate'          => 'title',
			'candidate_location' => 'candidate_location',
			'resume_expires'     => 'resume_expires',
			'featured_resume'    => 'featured_resume',
			'resume_skills'      => 'resume_skills',
		];
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Search custom fields as well as content.
	 *
	 * @since   0.9.5
	 * @version 1.0.2
	 *
	 * @param WP_Query $wp
	 */
	public function search_meta( $wp ) {
		global $pagenow, $wpdb;

		if ( 'edit.php' !== $pagenow || empty( $wp->query_vars['s'] ) || \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $wp->query_vars['post_type'] ) {
			return;
		}

		$search_term = esc_attr( $wp->query_vars['s'] );
		$post_type   = \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME;

		// Using direct SQL to allow optional meta query matching.
		$post_ids = array_unique(
			array_merge(
				$wpdb->get_col(
					$wpdb->prepare(
						"
						SELECT posts.ID
						FROM {$wpdb->posts} AS posts
						INNER JOIN {$wpdb->postmeta} AS p1 ON posts.ID = p1.post_id
						WHERE (p1.meta_value LIKE %s
							OR posts.post_title LIKE %s
							OR posts.post_content LIKE %s)
						AND posts.post_type = %s
						",
						'%' . $search_term . '%',
						'%' . $search_term . '%',
						'%' . $search_term . '%',
						$post_type
					)
				),
				[ 0 ]
			)
		);

		// Adjust the query vars.
		unset( $wp->query_vars['s'] );
		$wp->query_vars['resume_search'] = true;
		$wp->query_vars['post__in']      = $post_ids;
	}

	/**
	 * Change the label when searching meta.
	 *
	 * @since   0.9.5
	 * @version 0.9.7
	 *
	 * @param string $query
	 */
	public function search_meta_label( $query ) {
		global $pagenow, $typenow;

		if ( 'edit.php' !== $pagenow || \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $typenow || ! get_query_var( 'resume_search' ) ) {
			return $query;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		return sanitize_text_field( wp_unslash( $_GET['s'] ) );
	}

	/**
	 * Sort columns by meta key.
	 *
	 * @since 0.9.5
	 *
	 * @param array $vars
	 */
	public function sort_columns( $vars ) {
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Query used in admin only.
		if ( isset( $vars['orderby'] ) ) {
			if ( 'resume_expires' === $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					[
						'meta_key' => '_resume_expires',
						'orderby'  => 'meta_value',
					]
				);
			} elseif ( 'candidate_location' === $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					[
						'meta_key' => '_candidate_location',
						'orderby'  => 'meta_value',
					]
				);
			} elseif ( 'featured_resume' === $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					[
						'meta_key' => '_featured',
						'orderby'  => 'meta_value_num',
					]
				);
			} elseif ( 'resume_skills' === $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					[
						'meta_key' => '_resume_skills',
						'orderby'  => 'meta_value',
					]
				);
			}
		}
		return $vars;
	}

	/**
	 * Post updated messages function.
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 *
	 * @param array $messages
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['resume'] = [
			0  => '',
			1  => sprintf( __( 'Resume updated. <a href="%s">View Resume</a>', 'cariera-addons' ), esc_url( get_permalink( $post_ID ) ) ),
			2  => esc_html__( 'Custom field updated.', 'cariera-addons' ),
			3  => esc_html__( 'Custom field deleted.', 'cariera-addons' ),
			4  => esc_html__( 'Resume updated.', 'cariera-addons' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Resume restored to revision from %s', 'cariera-addons' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Resume published. <a href="%s">View Resume</a>', 'cariera-addons' ), esc_url( get_permalink( $post_ID ) ) ),
			7  => esc_html__( 'Resume saved.', 'cariera-addons' ),
			8  => sprintf( __( 'Resume submitted. <a target="_blank" href="%s">Preview Resume</a>', 'cariera-addons' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9  => sprintf(
				__( 'Resume scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Resume</a>', 'cariera-addons' ),
				date_i18n( __( 'M j, Y @ G:i', 'cariera-addons' ), strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),
			10 => sprintf( __( 'Resume draft updated. <a target="_blank" href="%s">Preview Resume</a>', 'cariera-addons' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		];
		return $messages;
	}

	/**
	 * Edit bulk actions
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 */
	public function add_bulk_actions() {
		global $post_type;

		if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME === $post_type ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
				jQuery('<option>').val('approve_resumes').text('<?php esc_html_e( 'Approve Resumes', 'cariera-addons' ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('approve_resumes').text('<?php esc_html_e( 'Approve Resumes', 'cariera-addons' ); ?>').appendTo("select[name='action2']");
				});
			</script>
			<?php
		}
	}

	/**
	 * Do custom bulk actions
	 *
	 * @since   0.9.5
	 * @version 1.0.6
	 */
	public function do_bulk_actions() {
		if ( empty( $_GET['post_type'] ) || $_GET['post_type'] !== \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME ) { // phpcs:ignore
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();

		switch ( $action ) {
			case 'approve_resumes':
				check_admin_referer( 'bulk-posts' );

				if ( ! current_user_can( 'manage_resumes' ) ) {
					return;
				}

				$post_ids         = array_map( 'absint', array_filter( (array) $_GET['post'] ) ); // phpcs:ignore
				$approved_resumes = [];

				if ( ! empty( $post_ids ) ) {
					foreach ( $post_ids as $post_id ) {
						$new_post_status = get_post_meta( $post_id, '_resume_edited_original_status', true );
						delete_post_meta( $post_id, '_resume_edited_original_status' );
						if ( ! $new_post_status ) {
							$new_post_status = 'publish';
						}
						$resume_data = [
							'ID'          => $post_id,
							'post_status' => $new_post_status,
						];

						if ( 'pending' === get_post_status( $post_id ) && wp_update_post( $resume_data ) ) {
							$approved_resumes[] = $post_id;
						}
					}
				}

				wp_safe_redirect( remove_query_arg( 'approve_resumes', add_query_arg( 'approved_resumes', $approved_resumes, admin_url( 'edit.php?post_type=' . \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME ) ) ) );
				exit;
		}
	}

	/**
	 * Approve a single resume
	 *
	 * @since   0.9.5
	 * @version 0.9.11
	 */
	public function approve_resume() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Data used safely and nonce should not be modified.
		if ( ! empty( $_GET['approve_resume'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'approve_resume' ) && current_user_can( 'manage_resumes' ) ) {
			$post_id         = absint( $_GET['approve_resume'] );
			$new_post_status = get_post_meta( $post_id, '_resume_edited_original_status', true );
			delete_post_meta( $post_id, '_resume_edited_original_status' );
			if ( ! $new_post_status ) {
				$new_post_status = 'publish';
			}

			$resume_data = [
				'ID'          => $post_id,
				'post_status' => $new_post_status,
			];
			wp_update_post( $resume_data );
			wp_safe_redirect( remove_query_arg( 'approve_resume', add_query_arg( 'approved_resumes', $post_id, admin_url( 'edit.php?post_type=' . \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME ) ) ) );
			exit;
		}
	}

	/**
	 * Show a notice if we did a bulk action or approval
	 *
	 * @since   0.9.5
	 * @version 1.0.2
	 */
	public function approved_notice() {
		global $post_type, $pagenow;

		if ( 'edit.php' === $pagenow && \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME === $post_type && ! empty( $_REQUEST['approved_resumes'] ) ) {
			$approved_resumes = wp_unslash( $_REQUEST['approved_resumes'] ); // phpcs:ignore
			if ( is_array( $approved_resumes ) ) {
				$approved_resumes = array_map( 'absint', $approved_resumes );
				$titles           = [];
				foreach ( $approved_resumes as $resume_id ) {
					$titles[] = get_the_title( $resume_id );
				}
				// translators: 1: List of resume titles.
				echo '<div class="updated"><p>' . sprintf( __( '%s approved', 'cariera-addons' ), '&quot;' . implode( '&quot;, &quot;', $titles ) . '&quot;' ) . '</p></div>';
			} else {
				// translators: 1: Resume title.
				echo '<div class="updated"><p>' . sprintf( __( '%s approved', 'cariera-addons' ), '&quot;' . get_the_title( $approved_resumes ) . '&quot;' ) . '</p></div>';
			}
		}
	}

	/**
	 * Show a dropdown to filter resumes by category.
	 *
	 * @since   0.9.5
	 * @version 0.9.11
	 *
	 * @param int    $show_counts
	 * @param int    $hierarchical
	 * @param int    $show_uncategorized
	 * @param string $orderby
	 */
	public function resumes_by_category( $show_counts = 1, $hierarchical = 1, $show_uncategorized = 1, $orderby = '' ) {
		global $typenow, $wp_query;

		if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $typenow || ! taxonomy_exists( \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY ) ) {
			return;
		}

		if ( file_exists( JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-category-walker.php' ) ) {
			require_once JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-category-walker.php';
		} else {
			require_once JOB_MANAGER_PLUGIN_DIR . '/includes/class-wp-job-manager-category-walker.php';
		}

		$r                 = [];
		$r['taxonomy']     = \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY;
		$r['pad_counts']   = 1;
		$r['hierarchical'] = $hierarchical;
		$r['hide_empty']   = 0;
		$r['show_count']   = $show_counts;
		$r['selected']     = isset( $wp_query->query[ \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY ] ) ? $wp_query->query[ \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY ] : '';
		$r['menu_order']   = false;

		if ( 'order' === $orderby ) {
			$r['menu_order'] = 'asc';
		} elseif ( $orderby ) {
			$r['orderby'] = $orderby;
		}

		$terms = get_terms( $r );

		if ( ! $terms ) {
			return;
		}

		$allowed_html = [
			'option' => [
				'value'    => [],
				'selected' => [],
				'class'    => [],
			],
		];

		echo "<select name='" . \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY . "' id='dropdown_resume_category'>";
		echo '<option value="" ' . selected( $r['selected'], '', false ) . '>' . __( 'Select a category', 'cariera-addons' ) . '</option>';
		echo wp_kses( $this->walk_category_dropdown_tree( $terms, 0, $r ), $allowed_html );
		echo '</select>';
	}

	/**
	 * Walk the Product Categories.
	 *
	 * @since 0.9.5
	 */
	private function walk_category_dropdown_tree() {
		$args = func_get_args();

		// the user's options are the third parameter.
		if ( empty( $args[2]['walker'] ) || ! is_a( $args[2]['walker'], 'Walker' ) ) {
			$walker = new \WP_Job_Manager_Category_Walker();
		} else {
			$walker = $args[2]['walker'];
		}

		return call_user_func_array( [ $walker, 'walk' ], $args );
	}

	/**
	 * Adds post status to the "submitdiv" Meta Box and post type WP List Table screens. Based on https://gist.github.com/franz-josef-kaiser/2930190
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 */
	public function extend_submitdiv_post_status() {
		global $wp_post_statuses, $post, $post_type;

		// Abort if we're on the wrong post type, but only if we got a restriction.
		if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post_type ) {
			return;
		}

		// Get all non-builtin post status and add them as <option>.
		$options = '';
		$display = '';
		foreach ( get_resume_post_statuses() as $status => $name ) {
			$selected = selected( $post->post_status, $status, false );

			// If we one of our custom post status is selected, remember it.
			if ( $selected ) {
				$display = $name;
			}

			// Build the options.
			$options .= "<option{$selected} value='{$status}'>" . esc_html( $name ) . '</option>';
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				<?php if ( ! empty( $display ) ) : ?>
					jQuery( '#post-status-display' ).html( '<?php echo $display; ?>' );
				<?php endif; ?>

				var select = jQuery( '#post-status-select' ).find( 'select' );
				jQuery( select ).html( "<?php echo $options; ?>" );
			} );
		</script>
		<?php
	}
}
