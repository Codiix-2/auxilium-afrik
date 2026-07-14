<?php

namespace Cariera_Core\Core\Resume_Manager;

use Cariera_Core\Core\Resume_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Fields extends Resume_Manager {

	/**
	 * Constructor
	 *
	 * @since 1.4.5
	 */
	public function __construct() {
		add_filter( 'submit_resume_form_fields', [ $this, 'frontend_wprm_fields' ] );
		add_action( 'resume_manager_update_resume_data', [ $this, 'wprm_update_data' ], 10, 2 );
		add_filter( 'resume_manager_resume_fields', [ $this, 'admin_wprm_fields' ] );
	}

	/**
	 * Adding custom fields for resumes - Front-End
	 *
	 * @since   1.4.5
	 * @version 1.8.5
	 *
	 * @param array $fields
	 */
	public function frontend_wprm_fields( $fields ) {
		if ( get_option( 'cariera_resume_manager_enable_rate' ) ) {
			$fields['resume_fields']['candidate_rate'] = [
				'label'       => esc_html__( 'Rate per Hour', 'cariera-core' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => esc_html__( 'e.g. 20', 'cariera-core' ),
				'priority'    => 9,
			];
		}

		if ( get_option( 'cariera_resume_manager_enable_education' ) ) {
			$fields['resume_fields']['candidate_education_level'] = [
				'label'       => esc_html__( 'Education Level', 'cariera-core' ),
				'type'        => 'term-select',
				'taxonomy'    => 'resume_education_level',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Choose your education level', 'cariera-core' ),
				'priority'    => 9,
			];
		}

		if ( get_option( 'cariera_resume_manager_enable_experience' ) ) {
			$fields['resume_fields']['candidate_experience_years'] = [
				'label'       => esc_html__( 'Experience', 'cariera-core' ),
				'type'        => 'term-select',
				'taxonomy'    => 'resume_experience',
				'required'    => false,
				'default'     => '',
				'placeholder' => esc_html__( 'Choose your experience', 'cariera-core' ),
				'priority'    => 9,
			];
		}

		$fields['resume_fields']['candidate_languages'] = [
			'label'       => esc_html__( 'Languages', 'cariera-core' ),
			'type'        => 'text',
			'required'    => false,
			'placeholder' => esc_html__( 'English, German, Chinese', 'cariera-core' ),
			'priority'    => 9,
		];

		$fields['resume_fields']['candidate_facebook'] = [
			'label'       => esc_html__( 'Facebook', 'cariera-core' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your Facebook page link', 'cariera-core' ),
			'priority'    => 9.4,
			'required'    => false,
		];

		$fields['resume_fields']['candidate_twitter'] = [
			'label'       => esc_html__( 'Twitter', 'cariera-core' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your Twitter page link', 'cariera-core' ),
			'priority'    => 9.5,
			'required'    => false,
		];

		$fields['resume_fields']['candidate_linkedin'] = [
			'label'       => esc_html__( 'LinkedIn', 'cariera-core' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your LinkedIn page link', 'cariera-core' ),
			'priority'    => 9.7,
			'required'    => false,
		];

		$fields['resume_fields']['candidate_instagram'] = [
			'label'       => esc_html__( 'Instagram', 'cariera-core' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your Instagram page link', 'cariera-core' ),
			'priority'    => 9.8,
			'required'    => false,
		];

		$fields['resume_fields']['candidate_youtube'] = [
			'label'       => esc_html__( 'Youtube', 'cariera-core' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Your Youtube page link', 'cariera-core' ),
			'priority'    => 9.9,
			'required'    => false,
		];

		if ( get_option( 'cariera_resume_manager_enable_portfolio' ) ) {
			$fields['resume_fields']['candidate_portfolio'] = [
				'label'              => esc_html__( 'Gallery', 'cariera-core' ),
				'type'               => 'file',
				'required'           => false,
				'description'        => esc_html__( 'Add images that will be shown in the Candidate\'s portfolio.', 'cariera-core' ),
				'priority'           => 9.9,
				'ajax'               => true,
				'multiple'           => true,
				'allowed_mime_types' => [
					'jpg'  => 'image/jpeg',
					'jpeg' => 'image/jpeg',
					'gif'  => 'image/gif',
					'png'  => 'image/png',
				],
			];
		}

		$fields['resume_fields']['candidate_photo']['priority'] = '15';

		$fields['resume_fields']['candidate_featured_image'] = [
			'label'              => esc_html__( 'Cover Image', 'cariera-core' ),
			'type'               => 'file',
			'required'           => false,
			'description'        => esc_html__( 'The cover image size should be max 1920x400px', 'cariera-core' ),
			'priority'           => 15,
			'ajax'               => true,
			'multiple'           => false,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
		];

		return $fields;
	}

	/**
	 * Update frontend fields.
	 *
	 * @since   1.3.0
	 * @version 1.8.2
	 *
	 * @param int   $resume_id
	 * @param array $values
	 */
	public function wprm_update_data( $resume_id, $values ) {
		if ( isset( $values['resume_fields']['candidate_rate'] ) ) {
			update_post_meta( $resume_id, '_rate', $values['resume_fields']['candidate_rate'] );
		}
		if ( isset( $values['resume_fields']['candidate_languages'] ) ) {
			update_post_meta( $resume_id, '_languages', $values['resume_fields']['candidate_languages'] );
		}
		if ( isset( $values['resume_fields']['candidate_featured_image'] ) ) {
			update_post_meta( $resume_id, '_featured_image', $values['resume_fields']['candidate_featured_image'] );
		}
		if ( isset( $values['resume_fields']['candidate_facebook'] ) ) {
			update_post_meta( $resume_id, '_facebook', $values['resume_fields']['candidate_facebook'] );
		}
		if ( isset( $values['resume_fields']['candidate_twitter'] ) ) {
			update_post_meta( $resume_id, '_twitter', $values['resume_fields']['candidate_twitter'] );
		}
		if ( isset( $values['resume_fields']['candidate_linkedin'] ) ) {
			update_post_meta( $resume_id, '_linkedin', $values['resume_fields']['candidate_linkedin'] );
		}
		if ( isset( $values['resume_fields']['candidate_instagram'] ) ) {
			update_post_meta( $resume_id, '_instagram', $values['resume_fields']['candidate_instagram'] );
		}
		if ( isset( $values['resume_fields']['candidate_youtube'] ) ) {
			update_post_meta( $resume_id, '_youtube', $values['resume_fields']['candidate_youtube'] );
		}
		if ( isset( $values['resume_fields']['candidate_portfolio'] ) ) {
			update_post_meta( $resume_id, '_candidate_portfolio', $values['resume_fields']['candidate_portfolio'] );
		}
	}

	/**
	 * Adding custom fields for resumes - Back-End
	 *
	 * @since   1.0.0
	 * @version 1.8.2
	 *
	 * @param array $fields
	 */
	public function admin_wprm_fields( $fields ) {
		$fields['_rate'] = [
			'label'        => esc_html__( 'Rate per Hour', 'cariera-core' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'e.g. 20', 'cariera-core' ),
			'description'  => '',
			'show_in_rest' => true,
		];

		$fields['_languages'] = [
			'label'        => esc_html__( 'Languages', 'cariera-core' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'English, German, Chinese', 'cariera-core' ),
			'description'  => '',
			'show_in_rest' => true,
		];

		$fields['_facebook'] = [
			'label'        => esc_html__( 'Facebook', 'cariera-core' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your Facebook page link', 'cariera-core' ),
			'show_in_rest' => true,
		];

		$fields['_twitter'] = [
			'label'        => esc_html__( 'Twitter', 'cariera-core' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your Twitter page link', 'cariera-core' ),
			'show_in_rest' => true,
		];

		$fields['_linkedin'] = [
			'label'        => esc_html__( 'LinkedIn', 'cariera-core' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your LinkedIn page link', 'cariera-core' ),
			'show_in_rest' => true,
		];

		$fields['_instagram'] = [
			'label'        => esc_html__( 'Instagram', 'cariera-core' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your Instagram page link', 'cariera-core' ),
			'show_in_rest' => true,
		];

		$fields['_youtube'] = [
			'label'        => esc_html__( 'Youtube', 'cariera-core' ),
			'type'         => 'text',
			'placeholder'  => esc_html__( 'Your Youtube page link', 'cariera-core' ),
			'show_in_rest' => true,
		];

		$fields['_featured_image'] = [
			'label'              => esc_html__( 'Resume Cover Image', 'cariera-core' ),
			'type'               => 'file',
			'description'        => '',
			'multiple'           => false,
			'allowed_mime_types' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
			],
			'show_in_rest'       => true,
		];

		if ( get_option( 'cariera_resume_manager_enable_portfolio' ) ) {
			$fields['_candidate_portfolio'] = [
				'label'              => esc_html__( 'Candidate Portfolio', 'cariera-core' ),
				'type'               => 'file',
				'description'        => '',
				'multiple'           => true,
				'allowed_mime_types' => [
					'jpg'  => 'image/jpeg',
					'jpeg' => 'image/jpeg',
					'gif'  => 'image/gif',
					'png'  => 'image/png',
				],
				'show_in_rest'       => false,
			];
		}

		return $fields;
	}
}
