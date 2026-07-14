<?php

namespace WPJMSF\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Setup
 *
 * @package WPJMSF\Admin
 */
class Setup {

	/**
	 * @var string
	 */
	protected $nonce = 'wpjmsf_admin_nonce';

	/**
	 * Setup constructor.
	 */
	public function __construct() {
		add_action( "wp_ajax_wpjmsf_get_setup_params", array( $this, 'get_setup_params' ) );
		add_action( "wp_ajax_wpjmsf_do_theme_setup", array( $this, 'do_theme_setup' ) );
	}

	/**
	 * Check Permission to Call AJAX Enpoint
	 *
	 * @param $no_priv
	 *
	 * @since 1.0.0
	 *
	 */
	public function check_permission( $no_priv = false ) {
		check_ajax_referer( $this->nonce, 'nonce' );
		if ( ! $no_priv ) {
			// Must have manage_options permissions to setup plugin
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'You do not have permission to do this.' );
			}
		}
	}

	/**
	 * Do Theme Setup
	 *
	 * @since 1.1.3
	 *
	 */
	public function do_theme_setup() {
		$this->check_permission();

		try {
			$result = WPJMSF()->themes->do_theme_setup();
			wp_send_json_success( $result );
		} catch( \Exception $e ){
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Get Setup Parameters
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_setup_params() {
		$this->check_permission();

		$theme_name = \WPJM_Search_Filtering::get_theme_name( true, false );

		$setup_params = array(
			'theme_name' => $theme_name,
			'job' => array(
				'theme_import_files' => $this->has_theme_import_files( $theme_name, 'job' ),
				'sections' => $this->get_import_files( $theme_name, 'job' )
			),
			'resume' => array(
				'active' => \WPJM_Search_Filtering::resumes_active(),
				'theme_import_files' => $this->has_theme_import_files( $theme_name, 'resume' ),
				'sections' => $this->get_import_files( $theme_name, 'resume' )
			),
			'setup' => WPJMSF()->themes->get_setup(),
			'max_input_vars' => ini_get( 'max_input_vars' )
		);

		wp_send_json_success( $setup_params );
	}

	/**
	 * Has Theme Import Files
	 *
	 * @param string $theme_name
	 * @param string $post_type_slug
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 *
	 */
	public function has_theme_import_files( $theme_name, $post_type_slug = 'job'  ) {
		$theme_import_dir = WPJM_SEARCH_FILTERING_PATH . "/imports/{$theme_name}/{$post_type_slug}/";
		return apply_filters( 'search_and_filtering_admin_setup_has_theme_import_files', is_dir( $theme_import_dir ), $theme_name, $post_type_slug, $this );
	}

	/**
	 * Get Available Import Files
	 *
	 * This method searches in the /imports/ directory for JSON files to import in the Setup process.  There's also a filter
	 * for plugins/themes to add custom JSON files as well to be included.  Each one will be listed on the setup screen for
	 * the user to choose from.
	 *
	 * @param string $theme_name
	 * @param string $post_type_slug
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_import_files( $theme_name, $post_type_slug = 'job' ) {

		$theme_import_dir = WPJM_SEARCH_FILTERING_PATH . "/imports/{$theme_name}/{$post_type_slug}/";
		$default_import_dir = WPJM_SEARCH_FILTERING_PATH . "/imports/default/{$post_type_slug}/";

		$import_files_dir = is_dir( $theme_import_dir ) ? $theme_import_dir : $default_import_dir;

		$import_files = is_dir( $import_files_dir ) ? list_files( $import_files_dir ) : array();
		/**
		 * Allow plugins and themes to add custom JSON files to import
		 */
		$import_files = apply_filters( 'search_and_filtering_admin_setup_import_files', $import_files, $post_type_slug, $theme_name, $this );

		$import_files_data = array();

		foreach( (array) $import_files as $import_file ){
			$import_files_data[] = file_get_contents( $import_file );
		}

		return apply_filters( 'search_and_filtering_admin_setup_import_files_data', $import_files_data, $post_type_slug, $theme_name, $this );
	}
}
