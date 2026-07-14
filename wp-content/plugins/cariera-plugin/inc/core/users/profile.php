<?php

namespace Cariera_Core\Core\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Profile {

	/**
	 * Constructor
	 */
	public function __construct() {
		// User Frontend "My Profile".
		add_shortcode( 'cariera_my_account', [ $this, 'my_profile' ] );
		add_action( 'wp_ajax_cariera_change_user_details', [ $this, 'change_user_details' ] );
		add_action( 'wp_ajax_cariera_change_user_password', [ $this, 'change_user_password' ] );
		add_action( 'wp_ajax_cariera_delete_account', [ $this, 'delete_account' ] );
	}

	/**
	 * My Account shortcode
	 * Usage: [cariera_my_profile]
	 *
	 * @since   1.5.2
	 * @version 1.9.8
	 */
	public function my_profile() {
		wp_enqueue_style( 'cariera-core-my-profile' );
		wp_enqueue_script( 'cariera-core-my-profile' );

		ob_start();

		if ( ! is_user_logged_in() ) {
			cariera_get_template_part( 'account/my-profile-login' );
		} else {
			cariera_get_template_part( 'account/my-profile' );
		}

		return ob_get_clean();
	}

	/**
	 * Change user details AJAX function
	 *
	 * @since   1.7.1
	 * @version 1.9.7
	 *
	 * TODO: refactor repetitive code & wp_json_encode.
	 */
	public function change_user_details() {
		$current_user   = wp_get_current_user();
		$user_id        = $current_user->ID;
		$user_avatar_id = isset( $_POST['cariera_avatar_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_avatar_id'] ) ) : ''; // phpcs:ignore
		$user_role      = isset( $_POST['cariera_user_role'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_user_role'] ) ) : ''; // phpcs:ignore
		$first_name     = isset( $_POST['first-name'] ) ? sanitize_text_field( wp_unslash( $_POST['first-name'] ) ) : ''; // phpcs:ignore
		$last_name      = isset( $_POST['last-name'] ) ? sanitize_text_field( wp_unslash( $_POST['last-name'] ) ) : ''; // phpcs:ignore
		$email          = isset( $_POST['user_email'] ) ? $_POST['user_email'] : ''; // phpcs:ignore
		$phone          = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : ''; // phpcs:ignore

		// Action before user details are changed.
		do_action( 'cariera_change_user_details_before', $_POST ); // phpcs:ignore

		// If email field is empty.
		if ( isset( $email ) && empty( $email ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'Email field is empty.', 'cariera-core' ),
				]
			);
			die();
		}

