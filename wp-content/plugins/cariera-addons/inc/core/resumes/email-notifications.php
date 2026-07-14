<?php

namespace Cariera_Addons\Core\Resumes;

use Cariera_Addons\Core\Resumes\Abstracts\Resume_Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Notifications {
	private const SCREEN_ID = 'resume_page_cariera_addons_resumes_settings';
	private const PAGE_SLUG = 'cariera_addons_resumes_settings';

	/**
	 * Sets up initial hooks.
	 *
	 * @since 0.9.5
	 */
	public static function init() {
		add_filter( 'job_manager_email_notifications', [ __CLASS__, 'add_resume_manager_notifications' ] );
		add_filter( 'resume_manager_settings', [ __CLASS__, 'add_resume_manager_email_settings' ], 1 );
		add_action( 'resume_manager_resume_submitted', [ __CLASS__, 'send_new_resume_notification' ] );
		add_action( 'resume_manager_apply_with_resume', [ __CLASS__, 'send_apply_with_resume_notification' ], 10, 4 );
		add_action( 'resume_manager_email_resume_details', [ __CLASS__, 'output_resume_details' ], 10, 4 );
		add_filter( 'job_manager_email_is_email_notification_enabled', [ __CLASS__, 'force_apply_with_resume_enabled' ], 10, 2 );
	}

	/**
	 * Add email notification settings for the resume manager context.
	 *
	 * @since   0.9.5
	 * @version 1.0.6
	 *
	 * @param array $settings
	 */
	public static function add_resume_manager_email_settings( $settings ) {
		if ( ! self::is_resume_settings_page() ) {
			return $settings;
		}

		return \WP_Job_Manager_Email_Notifications::add_email_settings( $settings, Resume_Email::get_context() );
	}

	/**
	 * Check if we're on the Resume settings page
	 *
	 * @since   1.0.6
	 * @version 1.1.0
	 */
	private static function is_resume_settings_page(): bool {
		// Only run in the admin area.
		if ( ! is_admin() ) {
			return false;
		}

		// Check via screen object if available.
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( isset( $screen->id ) && self::SCREEN_ID === $screen->id ) {
				return true;
			}
		}

		// Check via URL parameter.
		if ( isset( $_GET['page'] ) && self::PAGE_SLUG === $_GET['page'] ) { // phpcs:ignore
			return true;
		}

		return false;
	}

	/**
	 * Adds resume manager's email notifications.
	 *
	 * @since 0.9.5
	 *
	 * @param array $notifications
	 */
	public static function add_resume_manager_notifications( $notifications ) {
		$notifications[] = \Cariera_Addons\Core\Resumes\Emails\Admin_New_Resume::class;
		$notifications[] = \Cariera_Addons\Core\Resumes\Emails\Apply_With_Resume::class;

		return $notifications;
	}

	/**
	 * Fire the action to send a new resume notification to the admin.
	 *
	 * @since 0.9.5
	 *
	 * @param int $resume_id
	 */
	public static function send_new_resume_notification( $resume_id ) {
		do_action( 'job_manager_send_notification', 'admin_new_resume', [ 'resume_id' => $resume_id ] );
	}

	/**
	 * Enqueue the email notification for when a user applies with a resume.
	 *
	 * @since 0.9.5
	 *
	 * @param int    $user_id             User ID of the person who submitted the application.
	 * @param int    $job_id              Job post ID.
	 * @param int    $resume_id           Resume post ID.
	 * @param string $application_message Message that was sent along with resume application.
	 */
	public static function send_apply_with_resume_notification( $user_id, $job_id, $resume_id, $application_message ) {
		do_action(
			'job_manager_send_notification',
			'apply_with_resume',
			[
				'resume_id' => $resume_id,
				'job_id'    => $job_id,
				'message'   => $application_message,
			]
		);
	}

	/**
	 * Show details about the resume listing.
	 *
	 * @since 0.9.5
	 *
	 * @param WP_Post              $resume         The resume listing to show details for.
	 * @param WP_Job_Manager_Email $email          Email object for the notification.
	 * @param bool                 $sent_to_admin  True if this is being sent to an administrator.
	 * @param bool                 $plain_text     True if the email is being sent as plain text.
	 */
	public static function output_resume_details( $resume, $email, $sent_to_admin, $plain_text = false ) {
		$template_segment = self::locate_template_file( 'email-resume-details', $plain_text );
		if ( ! file_exists( $template_segment ) ) {
			return;
		}

		$fields = self::get_resume_detail_fields( $resume, $sent_to_admin, $plain_text );

		include $template_segment;
	}

	/**
	 * Locate template file.
	 *
	 * @since   0.9.5
	 * @version 1.0.4
	 *
	 * @param string $template_name
	 * @param bool   $plain_text
	 * @return string
	 */
	public static function locate_template_file( $template_name, $plain_text ) {
		return \WP_Job_Manager_Email_Notifications::locate_template_file( $template_name, $plain_text, Resume_Email::get_template_path(), Resume_Email::get_template_default_path() );
	}

	/**
	 * Get the resume fields to show in email templates.
	 *
	 * @since 0.9.5
	 *
	 * @param WP_Post $resume
	 * @param bool    $sent_to_admin
	 * @param bool    $plain_text
	 */
	private static function get_resume_detail_fields( $resume, $sent_to_admin, $plain_text = false ) {
		$fields = [];

		$fields['resume_candidate'] = [
			'label' => esc_html__( 'Candidate', 'cariera-addons' ),
			'value' => $resume->post_title,
		];

		if ( $sent_to_admin || 'publish' === $resume->post_status ) {
			$fields['resume_candidate']['url'] = get_permalink( $resume );
		}

		$resume_expires = get_post_meta( $resume->ID, '_resume_expires', true );
		if ( ! empty( $resume_expires ) ) {
			$resume_expires_str       = date_i18n( get_option( 'date_format' ), strtotime( $resume_expires ) );
			$fields['resume_expires'] = [
				'label' => esc_html__( 'Resume expires', 'cariera-addons' ),
				'value' => $resume_expires_str,
			];
		}

		$custom_fields = array_diff_key(
			Admin\Writepanels::resume_fields(),
			[
				'_resume_file'    => '',
				'_resume_expires' => '',
			]
		);

		foreach ( $custom_fields as $meta_key => $field ) {
			if ( empty( $field['type'] ) ) {
				$field['type'] = 'text';
			}
			if ( ! in_array( $field['type'], [ 'text', 'textarea' ], true ) ) {
				continue;
			}

			$meta_value = get_post_meta( $resume->ID, $meta_key, true );
			if ( ! empty( $meta_value ) && is_string( $meta_value ) ) {
				$fields[ 'resume_' . $meta_key ] = [
					'label' => $field['label'],
					'value' => esc_html( $meta_value ),
				];
			}
		}

		$links = get_post_meta( $resume->ID, '_links', true );
		if ( ! empty( $links ) ) {
			foreach ( $links as $key => $item ) {
				$fields[ 'resume_links_' . $key ] = [
					'label' => esc_html__( 'Link', 'cariera-addons' ),
					'value' => $item['name'],
					'url'   => esc_url( $item['url'] ),
				];
			}
		}

		$education = get_post_meta( $resume->ID, '_candidate_education', true );
		if ( ! empty( $education ) ) {
			$resume_education_str = '';
			foreach ( $education as $key => $item ) {
				// translators: Placeholder is location of education experience.
				$resume_education_str .= sprintf( __( 'Location: %s', 'cariera-addons' ), $item['location'] ) . PHP_EOL;
				// translators: Placeholder is date of education experience.
				$resume_education_str .= sprintf( __( 'Date: %s', 'cariera-addons' ), $item['date'] ) . PHP_EOL;
				// translators: Placeholder is qualifications/degrees of education experience.
				$resume_education_str .= sprintf( __( 'Certification: %s', 'cariera-addons' ), $item['qualification'] ) . PHP_EOL;
				// translators: Placeholder is notes for education experience.
				$resume_education_str .= sprintf( __( 'Notes: %s', 'cariera-addons' ), $item['notes'] ) . PHP_EOL;
				$resume_education_str .= PHP_EOL;
			}

			$fields['resume_education'] = [
				'label' => esc_html__( 'Education', 'cariera-addons' ),
				'value' => trim( $resume_education_str, PHP_EOL ),
			];
		}

		$experience = get_post_meta( $resume->ID, '_candidate_experience', true );
		if ( ! empty( $experience ) ) {
			$resume_experience_str = '';
			foreach ( $experience as $key => $item ) {
				// translators: Placeholder is employer name of experience.
				$resume_experience_str .= sprintf( __( 'Employer: %s', 'cariera-addons' ), $item['employer'] ) . PHP_EOL;
				// translators: Placeholder is date of experience.
				$resume_experience_str .= sprintf( __( 'Date: %s', 'cariera-addons' ), $item['date'] ) . PHP_EOL;
				// translators: Placeholder is job title of experience.
				$resume_experience_str .= sprintf( __( 'Job Title: %s', 'cariera-addons' ), $item['job_title'] ) . PHP_EOL;
				// translators: Placeholder is notes for experience.
				$resume_experience_str .= sprintf( __( 'Notes: %s', 'cariera-addons' ), $item['notes'] ) . PHP_EOL;
				$resume_experience_str .= PHP_EOL;
			}

			$fields['resume_experience'] = [
				'label' => esc_html__( 'Experience', 'cariera-addons' ),
				'value' => trim( $resume_experience_str, PHP_EOL ),
			];
		}

		if ( $sent_to_admin ) {
			$author = get_user_by( 'ID', $resume->post_author );
			if ( $author instanceof \WP_User ) {
				$fields['author'] = [
					'label' => esc_html__( 'Posted by', 'cariera-addons' ),
					'value' => $author->user_nicename,
					'url'   => 'mailto:' . $author->user_email,
				];
			}
		}

		/**
		 * Modify the fields shown in email notifications in the details summary a resume.
		 */
		return apply_filters( 'resume_manager_emails_resume_detail_fields', $fields, $resume, $sent_to_admin, $plain_text );
	}

	/**
	 * Force the apply with resume notification to be enabled. Not needed on WPJM 1.34.1 and newer.
	 *
	 * @since 0.9.5
	 *
	 * @param bool   $is_email_notification_enabled Filtered value for if the email notification is enabled.
	 * @param string $email_notification_key        Unique key for the email notification.
	 */
	public static function force_apply_with_resume_enabled( $is_email_notification_enabled, $email_notification_key ) {
		if ( Emails\Apply_With_Resume::get_key() === $email_notification_key ) {
			return true;
		}

		return $is_email_notification_enabled;
	}
}
