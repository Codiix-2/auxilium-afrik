<?php

namespace WPJMSF\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Specialty
 *
 * @package WPJMSF\Themes
 */
class Specialty {

	/**
	 * @var \WPJMSF\Themes
	 */
	public $themes;

	/**
	 * @var bool Child theme required for this theme
	 */
	public $child_theme_required = true;

	/**
	 * Specialty constructor.
	 *
	 * @param $themes \WPJMSF\Themes
	 */
	public function __construct( $themes ) {
		$this->themes = $themes;
	}

	/**
	 * Theme Setup
	 *
	 * @since 1.1.3
	 *
	 */
	public function theme_setup() {
		global $wp_filesystem;

		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();

		$files = $this->get_files_to_copy();

		$success = true;

		foreach( $files as $theme_file => $sf_file ){
			$copied = $wp_filesystem && method_exists( $wp_filesystem, 'copy' ) ? $wp_filesystem->copy( $sf_file, $theme_file ) : false;
			if( ! $copied ){
				$copied = copy( $sf_file, $theme_file );
			}

			if( ! $copied ){
				$success = false;
				break;
			}
		}

		if( ! $success ){
			throw new \Exception( __( 'Unable to copy files, please try again and if you continue to receive this error, copy the files using manual method.', 'wp-job-manager-search-and-filtering' ) );
		}

		return true;
	}

	/**
	 * Get Files to Copy
	 *
	 * Format will be 'theme/full/path' => 'plugin/full/path'
	 *
	 * @return string[]
	 * @since 1.1.3
	 *
	 */
	public function get_files_to_copy() {

		$part_job_filters = 'part-job-filters.php';
		$template_listing_jobs = 'template-listing-jobs.php';

		$child_theme_directory = get_stylesheet_directory();
		$theme_part_job_filters = trailingslashit( $child_theme_directory ) . $part_job_filters;
		$theme_template_listing_jobs = trailingslashit( $child_theme_directory ) . $template_listing_jobs;

		$sf_part_job_filters = WPJM_SEARCH_FILTERING_PATH . '/templates/specialty/' . $part_job_filters;
		$sf_template_listing_jobs = WPJM_SEARCH_FILTERING_PATH . '/templates/specialty/' . $template_listing_jobs;

		$files_to_copy = array(
			$theme_part_job_filters => $sf_part_job_filters,
			$theme_template_listing_jobs => $sf_template_listing_jobs
		);

		return $files_to_copy;
	}

	/**
	 * Check if Files Already Exist
	 *
	 * @return bool
	 * @since 1.1.3
	 *
	 */
	public function files_already_exist() {
		if( ! is_child_theme() ){
			return false;
		}

		$exist = true;

		$files = $this->get_files_to_copy();
		$theme_files = array_keys( $files );

		foreach( $theme_files as $theme_file ){
			if( ! file_exists( $theme_file ) ){
				$exist = false;
				break;
			}
		}

		return $exist;
	}

	/**
	 * Setup Parameters
	 *
	 * @since 1.1.3
	 *
	 */
	public function theme_setup_params() {

		if( $this->files_already_exist() ){
			return array();
		}

		$files_to_copy = array();
		foreach( $this->get_files_to_copy() as $theme_file => $sf_file ){
			$files_to_copy[] = array(
				'from' => str_replace( ABSPATH, '', $sf_file ),
				'to' => str_replace( ABSPATH, '', $theme_file ),
			);
		}

		return array(
			'description' => __( 'In order for this plugin to work correctly with your theme, we must copy a template file to your child theme. The template file was created so even if you decide to disable this plugin, your theme will still work correctly.  This template allows this plugin to inject the filters on the homepage as the theme not does natively support this.', 'wp-job-manager-search-and-filtering' ),
			'header' => __( 'Copy Theme Files', 'wp-job-manager-search-and-filtering' ),
			'copy_files' => $files_to_copy,
			'run_label' => __( 'Run Theme Setup / Copy Files', 'wp-job-manager-search-and-filtering' )
		);
	}
}
