<?php

namespace Cariera_Core\Core\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Avatar {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Extra User Avatar Field.
		add_action( 'show_user_profile', [ $this, 'backend_fields' ], 10 );
		add_action( 'edit_user_profile', [ $this, 'backend_fields' ], 10 );

		// Save Extra User Avatar Field.
		add_action( 'personal_options_update', [ $this, 'save_backend_field' ] );
		add_action( 'edit_user_profile_update', [ $this, 'save_backend_field' ] );

		// Modifying the Avatar function.
		add_filter( 'get_avatar', [ $this, 'custom_gravatar' ], 10, 6 );
		add_filter( 'get_avatar_url', [ $this, 'custom_avatar_url' ], 10, 3 );
	}

	/**
	 * Extra field for the user Avatar on the Backend
	 *
	 * @since   1.3.4
	 * @version 1.9.8
	 *
	 * @param mixed $user
	 */
	public function backend_fields( $user ) {
		wp_enqueue_media();
		?>
		<div class="cariera-user-avatar">
			<h3><?php esc_html_e( 'Cariera User Avatar', 'cariera-core' ); ?></h3>
			<table class="form-table">
				<tr>
					<th><label for="avatar"><?php esc_html_e( 'Profile Picture', 'cariera-core' ); ?></label></th>
					<td>
						<div id="avatar-preview">
							<?php
							$custom_avatar_id = get_the_author_meta( 'cariera_avatar_id', $user->ID );
							$custom_avatar    = wp_get_attachment_image_src( $custom_avatar_id, 'full' );
							if ( $custom_avatar ) {
								echo '<img src="' . esc_attr( $custom_avatar[0] ) . '" style="width:100px; height:auto;"/><br>';
							}
							?>
						</div>
	
						<input type="hidden" name="cariera_avatar_id" id="avatar" value="<?php echo esc_attr( get_the_author_meta( 'cariera_avatar_id', $user->ID ) ); ?>" class="regular-text" />
						<input type="button" class="cariera-user-avatar button-primary" value="<?php esc_html_e( 'Upload Image', 'cariera-core' ); ?>" id="uploadimage"/>
						<input type="button" class="cariera-remove-avatar button-secondary" value="<?php esc_html_e( 'Remove Image', 'cariera-core' ); ?>"/><br />
						<span class="description"><?php esc_html_e( 'This avatar will be displayed instead of the default profile picture.', 'cariera-core' ); ?></span>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Save the extra field
	 *
	 * @since   1.3.4
	 * @version 1.9.8
	 *
	 * @param int $user_id
	 */
	public function save_backend_field( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		// Get the avatar ID if provided.
		$avatar_id = isset( $_POST['cariera_avatar_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_avatar_id'] ) ) : ''; // phpcs:ignore

		if ( ! empty( $avatar_id ) ) {
			update_user_meta( $user_id, 'cariera_avatar_id', $avatar_id );
		} else {
			delete_user_meta( $user_id, 'cariera_avatar_id' );
		}
	}

	/**
	 * Modifying the Avatar function
	 *
	 * @since   1.3.4
	 * @version 1.5.2
	 *
	 * @param mixed $avatar
	 * @param mixed $id_or_email
	 * @param int   $size
	 * @param mixed $default
	 * @param mixed $alt
	 * @param array $args
	 */
	public function custom_gravatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
		if ( is_object( $id_or_email ) ) {
			$avatar_id = get_the_author_meta( 'cariera_avatar_id', $id_or_email->ID );

			if ( ! empty( $avatar_id ) ) {
				$avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
				if ( ! empty( $avatar_url[0] ) ) {
					$avatar = '<img src="' . esc_url( $avatar_url[0] ) . '" class="avatar avatar-' . esc_attr( $size ) . ' wp-user-avatar wp-user-avatar-' . esc_attr( $size ) . ' photo avatar-default cariera-avatar" width="' . esc_attr( $size ) . '" height="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '" />';
				}
			}
		} else {
			$avatar_id = get_the_author_meta( 'cariera_avatar_id', $id_or_email );

			if ( ! empty( $avatar_id ) ) {
				$avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
				if ( ! empty( $avatar_url[0] ) ) {
					$avatar = '<img src="' . esc_url( $avatar_url[0] ) . '" class="avatar avatar-' . esc_attr( $size ) . ' wp-user-avatar wp-user-avatar-' . esc_attr( $size ) . ' photo avatar-default cariera-avatar" width="' . esc_attr( $size ) . '" height="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '" />';
				}
			}
		}

		return $avatar;
	}

	/**
	 * Modifying the Avatar URL function
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 *
	 * @param string $url
	 * @param mixed  $id_or_email
	 * @param array  $args
	 */
	public function custom_avatar_url( $url, $id_or_email, $args = null ) {

		if ( is_object( $id_or_email ) ) {
			$avatar_id = get_the_author_meta( 'cariera_avatar_id', $id_or_email->ID );

			if ( ! empty( $avatar_id ) ) {
				$avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
				if ( ! empty( $avatar_url[0] ) ) {
					$url = esc_url( $avatar_url[0] );
				}
			}
		} else {
			$avatar_id = get_the_author_meta( 'cariera_avatar_id', $id_or_email );

			if ( ! empty( $avatar_id ) ) {
				$avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
				if ( ! empty( $avatar_url[0] ) ) {
					$url = esc_url( $avatar_url[0] );
				}
			}
		}

		return $url;
	}
}
