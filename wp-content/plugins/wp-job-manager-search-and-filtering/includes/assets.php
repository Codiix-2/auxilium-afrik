<?php
namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Scripts and Styles Class
 */
class Assets {

    function __construct() {
	    add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend' ), 5 );
	    add_action( 'admin_bar_init', array( $this, 'admin_bar_init' ) );
    }

	/**
	 * Admin Bar Initialized
	 *
	 * This method is only used to add an action when admin bar has been initialized,
	 * to add select2 admin bar fix in wp_head
	 *
	 * @since 1.0.0
	 *
	 */
	public function admin_bar_init() {
		add_action( 'wp_head', array( $this, 'output_select2_admin_bar_fix' ) );
	}

	/**
	 * Output Select2 Admin Bar Fix
	 *
	 * For some reason when using some themes, the WordPress admin bar on the frontend causes Select2 containers to look offset (only when attached to body [which is default]),
	 * to fix this, we add padding-top to the select2-container only when it immediately follows the wpadminbar DIV.
	 *
	 * @since 1.0.0
	 *
	 */
	public function output_select2_admin_bar_fix() {

		if( ! apply_filters( 'search_and_filtering_output_select2_admin_bar_fix', true, $this ) ){
			return;
		}

		$type_attr = current_theme_supports( 'html5', 'style' ) ? '' : ' type="text/css"';
		/**
		 * We use ~ here instead of + for any situations where something else ends up attaching to body as well,
		 * like when editing section and modal shows to delete field, etc.
		 */
		?>
		<style<?php echo $type_attr; ?> media="screen">
            div#wpadminbar ~ span.select2-container, body.admin-bar > span.select2-container {
                padding-top: 32px !important;
            }

