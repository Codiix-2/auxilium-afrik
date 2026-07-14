<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Theme
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Theme {
	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var string Slug form of theme specific name
	 */
	public $slug;
	/**
	 * @var \WPJMSF\Themes\Output
	 */
	public $outputs;
	/**
	 * @var \WPJMSF\Themes\Widgets
	 */
	public $widgets;
	/**
	 * @var string
	 */
	public $includes_directory = '';

	/**
	 * Theme constructor.
	 *
	 * @param $type \WPJMSF\Job|\WPJMSF\Resume
	 * @param $includes_directory
	 */
	public function __construct( $type, $slug ) {
		$this->type = $type;
		$this->slug = $slug;

		$this->includes_directory = WPJM_SEARCH_FILTERING_INCLUDES . "/themes/{$slug}/";
		$this->theme_includes();
		$this->construct(); // Extending class constructor call

		if( $this->type->output->is_enabled() ){
			$this->construct_enabled();
			$this->outputs = new \WPJMSF\Themes\Output( $this->type, $this );
		}

		if( method_exists( $this, 'field_type_groups' ) ){
			add_filter( 'search_and_filtering_field_type_groups', array( $this, 'field_type_groups' ) );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 99 );

		add_action( 'wp', array( $this, 'wp_enabled_action' ) );
	}

	/**
	 * Plugins Loaded Hook Enabled
	 *
	 * @since 1.1.9
	 *
	 */
	public function wp_enabled_action() {
		if( $this->type->output->is_enabled() ){
			$this->wp_enabled();
		}
	}

	/**
	 * Assets
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function assets(){
		$custom_css = $this->custom_css();
		if ( ! empty( $custom_css ) ) {
			wp_add_inline_style( 'wpjm-search-filtering-frontend', $custom_css );
			wp_add_inline_style( 'wpjm-search-filtering-frontend-edit', $custom_css );
		}

		$default_theme_config = array(
			'slug' => $this->slug,
			'fields' => $this->fields_config(),
			'sources' => $this->sources_config()
		);

		$theme_config = array_merge( $default_theme_config, $this->theme_config() );
		wp_localize_script( 'wpjm-search-filtering-frontend', 'wpjmsf_theme_config', $theme_config );
		wp_localize_script( 'wpjm-search-filtering-frontend-edit', 'wpjmsf_theme_config', $theme_config );
	}

	/**
	 * Handle/Include any Theme Specific Files
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function theme_includes() {

		/**
		 * Theme specific Widget class handling, load default widget class file,
		 * along with theme specific widget class file.
		 */
		$theme_type_directory    = $this->get_type_directory();
		$theme_type_widgets_file = "{$theme_type_directory}/widgets.php";

		if ( file_exists( $theme_type_directory ) && file_exists( $theme_type_widgets_file ) ) {
			require_once WPJM_SEARCH_FILTERING_INCLUDES . '/themes/widgets.php';
			// Main theme (non type) widgets file (only included if exists)
			$this->require_file( 'widgets.php' );
			require_once $theme_type_widgets_file;
		}

	}

	/**
	 * Call require_once on theme file
	 *
	 *
	 * @param $file
	 *
	 * @since 0.1.1
	 *
	 */
	public function require_file( $file ) {
		if ( file_exists( $this->includes_directory . $file ) ) {
			require_once $this->includes_directory . $file;
		}
	}

	/**
	 * Get Theme Type (Job/Resume) Specific Directory
	 *
	 * Would return something similar to /full/plugin/path/includes/themes/jobify/job/
	 *
	 *
	 * @return string
	 * @since 0.1.1
	 *
	 */
	public function get_type_directory(){
		return "{$this->includes_directory}{$this->type->slug}/";
	}

	/**
	 * Get Theme Specific Output Locations
	 *
	 *
	 * @param bool $keep_hooks
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 */
	public function get_output_locations( $keep_hooks = false ) {
		$widget_locations = $this->widgets ? $this->widgets->get_locations_from_widgets( $keep_hooks ) : array();
		$theme_locations = $this->output_locations();

		if ( ! $keep_hooks ) {
			foreach( (array) $theme_locations as $theme_loc_key => $theme_location ){
				foreach ( (array) $theme_location['locations'] as $loc_index => $loc_config ) {
					if ( array_key_exists( 'add_filter', $loc_config ) ) {
						unset( $theme_locations[ $theme_loc_key ]['locations'][ $loc_index ]['add_filter'] );
					}
					if ( array_key_exists( 'add_action', $loc_config ) ) {
						unset( $theme_locations[ $theme_loc_key ][ $loc_index ]['add_action'] );
					}
				}
			}
		}

		$locations = array_merge( $widget_locations, $theme_locations );
		return apply_filters( "search_and_filtering_section_output_{$this->slug}_theme_locations", $locations, $this );
	}

	/**
	 * Extending Class Constructor Placeholder
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function construct(){}

	/**
	 * Extending Class Constructor Placeholder (when type is enabled)
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function construct_enabled(){}

	/**
	 * Extending Class Constructor Placeholder (when type is enabled)
	 *
	 *
	 * @since 1.1.9
	 *
	 */
	public function wp_enabled(){}

	/**
	 * Theme Field Config Placeholder
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function fields_config(){
		return array();
	}

	/**
	 * Theme Sources Config Placeholder
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function sources_config(){
		return array();
	}

	/**
	 * Theme Config Placeholder
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function theme_config(){
		return array();
	}

	/**
	 * Output Locations Placeholder
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function output_locations(){
		return array();
	}

	/**
	 * Custom CSS Placeholder
	 *
	 *
	 * @return string
	 * @since 0.1.1
	 *
	 */
	public function custom_css(){
		return '';
	}
}