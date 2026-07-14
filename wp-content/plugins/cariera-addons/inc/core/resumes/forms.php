<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Forms {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'load_posted_form' ] );
	}

	/**
	 * If a form was posted, load its class so that it can be processed before display.
	 *
	 * @since 0.9.5
	 */
	public function load_posted_form() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		$resume_manager_form = ! empty( $_REQUEST['resume_manager_form'] ) ? sanitize_title( wp_unslash( $_REQUEST['resume_manager_form'] ) ) : false;

		if ( ! empty( $resume_manager_form ) ) {
			$this->load_form_class( sanitize_title( $resume_manager_form ) );
		}
	}

	/**
	 * Load a form's class
	 *
	 * @param  string $form_name
	 * @return string class name on success, false on failure
	 */
	private function load_form_class( $form_name ) {
		if ( ! class_exists( 'WP_Job_Manager_Form' ) ) {
			include JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php';
		}

		// Now try to load the form_name.
		$form_class = '\Cariera_Addons\Core\Resumes\Forms\\' . str_replace( '-', '_', $form_name );
		$form_file  = __DIR__ . '/forms/' . $form_name . '.php';

		if ( class_exists( $form_class ) ) {
			return call_user_func( [ $form_class, 'instance' ] );
		}

		if ( ! file_exists( $form_file ) ) {
			return false;
		}

		if ( ! class_exists( $form_class ) ) {
			include $form_file;
		}

		// Init the form.
		return call_user_func( [ $form_class, 'instance' ] );
	}

	/**
	 * Get form function.
	 *
	 * @since 0.9.5
	 *
	 * @param string $form_name
	 * @param array  $atts
	 */
	public function get_form( $form_name, $atts = [] ) {
		$form = $this->load_form_class( $form_name );
		if ( $form ) {
			ob_start();
			$form->output( $atts );
			return ob_get_clean();
		}
	}
}
