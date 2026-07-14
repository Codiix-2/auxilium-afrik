<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Integration {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! \Cariera_Addons\Helpers::core_feature_is_enabled( 'resumes' ) ) {
			return;
		}

		// Integrate with Resume Manager's apply form.
		add_action( 'applied_with_resume', [ $this, 'handle_applied_with_resume' ], 10, 5 );
	}

	/**
	 * Handle applications via Resume Manager's Form
	 *
	 * @since   0.9.3
	 * @version 0.9.4
	 *
	 * @param  int    $user_id
	 * @param  int    $job_id
	 * @param  int    $resume_id
	 * @param  string $application_message
	 * @param  bool   $sent_email
	 */
	public function handle_applied_with_resume( $user_id, $job_id, $resume_id, $application_message, $sent_email = true ) {
		if ( ! $job_id ) {
			return;
		}

		$user            = get_user_by( 'id', $user_id );
		$resume_link     = get_resume_share_link( $resume_id );
		$candidate_name  = get_post_meta( $resume_id, '_candidate_name', true );
		$candidate_email = get_post_meta( $resume_id, '_candidate_email', true );

		if ( empty( $candidate_email ) ) {
			$candidate_email = $user->user_email;
		}

		$application_meta               = [];
		$application_meta['_resume_id'] = $resume_id;

		$get_meta = [
			'_candidate_title'    => esc_html__( 'Title', 'cariera-addons' ),
			'_candidate_location' => esc_html__( 'Location', 'cariera-addons' ),
		];

		foreach ( $get_meta as $key => $label ) {
			$value = get_post_meta( $resume_id, $key, true );
			if ( $value ) {
				$application_meta[ $label ] = $value;
			}
		}

		create_job_application( $job_id, $candidate_name, $candidate_email, $application_message, $application_meta, ! $sent_email, 'resume-manager' );
	}
}
