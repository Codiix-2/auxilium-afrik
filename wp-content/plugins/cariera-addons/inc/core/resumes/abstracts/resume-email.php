<?php

namespace Cariera_Addons\Core\Resumes\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Resume_Email extends \WP_Job_Manager_Email_Template {

	/**
	 * Get the template path.
	 *
	 * @since 0.9.5
	 */
	public static function get_template_path() {
		return 'cariera-addons';
	}

	/**
	 * Get the default template path where WP Job Manager should look for the templates.
	 *
	 * @since 0.9.5
	 */
	public static function get_template_default_path() {
		return CARIERA_ADDONS_PATH . '/templates/resumes/';
	}

	/**
	 * Get the context for where this email notification is used.
	 *
	 * @since 0.9.5
	 */
	public static function get_context() {
		return 'resume_manager';
	}

	/**
	 * Expand arguments as necessary for the generation of the email.
	 *
	 * @since 0.9.5
	 *
	 * @param array $args Arguments used in generation of email.
	 */
	protected function prepare_args( $args ) {
		// Fill in the job details.
		$args = parent::prepare_args( $args );

		// Default object is resume so we want the `author` argument to be just for that.
		if ( isset( $args['author'] ) ) {
			$args['job_author'] = $args['author'];
			unset( $args['author'] );
		}

		if ( isset( $args['resume_id'] ) ) {
			$resume = get_post( $args['resume_id'] );
			if ( $resume instanceof \WP_Post ) {
				$args['resume'] = $resume;
			}
		}

		if ( isset( $args['resume'] ) && $args['resume'] instanceof \WP_Post ) {
			$author = get_user_by( 'ID', $args['resume']->post_author );
			if ( $author instanceof \WP_User ) {
				$args['author'] = $author;
			}
		}

		return $args;
	}
}
