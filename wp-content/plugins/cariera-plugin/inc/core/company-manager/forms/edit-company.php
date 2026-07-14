<?php

namespace Cariera_Core\Core\Company_Manager\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'submit-company.php';

class Edit_Company extends Submit_Company {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Form name slug.
	 *
	 * @var string
	 */
	public $form_name = 'edit-company';

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
		add_action( 'submit_company_form_start', [ $this, 'output_submit_form_nonce_field' ] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Check happens later when possible.
		$this->company_id = ! empty( $_REQUEST['company_id'] ) ? absint( $_REQUEST['company_id'] ) : 0;

		if ( ! cariera_user_can_edit_company( $this->company_id ) ) {
			$this->company_id = 0;
		}

		if ( ! empty( $this->company_id ) ) {
			if ( ! \Cariera_Core\Core\Company_Manager\CPT::company_is_editable( $this->company_id ) ) {
				$this->company_id = 0;
			}
		}
	}

	/**
	 * Output function.
	 *
	 * @since   1.4.4
	 * @version 1.8.7
	 *
	 * @param array $atts
	 */
	public function output( $atts = [] ) {
		if ( ! empty( $this->save_message ) ) {
			echo '<div class="job-manager-message">' . wp_kses_post( $this->save_message ) . '</div>';
		}
		if ( ! empty( $this->save_error ) ) {
			echo '<div class="job-manager-error">' . wp_kses_post( $this->save_error ) . '</div>';
		}

		$this->submit();
	}

	/**
	 * Submit Step
	 *
	 * @since   1.4.4
	 * @version 1.9.7
	 */
	public function submit() {
		global $post;

		$company = get_post( $this->company_id );

		if ( empty( $this->company_id ) ) {
			echo wp_kses_post( wpautop( esc_html__( 'Invalid Company ID', 'cariera-core' ) ) );
			return;
		}

		$this->init_fields();

		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				if ( ! isset( $this->fields[ $group_key ][ $key ]['value'] ) ) {
					if ( 'company_name' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = $company->post_title;

					} elseif ( 'company_content' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = $company->post_content;

					} elseif ( 'company_logo' === $key ) {
						$this->fields[ $group_key ][ $key ]['value'] = has_post_thumbnail( $company->ID ) ? get_post_thumbnail_id( $company->ID ) : get_post_meta( $company->ID, '_' . $key, true );

					} elseif ( ! empty( $field['taxonomy'] ) ) {
						$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $company->ID, $field['taxonomy'], [ 'fields' => 'ids' ] );

					} else {
						$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $company->ID, '_' . $key, true );
					}
				}
			}
		}

		$this->fields = apply_filters( 'cariera_submit_company_form_fields_get_company_data', $this->fields, $company );

		$save_button_text   = esc_html__( 'Save changes', 'cariera-core' );
		$published_statuses = [ 'publish', 'hidden', 'private' ];
		if (
			in_array( get_post_status( $this->company_id ), $published_statuses, true )
			&& cariera_company_published_submission_edits_require_moderation()
		) {
			$save_button_text = esc_html__( 'Submit changes for approval', 'cariera-core' );
		}

		get_job_manager_template(
			'company-submit.php',
			[
				'class'              => $this,
				'form'               => $this->form_name,
				'job_id'             => '',
				'company_id'         => $this->get_company_id(),
				'action'             => $this->get_action(),
				'company_fields'     => $this->get_fields( 'company_fields' ),
				'step'               => $this->get_step(),
				'submit_button_text' => $save_button_text,
			],
			'wp-job-manager-companies'
		);
	}

	/**
	 * Submit Step is posted
	 *
	 * @since   1.4.4
	 * @version 2.0.0
	 *
	 * @throws \Exception When invalid fields are submitted.
	 */
	public function submit_handler() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Check happens later when possible.
		if ( empty( $_POST['submit_company'] ) ) {
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

			$original_post_status = get_post_status( $this->company_id );
			$save_post_status     = $original_post_status;
			if ( cariera_company_published_submission_edits_require_moderation() ) {
				$save_post_status = 'pending';
			}

			// Update the company.
			$this->save_company( $values['company_fields']['company_name'], $values['company_fields']['company_content'], $save_post_status, $values );
			$this->update_company_data( $values );

			// Successful.
			$save_message = esc_html__( 'Your changes have been saved.', 'cariera-core' );
			$post_status  = get_post_status( $this->company_id );
			update_post_meta( $this->company_id, '_company_edited', time() );
			update_post_meta( $this->company_id, '_company_edited_original_status', $original_post_status );

			$published_statuses = [ 'publish', 'private', 'hidden' ];
			if ( 'publish' === $post_status ) {
				$save_message = $save_message . ' <a href="' . get_permalink( $this->company_id ) . '">' . esc_html__( 'View &rarr;', 'cariera-core' ) . '</a>';
			} elseif ( in_array( $original_post_status, $published_statuses, true ) && 'pending' === $post_status ) {
				$save_message = esc_html__( 'Your changes have been submitted and your company will be available again once approved.', 'cariera-core' );

				/**
				 * Resets the company expiration date when a user submits their company listing edit for re-approval.
				 * Defaults to `false`.
				 */
				if ( apply_filters( 'cariera_company_reset_listing_expiration_on_user_edit', false ) ) {
					delete_post_meta( $this->company_id, '_company_expires' );
				}
			}

			/**
			 * Fire action after the user edits a job listing.
			 *
			 * @since 1.7.0
			 *
			 * @param int    $company_id    Company ID.
			 * @param string $save_message  Save message to filter.
			 * @param array  $values        Submitted values for company listing.
			 */
			do_action( 'cariera_company_manager_user_edit_company', $this->company_id, $save_message, $values );

			/**
			 * Change the message that appears when a user edits a company.
			 *
			 * @since 1.7.0
			 *
			 * @param string $save_message  Save message to filter.
			 * @param int    $company_id    Company ID.
			 * @param array  $values        Submitted values for company.
			 */
			$this->save_message = apply_filters( 'cariera_update_company_listings_message', $save_message, $this->company_id, $values );

			// Add the message and redirect to the candidate dashboard if possible.
			\WP_Job_Manager\UI\Redirect_Message::redirect( remove_query_arg( [ 'action', 'company_id', '_wpnonce' ] ), $this->save_message, 'company_updated' );
		} catch ( \Exception $e ) {
			$this->save_error = $e->getMessage();
		}
	}
}
