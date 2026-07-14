<?php
namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Pages Handler
 */
class Admin {
	/**
	 * @var string Post type slug, should be 'job', 'resume' (set in extending class)
	 */
	public $post_type_slug;
	/**
	 * @var
	 */
	public $post_type;
	/**
	 * @var
	 */
	public $capability;
	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * Admin constructor.
	 */
	public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 50 );
		new Admin\Assets( $this->type );
	}

	/**
	 * Add Admin Menu Bar Item
	 *
	 *
	 * @param $wp_admin_bar
	 *
	 * @since 0.1.1
	 *
	 */
	public function admin_bar( $wp_admin_bar ) {

		if ( ! $this->type->has_listings_shortcode() && ! is_front_page() && ! apply_filters( 'search_and_filtering_always_show_admin_menu', false, $this ) ) {
			return;
		}

		$args = array(
			'id'    => 'search-and-filtering',
			'title' => __( 'Search & Filtering', 'wp-job-manager-search-and-filtering' ),
			'meta'  => array(
				'class' => 'search-and-filtering-class'
			)
		);

		$wp_admin_bar->add_node( $args );

		$args = array();

		$type_uc = ucfirst( $this->type->slug );

		if( $this->type->output->is_enabled() ){
			array_push( $args, array(
				'id'     => 'enable-search-filtering-edit',
				'title'  => __( 'Enable Editing', 'wp-job-manager-search-and-filtering' ),
				'href'   => '#',
				'parent' => 'search-and-filtering',
				'meta'   => array(
					'class' => 'enable-search-filtering-edit'
				)
			) );

			array_push( $args, array(
				'id'     => 'disable-search-filtering-edit',
				'title'  => __( 'Disable Editing', 'wp-job-manager-search-and-filtering' ),
				'href'   => '#',
				'parent' => 'search-and-filtering',
				'meta'   => array(
					'class' => 'disable-search-filtering-edit',
				)
			) );

			array_push( $args, array(
				'id'     => "disable_{$this->type->slug}_search_filtering",
				'title'  => sprintf( __( 'Disable %s Custom S&F', 'wp-job-manager-search-and-filtering' ), $type_uc ),
				'href'   => add_query_arg( array( 'wpjmsf_disable_sf' => $this->type->slug ) ),
				'parent' => 'search-and-filtering',
			) );

		} else {

			if( ! $this->type->output->disabled_by_exclude_page ){

				array_push( $args, array(
					'id'     => "enable_{$this->type->slug}_search_filtering",
					'title'  => sprintf( __( 'Enable %s Custom S&F', 'wp-job-manager-search-and-filtering' ), $type_uc ),
					'href'   => add_query_arg( array( 'wpjmsf_enable_sf' => $this->type->slug ) ),
					'parent' => 'search-and-filtering',
				) );

			} else {
				array_push( $args, array(
					'id'     => "{$this->type->slug}_exclude_sf",
					'title'  => sprintf( __( '%s S&F Disabled in Settings by Page Exclude', 'wp-job-manager-search-and-filtering' ), $type_uc ),
					'href'   => admin_url( "edit.php?post_type={$this->type->core_post_type}&page={$this->type->slug}-search-and-filtering&show=settings" ),
					'parent' => 'search-and-filtering',
				) );

			}

		}

		array_push( $args, array(
			'id'     => "{$this->type->slug}_section_list",
			'title'  => sprintf( __( '%s Section List', 'wp-job-manager-search-and-filtering' ), $type_uc ),
			'href'   => admin_url( "edit.php?post_type={$this->type->core_post_type}&page={$this->type->slug}-search-and-filtering" ),
			'parent' => 'search-and-filtering',
		) );

//		sort( $args );

		for ( $a = 0; $a < sizeOf( $args ); $a ++ ) {
			$wp_admin_bar->add_node( $args[ $a ] );
		}
	}

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu() {
        global $submenu;

	    $submenu_hook = add_submenu_page(
		    "edit.php?post_type={$this->post_type}",
		    __( 'Search & Filtering', 'wp-job-manager-search-and-filtering' ),
		    __( 'Search & Filtering', 'wp-job-manager-search-and-filtering' ),
		    $this->capability,
		    "{$this->post_type_slug}-search-and-filtering",
		    array( $this, 'plugin_page' )
	    );

        add_action( 'load-' . $submenu_hook, array( $this, 'init_hooks' ) );
    }

	/**
	 * Default Settings
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 *
	 */
    public function default_settings(){
    	$settings = array(
    		'enabled' => array(
				'label' => __( 'Enabled', 'wp-job-manager-search-and-filtering' ),
				'desc' => __( 'Whether or not to enable the custom search and filtering for these type of listings. This MUST be enabled if you want to use custom search and filtering.', 'wp-job-manager-search-and-filtering' ),
				'type' => 'switch'
		    )
	    );

    	return apply_filters( 'search_and_filtering_admin_default_settings', $settings, $this );
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts() {
	    $screen = get_current_screen();
	    $screen_ids = apply_filters( 'search_and_filtering_admin_screen_ids', array(
		    'job_listing_page_job-search-and-filtering',
		    'resume_page_resume-search-and-filtering'
	    ) );

	    /**
	     * Only enqueue on our specific pages, supports screen IDs above or any that match the format
	     */
	    if ( ! $screen || ! in_array( $screen->id, $screen_ids ) || $screen->id !== "{$this->post_type}_page_{$this->post_type_slug}-search-and-filtering" ) {
	    	return;
	    }

	    wp_localize_script( 'wpjm-search-filtering-admin', 'wpjmsf_field_types', $this->type->fields->get_field_type_groups() );
    	wp_localize_script( 'wpjm-search-filtering-admin', 'wpjmsf_data_sources', $this->type->fields->get_data_sources() );

	    wp_localize_script( 'wpjm-search-filtering-admin', '__wpjmsf_assets_path__', WPJM_SEARCH_FILTERING_ASSETS . '/js/' );
	    wp_localize_script( 'wpjm-search-filtering-admin', 'wpjmsf_post_type_slug', $this->post_type_slug );

	    $settings = get_option( $this->type->settings_option, array( 'enabled' => 0 ) );
	    wp_localize_script( 'wpjm-search-filtering-admin', 'wpjmsf_settings', $settings );

	    $listings_page_id = get_option( $this->type->listings_page_option, false );
		$listings_page_url = ! empty( $listings_page_id ) ? get_post_permalink( $listings_page_id ) : site_url();
	    wp_localize_script( 'wpjm-search-filtering-admin', 'wpjmsf_listings_page_url', $listings_page_url );

	    wp_localize_script( 'wpjm-search-filtering-admin', 'wpjmsf_output_locations', $this->type->fields->get_section_output_locations() );
	    wp_localize_script( 'wpjm-search-filtering-admin', 'wpjmsf_output_theme_locations', $this->type->fields->get_section_output_theme_locations() );

	    wp_localize_script( 'wpjm-search-filtering-admin', "wpjmsf_admin_nonce", wp_create_nonce( "wpjmsf_admin_nonce" ) );

	    $this->localize_setting_params();

	    wp_enqueue_style( 'wpjm-search-filtering-admin' );
	    wp_enqueue_script( 'wpjm-search-filtering-admin' );
    }

	/**
	 * Localize Params for Settings
	 *
	 * @since 1.1.3
	 *
	 */
	public function localize_setting_params() {

		$page_on_front = (int) get_option( 'page_on_front', 0 );
		$show_on_front = get_option( 'show_on_front', 'page' );

		$page_id_on_front = $show_on_front === 'page' && ! empty( $page_on_front ) ? $page_on_front : false;

		$all_pages    = get_pages();
		$page_options = array();
		foreach ( (array) $all_pages as $single_page ) {
			$is_homepage = $page_id_on_front && $single_page->ID === $page_on_front ? ' (Current Homepage)' : '';

			$page_options[] = array(
				'value' => "{$single_page->ID}",
				'label' => "{$single_page->post_title} (ID: {$single_page->ID}, Slug: {$single_page->post_name}){$is_homepage}"
			);
		}

		$setting_params = apply_filters( 'search_and_filtering_admin_setting_params', array( 'pages' => $page_options ) );

		wp_localize_script( 'wpjm-search-filtering-admin', "wpjmsf_setting_params", $setting_params );
    }

    /**
     * Render our admin page
     *
     * @return void
     */
    public function plugin_page() {
    	$show = isset( $_GET['show'] ) ? sanitize_text_field( $_GET['show'] ) : 'list-table';
	    echo "<div class=\"wrap\"><div id=\"search-filtering-admin\"><search-and-filtering-admin type-slug=\"{$this->type->slug}\" show=\"{$show}\"></search-and-filtering-admin></div></div>";
    }
}
