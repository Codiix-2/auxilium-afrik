<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Resume
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Resume {
	/**
	 * @var \WPJMSF\CPT
	 */
	public $cpt;
	/**
	 * @var \WPJM_Search_Filtering
	 */
	public $sf;
	/**
	 * @var \WPJMSF\Admin\Resume
	 */
	public $admin;
	/**
	 * @var string
	 */
	public $post_type = 'wpjmsf_res_sections';
	/**
	 * @var string
	 */
	public $settings_option = 'wpjmsf_resume_settings';
	/**
	 * @var string Main listings post type
	 */
	public $core_post_type = 'resume';
	/**
	 * @var string String representation of the shortcode used to output listings
	 */
	public $listings_shortcode = 'resumes';
	/**
	 * @var string Slug to use for this specific field group "type"
	 */
	public $slug = 'resume';
	/**
	 * @var array
	 */
	public $labels = array();
	/**
	 * @var \WPJMSF\Ajax
	 */
	public $ajax;
	/**
	 * @var \WPJMSF\Fields
	 */
	public $fields;
	/**
	 * @var \WPJMSF\Sections
	 */
	public $sections;
	/**
	 * @var \WPJMSF\Shortcodes
	 */
	public $shortcodes;
	/**
	 * @var \WPJMSF\Output
	 */
	public $output;
	/**
	 * @var \WPJMSF\Widget
	 */
	public $widget;
	/**
	 * @var \WPJMSF\Search
	 */
	public $search;
	/**
	 * @var \WPJMSF\Theme
	 */
	public $theme;
	/**
	 * @var bool Whether or not the nonce has been output already or not
	 */
	public $nonce_output = false;
	/**
	 * @var array
	 */
	public $listing_fields = array();
	/**
	 * @var array
	 */
	public $core_data_sources = array();
	/**
	 * @var string Meta key used for the 'post_title' post field
	 */
	public $post_title_meta_key = 'candidate_name';
	/**
	 * @var string Meta key used for the 'post_content' post field
	 */
	public $post_content_meta_key = 'resume_content';
	/**
	 * @var string Option name to pull from db to get URL for configured listings page
	 */
	public $listings_page_option = 'resume_manager_resumes_page_id';
	/**
	 * @var \WPJMSF\Cache
	 */
	public $cache;
	/**
	 * @var \WPJMSF\Debug\Resume
	 */
	public $debug;
	/**
	 * @var \WPJMSF\Shortcodes\Atts
	 */
	public $output_sc_atts;

	/**
	 * Resume constructor.
	 *
	 * @param $sf \WPJM_Search_Filtering
	 */
	function __construct( $sf ) {

		$this->labels = array(
			'name'          => __( 'WPJM Resume Search Sections', 'wp-job-manager-search-and-filtering' ),
			'singular_name' => __( 'WPJM Resume Search Section', 'wp-job-manager-search-and-filtering' ),
		);

		$this->cache    = new Cache( $this );
		$this->sections = new Sections( $this );
		$this->fields   = new Fields( $this );
		$this->sf       = $sf;
		$this->cpt      = new CPT( $this );
		$this->output   = new Output( $this );

		$this->theme = Themes::init_type( $this );

		$this->shortcodes = new Shortcodes( $this );
		$this->search     = new Search( $this );
		$this->widget     = new Widget( $this );

		$this->admin = new Admin\Resume( $this );

		if ( $sf->is_request( 'ajax' ) ) {
			new Admin\AJAX( $this );
			$this->ajax = new AJAX( $this );
		}

		$this->debug = new Debug\Resume( $this );

		$this->output_sc_atts = new Shortcodes\Atts\Resume( $this );

		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_deregister' ), 99 );
		add_filter( 'job_manager_locate_template', array( $this, 'locate_template' ), 200, 3 );

		add_action( 'resume_manager_sf_resume_filters_before', array( $this->output, 'set_template_triggered' ) );

		// TODO: RSS feed handling for custom fields
		// job_manager_get_listings_custom_filter_rss_args - add custom fields
//		 add_filter( 'job_feed_args', array( $this, 'job_feed_args' ) );

		if ( $this->output->is_enabled() ) {

			add_filter( 'resume_manager_ajax_filters_ajax_data', array( $this, 'resume_manager_ajax_filters_ajax_data' ), 9999 );

			add_filter( 'job_manager_resumes_shortcode_data_attributes', array( $this->output_sc_atts, 'add_data_attribute' ) );

			/**
			 * Added in WPRM 1.18.1+
			 */
			add_filter( 'resume_manager_get_listings_result', array( $this->search, 'get_listings_results' ), 10, 2 );

			// Before calling get_resumes() function
			add_filter( 'resume_manager_get_resumes_args', array( $this->search, 'get_listings_args' ), 9999 );

			// Before remove meta/tax queries
			add_filter( 'resume_manager_get_resumes', array( $this->search, 'get_listings_query_args' ), 9999, 2 );

			// After remove meta/tax queries
			add_filter( 'get_resumes_query_args', array( $this->search, 'get_listings_query_args_after' ), 9999, 2 );

			add_action( 'after_get_resumes', array( $this->search, 'after_query' ), 9999 );

			add_action( 'resume_listing_meta_end', array( $this->search->geolocation, 'output_distance' ), 99 );

		}
	}

	/**
	 * Get Job Listing Post Type Singular Label
	 *
	 *
	 * @return string|void
	 * @since 1.1.32
	 *
	 */
	public function get_singluar() {

		$resume_obj  = get_post_type_object( $this->core_post_type );
		$singular = is_object( $resume_obj ) ? $resume_obj->labels->singular_name : __( 'Resume', 'wp-job-manager-search-and-filtering' );

		return apply_filters( 'search_and_filtering_resume_singular', $singular, $this );

	}

	/**
	 * Get Plural Post Type Label
	 *
	 *
	 * @return string|void
	 * @since 1.1.32
	 *
	 */
	public function get_plural() {

		$resume_obj = get_post_type_object( $this->core_post_type );
		$plural  = is_object( $resume_obj ) ? $resume_obj->labels->name : __( 'Resumes', 'wp-job-manager-search-and-filtering' );

		return apply_filters( 'search_and_filtering_resume_plural', $plural, $this );
	}

	/**
	 * Add single and plural text for "type" of listings
	 *
	 * @param $data
	 *
	 * @return mixed
	 * @since 1.1.32
	 *
	 */
	public function resume_manager_ajax_filters_ajax_data( $data ) {

		// skip if already set
		if ( isset( $data['single_resume_text'] ) && isset( $data['plural_resume_text'] ) ) {
			return $data;
		}

		$new_data = array(
			'single_resume_text' => $this->get_singluar(),
			'plural_resume_text' => $this->get_plural(),
		);

		return array_merge( $data, $new_data );
	}

	/**
	 * Check if page has [resumes] shortcode in it
	 *
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public function has_listings_shortcode() {

		global $post;

		if ( is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $this->listings_shortcode ) ) {
			return true;
		}

		if ( is_post_type_archive( $this->core_post_type ) ) {
			return true;
		}

//      Support for archive URLs -- but also adds a query for post object on every page load, probably not good to use
//		$archive_url = get_post_type_archive_link( 'job_listing' );
//		$request_uri = strpos( $_SERVER['REQUEST_URI'], '?' ) !== false ? strtok( $_SERVER["REQUEST_URI"], '?' ) : $_SERVER['REQUEST_URI'];
//		if( $archive_url && $request_uri && strpos( $archive_url, $request_uri ) !== false ){
//			return true;
//		}

		return false;
	}

	/**
	 * Maybe Deregister Core WP Job Manager Ajax Filters Script
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function maybe_deregister() {

		if ( ! $this->output->is_enabled() || apply_filters( 'search_and_filtering_dont_deregister_default_resume_filters_script', $this->output->has_shortcode_disabled && ! $this->output->has_shortcode_enabled, $this ) ) {
			return;
		}

		if ( wp_script_is( 'wp-resume-manager-ajax-filters', 'registered' ) ) {
			wp_deregister_script( 'wp-resume-manager-ajax-filters' );
		}

		$ajax_url = admin_url( 'admin-ajax.php', 'relative' );

		$ajax_data = array(
			'ajax_url'                => $ajax_url,
			'is_rtl'                  => is_rtl() ? 1 : 0,
			'i18n_load_prev_listings' => __( 'Load previous listings', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ),
		);

		/**
		 * Retrieves the current language for use when caching requests.
		 *
		 * @param string|null $lang
		 *
		 * @since 1.26.0
		 *
		 */
		$ajax_data['lang'] = apply_filters( 'wpjm_lang', null );

		$ajax_data = apply_filters( 'resume_manager_ajax_filters_ajax_data', $ajax_data, $this );

		wp_localize_script( 'wpjm-search-filtering-frontend', 'resume_manager_ajax_filters', $ajax_data );
		wp_localize_script( 'wpjm-search-filtering-frontend-edit', 'resume_manager_ajax_filters', $ajax_data );
	}

	/**
	 * WP Job Manager Locate Template Filter
	 *
	 *
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 *
	 * @return bool|string
	 * @since 0.1.1
	 *
	 */
	public function locate_template( $template, $template_name, $template_path ) {

		if( strpos( $template_name, 'resume' ) === false || apply_filters( 'search_and_filtering_dont_override_default_resume_templates', false, $template_name, $template_path ) ){
			return $template;
		}

		$should_output = $this->output->template_has_enabled_section_auto_output( $template_name );
		if ( ! $should_output ) {
			return $template;
		}

		/**
		 * Support customized theme template overrides
		 *
		 * Theme template structure should match /templates/THEMENAME/
		 */
		$theme_name   = \WPJM_Search_Filtering::get_theme_name( true, false );
		$default_path = WPJM_SEARCH_FILTERING_PATH . '/templates/' . $template_name;
		$theme_path   = is_string( $theme_name ) && ! empty( $theme_name ) ? WPJM_SEARCH_FILTERING_PATH . "/templates/{$theme_name}/" . $template_name : false;

		/**
		 * If a default file exists, or one specific for a theme
		 */
		if ( file_exists( $default_path ) || file_exists( $theme_path ) ) {

			/**
			 * Template file requested has an override in our templates file section.  First we need to make sure that
			 * search and filtering is enabled, and then we allow returning our custom template.
			 */
			if ( ! $this->output->is_enabled() ) {
				return $template;
			}

			if ( $theme_path && file_exists( $theme_path ) ) {
				$template = $theme_path;
			} else {
				$template = $default_path;
			}
		}

		/**
		 * Check for user template override, and use if one exists
		 *
		 * This is done after checking for our own internal template, as some templates require specific scripts
		 * to be enqueued.  Checking for user template last allows those scripts to be enqueued, and still use
		 * the user's template override.
		 */
		$user_template = locate_template( trailingslashit( 'search_filtering' ) . $template_name );

		// If user template exists, use that over normal template
		$template = $user_template ? $user_template : $template;

		return $template;
	}

	/**
	 * Get Taxonomies that should use Slug instead of Term ID
	 *
	 *
	 * @param bool $only_taxonomies
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 */
	public function get_taxonomy_slug_fields( $only_taxonomies = false ) {
		$slug_taxonomies = array();
		$slug_taxonomies = apply_filters( 'search_and_filtering_get_resume_taxonomy_slug_fields', $slug_taxonomies, $this );
		return $only_taxonomies ? array_column( $slug_taxonomies, 'taxonomy' ) : $slug_taxonomies;
	}

	/**
	 * Get Taxonomies Meta Keys that Can Be Text Field
	 *
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 *
	 */
	public function get_taxonomy_text_fields() {
		// Format is 'meta_key' => array( 'taxonomy' => 'taxonomy_slug' )
		$text_fields = array( 'resume_skills' => array( 'taxonomy'=> 'resume_skill' ) );
		return apply_filters( 'search_and_filtering_get_resume_taxonomy_text_fields', $text_fields, $this );
	}

	/**
	 * Get Search Mappings for Query Params
	 *
	 * This method returns mappings for when search is handled by core in query params, to convert to the values expected by
	 * this plugin.  WP Job Manager has a few that do not match, this is used to match those to what this plugin expects.
	 *
	 * @return mixed|void
	 * @since 1.1.9
	 *
	 */
	public function get_query_param_search_maps() {

		$maps = array(
			'search_category' => 'search_categories',
		);

		return apply_filters( 'search_and_filtering_resume_get_query_param_search_maps', $maps, $this );
	}

	/**
	 * Get Search Mappings for Default Search Fields
	 *
	 * These are mappings for specific field types (taxonomies) that have a specific mapping to a custom
	 * default search 'query arg'
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_search_maps() {

		$maps = array(
			'resume_category' => 'search_categories',
		);

		return apply_filters( 'search_and_filtering_resume_get_search_maps', $maps, $this );
	}

	/**
	 * Get all Fields
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function get_fields( $force = false ) {

		if ( empty( $this->listing_fields ) || $force ) {

			if ( ! class_exists( 'WP_Job_Manager_Form' ) ) {
				include JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php';
			}

			if ( ! class_exists( 'WP_Resume_Manager_Form_Submit_Resume' ) ) {
				require_once( RESUME_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-resume-manager-form-submit-resume.php' );
			}

			// We don't want to remove admin only fields
			add_filter( 'field_editor_init_resume_fields_remove_admin_only_fields', '__return_false', 9999 );

			$wprm = \WP_Resume_Manager_Form_Submit_Resume::instance();
			$wprm->init_fields();
			$this->listing_fields = $wprm->get_fields( 'resume_fields' );

			remove_filter( 'field_editor_init_resume_fields_remove_admin_only_fields', '__return_false', 9999 );
		}

		return $this->listing_fields;
	}

	/**
	 * Get Post Status Types
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_post_status_options() {

		if ( function_exists( 'get_resume_post_statuses' ) ) {
			$defaults = get_resume_post_statuses();
		} else {
			$defaults = apply_filters( 'resume_post_statuses', array(
				'draft'           => _x( 'Draft', 'post status', 'wp-job-manager-resumes', 'wp-job-manager-search-and-filtering' ),
				'expired'         => _x( 'Expired', 'post status', 'wp-job-manager-resumes', 'wp-job-manager-search-and-filtering' ),
				'hidden'          => _x( 'Hidden', 'post status', 'wp-job-manager-resumes', 'wp-job-manager-search-and-filtering' ),
				'preview'         => _x( 'Preview', 'post status', 'wp-job-manager-resumes', 'wp-job-manager-search-and-filtering' ),
				'pending'         => _x( 'Pending approval', 'post status', 'wp-job-manager-resumes', 'wp-job-manager-search-and-filtering' ),
				'pending_payment' => _x( 'Pending payment', 'post status', 'wp-job-manager-resumes', 'wp-job-manager-search-and-filtering' ),
				'publish'         => _x( 'Published', 'post status', 'wp-job-manager-resumes', 'wp-job-manager-search-and-filtering' ),
			) );
		}

		$default_options = array();
		foreach ( (array) $defaults as $default_val => $default_label ) {
			$default_options[] = array(
				'label' => $default_label,
				'value' => $default_val
			);
		}

		return $default_options;
	}

	/**
	 * Get Core Data Source Fields (without label)
	 *
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function get_core_data_source_fields() {

		$data_sources = $this->get_core_data_sources();

		return $data_sources['fields'];
	}

	/**
	 * Get Core Data Search Sources
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_core_data_sources() {

		if ( empty( $this->core_data_sources ) ) {
			$sources                 = array(
				'label'  => __( 'WP Job Manager Defaults', 'wp-job-manager-search-and-filtering' ),
				'fields' => array(
					'search_keywords'   => array(
						'label' => __( 'Keywords', 'wp-job-manager-search-and-filtering' )
					),
					'search_location'   => array(
						'label' => __( 'Location', 'wp-job-manager-search-and-filtering' ),
						'type_config' => array(
							array(
								'label'       => __( 'Include Anywhere', 'wp-job-manager-search-and-filtering' ),
								'prop'        => 'include_anywhere',
								'type'        => 'checkbox',
								'send_config' => true,
								'cb_label'    => __( 'Yes, include listings in results that do not have a location value', 'wp-job-manager-search-and-filtering' ),
								'desc'        => __( 'By default, locations that do NOT have a value set on them (displayed as Anywhere) are not included in results when searching for a location.  Enable this setting to always include listings without a location value, in results.', 'wp-job-manager-search-and-filtering' ),
							),
						)
					),
					'search_categories' => array(
						'label'    => __( 'Categories', 'wp-job-manager-search-and-filtering' ),
						'taxonomy' => 'resume_category'
					),
					'featured'          => array(
						'label'   => __( 'Featured', 'wp-job-manager-search-and-filtering' ),
						'options' => array(
							array(
								'value' => "1",
								'label' => __( 'Featured', 'wp-job-manager-search-and-filtering' )
							)
						)
					),
					'orderby'           => array(
						'label'   => __( 'Order By', 'wp-job-manager-search-and-filtering' ),
						'options' => array(
							array(
								'value' => 'distance',
								'label' => __( 'Distance', 'wp-job-manager-search-and-filtering' )
							),
							array(
								'value' => 'featured',
								'label' => __( 'Date (Featured at Top)', 'wp-job-manager-search-and-filtering' )
							),
							array(
								'value' => 'date',
								'label' => __( 'Date', 'wp-job-manager-search-and-filtering' )
							),
							array(
								'value' => 'title',
								'label' => __( 'Title', 'wp-job-manager-search-and-filtering' )
							),
							array(
								'value' => 'modified',
								'label' => __( 'Modified', 'wp-job-manager-search-and-filtering' )
							),
							array(
								'value' => 'rand',
								'label' => __( 'Random', 'wp-job-manager-search-and-filtering' )
							),
							array(
								'value' => 'rand_featured',
								'label' => __( 'Random (Featured at Top)', 'wp-job-manager-search-and-filtering' )
							),
						)
					),
					'order'             => array(
						'label'   => __( 'Order', 'wp-job-manager-search-and-filtering' ),
						'options' => array(
							array(
								'value' => 'ASC',
								'label' => __( 'Ascending', 'wp-job-manager-search-and-filtering' )
							),
							array(
								'value' => 'DESC',
								'label' => __( 'Descending', 'wp-job-manager-search-and-filtering' )
							),
						)
					),
					'post_status'       => array(
						'label'   => __( 'Post Status', 'wp-job-manager-search-and-filtering' ),
						'options' => $this->get_post_status_options()
					),
				)
			);
			$this->core_data_sources = apply_filters( 'search_and_filtering_get_resume_core_data_sources', $sources, $this );
		}

		return $this->core_data_sources;
	}

	/**
	 * Get Form URL
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function get_form_url() {
		$url = get_permalink( get_option( 'resume_manager_resumes_page_id' ) );
		return ! empty( $url ) && ! is_wp_error( $url ) ? $url : '';
	}

	/**
	 * Get Form Page ID
	 *
	 * @return string
	 * @since 1.2.1
	 *
	 */
	public function get_form_page_id() {

		return get_option( 'resume_manager_resumes_page_id' );
	}

	/**
	 * Get Capability to Manage/Edit/Save S&F
	 *
	 * @return mixed|void
	 * @since 1.1.0
	 *
	 */
	public function get_capability() {
		return apply_filters( 'search_and_filtering_wpjmsf_res_sections_cpt_capability', 'manage_options', $this );
	}

	/**
	 * resume_manager_get_resumes_custom_filter_text
	 *
	 * @param $showing
	 * @param $found_posts
	 *
	 * @return mixed|void
	 * @since 1.1.26
	 */
	public function showing_filter( $showing, $found_posts ) {

		$search_values = [
			/**
			 * Add our custom values pass through the filter
			 */
			'wpjmsf_fields' => $this->search->get_field_values(),
			'found_posts'   => $found_posts
		];

		return apply_filters( 'resume_manager_get_resumes_custom_filter_text', $showing, $search_values );
	}

	/**
	 * Is Geolocation Enabled?
	 *
	 * @return bool
	 * @since 1.2.1
	 *
	 */
	public function geolocation_enabled() {
		return apply_filters( 'resume_manager_geolocation_enabled', true ) && get_option( 'job_manager_google_maps_api_key' );
	}
}