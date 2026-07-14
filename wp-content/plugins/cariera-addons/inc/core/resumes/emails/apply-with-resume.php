<?php

namespace Cariera_Addons\Core\Resumes\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Apply_With_Resume extends \Cariera_Addons\Core\Resumes\Abstracts\Resume_Email {
	const SETTING_NOTICE_INCLUDE_DETAILS    = 'include_resume_details';
	const SETTING_NOTICE_USE_LEGACY_MESSAGE = 'use_legacy';

	/**
	 * Get the unique email notification key.
	 *
	 * @since 0.9.5
	 */
	public static function get_key() {
		return 'apply_with_resume';
	}

	/**
	 * Get the friendly name for this email notification.
	 *
	 * @since 0.9.5
	 */
	public static function get_name() {
		return esc_html__( 'Employer Notice of Application With Resume', 'cariera-addons' );
	}

	/**
	 * Get the description for this email notification.
	 *
	 * @since 0.9.5
	 */
	public static function get_description() {
		return esc_html__( 'Send a notice to the employer when someone applies to a job listing with a resume when the job listing application method is email.', 'cariera-addons' );
	}

	/**
	 * Get the contents of a template.
	 *
	 * @since 0.9.5
	 *
	 * @param bool $plain_text
	 */
	public function get_template( $plain_text = false ) {
		$settings   = $this->get_settings();
		$use_legacy = has_filter( 'apply_with_resume_email_message' ) && ! empty( $settings[ self::SETTING_NOTICE_USE_LEGACY_MESSAGE ] );

		if ( $use_legacy ) {
			$args            = $this->get_args();
			$candidate_email = get_post_meta( $args['resume']->ID, '_candidate_email', true );

			return implode(
				'',
				apply_filters(
					'apply_with_resume_email_message',
					[
						'greeting'      => __( 'Hello', 'cariera-addons' ),
						// translators: Placeholder is the job title.
						'position'      => sprintf( "\n\n" . __( 'A candidate has applied online for the position "%s".', 'cariera-addons' ), get_the_title( $args['job']->ID ) ),
						'start_message' => "\n\n-----------\n\n",
						'message'       => $args['message'],
						'end_message'   => "\n\n-----------\n\n",
						// translators: Placeholder is the URL to their resume.
						'view_resume'   => sprintf( __( 'You can view their online resume here: %s.', 'cariera-addons' ), $args['resume_link'] ),
						// translators: Placeholder is the candidate email address.
						'contact'       => "\n" . sprintf( __( 'Or you can contact them directly at: %s.', 'cariera-addons' ), $candidate_email ),
					],
					isset( $args['author'] ) ? $args['author']->ID : 0,
					$args['job']->ID,
					$args['resume']->ID,
					$args['message']
				)
			);
		}

		return parent::get_template( $plain_text );
	}

	/**
	 * Get the email subject.
	 *
	 * @since 0.9.5
	 */
	public function get_subject() {
		$method = $this->get_application_method_details();

		if ( $method && ! empty( $method->subject ) ) {
			return wp_specialchars_decode( $method->subject, ENT_QUOTES );
		}

		$args = $this->get_args();

		// Job object coming from arguments.
		$job = $args['job'];

		// translators: Placeholder is title of resume.
		return sprintf( esc_html__( 'Resume submitted for job listing: %s', 'cariera-addons' ), get_the_title( $job ) );
	}

	/**
	 * Get `From:` address header value. Can be simple email or formatted `Firstname Lastname <email@example.com>`.
	 *
	 * @since 0.9.5
	 */
	public function get_from() {
		$args = $this->get_args();

		$candidate_name  = get_the_title( $args['resume'] );
		$candidate_email = $this->get_candidate_email();

		return $candidate_name . ' <' . $candidate_email . '>';
	}

	/**
	 * Get the base headers for the email. No need to add CC or From headers. Content-type is added when sending rich-text.
	 *
	 * @since 0.9.5
	 */
	public function get_headers() {
		$headers = parent::get_headers();

		$candidate_email = $this->get_candidate_email();
		if ( $candidate_email ) {
			$headers[] = 'Reply-To: ' . $candidate_email;
		}

		return $headers;
	}

