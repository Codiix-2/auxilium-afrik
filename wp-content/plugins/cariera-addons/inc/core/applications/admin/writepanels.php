<?php
namespace Cariera_Addons\Core\Applications\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Writepanels' ) && defined( 'JOB_MANAGER_PLUGIN_DIR' ) ) {
	include JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-writepanels.php';
}

if ( ! class_exists( 'WP_Job_Manager_Writepanels' ) ) {
	return;
}

class Writepanels extends \WP_Job_Manager_Writepanels {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 1, 2 );
		add_action( 'job_manager_applications_save_job_application', [ $this, 'save_job_application_data' ], 1, 2 );

		// Fetch select2 listing via AJAX.
		add_action( 'wp_ajax_ajax_listing_search', [ $this, 'ajax_listing_search' ] );
	}

	/**
	 * Job application fields
	 *
	 * @since   0.9.3
	 * @version 1.0.3
	 */
	public function job_application_fields() {
		global $post;

		$fields = apply_filters(
			'job_manager_applications_job_application_fields',
			[
				'_candidate_email'        => [
					'label'       => esc_html__( 'Contact Email', 'cariera-addons' ),
					'placeholder' => esc_html__( 'you@yourdomain.com', 'cariera-addons' ),
					'description' => '',
				],
				'_attachment'             => [
					'label'       => esc_html__( 'Attachment', 'cariera-addons' ),
					'placeholder' => esc_html__( 'URL to the attachment if the candidate provided one', 'cariera-addons' ),
					'type'        => 'file',
					'multiple'    => true,
				],
				'_job_application_author' => [
					'label'       => esc_html__( 'Posted by', 'cariera-addons' ),
					'type'        => 'author',
					'placeholder' => '',
				],
				'_rating'                 => [
					'label'       => esc_html__( 'Rating (out of 5)', 'cariera-addons' ),
					'type'        => 'text',
					'placeholder' => '0',
				],
				'_resume_id'              => [
					'label'       => esc_html__( 'Online Resume', 'cariera-addons' ),
					'type'        => 'ajax_listing',
					'post_type'   => \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME,
					'placeholder' => esc_html__( 'Search for a resume...', 'cariera-addons' ),
				],
				'post_parent'             => [
					'label'       => esc_html__( 'Job Listing', 'cariera-addons' ),
					'type'        => 'ajax_listing',
					'post_type'   => 'job_listing',
					'placeholder' => esc_html__( 'Search for a job listing...', 'cariera-addons' ),
					'value'       => $post->post_parent,
				],
			]
		);

		if ( ! function_exists( 'get_resume_share_link' ) ) {
			unset( $fields['_resume_id'] );
		}

		return $fields;
	}

	/**
	 * Add meta box function.
	 *
	 * @since 0.9.3
	 */
	public function add_meta_boxes() {
		add_meta_box( 'job_application_save', esc_html__( 'Save Application', 'cariera-addons' ), [ $this, 'job_application_save' ], \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION, 'side', 'high' );
		add_meta_box( 'job_application_data', esc_html__( 'Job Application Data', 'cariera-addons' ), [ $this, 'job_application_data' ], \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION, 'normal', 'high' );
		add_meta_box( 'job_application_notes', esc_html__( 'Application Notes', 'cariera-addons' ), [ $this, 'application_notes' ], \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION, 'side', 'default' );
		remove_meta_box( 'submitdiv', \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION, 'side' );
	}

	/**
	 * Publish meta box
	 *
	 * @since 0.9.3
	 *
	 * @param object $post Post object.
	 */
	public function job_application_save( $post ) {
		$statuses = get_job_application_statuses();
		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="misc-publishing-actions">
					<div class="misc-pub-section misc-pub-post-status">
						<div id="post-status-select">
							<select name='post_status' id='post_status'>
								<?php
								foreach ( $statuses as $key => $label ) {
									$selected = selected( $post->post_status, $key, false );
									echo "<option{$selected} value='" . esc_attr( $key ) . "'>" . esc_html( $label ) . '</option>';
								}
								?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php esc_html_e( 'Move to Trash', 'cariera-addons' ); ?></a>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="save" class="button button-primary" type="submit" value="<?php esc_html_e( 'Save', 'cariera-addons' ); ?>">
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Job application data
	 *
	 * @since 0.9.3
	 *
	 * @param mixed $post
	 */
	public function job_application_data( $post ) {
		global $post, $thepostid;

		$thepostid = $post->ID;

		echo '<div class="wp_job_manager_meta_data">';

		wp_nonce_field( 'save_meta_data', 'job_manager_applications_nonce' );

		do_action( 'job_application_data_start', $thepostid );

		foreach ( $this->job_application_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			if ( ! isset( $field['value'] ) && metadata_exists( 'post', $thepostid, $key ) ) {
				$field['value'] = get_post_meta( $thepostid, $key, true );
			}

			if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
				$field['value'] = $field['default'];
			} elseif ( ! isset( $field['value'] ) ) {
				$field['value'] = '';
			}

			if ( method_exists( $this, 'input_' . $type ) ) {
				call_user_func( [ $this, 'input_' . $type ], $key, $field );
			} else {
				do_action( 'job_manager_applications_input_' . $type, $key, $field );
			}
		}

		do_action( 'job_application_data_end', $thepostid );

		echo '</div>';
	}

	/**
	 * Triggered on Save Post
	 *
	 * @since   0.9.3
	 * @version 0.9.11
	 *
	 * @param mixed $post_id
	 * @param mixed $post
	 */
	public function save_post( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( is_int( wp_is_post_revision( $post ) ) ) {
			return;
		}
		if ( is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( empty( $_POST['job_manager_applications_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['job_manager_applications_nonce'] ), 'save_meta_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION !== $post->post_type ) {
			return;
		}

		do_action( 'job_manager_applications_save_job_application', $post_id, $post );
	}

	/**
	 * Save application Meta
	 *
	 * @since   0.9.3
	 * @version 1.0.3
	 *
	 * @param mixed $post_id
	 * @param mixed $post
	 */
	public function save_job_application_data( $post_id, $post ) {
		global $wpdb;

		foreach ( $this->job_application_fields() as $key => $field ) {
			if ( '_job_application_author' === $key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce check handled by WP core.
				if ( empty( $_POST[ $key ] ) ) {
					$_POST[ $key ] = 0;
				}

				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce check handled by WP core.
				$input_post_author = $_POST[ $key ] > 0 ? intval( $_POST[ $key ] ) : 0;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Avoid update post within `save_post` action.
				$wpdb->update( $wpdb->posts, [ 'post_author' => $input_post_author ], [ 'ID' => $post_id ] );
			} elseif ( 'post_parent' === $key ) {
				$new_parent_id = isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : 0; // phpcs:ignore

				if ( $new_parent_id > 0 ) {
					$parent_post = get_post( $new_parent_id );
					if ( $parent_post ) {
						update_post_meta( $post_id, '_job_applied_for', $parent_post->post_title );
					}
				}
			} else {
				$type = ! empty( $field['type'] ) ? $field['type'] : '';

				switch ( $type ) {
					case 'textarea':
						update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) );
						break;
					case 'checkbox':
						if ( isset( $_POST[ $key ] ) ) {
							update_post_meta( $post_id, $key, 1 );
						} else {
							update_post_meta( $post_id, $key, 0 );
						}
						break;
					case 'ajax_listing':
						$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : null;

						if ( empty( $value ) ) {
							delete_post_meta( $post_id, $key );
						} else {
							update_post_meta( $post_id, $key, $value );
						}
						break;
					default:
						if ( isset( $_POST[ $key ] ) ) {
							if ( is_array( $_POST[ $key ] ) ) {
								update_post_meta( $post_id, $key, array_filter( array_map( 'sanitize_text_field', $_POST[ $key ] ) ) ); // phpcs:ignore
							} else {
								update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ); // phpcs:ignore
							}
						}
						break;
				}
			}
		}
	}

	/**
	 * Application notes metabox
	 *
	 * @since 0.9.3
	 *
	 * @param mixed $post
	 */
	public static function application_notes( $post ) {
		job_application_notes( $post );
		?>
		<script type="text/javascript">
			jQuery(function(){
				jQuery('#job_application_notes')
					.on( 'click', '.job-application-note-add input.button', function() {
						var button                     = jQuery(this);
						var application_id             = button.data('application_id');
						var job_application            = jQuery(this).closest('#job_application_notes');
						var job_application_note       = job_application.find('textarea');
						var disabled_attr              = jQuery(this).attr('disabled');
						var job_application_notes_list = job_application.find('ul.job-application-notes-list');

						if ( typeof disabled_attr !== 'undefined' && disabled_attr !== false ) {
							return false;
						}
						if ( ! job_application_note.val() ) {
							return false;
						}

						button.attr( 'disabled', 'disabled' );

						var data = {
							action: 'add_job_application_note',
							note: job_application_note.val(),
							application_id: application_id,
							security: '<?php echo wp_create_nonce( 'job-application-notes' ); ?>'
						};

						jQuery.post( '<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function( response ) {
							job_application_notes_list.append( response );
							button.removeAttr( 'disabled' );
							job_application_note.val( '' );
						});

						return false;
					})
					.on( 'click', 'a.delete_note', function() {
						var answer = confirm( '<?php echo esc_html__( 'Are you sure you want to delete this? There is no undo.', 'cariera-addons' ); ?>' );
						if ( answer ) {
							var button  = jQuery(this);
							var note    = jQuery(this).closest('li');
							var note_id = note.attr('rel');

							var data = {
								action: 'delete_job_application_note',
								note_id: note_id,
								security: '<?php echo wp_create_nonce( 'job-application-notes' ); ?>'
							};

							jQuery.post( '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', data, function( response ) {
								note.fadeOut( 500, function() {
									note.remove();
								});
							});
						}
						return false;
					});
			});
		</script>
		<?php
	}

	/**
	 * Render a reusable AJAX-powered select field for any post type.
	 *
	 * @since 1.0.3
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_ajax_listing( $key, $field ) {
		$name        = ! empty( $field['name'] ) ? $field['name'] : $key;
		$post_type   = ! empty( $field['post_type'] ) ? $field['post_type'] : 'post';
		$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
		?>

		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>">
				<?php echo esc_html( $field['label'] ); ?>:
			</label>
			<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" data-post-type="<?php echo esc_attr( $post_type ); ?>" data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
				<?php
				// Show selected post if exists.
				if ( ! empty( $field['value'] ) ) {
					$selected_post = get_post( $field['value'] );
					if ( $selected_post ) {
						?>
						<option value="<?php echo esc_attr( $selected_post->ID ); ?>" selected="selected">
							<?php echo esc_html( $selected_post->post_title ); ?>
						</option>
						<?php
					}
				}
				?>
			</select>
		</p>
		<?php
	}

	/**
	 * AJAX callback for Select2 post search.
	 *
	 * @since 1.0.3
	 */
	public function ajax_listing_search() {
		$search    = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : ''; // phpcs:ignore
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post'; // phpcs:ignore

		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => 20,
			'post_status'    => [ 'publish' ],
			's'              => $search,
		];

		$posts   = get_posts( $args );
		$results = [];

		foreach ( $posts as $post ) {
			$results[] = [
				'id'   => $post->ID,
				'text' => $post->post_title,
			];
		}

		wp_send_json( $results );
	}
}
