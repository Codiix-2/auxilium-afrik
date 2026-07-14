<?php

namespace Cariera_Core\Core\Resume_Manager;

use Cariera_Core\Core\Resume_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings extends Resume_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		add_filter( 'resume_manager_settings', [ $this, 'settings' ] );
	}

	/**
	 * Add extra settings to Resume Options
	 *
	 * @since   1.3.0
	 * @version 1.9.8
	 *
	 * @param array $settings
	 */
	public function settings( $settings = [] ) {
		$settings['resume_listings'][1][] = [
			'name'       => 'cariera_resume_manager_enable_rate',
			'std'        => '1',
			'label'      => esc_html__( 'Rate', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Enable Rate', 'cariera-core' ),
			'desc'       => esc_html__( 'Allows users to specify their rate when submitting a resume.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['resume_listings'][1][] = [
			'name'       => 'cariera_resume_manager_enable_education',
			'std'        => '1',
			'label'      => esc_html__( 'Education', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Enable listing education', 'cariera-core' ),
			'desc'       => esc_html__( 'Allows users select their education when submitting a resume. Note: an admin has to create experience before site users can select them.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['resume_listings'][1][] = [
			'name'       => 'cariera_resume_manager_enable_experience',
			'std'        => '1',
			'label'      => esc_html__( 'Experience', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Enable listing experience', 'cariera-core' ),
			'desc'       => esc_html__( 'Allows users select their experience when submitting a resume. Note: an admin has to create experience before site users can select them.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['resume_listings'][1][] = [
			'name'       => 'cariera_resume_manager_enable_portfolio',
			'std'        => '1',
			'label'      => esc_html__( 'Portfolio', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Enable Candidate Portfolio', 'cariera-core' ),
			'desc'       => esc_html__( 'When enabled, the submission form will include a "gallery" upload field, allowing the candidate\'s portfolio to be displayed on their profile page.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];

		// Single Resume Settings.
		$settings['single_resume'] = [
			esc_html__( 'Single Resume', 'cariera-core' ),
			[
				[
					'name'    => 'resume_manager_single_resume_contact_form',
					'std'     => '',
					'label'   => esc_html__( 'Single Resume Contact Form', 'cariera-core' ),
					'desc'    => esc_html__( 'Select the contact form that you want to show on a single resume page. The contact form will show only if the private messages are disabled for resumes.', 'cariera-core' ),
					'type'    => 'select',
					'options' => cariera_get_forms(),
				],
				[
					'name'       => 'cariera_resume_manager_contact_owner',
					'std'        => '0',
					'label'      => esc_html__( 'Owner Contact', 'cariera-core' ),
					'cb_label'   => esc_html__( 'Hide Contact to Owner', 'cariera-core' ),
					'desc'       => esc_html__( 'When enabled the "contact button & form" of the Resume will be hidden from the owner of the Resume. This will avoid Candidates being able to send emails to themselves via their own Resume.', 'cariera-core' ),
					'type'       => 'checkbox',
					'attributes' => [],
				],

				[
					'name'    => 'cariera_resume_manager_single_resume_layout',
					'std'     => 'v1',
					'label'   => esc_html__( 'Single Resume Layout', 'cariera-core' ),
					'desc'    => esc_html__( 'Select the default layout version for your single resume page.', 'cariera-core' ),
					'type'    => 'select',
					'options' => [
						'v1' => esc_html__( 'Version 1', 'cariera-core' ),
						'v2' => esc_html__( 'Version 2', 'cariera-core' ),
						'v3' => esc_html__( 'Version 3', 'cariera-core' ),
					],
				],
				[
					'name'     => 'cariera_resume_manager_related_resumes',
					'std'      => '1',
					'label'    => esc_html__( 'Related Resumes', 'cariera-core' ),
					'cb_label' => esc_html__( 'Enable related listings', 'cariera-core' ),
					'desc'     => esc_html__( 'Show related listings in single listing page.', 'cariera-core' ),
					'type'     => 'checkbox',
				],
				[
					'name'     => 'cariera_resume_manager_featured_resumes',
					'std'      => '1',
					'label'    => esc_html__( 'Featured Resumes', 'cariera-core' ),
					'cb_label' => esc_html__( 'Enable featured listings', 'cariera-core' ),
					'desc'     => esc_html__( 'Show featured listings in single listing page v1.', 'cariera-core' ),
					'type'     => 'checkbox',
				],
				[
					'name'     => 'cariera_resume_manager_invite_candidate',
					'std'      => '1',
					'label'    => esc_html__( 'Invite Candidate', 'cariera-core' ),
					'cb_label' => esc_html__( 'Enable candidate invitation', 'cariera-core' ),
					'desc'     => esc_html__( 'Enabling this option will allow candidates to be invited to apply to an employer\'s job.', 'cariera-core' ),
					'type'     => 'checkbox',
				],
				[
					'name'    => 'cariera_resume_manager_invite_candidate_employer_email',
					'std'     => 'user_email',
					'label'   => esc_html__( 'Invitation Email Sender', 'cariera-core' ),
					'desc'    => esc_html__( 'Choose the email that the invitation email will be sent from. It can be sent from the Employer\'s user email or from the job\'s application email (_application meta).', 'cariera-core' ),
					'type'    => 'select',
					'options' => [
						'user_email'        => esc_html__( 'User Email', 'cariera-core' ),
						'application_email' => esc_html__( 'Application Email', 'cariera-core' ),
					],
				],
			],
		];

		// Email Setting.
		$settings['email_notifications'][1][] = [
			'name'       => 'cariera_resume_manager_approved_resume_notification',
			'std'        => '1',
			'label'      => esc_html__( 'Approved Resume', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Approved Resume Notification', 'cariera-core' ),
			'desc'       => esc_html__( 'When enabled the Candidate will receive an email notification when their resume get\'s approved.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];
		$settings['email_notifications'][1][] = [
			'name'       => 'cariera_resume_manager_expired_resume_notification',
			'std'        => '1',
			'label'      => esc_html__( 'Expired Resume', 'cariera-core' ),
			'cb_label'   => esc_html__( 'Expired Resume Notification', 'cariera-core' ),
			'desc'       => esc_html__( 'When enabled the Candidate will receive an email notification when their resume get\'s expired.', 'cariera-core' ),
			'type'       => 'checkbox',
			'attributes' => [],
		];

		// Remove Contact Form setting if Contact Form 7 is not installed.
		if ( ! class_exists( 'WPCF7' ) ) {
			foreach ( $settings['single_resume'][1] as $i => $field ) {
				if ( isset( $field['name'] ) && 'resume_manager_single_resume_contact_form' === $field['name'] ) {
					unset( $settings['single_resume'][1][ $i ] );
				}
			}

			// Reindex the array to avoid gaps.
			$settings['single_resume'][1] = array_values( $settings['single_resume'][1] );
		}

		return $settings;
	}
}