		// If email is not valid.
		if ( ! is_email( $email ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'The Email you entered is not valid or empty. Please try again...', 'cariera-core' ),
				]
			);
			die();
		}

		// If email already exists.
		if ( email_exists( $email ) && ( email_exists( $email ) != $current_user->ID ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'This email is already used by another user, please try a different one.', 'cariera-core' ),
				]
			);
			die();
		}

		wp_update_user(
			[
				'ID'         => $user_id,
				'user_email' => sanitize_email( $email ),
			]
		);

		// Get the avatar ID if provided.
		$avatar_id = isset( $_POST['cariera_avatar_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_avatar_id'] ) ) : ''; // phpcs:ignore

		// Update or remove avatar meta.
		if ( ! empty( $avatar_id ) ) {
			update_user_meta( $user_id, 'cariera_avatar_id', $avatar_id );
		} else {
			delete_user_meta( $user_id, 'cariera_avatar_id' );
		}

		// Submitting the user role field.
		if ( ! empty( $user_role ) ) {
			if ( 'administrator' === $user_role || empty( $user_role ) ) {
				$user_role = get_option( 'default_role' );
			}

			wp_update_user(
				[
					'ID'   => $user_id,
					'role' => $user_role,
				]
			);
		}

		// Submitting the first name field.
		if ( isset( $_POST['first-name'] ) ) {
			update_user_meta( $user_id, 'first_name', $first_name );
		}

		// Submitting the last name field.
		if ( isset( $_POST['last-name'] ) ) {
			update_user_meta( $user_id, 'last_name', $last_name );
		}

		// Submitting the phone field.
		if ( isset( $_POST['phone'] ) ) {
			update_user_meta( $user_id, 'phone', $phone );
		}

		// Action after user details are changed.
		do_action( 'cariera_change_user_details_after', $_POST ); // phpcs:ignore

		echo wp_json_encode(
			[
				'status' => true,
				'msg'    => esc_html__( 'Your profile details has been updated.', 'cariera-core' ),
			]
		);

		die();
	}

	/**
	 * Change user password AJAX function
	 *
	 * @since   1.7.1
	 * @version 1.9.7
	 *
	 * TODO: refactor repetitive code & wp_json_encode.
	 */
	public function change_user_password() {
		$user             = wp_get_current_user();
		$current_password = isset( $_POST['current_password'] ) ? sanitize_text_field( wp_unslash( $_POST['current_password'] ) ) : ''; // phpcs:ignore
		$new_password     = isset( $_POST['new_password'] ) ? sanitize_text_field( wp_unslash( $_POST['new_password'] ) ) : ''; // phpcs:ignore
		$confrim_password = isset( $_POST['confirm_password'] ) ? sanitize_text_field( wp_unslash( $_POST['confirm_password'] ) ) : ''; // phpcs:ignore

		// If the fields are empty.
		if ( empty( $current_password ) || empty( $new_password ) || empty( $confrim_password ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'All fields are required.', 'cariera-core' ),
				]
			);
			die();
		}

		// If the new password is not the same with the confirm password.
		if ( $new_password !== $confrim_password ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'New password and confirm password are not same.', 'cariera-core' ),
				]
			);
			die();
		}

		// If the current password is not correct.
		if ( ! wp_check_password( $current_password, $user->data->user_pass, $user->ID ) ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'Your current password is not correct.', 'cariera-core' ),
				]
			);
			die();
		}

		do_action( 'cariera_change_user_password_before', $_POST ); // phpcs:ignore

		wp_set_password( $new_password, $user->ID );
		echo wp_json_encode(
			[
				'status' => true,
				'msg'    => esc_html__( 'Your password has been successfully changed.', 'cariera-core' ),
			]
		);

		die();
	}

	/**
	 * Delete Account AJAX function
	 *
	 * @since   1.7.1
	 * @version 1.9.3
	 *
	 * TODO: refactor repetitive code & wp_json_encode.
	 */
	public function delete_account() {
		$user_id  = get_current_user_id();
		$user     = get_userdata( $user_id );
		$userdata = get_user_by( 'ID', $user_id );
		$password = isset( $_POST['current_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['current_pass'] ) ) : '';

		// Nonce verification.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'cariera_delete_account' ) ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'Your nonce did not verify.', 'cariera-core' ),
			];
			echo wp_json_encode( $return );
			exit;
		}

		// If password field is empty.
		if ( empty( $password ) ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'Please enter your password.', 'cariera-core' ),
			];
			echo wp_json_encode( $return );
			exit;
		}

		// If password is not correct.
		if ( ! is_object( $userdata ) || ! wp_check_password( $password, $userdata->data->user_pass, $user_id ) ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'Please enter the correct password.', 'cariera-core' ),
			];
			echo wp_json_encode( $return );
			exit;
		}

		// Mail args for the Send email notification.
		$mail_args = [
			'email'        => $user->user_email,
			'first_name'   => $user->first_name,
			'last_name'    => $user->last_name,
			'display_name' => $user->display_name,
		];

		do_action( 'cariera_delete_account_email', $mail_args );

		// Before deleting account action.
		do_action( 'cariera_delete_account_before', $user_id, $userdata );

		wp_delete_user( $user_id );

		$return = [
			'status' => true,
			'msg'    => esc_html__( 'Your account has been successfully deleted.', 'cariera-core' ),
		];
		echo wp_json_encode( $return );
		exit;
	}
}
