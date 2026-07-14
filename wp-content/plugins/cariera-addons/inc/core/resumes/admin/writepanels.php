<?php

namespace Cariera_Addons\Core\Resumes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Writepanels' ) ) {
	include JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-writepanels.php';
}

class Writepanels extends \WP_Job_Manager_Writepanels {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 1, 2 );
		add_action( 'resume_manager_save_resume', [ $this, 'save_resume_data' ], 1, 2 );
	}

	/**
	 * Resume fields
	 *
	 * @since 0.9.5
	 */
	public static function resume_fields() {
		global $post_id;

		$current_user = wp_get_current_user();
		$fields_raw   = \Cariera_Addons\Core\Resumes\Post_Types::get_resume_fields();
		$fields       = [];

		if ( $current_user->has_cap( 'edit_others_posts' ) ) {
			$fields['_resume_author'] = [
				'label'    => esc_html__( 'Posted by', 'cariera-addons' ),
				'type'     => 'author',
				'priority' => 0,
			];
		}

		foreach ( $fields_raw as $meta_key => $field ) {
			$show_in_admin = $field['show_in_admin'];
			if ( is_callable( $show_in_admin ) ) {
				$show_in_admin = (bool) call_user_func( $show_in_admin, true, $meta_key, $post_id, $current_user->ID );
			}

			if ( ! $show_in_admin ) {
				continue;
			}

			if ( ! call_user_func( $field['auth_edit_callback'], false, $meta_key, $post_id, $current_user->ID ) ) {
				continue;
			}

			$fields[ $meta_key ] = $field;
		}

		/**
		 * Filters resume data fields shown in WP admin.
		 *
		 * To add resume data fields, use the `resume_manager_resume_fields` found in `includes/class-wp-resume-manager-post-types.php`.
		 *
		 * @since 0.9.5
		 */
		$fields = apply_filters( 'resume_manager_resume_wp_admin_fields', $fields, $post_id );

		uasort( $fields, [ __CLASS__, 'sort_by_priority' ] );

		return $fields;
	}

	/**
	 * Sorts array of custom fields by priority value.
	 *
	 * @since 0.9.5
	 *
	 * @param array $a
	 * @param array $b
	 */
	protected static function sort_by_priority( $a, $b ) {
		if ( ! isset( $a['priority'] ) || ! isset( $b['priority'] ) || $a['priority'] === $b['priority'] ) {
			return 0;
		}

		return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
	}

	/**
	 * Add meta boxes function.
	 *
	 * @since 0.9.5
	 */
	public function add_meta_boxes() {
		add_meta_box( 'resume_data', esc_html__( 'Candidate Data', 'cariera-addons' ), [ $this, 'resume_data' ], 'resume', 'normal', 'high' );
		add_meta_box( 'resume_url_data', esc_html__( 'URL(s)', 'cariera-addons' ), [ $this, 'url_data' ], 'resume', 'side', 'low' );
		add_meta_box( 'resume_education_data', esc_html__( 'Education', 'cariera-addons' ), [ $this, 'education_data' ], 'resume', 'normal', 'high' );
		add_meta_box( 'resume_experience_data', esc_html__( 'Experience', 'cariera-addons' ), [ $this, 'experience_data' ], 'resume', 'normal', 'high' );
	}

	/**
	 * Resume data
	 *
	 * @since 0.9.5
	 *
	 * @param mixed $post
	 */
	public function resume_data( $post ) {
		global $post, $thepostid;

		$thepostid = $post->ID;

		echo '<div class="cariera_addons_resumes_meta_data wp_job_manager_meta_data">';

		wp_nonce_field( 'save_meta_data', 'resume_manager_nonce' );

		do_action( 'resume_manager_resume_data_start', $thepostid );

		foreach ( $this->resume_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			if ( ! isset( $field['value'] ) && metadata_exists( 'post', $thepostid, $key ) ) {
				$field['value'] = get_post_meta( $thepostid, $key, true );
			}

			if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
				$field['value'] = $field['default'];
			} elseif ( ! isset( $field['value'] ) ) {
				$field['value'] = '';
			}

			if ( '_resume_file' === $key ) {
				if ( is_array( $field['value'] ) ) {
					$field['download'] = array_map(
						function ( $value, $key ) use ( $thepostid ) {
							return get_resume_file_download_url( $thepostid, $key, site_url() );
						},
						$field['value'],
						array_keys( $field['value'] )
					);
				} else {
					$field['download'] = get_resume_file_download_url( $thepostid, 0, site_url() );
				}
			}

			if ( has_action( 'resume_manager_input_' . $type ) ) {
				do_action( 'resume_manager_input_' . $type, $key, $field );
			} elseif ( method_exists( $this, 'input_' . $type ) ) {
				call_user_func( [ $this, 'input_' . $type ], $key, $field );
			}
		}

		$user_edited_date = get_post_meta( $post->ID, '_resume_edited', true );
		if ( $user_edited_date ) {
			echo '<p class="form-field"><em>';
			printf(
				// translators: %1$s placeholder is the object type singular name; %2$s is the relative date the resume was edited.
				esc_html__( '%1$s was last modified by the user on %2$s.', 'cariera-addons' ),
				esc_html( get_post_type_object( 'resume' )->labels->singular_name ),
				esc_html( date_i18n( get_option( 'date_format' ), $user_edited_date ) )
			);
			echo '</em></p>';
		}

		do_action( 'resume_manager_resume_data_end', $thepostid );

		echo '</div>';
	}

	/**
	 * Output repeated rows
	 *
	 * @since 0.9.5
	 *
	 * @param string $group_name
	 * @param array  $fields
	 * @param array  $data
	 */
	public static function repeated_rows_html( $group_name, $fields, $data ) {
		// Generate the empty row template for "Add Row".
		ob_start();

		echo '<tr>';
		echo '<td class="sort-column" width="1%">&nbsp;</td>';

		foreach ( $fields as $key => $field ) {
			echo '<td>';
			$type           = ! empty( $field['type'] ) ? $field['type'] : 'text';
			$field['value'] = ''; // Empty for new row.

			if ( method_exists( __CLASS__, 'input_' . $type ) ) {
				call_user_func( [ __CLASS__, 'input_' . $type ], $key, $field );
			} else {
				do_action( 'resume_manager_input_' . $type, $key, $field );
			}
			echo '</td>';
		}

		echo '</tr>';
		$empty_row = esc_attr( ob_get_clean() );
		?>
		<table class="wc-job-manager-resumes-repeated-rows">
			<thead>
				<tr>
					<th class="sort-column">&nbsp;</th>
					<?php foreach ( $fields as $field ) { ?>
						<th><label><?php echo esc_html( $field['label'] ); ?></label></th>
					<?php } ?>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<td colspan="<?php echo esc_attr( count( $fields ) + 1 ); ?>">
						<div class="submit">
							<input type="submit" class="button resume_manager_add_row" value="<?php echo esc_attr( sprintf( __( 'Add %s', 'cariera-addons' ), $group_name ) ); ?>" data-row="<?php echo $empty_row; ?>" />
						</div>
					</td>
				</tr>
			</tfoot>

			<tbody>
				<?php if ( $data ) { ?>
					<?php foreach ( $data as $item ) { ?>
						<tr>
							<td class="sort-column" width="1%">&nbsp;</td>
							<?php foreach ( $fields as $key => $field ) { ?>
								<td>
									<?php
									$type           = ! empty( $field['type'] ) ? $field['type'] : 'text';
									$field['value'] = isset( $item[ $key ] ) ? $item[ $key ] : '';

									if ( method_exists( __CLASS__, 'input_' . $type ) ) {
										call_user_func( [ __CLASS__, 'input_' . $type ], $key, $field );
									} else {
										do_action( 'resume_manager_input_' . $type, $key, $field );
									}
									?>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>
				<?php } ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Resume fields
	 *
	 * @since 0.9.5
	 */
	public static function resume_links_fields() {
		return apply_filters(
			'resume_manager_resume_links_fields',
			[
				'name' => [
					'label'       => esc_html__( 'Name', 'cariera-addons' ),
					'name'        => 'resume_url_name[]',
					'placeholder' => esc_html__( 'Your site', 'cariera-addons' ),
					'description' => '',
					'required'    => true,
				],
				'url'  => [
					'label'       => esc_html__( 'URL', 'cariera-addons' ),
					'name'        => 'resume_url[]',
					'placeholder' => 'http://',
					'description' => '',
					'required'    => true,
				],
			]
		);
	}

	/**
	 * Resume fields
	 *
	 * @since 0.9.5
	 */
	public static function resume_education_fields() {
		return apply_filters(
			'resume_manager_resume_education_fields',
			[
				'location'      => [
					'label'       => esc_html__( 'Institution', 'cariera-addons' ),
					'name'        => 'resume_education_location[]',
					'placeholder' => '',
					'description' => '',
					'required'    => true,
				],
				'qualification' => [
					'label'       => esc_html__( 'Certification(s)', 'cariera-addons' ),
					'name'        => 'resume_education_qualification[]',
					'placeholder' => '',
					'description' => '',
				],
				'date'          => [
					'label'       => esc_html__( 'Start/end date', 'cariera-addons' ),
					'name'        => 'resume_education_date[]',
					'placeholder' => '',
					'description' => '',
				],
				'notes'         => [
					'label'       => esc_html__( 'Notes', 'cariera-addons' ),
					'name'        => 'resume_education_notes[]',
					'placeholder' => '',
					'description' => '',
					'type'        => 'textarea',
				],
			]
		);
	}

	/**
	 * Resume fields
	 *
	 * @since 0.9.5
	 */
	public static function resume_experience_fields() {
		return apply_filters(
			'resume_manager_resume_experience_fields',
			[
				'employer'  => [
					'label'       => esc_html__( 'Employer', 'cariera-addons' ),
					'name'        => 'resume_experience_employer[]',
					'placeholder' => '',
					'description' => '',
					'required'    => true,
				],
				'job_title' => [
					'label'       => esc_html__( 'Job Title', 'cariera-addons' ),
					'name'        => 'resume_experience_job_title[]',
					'placeholder' => '',
					'description' => '',
				],
				'date'      => [
					'label'       => esc_html__( 'Start/end date', 'cariera-addons' ),
					'name'        => 'resume_experience_date[]',
					'placeholder' => '',
					'description' => '',
				],
				'notes'     => [
					'label'       => esc_html__( 'Notes', 'cariera-addons' ),
					'name'        => 'resume_experience_notes[]',
					'placeholder' => '',
					'description' => '',
					'type'        => 'textarea',
				],
			]
		);
	}

	/**
	 * Resume URL data
	 *
	 * @since 0.9.5
	 *
	 * @param mixed $post
	 */
	public function url_data( $post ) {
		echo '<p>' . esc_html__( 'Optionally provide links to any of your websites or social network profiles.', 'cariera-addons' ) . '</p>';
		$fields = $this->resume_links_fields();
		$this->repeated_rows_html( esc_html__( 'URL', 'cariera-addons' ), $fields, get_post_meta( $post->ID, '_links', true ) );
	}

	/**
	 * Resume Education data
	 *
	 * @since 0.9.5
	 *
	 * @param mixed $post
	 */
	public function education_data( $post ) {
		$fields = $this->resume_education_fields();
		$this->repeated_rows_html( esc_html__( 'Education', 'cariera-addons' ), $fields, get_post_meta( $post->ID, '_candidate_education', true ) );
	}

	/**
	 * Resume Education data
	 *
	 * @since 0.9.5
	 *
	 * @param mixed $post
	 */
	public function experience_data( $post ) {
		$fields = $this->resume_experience_fields();
		$this->repeated_rows_html( esc_html__( 'Experience', 'cariera-addons' ), $fields, get_post_meta( $post->ID, '_candidate_experience', true ) );
	}

	/**
	 * Triggered on Save Post
	 *
	 * @since   0.9.5
	 * @version 0.9.7
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
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce should not be modified.
		if ( empty( $_POST['resume_manager_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['resume_manager_nonce'] ), 'save_meta_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
			return;
		}

		do_action( 'resume_manager_save_resume', $post_id, $post );
	}

	/**
	 * Save Resume Meta
	 *
	 * @since   0.9.5
	 * @version 1.0.5
	 *
	 * @param mixed $post_id
	 * @param mixed $post
	 */
	public function save_resume_data( $post_id, $post ) {
		global $wpdb;

		// These need to exist.
		add_post_meta( $post_id, '_featured', 0, true );

		foreach ( $this->resume_fields() as $key => $field ) {

			// TODO: improved resume expiry when the time comes.
			// Expiry date.
			if ( '_resume_expires' === $key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce check handled by WP core.
				if ( ! empty( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, date( 'Y-m-d', strtotime( sanitize_text_field( $_POST[ $key ] ) ) ) ); // phpcs:ignore
				} else {
					update_post_meta( $post_id, $key, '' );
				}
			} elseif ( '_candidate_location' === $key ) {
				if ( update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) ) ) {
					do_action( 'resume_manager_candidate_location_edited', $post_id, sanitize_text_field( $_POST[ $key ] ) );
				} elseif ( apply_filters( 'resume_manager_geolocation_enabled', true ) && ! \WP_Job_Manager_Geocode::has_location_data( $post_id ) ) {
					\WP_Job_Manager_Geocode::generate_location_data( $post_id, sanitize_text_field( $_POST[ $key ] ) );
				}
				continue;
			} elseif ( '_resume_author' === $key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce check handled by WP core.
				if ( empty( $_POST[ $key ] ) ) {
					$_POST[ $key ] = 0;
				}

				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce check handled by WP core.
				$input_post_author = $_POST[ $key ] > 0 ? intval( $_POST[ $key ] ) : 0;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Avoid update post within `save_post` action.
				$wpdb->update( $wpdb->posts, [ 'post_author' => $input_post_author ], [ 'ID' => $post_id ] );
			}

			// Everything else.
			else {
				$type = ! empty( $field['type'] ) ? $field['type'] : '';

				switch ( $type ) {
					case 'textarea':
						update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) );
						break;
					case 'checkbox':
						// phpcs:ignore
						if ( isset( $_POST[ $key ] ) ) {
							update_post_meta( $post_id, $key, 1 );
						} else {
							update_post_meta( $post_id, $key, 0 );
						}
						break;
					default:
						// phpcs:ignore
						if ( ! empty( $_POST[ $key ] ) ) {
							if ( is_array( $_POST[ $key ] ) ) {
								update_post_meta( $post_id, $key, array_filter( array_map( 'sanitize_text_field', $_POST[ $key ] ) ) ); // phpcs:ignore
							} else {
								update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) ); // phpcs:ignore
							}
						}
						break;
				}
			}
		}

		$save_repeated_fields = [
			'_links'                => $this->resume_links_fields(),
			'_candidate_education'  => $this->resume_education_fields(),
			'_candidate_experience' => $this->resume_experience_fields(),
		];

		foreach ( $save_repeated_fields as $meta_key => $fields ) {
			$this->save_repeated_row( $post_id, $meta_key, $fields );
		}
	}

	/**
	 * Save repeated rows
	 *
	 * @since 0.9.5
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 * @param array  $fields
	 */
	public static function save_repeated_row( $post_id, $meta_key, $fields ) {
		$items            = [];
		$first_field      = current( $fields );
		$first_field_name = str_replace( '[]', '', $first_field['name'] );

		if ( ! empty( $_POST[ $first_field_name ] ) && is_array( $_POST[ $first_field_name ] ) ) {
			$keys = array_keys( $_POST[ $first_field_name ] );
			foreach ( $keys as $posted_key ) {
				$item = [];
				foreach ( $fields as $key => $field ) {
					$input_name = str_replace( '[]', '', $field['name'] );
					$type       = ! empty( $field['type'] ) ? $field['type'] : 'text';

					switch ( $type ) {
						case 'textarea':
							$item[ $key ] = wp_kses_post( stripslashes( $_POST[ $input_name ][ $posted_key ] ) );
							break;
						default:
							if ( is_array( $_POST[ $input_name ][ $posted_key ] ) ) {
								$item[ $key ] = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $_POST[ $input_name ][ $posted_key ] ) ) );
							} else {
								$item[ $key ] = sanitize_text_field( stripslashes( $_POST[ $input_name ][ $posted_key ] ) );
							}
							break;
					}
					if ( empty( $item[ $key ] ) && ! empty( $field['required'] ) ) {
						continue 2;
					}
				}
				$items[] = $item;
			}
		}
		update_post_meta( $post_id, $meta_key, $items );
	}
}