	/**
	 * Get the candidate's email address.
	 *
	 * @since 0.9.5
	 */
	private function get_candidate_email() {
		$args            = $this->get_args();
		$candidate_email = get_post_meta( $args['resume']->ID, '_candidate_email', true );

		if ( empty( $candidate_email ) && isset( $args['author'] ) && $args['author'] instanceof WP_User ) {
			$candidate_email = $args['author']->user_email;
		}

		if ( empty( $candidate_email ) ) {
			return false;
		}

		return sanitize_email( $candidate_email );
	}

	/**
	 * Get the job application method details.
	 *
	 * @since 0.9.5
	 */
	private function get_application_method_details() {
		$args = $this->get_args();
		if ( ! isset( $args['job'] ) ) {
			return false;
		}

		$method = get_the_job_application_method( $args['job'] );
		if ( ! is_object( $method ) || empty( $method->raw_email ) ) {
			return false;
		}

		return $method;
	}

	/**
	 * Get array or comma-separated list of email addresses to send message.
	 *
	 * @since 0.9.5
	 */
	public function get_to() {
		$method = $this->get_application_method_details();

		if ( ! $method ) {
			return false;
		}

		return $method->raw_email;
	}

	/**
	 * Returns the list of file paths to attach to an email.
	 *
	 * @since 0.9.5
	 */
	public function get_attachments() {
		$attachments = parent::get_attachments();
		$args        = $this->get_args();

		$files = get_resume_attachments( $args['resume']->ID );
		if ( ! empty( $files['attachments'] ) ) {
			$attachments = array_merge( $attachments, $files['attachments'] );
		}

		return $attachments;
	}

	/**
	 * Checks if we should show resume details on the email.
	 *
	 * @since 0.9.5
	 */
	public function show_resume_details() {
		$settings = $this->get_settings();

		return ! empty( $settings[ self::SETTING_NOTICE_INCLUDE_DETAILS ] );
	}

	/**
	 * Checks the arguments and returns whether the email notification is properly set up.
	 *
	 * @since 0.9.5
	 */
	public function is_valid() {
		$args = $this->get_args();

		return isset( $args['resume'] )
				&& isset( $args['job'] )
				&& isset( $args['message'] )
				&& $args['resume'] instanceof \WP_Post
				&& $args['job'] instanceof \WP_Post
				&& $this->get_to();
	}

	/**
	 * Force the email notification to be enabled.
	 *
	 * @since 0.9.5
	 */
	public static function get_enabled_force_value() {
		return true;
	}

	/**
	 * Get the settings for this email notifications.
	 *
	 * @since 0.9.5
	 */
	public static function get_setting_fields() {
		$fields = parent::get_setting_fields();

		// Keep support for the legacy message.
		if ( has_filter( 'apply_with_resume_email_message' ) ) {
			$fields[] = [
				'name'     => self::SETTING_NOTICE_USE_LEGACY_MESSAGE,
				'std'      => 1,
				'label'    => esc_html__( 'Legacy Message', 'cariera-addons' ),
				'cb_label' => esc_html__( 'Use legacy filter to generate email content', 'cariera-addons' ),
				'desc'     => wp_kses_post( __( 'Legacy filter <code>apply_with_resume_email_message</code> has been set with a customization. Disable to use new template.', 'cariera-addons' ) ),
				'type'     => 'checkbox',
			];
		}

		$fields[] = [
			'name'     => self::SETTING_NOTICE_INCLUDE_DETAILS,
			'std'      => 0,
			'label'    => esc_html__( 'Resume Details', 'cariera-addons' ),
			'cb_label' => esc_html__( 'Include resume details in the content of the email', 'cariera-addons' ),
			'type'     => 'checkbox',
		];

		return $fields;
	}

	/**
	 * Expand arguments as necessary for the generation of the email.
	 *
	 * @since 0.9.5
	 *
	 * @param array $args Arguments used in generation of email.
	 */
	protected function prepare_args( $args ) {
		$args = parent::prepare_args( $args );

		$args['resume_link'] = null;
		if ( ! empty( $args['resume'] ) ) {
			$args['resume_link'] = get_resume_share_link( $args['resume']->ID );
		}

		return $args;
	}
}