            @media screen and ( max-width: 782px ) {
                div#wpadminbar ~ span.select2-container, body.admin-bar > span.select2-container {
                    padding-top: 46px !important;
                }
            }
		</style>
		<?php
	}

	/**
	 * Register Frontend Scripts/Assets
	 *
	 *
	 * @since 0.1.1
	 *
	 */
    public function register_frontend(){

    	if( class_exists( 'WP_Job_Manager' ) ){
		    // TODO: only register and enqueue when fields used that require it
		    \WP_Job_Manager::register_select2_assets();
	    }

	    $this->register();
//
//	    wp_enqueue_script( 'select2' );
//	    wp_enqueue_style( 'select2' );

	    wp_localize_script( 'wpjm-search-filtering-frontend-edit-mobile', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
	    wp_localize_script( 'wpjm-search-filtering-frontend-edit', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
	    wp_localize_script( 'wpjm-search-filtering-frontend', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
	    wp_localize_script( 'wpjm-search-filtering-frontend-edit-mobile', '__wpjmsf_assets_path__', WPJM_SEARCH_FILTERING_ASSETS . '/js/' );
	    wp_localize_script( 'wpjm-search-filtering-frontend-edit', '__wpjmsf_assets_path__', WPJM_SEARCH_FILTERING_ASSETS . '/js/' );
	    wp_localize_script( 'wpjm-search-filtering-frontend', '__wpjmsf_assets_path__', WPJM_SEARCH_FILTERING_ASSETS . '/js/' );
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register() {
        self::register_scripts( $this->get_scripts() );
        self::register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    public static function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPJM_SEARCH_FILTERING_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );

            if( isset( $script['i18n'] ) && $script['i18n'] ){

	            wp_localize_script(
		            $handle,
		            'wpjm_search_filtering_i18n',
		            array(
			            'wp-job-manager-search-and-filtering' => self::get_jed_json_translations(),
		            )
	            );

            }
        }
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public static function register_styles( $styles ) {

	    $debug = defined( 'SMYLES_DEVN' ) && SMYLES_DEVN;

        foreach ( $styles as $handle => $style ) {

            $deps = isset( $style['deps'] ) ? $style['deps'] : array();
            wp_register_style( $handle, $style['src'], $deps, $debug ? time() : WPJM_SEARCH_FILTERING_VERSION );
        }

    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        $debug = defined( 'SMYLES_DEVN' ) && SMYLES_DEVN || isset( $_GET['debug_sf'] ) ? '' : '.min';

        $frontend_js_deps = array( 'wpjm-search-filtering-vendor', 'jquery' );

	    if ( apply_filters( 'search_and_filtering_output_enqueue_select2_js', true, $this ) ) {
	    	$frontend_js_deps[] = 'select2';
	    }

        $scripts = array(
            'wpjm-search-filtering-vendor' => array(
                'src'       => WPJM_SEARCH_FILTERING_ASSETS . "/js/vendor{$debug}.js",
                'version'   => ! empty( $debug ) ? WPJM_SEARCH_FILTERING_VERSION : filemtime( WPJM_SEARCH_FILTERING_PATH . "/assets/js/vendor.js" ),
                'in_footer' => true
            ),
            'wpjm-search-filtering-frontend' => array(
                'src'       => WPJM_SEARCH_FILTERING_ASSETS . "/js/frontend{$debug}.js",
                'deps'      => $frontend_js_deps,
                'version'   => ! empty( $debug ) ? WPJM_SEARCH_FILTERING_VERSION : filemtime( WPJM_SEARCH_FILTERING_PATH . '/assets/js/frontend.js' ),
                'in_footer' => true,
                'i18n'      => true
            ),
            'wpjm-search-filtering-frontend-edit' => array(
                'src'       => WPJM_SEARCH_FILTERING_ASSETS . "/js/frontend_edit{$debug}.js",
                'deps'      => $frontend_js_deps,
                'version'   => ! empty( $debug ) ? WPJM_SEARCH_FILTERING_VERSION : filemtime( WPJM_SEARCH_FILTERING_PATH . '/assets/js/frontend_edit.js' ),
                'in_footer' => true,
                'i18n'      => true
            ),
            'wpjm-search-filtering-frontend-edit-mobile' => array(
                'src'       => WPJM_SEARCH_FILTERING_ASSETS . "/js/frontend_edit_mobile{$debug}.js",
                'deps'      => $frontend_js_deps,
                'version'   => ! empty( $debug ) ? WPJM_SEARCH_FILTERING_VERSION : filemtime( WPJM_SEARCH_FILTERING_PATH . '/assets/js/frontend_edit_mobile.js' ),
                'in_footer' => true,
                'i18n'      => true
            )
        );

	    return apply_filters( 'search_and_filtering_assets_get_scripts', $scripts, $this );
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles() {

	    $prefix = defined( 'SMYLES_DEVN' ) && SMYLES_DEVN || isset( $_GET['debug_sf'] ) ? '' : '.min';

	    $styles = array(
            'wpjm-search-filtering-frontend' => array(
                'src' =>  WPJM_SEARCH_FILTERING_ASSETS . "/css/frontend{$prefix}.css",
                // Some reason theres an error when adding select2 with workscout, causing this css to not load
            ),
            'wpjm-search-filtering-frontend-edit' => array(
                'src' =>  WPJM_SEARCH_FILTERING_ASSETS . "/css/frontend_edit{$prefix}.css",
            ),
            'wpjm-search-filtering-frontend-edit-mobile' => array(
                'src' =>  WPJM_SEARCH_FILTERING_ASSETS . "/css/frontend_edit_mobile{$prefix}.css",
            )
        );

        return apply_filters( 'search_and_filtering_assets_get_styles', $styles, $this );
    }

	/**
	 * Get JSON Translations
	 *
	 *
	 * @return array|mixed
	 * @since 1.0.0
	 *
	 */
	public static function get_jed_json_translations() {

    	$locale = get_locale();

    	if( in_array( $locale, array( 'en_US', 'en' ) ) ){
		    $file = WPJM_SEARCH_FILTERING_PATH . '/languages/wp-job-manager-search-and-filtering.json';
	    } else {
		    $file = WPJM_SEARCH_FILTERING_PATH . '/languages/wp-job-manager-search-and-filtering-' . get_locale() . '.jed.json';
	    }

		if ( ! $file || ! is_readable( $file ) ) {
			return array(
				'locale_data' => array(
					'messages' => array()
				)
			);
		}

		if ( file_exists( $file ) ) {
			$file = file_get_contents( $file );
			if ( is_string( $file ) && $file !== '' ) {
				return json_decode( $file, true );
			}
		}
	}
}
