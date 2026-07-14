<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Job
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Job {

	/**
	 * @var \WPJMSF\CPT
	 */
	public $cpt;
	/**
	 * @var \WPJM_Search_Filtering
	 */
	public $sf;
	/**
	 * @var \WPJMSF\Admin\Job
	 */
	public $admin;
	/**
	 * @var string
	 */
	public $post_type = 'wpjmsf_job_sections';
	/**
	 * @var string
	 */
	public $settings_option = 'wpjmsf_job_settings';
	/**
	 * @var string Main listings post type
	 */
	public $core_post_type = 'job_listing';
	/**
	 * @var string String representation of the shortcode used to output listings
	 */
	public $listings_shortcode = 'jobs';
	/**
	 * @var string Slug to use for this specific field group "type"
	 */
	public $slug = 'job';
	/**
	 * @var array
	 */
	public $labels = array();
	/**
	 * @var \WPJMSF\AJAX|\WPJMSF\Admin\AJAX
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
	 * @var \WPJMSF\Debug\Job
	 */
	public $debug;
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
	public $post_title_meta_key = 'job_title';
	/**
	 * @var string Meta key used for the 'post_content' post field
	 */
	public $post_content_meta_key = 'job_description';
	/**
	 * @var string Option name to pull from db to get URL for configured listings page
	 */
	public $listings_page_option = 'job_manager_jobs_page_id';
	/**
	 * @var \WPJMSF\Cache
	 */
	public $cache;
	/**
	 * @var \WPJMSF\Shortcodes\Atts
	 */
	public $output_sc_atts;

	/**
	 * Job constructor.
	 *
	 * @param $sf \WPJM_Search_Filtering
	 */
	function __construct( $sf ) {

		$this->labels = array(
			'name'          => __( 'WPJM Job Search Sections', 'wp-job-manager-search-and-filtering' ),
			'singular_name' => __( 'WPJM Job Search Section', 'wp-job-manager-search-and-filtering' ),
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

		// Don't check is_admin() to support adminbar handling on frontend
		$this->admin = new Admin\Job( $this );

		if ( $sf->is_request( 'ajax' ) ) {
			new Admin\AJAX( $this );
			$this->ajax = new AJAX( $this );
		}

		$this->output_sc_atts = new Shortcodes\Atts\Job( $this );

		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_deregister' ), 99 );
		add_filter( 'job_manager_locate_template', array( $this, 'locate_template' ), 200, 3 );

		add_filter( 'job_listing_searchable_meta_keys', array( $this, 'check_search_all_meta' ) );
		add_filter( 'get_job_listings_cache_results', array( $this, 'check_disable_cache_results' ) );
		add_action( 'job_manager_sf_job_filters_before', array( $this->output, 'set_template_triggered' ) );

		if ( $this->output->is_enabled() ) {

			// 9999 to be higher priority than workscout so we don't overwrite if already exists
			add_filter( 'job_manager_ajax_filters_ajax_data', array( $this, 'job_manager_ajax_filters_ajax_data' ), 9999 );

			/**
			 * RSS Feed Handling
			 *
			 * Make sure we only run this when actual values are in the RSS URL for this plugin
			 */
			if( $this->search->has_sf_queries() ){
				add_filter( 'job_feed_args', array( $this->search, 'get_listings_args' ), 9999 );
				add_filter( 'job_feed_args', array( $this->search, 'get_listings_query_args' ), 9999 );
				add_filter( 'job_feed_args', array( $this->search, 'get_listings_query_args_after' ), 9999 );
			}

			$this->debug = new Debug\Job( $this );

			add_filter( 'job_manager_jobs_shortcode_data_attributes', array( $this->output_sc_atts, 'add_data_attribute' ) );

			add_filter( 'job_manager_get_listings_result', array( $this->search, 'get_listings_results' ), 10, 2 );

			add_action( 'plugins_loaded', array( $this, 'plugin_loaded_integration' ), 99999 );

			// Prevent WP Job Manager from removing the 'reset' link in showing results links
			add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );

			// Before calling get_job_listings (call made from AJAX)
			add_filter( 'job_manager_get_listings_args', array( $this->search, 'get_listings_args' ), 9999 );

			// Before remove meta/tax queries
			add_filter( 'job_manager_get_listings', array( $this->search, 'get_listings_query_args' ), 9999, 2 );

			// After remove meta/tax queries
			add_filter( 'get_job_listings_query_args', array( $this->search, 'get_listings_query_args_after' ), 9999, 2 );

			add_action( 'after_get_job_listings', array( $this->search, 'after_query' ), 9999 );

			add_filter( 'job_manager_get_listings_custom_filter_rss_args', array( $this->search, 'add_rss_args' ) );

			/**
			 * Geolocation distance outputs
			 */
			add_action( 'job_listing_meta_end', array( $this->search->geolocation, 'output_distance' ), 99 );
			add_action( 'workscout_job_listing_meta_start', array( $this->search->geolocation, 'output_distance' ), 99 );
			add_action( 'listify_content_job_listing_before', array( $this->search->geolocation, 'output_distance' ), 99 );
			add_action( 'listable_job_listing_card_image_top', array( $this->search->geolocation, 'output_distance' ), 99 );
			/**
			 * Used to return true if distance output, false if not, when Listing Easy outputs the "formatted address"
			 *
			 * @see gt3_get_formatted_address()
			 */
			add_filter( 'gt3_skip_geolocation_formatted_address', array( $this->search->geolocation, 'output_distance' ), 99 );
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

		$job_obj  = get_post_type_object( $this->core_post_type );
		$singular = is_object( $job_obj ) ? $job_obj->labels->singular_name : __( 'Job', 'wp-job-manager-search-and-filtering' );

		return apply_filters( 'search_and_filtering_job_singular', $singular, $this );

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

		$job_obj = get_post_type_object( $this->core_post_type );
		$plural  = is_object( $job_obj ) ? $job_obj->labels->name : __( 'Jobs', 'wp-job-manager-search-and-filtering' );

		return apply_filters( 'search_and_filtering_job_plural', $plural, $this );
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
	public function job_manager_ajax_filters_ajax_data( $data ) {

		// skip if already set
		if( isset( $data['single_job_text'] ) && isset( $data['plural_job_text'] ) )
			return $data;

		$new_data = array(
			'single_job_text' => $this->get_singluar(),
			'plural_job_text' => $this->get_plural(),
		);

		return array_merge( $data, $new_data );
	}

	/**
	 * Initialize Plugin Integration Handling (when output enabled)
	 *
	 * @since 1.0.0
	 *
	 */
	public function plugin_loaded_integration() {
		new Plugins\Tags( $this );
		new Plugins\Regions( $this );
		new Plugins\AFJCL( $this );
	}

	public function display_error() {

		echo '<div class="error">';
		?>
      <p>
        The <strong>BETA</strong> version of <strong>Search and Filtering for WP Job Manager</strong> has <strong>EXPIRED</strong> and will AUTOMATICALLY deactivate itself!
        <em><strong>You MUST upgrade to a newer version!</strong></em>
      </p>
		<?php
		echo '</div>';
	}

	/**
	 * Check if search_keywords should search all meta keys
	 *
	 *
	 * @param $meta_keys
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public function check_search_all_meta( $meta_keys ) {

		$settings = get_option( $this->settings_option, array( 'enabled' => 0, 'search_all' => 0 ) );

		// return false to force searching all post meta, if this type is enabled, as well as search all setting
		if ( $settings && isset( $settings['enabled'], $settings['search_all'] ) && ! empty( $settings['enabled'] ) && ! empty( $settings['search_all'] ) ) {
			return false;
		}

		return $meta_keys;
	}

	/**
	 * Check if should cache results
	 *
	 *
	 * @param $cache_results
	 *
	 * @return bool
	 * @since 1.1.6
	 *
	 */
	public function check_disable_cache_results( $cache_results ) {

		$settings = get_option( $this->settings_option, array( 'enabled' => 0, 'disable_cache' => 0 ) );

		if ( $settings && isset( $settings['enabled'], $settings['disable_cache'] ) && ! empty( $settings['enabled'] ) && ! empty( $settings['disable_cache'] ) ) {
			return false;
		}

		return $cache_results;
	}

	/**
	 * Check if page has [jobs] shortcode in it
	 *
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public function has_listings_shortcode() {

		global $post;
		$has_shortcode = false;

		if ( is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $this->listings_shortcode ) ) {
			$has_shortcode = true;
		} elseif ( is_post_type_archive( 'job_listing' ) ) {
			$has_shortcode = true;
		}

//      Support for archive URLs -- but also adds a query for post object on every page load, probably not good to use
//		$archive_url = get_post_type_archive_link( 'job_listing' );
//		$request_uri = strpos( $_SERVER['REQUEST_URI'], '?' ) !== false ? strtok( $_SERVER["REQUEST_URI"], '?' ) : $_SERVER['REQUEST_URI'];
//		if( $archive_url && $request_uri && strpos( $archive_url, $request_uri ) !== false ){
//			return true;
//		}

		return apply_filters( 'search_and_filtering_job_has_listings_shortcode', $has_shortcode, $post, $this );
	}

	/**
	 * Maybe Deregister Core WP Job Manager Ajax Filters Script
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function maybe_deregister() {

		if ( ! $this->output->is_enabled() ) {
			return;
		}

		$dont_deregister = apply_filters( 'search_and_filtering_dont_deregister_default_job_filters_script', $this->output->has_shortcode_disabled && ! $this->output->has_shortcode_enabled, $this );
		if( $dont_deregister ){
			return;
		}

		/**
		 * Legacy deregister filter, new one added above to match the return value language
		 */
		$dont_deregister_legacy = apply_filters( 'search_and_filtering_deregister_default_job_filters_script', $this->output->has_shortcode_disabled && ! $this->output->has_shortcode_enabled, $this );
		if( $dont_deregister_legacy ){
			return;
		}

		if ( wp_script_is( 'wp-job-manager-ajax-filters', 'registered' ) ) {
			wp_deregister_script( 'wp-job-manager-ajax-filters' );
		}

		/**
		 * We register a dummy script here, specifically for themes or addons that have wp-job-manager-ajax-filters set as a dependency,
		 * because us removing the dependency above will ultimately cause that addon or theme script to not load, due to the dependency
		 * not being registered/available anymore.
		 */
		if( apply_filters( 'search_and_filtering_job_register_dummy_ajax_filters_script', true, $this ) ){
			wp_register_script( 'wp-job-manager-ajax-filters', WPJM_SEARCH_FILTERING_ASSETS . "/js/ajax-filters.min.js", array( 'jquery' ) );
		}

		$ajax_url = class_exists( 'WP_Job_Manager_Ajax' ) ? \WP_Job_Manager_Ajax::get_endpoint() : '';

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
		$ajax_data = apply_filters( 'job_manager_ajax_filters_ajax_data', $ajax_data, $this );

		wp_localize_script( 'wpjm-search-filtering-frontend', 'job_manager_ajax_filters', $ajax_data );
		wp_localize_script( 'wpjm-search-filtering-frontend-edit', 'job_manager_ajax_filters', $ajax_data );
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

		if ( strpos( $template_name, 'job' ) === false || apply_filters( 'search_and_filtering_dont_override_default_job_templates', false, $template_name, $template_path ) ) {
			return $template;
		}

		$should_output = $this->output->template_has_enabled_section_auto_output( $template_name );
		if( ! $should_output ){
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
		$slug_taxonomies = array( 'job_tags' => array( 'taxonomy' => 'job_listing_tag' ), 'job_type' => array( 'taxonomy' => 'job_listing_type' )  );
		$slug_taxonomies = apply_filters( 'search_and_filtering_get_job_taxonomy_slug_fields', $slug_taxonomies, $this );
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
		$text_fields = array( 'job_tags' => array( 'taxonomy' => 'job_listing_tag' ) );
		return apply_filters( 'search_and_filtering_get_job_taxonomy_text_fields', $text_fields, $this );
	}

	/**
	 * Check for Expired
	 *
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public static function is_expired() {

		$start   = \WPJMSF\Output::$nonce;
		$expires = ( 90 * 86400 );

		return time() > ( absint( $start ) + $expires );
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
			'search_job_type' => 'job_types'
		);

		return apply_filters( 'search_and_filtering_job_get_query_param_search_maps', $maps, $this );
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
			'job_listing_category' => 'search_categories',
			'job_listing_type'     => 'job_types'
		);

		return apply_filters( 'search_and_filtering_job_get_search_maps', $maps, $this );
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

			if ( ! class_exists( 'WP_Job_Manager_Form_Submit_Job' ) ) {

				// As of WPJM 1.25.2 and newer, the job_manager_multi_job_type() function is called in the init_fields() method, if for some reason
				// that function is not available, we have to manually load the files, and remove the core method that includes them to prevent fatal PHP errors.
				if ( defined( 'JOB_MANAGER_VERSION' ) && version_compare( JOB_MANAGER_VERSION, '1.25.2', 'ge' ) && ! function_exists( 'job_manager_multi_job_type' ) ) {
					remove_class_filter( 'after_setup_theme', 'WP_Job_Manager', 'include_template_functions', 11 );
					require_once JOB_MANAGER_PLUGIN_DIR . '/wp-job-manager-functions.php';
					require_once JOB_MANAGER_PLUGIN_DIR . '/wp-job-manager-template.php';
				}

				require_once JOB_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-job-manager-form-submit-job.php';
			}

			// We don't want to remove admin only fields
			add_filter( 'field_editor_init_job_fields_remove_admin_only_fields', '__return_false', 9999 );

			$wpjm = \WP_Job_Manager_Form_Submit_Job::instance();
			$wpjm->init_fields();
			$job_fields     = $wpjm->get_fields( 'job' );
			$company_fields = $wpjm->get_fields( 'company' );

			remove_filter( 'field_editor_init_job_fields_remove_admin_only_fields', '__return_false', 9999 );

			$this->listing_fields = array_merge( $job_fields, $company_fields );
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

		if ( function_exists( 'get_job_listing_post_statuses' ) ) {
			$defaults = get_job_listing_post_statuses();
		} else {
			$defaults = apply_filters( 'job_listing_post_statuses', array(
				                                                      'draft'           => _x( 'Draft', 'post status', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ),
				                                                      'expired'         => _x( 'Expired', 'post status', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ),
				                                                      'preview'         => _x( 'Preview', 'post status', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ),
				                                                      'pending'         => _x( 'Pending approval', 'post status', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ),
				                                                      'pending_payment' => _x( 'Pending payment', 'post status', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ),
				                                                      'publish'         => _x( 'Active', 'post status', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ),
			                                                      )
			);
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
			$order_by_options = array(
				'distance_featured' => __( 'Distance (Featured at Top)', 'wp-job-manager-search-and-filtering' ),
				'distance'          => __( 'Distance', 'wp-job-manager-search-and-filtering' ),
				'featured'          => __( 'Date (Featured at Top)', 'wp-job-manager-search-and-filtering' ),
				'date'              => __( 'Date', 'wp-job-manager-search-and-filtering' ),
				'title'             => __( 'Title', 'wp-job-manager-search-and-filtering' ),
				'modified'          => __( 'Modified (date)', 'wp-job-manager-search-and-filtering' ),
				'rand'              => __( 'Random', 'wp-job-manager-search-and-filtering' ),
				'rand_featured'     => __( 'Random (Featured at Top)', 'wp-job-manager-search-and-filtering' ),
			);

			$value_label_order_by_opts = \WPJMSF\Fields::build_options( $order_by_options );

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
						'taxonomy' => 'job_listing_category'
					),
					'job_types'         => array(
						'label'    => __( 'Job Types', 'wp-job-manager-search-and-filtering' ),
						'taxonomy' => 'job_listing_type'
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
					'filled'            => array(
						'label'   => __( 'Filled', 'wp-job-manager-search-and-filtering' ),
						'options' => array(
							array(
								'value' => '1',
								'label' => __( 'Filled', 'wp-job-manager-search-and-filtering' )
							)
						)
					),
					'remote_position'            => array(
						'label'   => __( 'Remote Position', 'wp-job-manager-search-and-filtering' ),
						'options' => array(
							array(
								'value' => '1',
								'label' => __( 'Include only Remote Positions', 'wp-job-manager-search-and-filtering' )
							)
						)
					),
					'orderby'           => array(
						'label'   => __( 'Order By', 'wp-job-manager-search-and-filtering' ),
						'options' => $value_label_order_by_opts,
						'type_config' => array(
							array(
								'label'       => __( 'Radius Default', 'wp-job-manager-search-and-filtering' ),
								'prop'        => 'radius_orderby_default',
								'type'        => 'select',
								'send_config' => true,
								'options' => $order_by_options,
								'desc'    => __( 'When a radius search is first triggered (location field value changes), if this field has a value, it will set the orderby to this value.', 'wp-job-manager-search-and-filtering' )
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
			$this->core_data_sources = apply_filters( 'search_and_filtering_get_job_core_data_sources', $sources, $this );
		}

		return $this->core_data_sources;
	}

	/**
	 * Check Cleanup
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function check_cleanup() {

		if ( self::is_expired() ) {

		}
	}

	/**
	 * Get Form URL
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function get_form_url() {
		$url = get_permalink( get_option( 'job_manager_jobs_page_id' ) );
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
		return get_option( 'job_manager_jobs_page_id' );
	}

	/**
	 * Get Capability to Manage/Edit/Save S&F
	 *
	 * @return mixed|void
	 * @since 1.1.0
	 *
	 */
	public function get_capability() {
		return apply_filters( 'search_and_filtering_wpjmsf_job_sections_cpt_capability', 'manage_options', $this );
	}

	/**
	 * job_manager_get_listings_custom_filter_text
	 *
	 * @param $showing
	 * @param $found_posts
	 *
	 * @return mixed|void
	 * @since 1.1.26
	 */
	public function showing_filter( $showing, $found_posts ) {

		$search_location   = isset( $_REQUEST['search_location'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search_location'] ) ) : '';
		$search_keywords   = isset( $_REQUEST['search_keywords'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search_keywords'] ) ) : '';
		$search_categories = isset( $_REQUEST['search_categories'] ) ? wp_unslash( $_REQUEST['search_categories'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized below.

		$search_values = [
			/**
			 * Default values passed
			 */
			'location'   => $search_location,
			'keywords'   => $search_keywords,
			'categories' => $search_categories,
			/**
			 * Add our custom values pass through the filter
			 */
			'wpjmsf_fields' => $this->search->get_field_values(),
			'found_posts' => $found_posts
		];

		return apply_filters( 'job_manager_get_listings_custom_filter_text', $showing, $search_values );
	}

	/**
	 * Is Geolocation Enabled?
	 *
	 * @return bool
	 * @since 1.2.1
	 *
	 */
	public function geolocation_enabled() {
		return apply_filters( 'job_manager_geolocation_enabled', true ) && get_option( 'job_manager_google_maps_api_key' );
	}
}