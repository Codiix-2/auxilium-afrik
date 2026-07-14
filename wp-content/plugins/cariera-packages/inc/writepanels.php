<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the management of Cariera Packages meta fields.
 */
class Writepanels extends \Cariera_Core\Core\Metabox {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 1, 2 );
		add_action( 'cariera_save_package', [ $this, 'save_package_data' ], 1, 2 );
	}

	/**
	 * Cariera Package Fields
	 *
	 * @since 0.9.0
	 */
	public static function package_fields() {
		$fields = \Cariera_Packages\Post_Types\Cariera_Package::get_package_fields();

		return $fields;
	}

	/**
	 * Sorts array of custom fields by priority value.
	 *
	 * @since 0.9.0
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
	 * @since   0.9.0
	 * @version 0.9.8
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'cariera_package_data',
			esc_html__( 'Cariera Package Data', 'cariera-packages' ),
			[ $this, 'package_data' ],
			\Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
			'normal',
			'high'
		);
	}

	/**
	 * Package Data
	 *
	 * @since   0.9.0
	 * @version 0.9.18
	 *
	 * @param WP_POST $post
	 */
	public function package_data( $post ) {
		global $post, $thepostid;

		$thepostid = $post->ID;

		echo '<div class="cariera_meta_data cariera_packages_meta_data">';

		wp_nonce_field( 'save_meta_data', 'cariera_meta_nonce' );

		do_action( 'cariera_packages_data_start', $thepostid );

		foreach ( $this->package_fields() as $key => $field ) {
			$type      = ! empty( $field['type'] ) ? $field['type'] : 'text';
			$data_attr = '';

			// Fix not saving fields.
			if ( ! isset( $field['value'] ) && metadata_exists( 'post', $thepostid, $key ) ) {
				$field['value'] = get_post_meta( $thepostid, $key, true );
			}

			if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
				$field['value'] = $field['default'];
			} elseif ( ! isset( $field['value'] ) ) {
				$field['value'] = '';
			}

			if ( isset( $field['data']['package-type'] ) ) {
				$package_type = $field['data']['package-type'];

				// If multiple package types are provided as an array, join them with spaces.
				if ( is_array( $package_type ) ) {
					$package_type = implode( ' ', $package_type );
				}

				$data_attr = ' data-package-type="' . esc_attr( $package_type ) . '"';
			}

			echo '<div class="cariera-package-field"' . wp_kses_post( $data_attr ) . '>';
			if ( has_action( 'cariera_packages_input_' . $type ) ) {
				do_action( 'cariera_packages_input_' . $type, $key, $field );
			} elseif ( method_exists( $this, 'input_' . $type ) ) {
				call_user_func( [ $this, 'input_' . $type ], $key, $field );
			}
			echo '</div>';
		}

		do_action( 'cariera_packages_data_end', $thepostid );

		echo '</div>';
	}

	/**
	 * Triggered on Save Post
	 *
	 * @since   0.9.0
	 * @version 0.9.8
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
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( empty( $_POST['cariera_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['cariera_meta_nonce'] ), 'save_meta_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE !== $post->post_type ) {
			return;
		}

		do_action( 'cariera_save_package', $post_id, $post );
	}

	/**
	 * Save Cariera_Packages Meta
	 *
	 * @since   0.9.0
	 * @version 0.9.9
	 *
	 * @param int     $post_id
	 * @param WP_POST $post
	 */
	public function save_package_data( $post_id, $post ) {

		foreach ( $this->package_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : '';

			// Custom handling for cariera_packages_user_id meta key.
			if ( 'cariera_packages_user_id' === $key ) {
				// Check if the key exists in $_POST before using it.
				$user_id = isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : 0; // phpcs:ignore
				// Update the post meta with the user ID, ensuring it's a valid value.
				update_post_meta( $post_id, $key, $user_id > 0 ? $user_id : 0 );
				continue;
			}

			switch ( $type ) {
				case 'textarea':
				case 'wp_editor':
				case 'wp-editor':
					update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) ); // phpcs:ignore
					break;
				case 'checkbox':
				case 'switch':
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
					if ( isset( $_POST[ $key ] ) ) {
						update_post_meta( $post_id, $key, 1 );
					} else {
						update_post_meta( $post_id, $key, 0 );
					}
					break;
				case 'heading':
					// nothing.
					break;
				default:
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
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
