<?php

namespace Cariera_Core\Extensions\Resume;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Invite_Candidate {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Construct
	 */
	public function __construct() {
		$this->display_invite_candidate_hook();

		// AJAX handling.
		add_action( 'wp_ajax_cariera_invite_candidate', [ $this, 'process_invite_candidate' ] );
	}

	/**
	 * Register invite candidate display hook
	 *
	 * @since 1.9.3
	 */
	public function display_invite_candidate_hook() {
		$layout = get_option( 'cariera_resume_manager_single_resume_layout' );

		if ( isset( $_GET['resume-layout'] ) && ! empty( $_GET['resume-layout'] ) ) {
			$layout = sanitize_text_field( wp_unslash( $_GET['resume-layout'] ) );
		}

		$layout = apply_filters( 'cariera_resume_layout', $layout );

		if ( 'v1' === $layout ) {
			add_action( 'single_resume_meta_end', [ $this, 'display_invite_candidate' ], 100 );
		} else {
			add_action( 'cariera_candidate_detail_actions', [ $this, 'display_invite_candidate' ], 50 );
		}
	}

	/**
	 * Adding Invite Candidate to the single resume page
	 *
	 * @since   1.9.2
	 * @version 2.0.0
	 */
	public static function display_invite_candidate() {
		global $post;

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( 1 !== absint( get_option( 'cariera_resume_manager_invite_candidate' ) ) ) {
			return;
		}

		$query = [
			'post_type'              => 'job_listing',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'date',
			'order'                  => 'desc',
			'author'                 => get_current_user_id(),
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		];

		$jobs = new \WP_Query( $query );

		if ( class_exists( 'Cariera_Addons\Core\Resumes\Resumes' ) ) {
			get_job_manager_template(
				'resumes/single-resume/invite-candidate.php',
				[
					'jobs' => $jobs->posts,
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);
		} else {
			get_job_manager_template(
				'single-resume/invite-candidate.php',
				[
					'jobs' => $jobs->posts,
				],
				'wp-job-manager-resumes'
			);
		}
	}

	/**
	 * AJAX Process to invite candidate to apply
	 *
	 * @since   1.9.2
	 * @version 1.9.3
	 */
	public function process_invite_candidate() {
		if ( 1 !== absint( get_option( 'cariera_resume_manager_invite_candidate' ) ) ) {
			return;
		}

		// Check if nonce is set and valid.
		if ( empty( $_POST['cariera_invite_candidate_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['cariera_invite_candidate_nonce'] ), 'cariera_invite_candidate_action' ) ) {
			wp_send_json(
				[
					'status' => false,
					'msg'    => esc_html__( 'Security check failed. Please refresh and try again.', 'cariera-core' ),
				]
			);
		}

		// If user is not logged in.
		if ( ! is_user_logged_in() ) {
			echo wp_json_encode(
				[
					'status' => false,
					'msg'    => esc_html__( 'Please login in order to be able to invite a Candidate.', 'cariera-core' ),
				]
			);
			exit;
		}

		// Check if candidate exists.
		$candidate_id = ! empty( $_POST['candidate_id'] ) ? absint( $_POST['candidate_id'] ) : 0;
		$resume       = get_post( $candidate_id );

		if ( ! $resume || empty( $resume->ID ) ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'Candidate doesn\'t exist!', 'cariera-core' ),
			];
			wp_send_json( $return );
		}

		// Check if a job has been selected.
		$job_id = ! empty( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
		if ( empty( $job_id ) ) {
			$return = [
				'status' => false,
				'msg'    => esc_html__( 'Please select a job!', 'cariera-core' ),
			];
			wp_send_json( $return );
		}

		// Check if candidate is already invited.
		$job_invited_list = get_post_meta( $job_id, '_cariera_job_invited_candidate_apply', true );
		$job_invited_list = ! empty( $job_invited_list ) ? $job_invited_list : [];

		if ( in_array( $candidate_id, $job_invited_list, true ) ) {
			wp_send_json(
				[
					'status' => false,
					'msg'    => esc_html__( 'You have already invited this candidate to apply for this job.', 'cariera-core' ),
				]
			);
			exit;
		}

		// Add candidate to invited list.
		$job_invited_list[] = $candidate_id;
		update_post_meta( $job_id, '_cariera_job_invited_candidate_apply', $job_invited_list );

		// Get employer details.
		$user_id           = get_current_user_id();
		$user              = get_userdata( $user_id );
		$employer_fullname = trim( $user->first_name . ' ' . $user->last_name );
		$employer_email    = $user->user_email;

		// Use application email if configured.
		if ( 'application_email' === get_option( 'cariera_resume_manager_invite_candidate_employer_email' ) ) {
			$application_email = get_post_meta( $job_id, '_application', true );
			if ( ! empty( $application_email ) && is_email( $application_email ) ) {
				$employer_email = $application_email;
			}
		}

		// Get candidate email.
		$candidate_email = get_post_meta( $candidate_id, '_candidate_email', true );
		if ( empty( $candidate_email ) || ! is_email( $candidate_email ) ) {
			wp_send_json(
				[
					'status' => false,
					'msg'    => esc_html__( 'Invalid candidate email address.', 'cariera-core' ),
				]
			);
			exit;
		}

		// Prepare email content.
		$email_subject  = esc_html__( 'Job Invitation - Apply now!', 'cariera-core' );
		$email_content  = sprintf(
			esc_html__( 'Your resume "%s" has been invited to apply to the job listed below:', 'cariera-core' ),
			get_the_title( $candidate_id )
		);
		$email_content .= '<br><br><a href="' . get_permalink( $job_id ) . '">' . get_the_title( $job_id ) . '</a>';

		// Just pass the from address as an additional header if needed.
		$additional_headers = [
			'From: ' . $employer_fullname . ' <' . $employer_email . '>',
		];

		$result = \Cariera_Core\Core\Emails::send( $candidate_email, $email_subject, $email_content, [], $additional_headers );

		/**
		 * Action to allow other plugins to hook into the invitation process.
		 * This can be used to send custom emails or perform additional actions.
		 *
		 * @since 1.9.2
		 *
		 * @param int   $candidate_id The ID of the candidate being invited.
		 * @param array $job_ids      An array of job IDs the candidate is invited to apply for.
		 * @param int   $user_id      The ID of the user sending the invitation.
		 */
		do_action( 'cariera_invite_candidate_to_apply', $candidate_id, [ $job_id ], $user_id );

		if ( $result ) {
			wp_send_json(
				[
					'status' => true,
					'msg'    => esc_html__( 'Candidate invited successfully!', 'cariera-core' ),
				]
			);
		} else {
			wp_send_json(
				[
					'status' => false,
					'msg'    => esc_html__( 'There was an error sending the invitation.', 'cariera-core' ),
				]
			);
		}
	}
}
