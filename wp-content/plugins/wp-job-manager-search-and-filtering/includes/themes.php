<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Themes
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Themes {
	/**
	 * @var \WPJMSF\Themes\Jobify
	 */
	public $theme = null;
	/**
	 * @var string Slug of theme name
	 */
	public $slug;

	/**
	 * Themes constructor.
	 *
	 */
	public function __construct() {
		$this->init_theme();
		add_filter( 'search_and_filtering_assets_get_styles', array( $this, 'add_style_dep' ) );
	}

	/**
	 * Add Theme Style Files
	 *
	 * @param $styles
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function add_style_dep( $styles ) {

		if( ! $this->theme || ! $this->slug ){
			return $styles;
		}

		$debug = defined( 'SMYLES_DEVN' ) && SMYLES_DEVN ? '' : '.min';
		$style_relative_path = "/assets/css/themes/{$this->slug}{$debug}.css";

		// Backup check to make sure minified file exists, otherwise load the standard non-minified file
		$style_relative_path = ! empty( $debug ) && ! file_exists( WPJM_SEARCH_FILTERING_PATH . $style_relative_path  ) ? "/assets/css/themes/{$this->slug}.css" : $style_relative_path;

		if( file_exists( WPJM_SEARCH_FILTERING_PATH . $style_relative_path ) ){
			wp_register_style( "wpjm-search-filtering-{$this->slug}", WPJM_SEARCH_FILTERING_URL . $style_relative_path, array(), ( empty( $debug ) ? time() : WPJM_SEARCH_FILTERING_VERSION ) );
			$styles['wpjm-search-filtering-frontend']['deps'][] = "wpjm-search-filtering-{$this->slug}";
			$styles['wpjm-search-filtering-frontend-edit']['deps'][] = "wpjm-search-filtering-{$this->slug}";
		}

		return $styles;
	}

	/**
	 * Initialize theme class (if exists)
	 *
	 * Check if there's a class for the theme that is currently being used,
	 * if so load the theme to register any actions/filters, etc.
	 *
	 * @since 0.1.1
	 *
	 */
	function init_theme() {

		$possible_names = \WPJM_Search_Filtering::get_theme_name();

		foreach ( $possible_names as $type => $name ) {

			$theme_file = WPJM_SEARCH_FILTERING_INCLUDES . "/themes/{$name}.php";

			if ( file_exists( $theme_file ) ) {
				require_once $theme_file;
				$theme_name = ucfirst( $name );
				$this->slug = $name;
				$theme_class = "\WPJMSF\Themes\\$theme_name";
				$this->theme = new $theme_class( $this );
				break;
			}

		}
	}

	/**
	 * Initialize and Return Specific Theme Type (Job/Resume) Instance
	 *
	 *
	 * @param $type
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	static function init_type( $type ) {

		$possible_names = \WPJM_Search_Filtering::get_theme_name();
		$theme_instance = false;
		$type_slug = $type->slug;

		foreach ( $possible_names as $theme_string_type => $name ) {

			$theme_type_file = WPJM_SEARCH_FILTERING_INCLUDES . "/themes/{$name}/{$type_slug}.php";

			if( file_exists( $theme_type_file ) ){
				require_once $theme_type_file;
				$theme_name = ucfirst( $name );
				$theme_type = ucfirst( $type_slug );
				$theme_class = "\WPJMSF\Themes\\$theme_name\\$theme_type";
				$theme_instance = new $theme_class( $type, $name );
				break;
			}

		}

		return $theme_instance;
	}

	/**
	 * Check if Child Theme is Required
	 *
	 * @return false
	 * @since 1.1.3
	 *
	 */
	public function child_theme_required(){
		return $this->theme && property_exists( $this->theme, 'child_theme_required' ) ? $this->theme->child_theme_required : false;
	}

	/**
	 * Do Theme Setup
	 *
	 * @since 1.1.3
	 *
	 */
	public function do_theme_setup() {
		if( $this->theme && method_exists( $this->theme, 'theme_setup' ) ){
			return $this->theme->theme_setup();
		}

		return true;
	}

	/**
	 * Get Setup Configuration
	 *
	 * @since 1.1.3
	 *
	 */
	public function get_setup() {

		$setup_params = $this->theme && method_exists( $this->theme, 'theme_setup_params' ) ? $this->theme->theme_setup_params() : array();

		return array(
			'using_child_theme' => is_child_theme(),
			'child_theme_required' => $this->child_theme_required(),
			'theme_setup_params' => $setup_params,
			'plugin_search_url' => admin_url( 'plugin-install.php?s=child%20theme&tab=search&type=term' )
		);
	}
}
