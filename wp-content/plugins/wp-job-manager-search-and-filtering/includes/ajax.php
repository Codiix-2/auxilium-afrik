<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ajax
 *
 * @package WPJMSF
 */
class Ajax {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * @var string
	 */
	protected $nonce = 'wpjmsf_frontend_nonce';

	/**
	 * Ajax constructor.
	 *
	 * @param $type
	 */
	public function __construct( $type ) {
		$this->type = $type;

		add_action( "wp_ajax_wpjmsf_save_{$type->slug}_section", array( $this, 'save_section' ) );
		add_action( "wp_ajax_wpjmsf_get_{$type->slug}_section", array( $this, 'get_section' ) );

		add_action( "wp_ajax_wpjmsf_get_{$type->slug}_min_max_values", array( $this, 'min_max_values' ) );
		add_action( "wp_ajax_nopriv_wpjmsf_get_{$type->slug}_min_max_values", array( $this, 'min_max_values' ) );

		add_action( "wp_ajax_wpjmsf_get_{$type->slug}_tag_cloud", array( $this, 'tag_cloud' ) );
		add_action( "wp_ajax_nopriv_wpjmsf_get_{$type->slug}_tag_cloud", array( $this, 'tag_cloud' ) );

//		add_action( "wp_ajax_nopriv_wpjmsf_get_{$type->slug}_section", array( $this, 'get_section_visitor' ) );

		add_action( "wp_ajax_wpjmsf_{$type->slug}_enable_disable", array( $this, 'enable_disable' ) );

		add_filter( 'allowed_http_origins', array( $this, 'add_allowed_origins' ) );
	}

	/**
	 * Get Tag Cloud Output
	 *
	 * @since 1.0.0
	 *
	 */
	public function tag_cloud() {
		$this->check_permission( true );

		if( isset( $_POST['type_config']['separator'] ) ){
			$_POST['type_config']['separator'] = stripslashes( html_entity_decode( $_POST['type_config']['separator'] ) );
		}

		$sanitizer = new Sanitizer();
		$sanitizer->set_html_array_keys( array( 'separator' ) );
		$config = isset( $_POST['type_config'] )  ? $sanitizer->sanitize( $_POST['type_config'] ) : array();

		$taxonomy = sanitize_text_field( $_POST['taxonomy'] );

		$tc = new TagCloud( $this->type, $taxonomy );
		$tc->set_config( $config );

		wp_send_json_success( array( 'html' => $tc->get_html() ) );
	}

