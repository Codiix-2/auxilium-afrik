<?php

namespace Cariera_Addons\Core\Resumes\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Edit_Resume extends Submit_Resume {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Form name slug.
	 *
	 * @var string
	 */
	public $form_name = 'edit-resume';

	/**
	 * Messaged shown on save.
	 *
	 * @var bool|string
	 */
	private $save_message = false;

	/**
	 * Message shown on error.
	 *
	 * @var bool|string
	 */
	private $save_error = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'submit_handler' ] );
		add_action( 'submit_resume_form_start', [ $this, 'output_submit_form_nonce_field' ] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		$this->resume_id = ! empty( $_REQUEST['resume_id'] ) ? absint( $_REQUEST['resume_id'] ) : 0;

		if ( ! resume_manager_user_can_edit_resume( $this->resume_id ) ) {
			$this->resume_id = 0;
		}

		if ( ! empty( $this->resume_id ) ) {
			if ( ! \Cariera_Addons\Core\Resumes\Post_Types::resume_is_editable( $this->resume_id ) ) {
				$this->resume_id = 0;
			}
		}
	}

	/**
	 * Output the edit resume form.
	 *
	 * @since 0.9.5
	 *
	 * @param array $atts Attributes passed (ignored).
	 */
	public function output( $atts = [] ) {
		if ( ! empty( $this->save_message ) ) {
			echo '<div class="job-manager-message">' . wp_kses_post( $this->save_message ) . '</div>';
			return;
		}
		if ( ! empty( $this->save_error ) ) {
			echo '<div class="job-manager-error">' . wp_kses_post( $this->save_error ) . '</div>';
		}

		$this->submit();
	}

	/**
	 * Submit step.
	 *
	 * @since   0.9.5
	 * @version 0.9.10
	 */
	public function submit() {
		$resume = get_post( $this->resume_id );

		if ( empty( $this->resume_id ) ) {
			echo wp_kses_post( wpautop( __( 'Invalid resume', 'cariera-addons' ) ) );
			return;
		}

		$this->init_fields();

		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				if ( ! isset( $this->fields[ $group_key ][ $key ]['value'] ) ) {
					if ( 'candidate_name' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = $resume->post_title;

					} elseif ( 'resume_content' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = $resume->post_content;

					} elseif ( ! empty( $field['taxonomy'] ) ) {
						$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $resume->ID, $field['taxonomy'], [ 'fields' => 'ids' ] );

					} elseif ( 'resume_skills' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = implode( ', ', wp_get_object_terms( $resume->ID, \Cariera_Addons\Core\Resumes\Post_Types::TAX_SKILL, [ 'fields' => 'names' ] ) );

					} else {
						$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $resume->ID, '_' . $key, true );
					}
				}
			}
		}

		$this->fields = apply_filters( 'submit_resume_form_fields_get_resume_data', $this->fields, $resume );

		$save_button_text   = esc_html__( 'Save changes', 'cariera-addons' );
		$published_statuses = [ 'publish', 'hidden' ];
		if (
			in_array( get_post_status( $this->resume_id ), $published_statuses, true )
			&& resume_manager_published_submission_edits_require_moderation()
		) {
			$save_button_text = esc_html__( 'Submit changes for approval', 'cariera-addons' );
		}

		/**
		 * Change button text for submitting changes to a resume.
		 */
		$save_button_text = apply_filters( 'cariera_addons_update_resume_form_submit_button_text', $save_button_text, $this->resume_id );

		get_job_manager_template(
			'resumes/resume-submit.php',
			[
				'class'              => $this,
				'form'               => $this->form_name,
				'job_id'             => '',
				'resume_id'          => $this->get_resume_id(),
				'action'             => $this->get_action(),
				'resume_fields'      => $this->get_fields( 'resume_fields' ),
				'step'               => $this->get_step(),
				'submit_button_text' => $save_button_text,
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}

	/**
	 * Submit Step is posted.
	 *
	 * @since   0.9.5
	 * @version 1.0.2
	 *
	 * @throws \Exception When invalid fields are submitted.
	 */
	public function submit_handler() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Check happens later when possible.
		if ( empty( $_POST['submit_resume'] ) ) {
			return;
		}

		$this->check_submit_form_nonce_field();

		try {
			// Init fields.
			$this->init_fields();

			// Get posted values.
			$values = $this->get_posted_fields();

			// Validate required.
			$validation_result = $this->validate_fields( $values );
			if ( is_wp_error( $validation_result ) ) {
				throw new \Exception( $validation_result->get_error_message() );
			}

			$original_post_status = get_post_status( $this->resume_id );
			$save_post_status     = $original_post_status;
			if ( resume_manager_published_submission_edits_require_moderation() ) {
				$save_post_status = 'pending';
			}

			// Update the resume.
			$this->save_resume( $values['resume_fields']['candidate_name'], $values['resume_fields']['resume_content'], $save_post_status, $values );
			$this->update_resume_data( $values );

			// Successful.
			$save_message = esc_html__( 'Your changes have been saved.', 'cariera-addons' );
			$post_status  = get_post_status( $this->resume_id );
			update_post_meta( $this->resume_id, '_resume_edited', time() );
			update_post_meta( $this->resume_id, '_resume_edited_original_status', $original_post_status );

			$published_statuses = [ 'publish', 'hidden' ];
			if ( 'publish' === $post_status ) {
				$save_message = $save_message . ' <a href="' . get_permalink( $this->resume_id ) . '">' . __( 'View &rarr;', 'cariera-addons' ) . '</a>';
			} elseif ( in_array( $original_post_status, $published_statuses, true ) && 'pending' === $post_status ) {
				$save_message = esc_html__( 'Your changes have been submitted and your resume will be available again once approved.', 'cariera-addons' );

				/**
				 * Resets the resume expiration date when a user submits their resume listing edit for re-approval.
				 * Defaults to `false`.
				 */
				if ( apply_filters( 'resume_manager_reset_listing_expiration_on_user_edit', false ) ) {
					delete_post_meta( $this->resume_id, '_resume_expires' );
				}
			}

			/**
			 * Fire action after the user edits a resume.
			 */
			do_action( 'resume_manager_user_edit_resume', $this->resume_id, $save_message, $values );

			/**
			 * Change the message that appears when a user edits a resume.
			 */
			$this->save_message = apply_filters( 'resume_manager_update_resume_listings_message', $save_message, $this->resume_id, $values );

			\WP_Job_Manager\UI\Redirect_Message::redirect( remove_query_arg( [ 'action', 'resume_id', '_wpnonce' ] ), $this->save_message, 'resume_updated' );
		} catch ( \Exception $e ) {
			$this->save_error = $e->getMessage();
		}
	}
}
