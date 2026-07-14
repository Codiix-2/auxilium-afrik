<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Writepanels' ) ) {
	include JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-writepanels.php';
}

/**
 * Handles the management of Company Listing meta fields.
 *
 * @since 1.3.0
 */
class Writepanels extends \WP_Job_Manager_Writepanels {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 1, 2 );
		add_action( 'cariera_save_company', [ $this, 'save_company_data' ], 1, 2 );
	}

	/**
	 * Company Fields
	 *
	 * @since  1.3.0
	 */
	public static function company_fields() {
		global $post_id;

		$current_user = wp_get_current_user();
		$fields_raw   = \Cariera_Core\Core\Company_Manager\CPT::get_company_fields();
		$fields       = [];

		if ( $current_user->has_cap( 'edit_others_posts' ) ) {
			$fields['_company_author'] = [
				'label'    => esc_html__( 'Posted by', 'cariera-core' ),
				'type'     => 'author',
				'priority' => 25,
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

			/* Documented in wpjm plugin job_fields(); */
			if ( ! call_user_func( $field['auth_edit_callback'], false, $meta_key, $post_id, $current_user->ID ) ) {
				continue;
			}

			$fields[ $meta_key ] = $field;
		}

		/**
		 * Filters company data fields shown in WP admin.
		 *
		 * To add company data fields, use the `cariera_company_manager_fields`.
		 */
		$fields = apply_filters( 'cariera_company_manager_company_wp_admin_fields', $fields, $post_id );

		uasort( $fields, [ __CLASS__, 'sort_by_priority' ] );

		return $fields;
	}

	/**
	 * Sorts array of custom fields by priority value.
	 *
	 * @since 1.5.6
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
	 * Add meta box
	 *
	 * @since  1.3.0
	 */
	public function add_meta_boxes() {
		add_meta_box( 'company_data', esc_html__( 'Company Data', 'cariera-core' ), [ $this, 'company_data' ], \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY, 'normal', 'high' );
	}

	/**
	 * Company Data
	 *
	 * @since  1.3.0
	 *
	 * @param WP_POST $post
	 */
	public function company_data( $post ) {
		global $post, $thepostid;

		$thepostid = $post->ID;

		echo '<div class="wp_company_manager_meta_data wp_job_manager_meta_data">';

		wp_nonce_field( 'save_meta_data', 'company_manager_nonce' );

		do_action( 'cariera_company_manager_company_data_start', $thepostid );

		foreach ( $this->company_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			// Fix not saving fields.
			if ( ! isset( $field['value'] ) && metadata_exists( 'post', $thepostid, $key ) ) {
				$field['value'] = get_post_meta( $thepostid, $key, true );
			}

			if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
				$field['value'] = $field['default'];
			} elseif ( ! isset( $field['value'] ) ) {
				$field['value'] = '';
			}

			if ( has_action( 'company_manager_input_' . $type ) ) {
				do_action( 'company_manager_input_' . $type, $key, $field );
			} elseif ( method_exists( $this, 'input_' . $type ) ) {
				call_user_func( [ $this, 'input_' . $type ], $key, $field );
			}
		}

		do_action( 'cariera_company_data_end', $thepostid );

		echo '</div>';
	}

	/**
	 * Triggered on Save Post
	 *
	 * @since   1.3.0
	 * @version 1.9.3
	 *
	 * @param int     $post_id
	 * @param WP_POST $post
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
		// phpcs:ignore
		if ( empty( $_POST['company_manager_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['company_manager_nonce'] ), 'save_meta_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY !== $post->post_type ) {
			return;
		}

		do_action( 'cariera_save_company', $post_id, $post );
	}

	/**
	 * Save Company Meta
	 *
	 * @since   1.3.0
	 * @version 2.0.0
	 *
	 * @param int     $post_id
	 * @param WP_POST $post
	 */
	public function save_company_data( $post_id, $post ) {
		global $wpdb;

		// These need to exist.
		add_post_meta( $post_id, '_featured', 0, true );

		foreach ( $this->company_fields() as $key => $field ) {

			// Expirey date.
			if ( '_company_expires' === $key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( ! empty( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, date( 'Y-m-d', strtotime( sanitize_text_field( $_POST[ $key ] ) ) ) ); // phpcs:ignore
				} else {
					update_post_meta( $post_id, $key, '' );
				}
			} elseif ( '_company_location' === $key ) {
				// phpcs:ignore
				if ( update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) ) ) {
					do_action( 'cariera_company_manager_company_location_edited', $post_id, sanitize_text_field( $_POST[ $key ] ) ); // phpcs:ignore
				} elseif ( apply_filters( 'cariera_company_manager_geolocation_enabled', true ) && ! \WP_Job_Manager_Geocode::has_location_data( $post_id ) ) {
					\WP_Job_Manager_Geocode::generate_location_data( $post_id, sanitize_text_field( $_POST[ $key ] ) ); // phpcs:ignore
				}
				continue;
			} elseif ( '_company_author' === $key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce check handled by WP core.
				if ( empty( $_POST[ $key ] ) ) {
					$_POST[ $key ] = 0;
				}

				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce check handled by WP core.
				$input_post_author = $_POST[ $key ] > 0 ? intval( $_POST[ $key ] ) : 0;

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Avoid update post within `save_post` action.
				$wpdb->update( $wpdb->posts, [ 'post_author' => $input_post_author ], [ 'ID' => $post_id ] );
			} elseif ( '_company_hr_users' === $key ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce check handled by WP core.
				if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, array_filter( array_map( 'sanitize_text_field', $_POST[ $key ] ) ) ); // phpcs:ignore
				} else {
					update_post_meta( $post_id, $key, [] );
				}
			}
			// Everything else.
			else {
				$type = ! empty( $field['type'] ) ? $field['type'] : '';

				switch ( $type ) {
					case 'textarea':
					case 'wp_editor':
					case 'wp-editor':
						update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) ); // phpcs:ignore
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
						if ( is_array( $_POST[ $key ] ) ) {
							update_post_meta( $post_id, $key, array_filter( array_map( 'sanitize_text_field', $_POST[ $key ] ) ) ); // phpcs:ignore
						} else {
							update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) ); // phpcs:ignore
						}
						break;
				}
			}
		}
	}

	/**
	 * Displays AJAX select fields
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_select_ajax( $key, $field ) {
		$name        = ! empty( $field['name'] ) ? $field['name'] : $key;
		$value       = ! empty( $field['value'] ) ? $field['value'] : '';
		$multiple    = ! empty( $field['multiple'] ) ? 'multiple' : '';
		$ajax_action = ! empty( $field['ajax_action'] ) ? $field['ajax_action'] : '';
		$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';

		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>">
				<?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
				<?php if ( ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
				<?php endif; ?>
			</label>

			<select
				class="cariera-select2-ajax"
				name="<?php echo esc_attr( $name ); ?><?php echo $multiple ? '[]' : ''; ?>"
				id="<?php echo esc_attr( $key ); ?>"
				data-action="<?php echo esc_attr( $ajax_action ); ?>"
				data-placeholder="<?php echo esc_attr( $placeholder ); ?>"
				<?php echo $multiple; ?>
			>
				<?php
				// Preload selected values (important for edit screen).
				if ( ! empty( $value ) ) {
					$values = is_array( $value ) ? $value : [ $value ];

					foreach ( $values as $val ) {
						$label = apply_filters( 'cariera_select_ajax_label', '', $val, $field );

						if ( $label ) {
							echo '<option value="' . esc_attr( $val ) . '" selected="selected">' . esc_html( $label ) . '</option>';
						}
					}
				}
				?>
			</select>
		</p>
		<?php
	}
}