	/**
	 * Get Min/Max Values for Range Fields
	 *
	 * @since 1.0.0
	 *
	 */
	public function min_max_values() {
		global $wpdb;
		$this->check_permission( true );

		$post_type = $this->type->core_post_type;
		$min_meta_key = sanitize_text_field( $_POST['min_meta_key'] );
		$max_meta_key = sanitize_text_field( $_POST['max_meta_key'] );

		$min_meta_key = $this->type->search->meta->get_meta_query_meta_key( "_{$min_meta_key}" );
		$max_meta_key = $this->type->search->meta->get_meta_query_meta_key( "_{$max_meta_key}" );

		$min = ! empty( $min_meta_key ) ? floor( $wpdb->get_var( "
	            SELECT min(meta_value + 0)
	            FROM $wpdb->posts AS p
	        	LEFT JOIN $wpdb->postmeta AS m ON (p.ID = m.post_id)
	            WHERE meta_key IN ('{$min_meta_key}')
	            AND meta_value != ''  AND post_status = 'publish' AND post_type = '{$post_type}'
	       " ) ) : 0;

		$max = ! empty( $max_meta_key ) ? ceil( $wpdb->get_var( "
		    SELECT max(meta_value + 0)
		    FROM $wpdb->posts AS p
        	LEFT JOIN $wpdb->postmeta AS m ON (p.ID = m.post_id)
		    WHERE meta_key IN ('{$max_meta_key}')  AND post_status = 'publish' AND post_type = '{$post_type}'
		" ) ) : 1000;

		wp_send_json_success( array( 'min' => $min, 'max' => $max ));
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
		if( ! $no_priv ){
			if( ! current_user_can( $this->type->get_capability() ) ){
				wp_die( 'You do not have permission to do this.' );
			}
		}
	}

	/**
	 * Enable/Disable a Section
	 *
	 * @since 1.0.0
	 *
	 */
	public function enable_disable(){
		$this->check_permission();
		$result = false;
		$section_id = absint( $_POST['section_id'] );

		if( $_POST['action_type'] === 'enable' ){
			$result = $this->type->sections->enable_section( $section_id );
		} elseif ( $_POST['action_type'] === 'disable' ){
			$result = $this->type->sections->disable_section( $section_id );
		}

		if( $result ){
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Death to Heartbeat
	 *
	 * @since 1.0.0
	 *
	 */
	public function death_to_heartbeat() {
		wp_deregister_script( 'heartbeat' );
	}

	/**
	 * Get Section (Unauthenticated User)
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_section_visitor() {
		$this->get_section( true );
	}

	/**
	 * Get Section
	 *
	 * @param boolean $no_priv
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_section( $no_priv = false ){

		$this->check_permission( $no_priv );

		$post_id = absint( $_POST['ID'] );

		if( empty( $post_id ) ){
			wp_send_json_error( __( 'Section ID is missing from request!', 'wp-job-manager-search-and-filtering' ), 500 );
		}

		$section_data = $this->type->sections->get_section( $post_id );

		if ( empty( $section_data ) ) {
			wp_send_json_error( __( 'Unable to find section based on ID!', 'wp-job-manager-search-and-filtering' ), 500 );
		}

		if( is_wp_error( $section_data ) ){
			wp_send_json_error( $section_data, 500 );
		}

		wp_send_json_success( $section_data );
	}

	/**
	 * Remove Section
	 *
	 * @since 1.0.0
	 *
	 */
	public function remove_section() {

		$this->check_permission();

		$post_id = isset( $_POST['ID'] ) ? absint( $_POST['ID'] ) : false;

		if ( empty( $post_id ) ) {
			wp_send_json_error( __( 'Error, section ID was not specified!', 'wp-job-manager-search-and-filtering' ) );
		}

		$result = wp_delete_post( $post_id );
		if ( $result ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Get All Sections
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_sections() {

		$this->check_permission();
		$sections = $this->type->sections->get_sections();
		wp_send_json_success( $sections );

	}

	/**
	 * Get Settings
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_settings() {

		$this->check_permission();
		if ( ! wp_next_scheduled( 'job_manager_check_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'job_manager_check_cleanup' );
		}
		$settings = get_option( $this->type->settings_option, array() );
		wp_send_json_success( $settings );
	}

	/**
	 * Save Settings
	 *
	 * @since 1.0.0
	 *
	 */
	public function save_settings() {

		$this->check_permission();
		$settings = isset( $_REQUEST['wpjmsf_settings'] ) ? $_REQUEST['wpjmsf_settings'] : array();

		foreach ( (array) $settings as $setting => $setting_val ) {
			if( is_array( $setting_val ) ){
				$settings[ $setting ] = array_map( 'sanitize_text_field', $setting_val );
			} else {
				$settings[ $setting ] = sanitize_text_field( $setting_val );
			}
		}

		$result = update_option( $this->type->settings_option, $settings, true );

		wp_send_json_success( __( 'Settings saved successfully', 'wp-job-manager-search-and-filtering' ) );
	}

	/**
	 * Remove Vuex ORM added values from single level array
	 *
	 *
	 * @param       $data
	 * @param array $sub_keys
	 * @param array $remove_vals
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function remove_orm_values_single( $data, $sub_keys = array(), $remove_vals = array() ){

		$remove = ! empty( $remove_vals ) ? $remove_vals : array( '$uid', '$id', 'ID', 'id', 'section_id', 'field_slug', 'section', 'field', 'field_id' );

		foreach ( (array) $remove as $remove_key ) {

			if ( array_key_exists( $remove_key, $data ) ) {
				unset( $data[ $remove_key ] );
			}

		}

		if ( ! empty( $sub_keys ) ) {
			/**
			 * Check if passed value for $sub_keys is standard numerical array
			 * array( 'sub_field', 'sub_field_two' )
			 * OR if array that defines removal vals
			 * array( 'sub_field' => array( 'ID', '$id' ) )
			 */
			$non_numeric_keys  = count( array_filter( array_keys( $sub_keys ), 'is_string' ) ) > 0;
			$sub_keys_to_check = ! $non_numeric_keys ? $sub_keys : array_keys( $sub_keys );

			foreach ( (array) $sub_keys_to_check as $sub_key_to_check ) {
				if ( array_key_exists( $sub_key_to_check, $data ) ) {
					$sub_remove_vals                          = $non_numeric_keys ? $sub_keys[ $sub_key_to_check ] : array();
					$data[ $sub_key_to_check ] = $this->remove_orm_values_single( $data[ $sub_key_to_check ], false, $sub_remove_vals );
				}
			}
		}

		return $data;
	}

	/**
	 * Remove Vuex ORM added values from array of arrays
	 *
	 *
	 * @param       $data
	 * @param array $sub_keys
	 * @param array $remove_vals
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function remove_orm_values( $data, $sub_keys = array(), $remove_vals = array() ){

		foreach( (array) $data as $data_index => $data_values ){
			$data[ $data_index ] = $this->remove_orm_values_single( $data[ $data_index ], $sub_keys, $remove_vals );
		}

		return $data;
	}

	/**
	 * Sanitize $_POST Variable
	 *
	 * @param string   $key
	 * @param boolean  $not_set_val     Value to return if $key does not exist in $_POST
	 * @param string   $sanitizer       Sanitizer function to use 'sanitize_text_field' by default
	 *
	 * @return array|false|mixed
	 * @since 1.0.0
	 *
	 */
	public function sanitize_POST( $key, $not_set_val = false, $sanitizer = 'sanitize_text_field' ) {

		if ( ! isset( $_POST[ $key ] ) ) {
			return $not_set_val;
		}

		$raw_value = $_POST[ $key ];

		return wpjmsf_sanitize_value( $raw_value, $sanitizer );
	}

	/**
	 * Sanitize RAW Fields from $_POST array
	 *
	 * @param        $key
	 * @param false  $not_set_val
	 * @param string $sanitizer
	 *
	 * @return array|false|mixed
	 * @since 1.0.0
	 *
	 */
	public function sanitize_FIELDS( $key, $not_set_val = false, $sanitizer = 'sanitize_text_field' ){

		if( ! isset( $_POST[ $key ] ) ){
			return $not_set_val;
		}

		$raw_fields = $_POST[ $key ];

		$html_allowed_fields = apply_filters( 'search_and_filtering_sanitize_fields_html_allowed_fields', array( 'html', 'caption', 'label', 'separator' ), $this );

		foreach( (array) $raw_fields as $raw_field_index => $raw_field_config ){

			foreach( (array) $html_allowed_fields as $html_allowed_field ){

				if ( array_key_exists( $html_allowed_field, $raw_field_config ) && ! empty( $raw_field_config[$html_allowed_field] ) ) {

					$raw_fields[ $raw_field_index ][$html_allowed_field] = htmlentities( $raw_field_config[$html_allowed_field] );

				} elseif( isset( $raw_field_config['type_config'] ) && array_key_exists( $html_allowed_field, $raw_field_config['type_config'] ) && ! empty( $raw_field_config['type_config'][ $html_allowed_field ] ) ){

					$raw_fields[ $raw_field_index ]['type_config'][ $html_allowed_field ] = htmlentities( $raw_field_config['type_config'][ $html_allowed_field ] );

				}

			}

		}

		return wpjmsf_sanitize_value( $raw_fields, $sanitizer );
	}

	/**
	 * Save Section (from Frontend)
	 *
	 * @since 1.0.0
	 *
	 */
	public function save_section() {
		$this->check_permission();

		$post_id = $this->sanitize_POST( 'ID', false, 'absint' );
		$is_new  = empty( $post_id );

		$section_label = $this->sanitize_POST( 'label', '' );
		$breakpoints = $this->sanitize_POST( 'breakpoints', '' );
		$section_wrapper_classes = $this->sanitize_POST( 'wrapper_classes', '' );
		$section_spacing = $this->sanitize_POST( 'spacing', array() );

		$grids        = $this->sanitize_POST( 'grids', array() );
		$fields       = $this->sanitize_FIELDS( 'fields', array() );
		$section_styles = $this->sanitize_POST( 'styles', array() );
		$section_logic = $this->sanitize_POST( 'logic', array() );

		$fields = $this->remove_orm_values( $fields, array( 'styles', 'spacing' ) );
		$grids = $this->remove_orm_values( $grids );
		$section_spacing = $this->remove_orm_values_single( $section_spacing );
		$section_styles = $this->remove_orm_values_single( $section_styles );

		if( isset( $section_styles['custom'] ) ){
			$section_styles['custom'] = stripslashes( $section_styles['custom'] );
		}

		$disable_form_on_listing_page = isset( $_POST['disable_form_on_listings_page'] ) ? absint( $_POST['disable_form_on_listings_page'] ) : 0;
		$is_form = isset( $_POST['is_form'] ) ? absint( $_POST['is_form'] ) : 0;

		/**
		 * When saving from the frontend, if the disable_form_on_listings_page is set to 1, we need to force set is_form to 1
		 * otherwise it will be saved as 0 (disabled) since we set this value to 0 when on the "Listings Page" to allow normal functionality in config of the fields/section.
		 */
		if( $disable_form_on_listing_page > 0 && $is_form < 1 ){
			$is_form = 1;
		}

		$post_data = array(
			'post_title'  => $section_label,
			'post_status' => 'publish',
			'post_type'   => $this->type->post_type,
			'meta_input'  => array(
				'is_form'            => $is_form,
				'disable_form_on_listings_page' => $disable_form_on_listing_page,
				'enable_mobile_mode' => isset( $_POST['enable_mobile_mode'] ) ? absint( $_POST['enable_mobile_mode'] ) : 0,
				'spacing'            => $section_spacing,
				'grids'              => $grids,
				'fields'             => $fields,
				'styles'             => $section_styles,
				'output'             => isset( $_POST['output'] ) ? sanitize_text_field( $_POST['output'] ) : '',
				'priority'           => isset( $_POST['priority'] ) ? floatval( $_POST['priority'] ) : 10,
				'show_label'         => isset( $_POST['show_label'] ) ? absint( $_POST['show_label'] ) : 0,
				'checkbox'           => isset( $_POST['checkbox'] ) ? sanitize_text_field( $_POST['checkbox'] ) : 'hide',
				'logic'              => $section_logic,
				'wrapper_classes'    => $section_wrapper_classes
			)
		);

		if( ! empty( $breakpoints ) ){
			$post_data['meta_input']['breakpoints'] = $breakpoints;
		}

		/**
		 * If post id is passed, make sure it's actually this post type, otherwise
		 * set to false to force creating a new insert (we don't want to update another post type)
		 */
		if( ! empty( $post_id ) && get_post_type( $post_id ) !== $this->type->post_type ){
			$post_id = false;
		}

		if ( ! $post_id ) {
			$post_id = wp_insert_post( $post_data );
		} else {
			$post_data['ID'] = $post_id;
			$post_data       = wp_slash( $post_data );
			$post_id         = wp_update_post( $post_data );
		}

		if ( is_wp_error( $post_id ) ) {
			$err_msg = $is_new ? __( 'Error creating and saving new section!', 'wp-job-manager-search-and-filtering' ) : __( 'Error saving, post ID is not defined!', 'wp-job-manager-search-and-filtering' );
			$err_msg .= " " . $post_id->get_error_message();
			wp_send_json_error( $err_msg, 500 );
		}

		if ( ! $post_id ) {
			wp_send_json_error( $is_new ? __( 'Error creating and saving new section!', 'wp-job-manager-search-and-filtering' ) : __( 'Error saving, post ID is not defined!', 'wp-job-manager-search-and-filtering' ), 500 );
		}

		$msg = $is_new ? __( 'Successfully created and saved new section!', 'wp-job-manager-search-and-filtering' ) : __( 'Succesfully updated and saved section!', 'wp-job-manager-search-and-filtering' );
		wp_send_json_success( $msg );
	}

	/**
	 * Add Localhost Origins for CORS issues when using WebPack Dev server
	 *
	 * @param $origin
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function add_allowed_origins( $origin ) {

		$origin[] = 'http://localhost:3000';
		$origin[] = 'http://localhost';

		// Access-Control headers are received during OPTIONS requests
		if ( $origin && 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {

			if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ) ) {
				@header( 'Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] );
			}

		}

		return $origin;
	}
}