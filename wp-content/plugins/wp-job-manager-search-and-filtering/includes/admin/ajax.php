<?php

namespace WPJMSF\Admin;

use WPJMSF\AJAX as AJAXBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AJAX
 *
 * @package WPJMSF\Admin
 */
class AJAX extends AJAXBase {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var string
	 */
	protected $nonce = 'wpjmsf_admin_nonce';

	/**
	 * AJAX constructor.
	 *
	 * @param \WPJMSF\Job|\WPJMSF\Resume $type
	 */
	public function __construct( $type ) {

		$this->type = $type;

		add_action( "wp_ajax_wpjmsf_save_{$type->slug}_section_admin", array( $this, 'save_section_admin' ) );
		add_action( "wp_ajax_wpjmsf_remove_{$type->slug}_section_admin", array( $this, 'remove_section' ) );

		add_action( "wp_ajax_wpjmsf_{$type->slug}_bulk_action", array( $this, 'bulk_action' ) );

		add_action( "wp_ajax_wpjmsf_import_{$type->slug}_section_admin", array( $this, 'save_section' ) );

		add_action( "wp_ajax_wpjmsf_get_{$type->slug}_sections_admin", array( $this, 'get_sections' ) );

		add_action( "wp_ajax_wpjmsf_get_{$type->slug}_section_admin", array( $this, 'get_section' ) );

		add_action( "wp_ajax_wpjmsf_{$type->slug}_enable_disable_admin", array( $this, 'enable_disable' ) );

		add_action( "wp_ajax_wpjmsf_get_{$type->slug}_settings_admin", array( $this, 'get_settings' ) );
		add_action( "wp_ajax_wpjmsf_save_{$type->slug}_settings_admin", array( $this, 'save_settings' ) );
	}

	/**
	 * Handle Bulk Actions
	 *
	 * @since 1.0.0
	 *
	 */
	public function bulk_action() {
		$this->check_permission();
		$action = sanitize_text_field( $_POST['bulk_action'] );
		$raw_post_ids = $_POST['post_ids'];

		$deleted = 0;

		switch( $action ){

			case 'trash':
				foreach( (array) $raw_post_ids as $raw_post_id ){
					$post_id = absint( $raw_post_id );
					if( wp_delete_post( $post_id ) ){
						$deleted++;
					}
				}

				/* translators; %s: number of removed sections */
				wp_send_json_success( sprintf( __( 'Successfully Removed %s Sections', 'wp-job-manager-search-and-filtering'), $deleted ) );

				break;
		}

		wp_send_json_success();
	}

	/**
	 * Save Section from Admin Area
	 *
	 * @since 1.0.0
	 *
	 */
	public function save_section_admin() {

		$this->check_permission();
		if ( ! wp_next_scheduled( 'job_manager_check_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'job_manager_check_cleanup' );
		}

		$post_id  = isset( $_POST['ID'] ) ? absint( $_POST['ID'] ) : false;
		$is_clone = isset( $_POST['clone'] ) ? true : false;
		$is_new   = empty( $post_id ) || $is_clone;

		$post_data = array(
			'post_title'  => sanitize_text_field( $_POST['label'] ),
			'post_status' => 'publish',
			'post_type'   => $this->type->post_type,
			'meta_input'  => array(
				'output'   => sanitize_text_field( $_POST['output'] ),
				'is_form'  => absint( $_POST['is_form'] ),
				'disable_form_on_listings_page'  => absint( $_POST['disable_form_on_listings_page'] ),
				'priority' => isset( $_POST['priority'] ) ? floatval( $_POST['priority'] ) : 10
			)
		);

		if ( $is_clone && $post_id ) {
			$section_data = $this->type->sections->get_section( $post_id );
			if ( is_wp_error( $section_data ) ) {
				wp_send_json_error( $section_data );
			}

			if ( ! $section_data ) {
				wp_send_json_error( __( 'Unknown error getting section to clone!', 'wp-job-manager-search-and-filtering' ) );
			}

			unset( $section_data['ID'] );
			$post_data['post_title'] = $section_data['label'] . ' ' . __( 'Clone', 'wp-job-manager-search-and-filtering' );
			unset( $section_data['label'] );
			$post_data['post_status'] = $section_data['post_status'];
			unset( $section_data['post_status'] );

			$post_data['meta_input'] = $section_data;
		}

		if ( isset( $_POST['import'] ) ) {

		}

		if ( ! $post_id || $is_clone ) {
			$post_id = wp_insert_post( $post_data );
		} else {
			$post_data['ID'] = $post_id;
			$post_data       = wp_slash( $post_data );
			$post_id         = wp_update_post( $post_data );
		}

		if ( is_wp_error( $post_id ) ) {
			$err_msg = $is_new ? __( 'Error creating and saving new section!', 'wp-job-manager-search-and-filtering' ) : __( 'Error saving, post ID is not defined!', 'wp-job-manager-search-and-filtering' );
			if ( $is_clone ) {
				$err_msg = __( 'Error cloning section!', 'wp-job-manager-search-and-filtering' );
			}
			$err_msg .= " " . $post_id->get_error_message();
			wp_send_json_error( $err_msg, 500 );
		}

		if ( ! $post_id ) {
			$msg = $is_new ? __( 'Error creating and saving new section!', 'wp-job-manager-search-and-filtering' ) : __( 'Error saving, post ID is not defined!', 'wp-job-manager-search-and-filtering' );
			if ( $is_clone ) {
				$msg = __( 'Error cloning section, no section ID returned on insert!', 'wp-job-manager-search-and-filtering' );
			}
			wp_send_json_error( $msg, 500 );
		}

		wp_send_json_success( $post_id );
	}
}