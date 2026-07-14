<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mobile
 *
 * @package WPJMSF
 */
class Mobile {

	/**
	 * @var \WPJM_Search_Filtering
	 */
	public $sf;
	/**
	 * @var string $_GET parameter used to define should be showing the editor (top window)
	 */
	public static $editor_param = 'sf_mobile_editor';
	/**
	 * @var string $_GET parameter used to define that the page is being loaded in iframe
	 */
	public static $editor_frame_param = 'sf_mobile_frame';
	/**
	 * @var bool Whether or not in editor mode
	 */
	public $editor_mode = false;
	/**
	 * @var string Nonce action string
	 */
	private $nonce = 'wpjmsf_mobile_mode_nonce';

	/**
	 * Mobile constructor.
	 *
	 * @param \WPJM_Search_Filtering $sf
	 */
	public function __construct( $sf ) {
		$this->sf = $sf;
		add_action( 'search_and_filtering_section_output_init', array( $this, 'output_init' ), 10, 3 );

		$this->editor_mode = isset( $_GET[ self::$editor_param ] ) && ! empty( $_GET[ self::$editor_param ] );
		add_action( "wp_ajax_wpjmsf_save_mobile_mode_data", array( $this, 'save_data' ) );

		if( wp_doing_ajax() || is_admin() || ! $this->editor_mode ){
			return;
		}

		add_action( 'init', array( $this, 'disable_debug_display' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
		add_filter( 'body_class', array( $this, 'body_class') );
		add_filter( 'search_and_filtering_output_section_frontend_script', array( $this, 'frontend_script' ), 10, 2 );
		add_filter( 'search_and_filtering_output_section_enabled', array( $this, 'section_enabled' ), 10, 3 );
		add_filter( 'show_admin_bar', '__return_false' );
	}

	/**
	 * Disable Debug Output
	 *
	 * This is required to prevent causing the scripts/styles to be output in the body instead of <head> tag
	 *
	 * @since 1.1.26
	 *
	 */
	public function disable_debug_display() {
		@ini_set( 'display_errors', 0 );
		$GLOBALS['wpdb']->hide_errors();
	}

	/**
	 * Add Body Class
	 *
	 * @param $classes
	 *
	 * @since 1.1.26
	 *
	 */
	public function body_class( $classes ) {
		$classes = (array) $classes;
		$classes[] = 'wpjmsf-mobile-editor';
		return array_unique( $classes );
	}

	/**
	 * Save AJAX Data
	 *
	 * @since 1.1.0
	 *
	 */
	public function save_data() {

		check_ajax_referer( $this->nonce, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to do this.' );
		}

		if( isset( $_POST['hide_popup'] ) ){
			update_option( 'wpjmsf_mobile_mode_hide_popup', true, false );
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Enqueue Frontend Style
	 *
	 * This is required so that our style is enqueued in the header, as this is required for how the iframe
	 * is handled.  Without this the entire frontend would not work correctly.
	 *
	 * Mobile mode is already checked in $this->__construct()
	 *
	 * @since 1.1.0
	 *
	 */
	public function enqueue_frontend() {
		wp_enqueue_style( 'wpjm-search-filtering-frontend-edit-mobile' );
		wp_enqueue_style( 'google-fonts-montserrat', '//fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,700;1,200&display=swap' );
	}

	/**
	 * Prevent Section Output
	 *
	 * This method is used to prevent outputting other sections on the page, as only one is required
	 * to output the handling required for entering mobile mode
	 *
	 * @param $enabled
	 * @param $section_id
	 * @param $that
	 *
	 * @return bool
	 * @since 1.1.0
	 *
	 */
	public function section_enabled( $enabled, $section_id, $that ) {
		return $that->type->nonce_output ? false : true;
	}

	/**
	 * Output Initialized
	 *
	 * @param $frontend_script
	 * @param $user_can_edit
	 * @param $that
	 *
	 * @since 1.1.0
	 */
	public function output_init( $frontend_script, $user_can_edit, $that ) {

		if( empty( $user_can_edit ) ){
			return;
		}

		$iframe_url = remove_query_arg( self::$editor_param );
		$iframe_url = add_query_arg( self::$editor_frame_param, 'true', $iframe_url );
		$hide_popup = get_option( 'wpjmsf_mobile_mode_hide_popup', false );

		$values = array(
			'type' => $that->type->slug,
			'iframe_url' => $iframe_url,
			'plugin_dir' => WPJM_SEARCH_FILTERING_PLUGIN_DIR,
			'editor_param' => self::$editor_param,
			'editor_frame_param' => self::$editor_frame_param,
			'devices' => $this->editor_mode ? $this->get_devices() : array(),
			'show_popup' => empty( $hide_popup ),
			'nonce' => wp_create_nonce( $this->nonce )
		);

		wp_localize_script( $frontend_script, "wpjmsf_mobile_edit_mode", $values );
	}

	/**
	 * Frontend Script
	 *
	 * @param $handle
	 * @param $can_edit
	 *
	 * @return mixed|string
	 * @since 1.1.0
	 *
	 */
	public function frontend_script( $handle, $can_edit ) {

		if( $can_edit ){
			wp_enqueue_style( 'wpjm-search-filtering-frontend-edit-mobile' );
		}

		return $can_edit ? 'wpjm-search-filtering-frontend-edit-mobile' : $handle;
	}

	/**
	 * Get Device Resolutions
	 *
	 * @return mixed|void
	 * @since 1.1.0
	 *
	 */
	public function get_devices() {

		$devices = array(
			'mobile' => array(
				'label' => __( 'Mobile', 'wp-job-manager-search-and-filtering' ),
				'devices' => array(
					'mobile_most_common' => array(
						'label'  => __( 'Most Common 13%', 'wp-job-manager-search-and-filtering' ),
						'width'  => 360,
						'height' => 640,
						'known'  => array( 'Galaxy S4/S5/S6/S7', 'LG G3/G4/G5', 'HTC One/Evo 3D', 'Galaxy Note 2/3/4', 'Sony Xperia P' )
					),
					'mobile_most_common_2' => array(
						'label'  => __( 'Most Common 8%', 'wp-job-manager-search-and-filtering' ),
						'width'  => 414,
						'height' => 896
					),
					'iphone_6_7_8_plus' => array(
						'label'  => __( 'iPhone 6/7/8 Plus', 'wp-job-manager-search-and-filtering' ),
						'width'  => 414,
						'height' => 736
					),
					'iphone_6_7_8' => array(
						'label'  => __( 'iPhone 6/7/8', 'wp-job-manager-search-and-filtering' ),
						'width'  => 375,
						'height' => 667
					),
					'iphone_x' => array(
						'label'  => __( 'iPhone X', 'wp-job-manager-search-and-filtering' ),
						'width'  => 375,
						'height' => 812
					),
					'galaxy_s8_s9_s10' => array(
						'label'  => __( 'Galaxy S8/S9/S10 Note 8/9', 'wp-job-manager-search-and-filtering' ),
						'width'  => 360,
						'height' => 740
					),
					'pixel' => array(
						'label'  => __( 'Pixel (varies)', 'wp-job-manager-search-and-filtering' ),
						'width'  => 411,
						'height' => 823
					),
				)
			),
			'tablet' => array(
				'label' => __( 'Tablet', 'wp-job-manager-search-and-filtering' ),
				'devices' => array(
					'tablet_most_common' => array(
						'label'  => __( 'Most Common 46%', 'wp-job-manager-search-and-filtering' ),
						'width'  => 768,
						'height' => 1024,
						'known'  => array( 'iPad Mini/Pro 9.7', 'iPad 1/2/3/4/Air/Air2', 'iPad Mini 2/3/4', 'HTC Nexus 9', '' )
					),
					'tablet_most_common_2' => array(
						'label'  => __( 'Most Common 8%', 'wp-job-manager-search-and-filtering' ),
						'width'  => 800,
						'height' => 1280,
						'known'  => array( 'Nexus 10', 'Kindle Fire HD 8.9', 'Galaxy Tab 2/3 10"', '' )
					),
					'ipad_pro' => array(
						'label'  => __( 'Apple iPad Pro', 'wp-job-manager-search-and-filtering' ),
						'width'  => 1024,
						'height' => 1366
					),
					'surface_duo' => array(
						'label'  => __( 'Surface Duo', 'wp-job-manager-search-and-filtering' ),
						'width'  => 540,
						'height' => 720
					)
				)
			),
			'desktop' => array(
				'label'   => __( 'Desktop/Laptop', 'wp-job-manager-search-and-filtering' ),
				'devices' => array(
					'desktop_most_common'   => array(
						'label'  => __( 'Most Common 22%', 'wp-job-manager-search-and-filtering' ),
						'width'  => 1920,
						'height' => 1080
					),
					'desktop_most_common_2' => array(
						'label'  => __( 'Most Common 21%', 'wp-job-manager-search-and-filtering' ),
						'width'  => 1366,
						'height' => 768
					),
					'desktop_most_common_3' => array(
						'label'  => __( 'Most Common 10%', 'wp-job-manager-search-and-filtering' ),
						'width'  => 1536,
						'height' => 864
					)
				)
			),
		);

		return apply_filters( 'search_and_filtering_get_devices', $devices, $this );
	}
}