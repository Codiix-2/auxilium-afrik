<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Output
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Output {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var string Nonce salt to use for output
	 */
	public static $nonce = '1600646630';
	/**
	 * @var bool Whether or not S&F is disabled based on the current page (and excluded by user)
	 */
	public $disabled_by_exclude_page = false;
	/**
	 * @var bool Whether or not one of our custom templates was triggered or not
	 */
	private $template_triggered = false;
	/**
	 * @var bool Whether or not the listings [jobs] or [resumes] shortcode exists on the page and S&F IS specifically disabled in it
	 */
	public $has_shortcode_disabled = false;
	/**
	 * @var bool Whether or not the listings [jobs] or [resumes] shortcode exists on the page and S&F IS NOT specifically disabled in it
	 */
	public $has_shortcode_enabled = false;

	/**
	 * Output constructor.
	 *
	 * @param $type
	 */
	public function __construct( $type ) {
		$this->type = $type;
		add_action( "search_and_filtering_filters_start_{$this->type->slug}", array( $this, 'search_and_filtering_filters_start' ) );
		add_action( "search_and_filtering_filters_search_{$this->type->slug}", array( $this, 'search_and_filtering_filters_search' ) );
		add_action( "search_and_filtering_filters_end_{$this->type->slug}", array( $this, 'search_and_filtering_filters_end' ) );
		add_action( "search_and_filtering_listings_end_after_{$this->type->slug}", array( $this, 'search_and_filtering_listings_end_after' ) );

		add_action( 'wp', array( $this, 'check_enable_disable' ) );

		add_action( 'wp_print_footer_scripts', array( $this, 'check_did_output' ) );
	}

	/**
	 * Check if Sections Output
	 *
	 * This method is used to check if a template was triggered (one of ours) which requires a section to initialize the listings.
	 * If no sections were output for this type, output code in footer to unhide the "init" error div for the user.
	 *
	 * @since 1.1.3
	 *
	 */
	public function check_did_output() {
		if( $this->template_triggered && ! $this->type->nonce_output ){
			echo "<script>jQuery(function($){ $('#search-filtering-{$this->type->slug}-not-init-error').show(); })</script>";
		}
	}

	/**
	 * Check if Admin Bar used to Enable/Disable Custom Search & Filtering
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function check_enable_disable(){
		global $wp;

		if( isset( $_GET['wpjmsf_disable_sf' ] ) && $_GET['wpjmsf_disable_sf'] === $this->type->slug ){
			if( ! current_user_can( $this->type->get_capability() ) ){
				return;
			}

			$current_url = home_url( add_query_arg( array(), $wp->request ) );
			remove_query_arg( array( 'wpjmsf_disable_sf', 'wpjmsf_enable_sf' ), $current_url );

			$current = get_option( $this->type->settings_option, array( 'enabled' => 0 ) );
			$current['enabled'] = 0;
			update_option( $this->type->settings_option, $current );

			wp_redirect( $current_url );
		}

		if ( isset( $_GET['wpjmsf_enable_sf'] ) && $_GET['wpjmsf_enable_sf'] === $this->type->slug ) {
			if ( ! current_user_can( $this->type->get_capability() ) ) {
				return;
			}
			$current_url = home_url( add_query_arg( array(), $wp->request ) );
			remove_query_arg( array( 'wpjmsf_disable_sf', 'wpjmsf_enable_sf' ), $current_url );

			$current            = get_option( $this->type->settings_option, array( 'enabled' => 0 ) );
			$current['enabled'] = 1;
			update_option( $this->type->settings_option, $current );
			wp_redirect( $current_url );
		}
	}

	/**
	 * Check Listing Shortcodes
	 *
	 * This method is meant for checking the content of the page (not always available when called) for the [jobs] or [resumes] shortcodes to see
	 * if any of them are specifically disabled or if there are multiple shortcodes on the same page (IE one with S&F disabled one that is not).
	 *
	 * @return bool
	 * @since 1.1.26
	 *
	 */
	public function check_listing_shortcodes() {
		global $post;
		if( ! $post || ! isset( $post->post_content ) || ! $post->post_content ){
			return false;
		}

		preg_match_all( '/' . get_shortcode_regex( array( $this->type->listings_shortcode ) ) . '/', $post->post_content, $matches, PREG_SET_ORDER );

		foreach( $matches as $match ){

			if( isset( $match[0] ) && ! empty( $match[0] ) ){
				$atts = shortcode_parse_atts( $match[0] );

				/**
				 * Has [jobs] or [resumes] shortcode and does not have attribute to disable S&F
				 *
				 * Even if the shortcode is on the page twice, and one of them has show_sf_filters set to false, since we detected a shortcode that would have S&F enabled,
				 * we don't want to return true so we immediately return false.  Reason being is this is most likely used to detect if we should prevent deregistering the AJAX
				 * script, and if we're loading S&F on the page it's fine to deregister as S&F will still handle the AJAX request for "Load More Listings", etc
				 */
				if ( empty( $atts ) || ! isset( $atts['show_sf_filters'] ) ) {
					$this->has_shortcode_enabled = true;
				}

				/**
				 * Otherwise, check if the shortcode has S&F disabled in it and set variable so we can loop through remaining matched shortcodes
				 */
				if ( ! empty( $atts ) && isset( $atts['show_sf_filters'] ) && in_array( $atts['show_sf_filters'], array( 'no', 'false', '0' ) ) ) {
					$this->has_shortcode_disabled = true;
				}

			}

		}

		return $this->has_shortcode_disabled && ! $this->has_shortcode_enabled;
	}

	/**
	 * Check if Custom Search and Filtering is Enabled
	 *
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public function is_enabled(){
		global $post;
		$is_enabled = true;
//		$object_id = get_queried_object();

		/**
		 * Check shortcode attributes
		 */
		if( $this->type->output_sc_atts && ! $this->type->output_sc_atts->show_sf_filters() ){
			$is_enabled = false;
		}

		if( $this->check_listing_shortcodes() ){
			$is_enabled = false;
		}

		/**
		 * Check setting/option
		 */
		if( $is_enabled ){
			$settings = get_option( $this->type->settings_option, array( 'enabled' => 0 ) );
			if ( ! is_array( $settings ) || ! isset( $settings['enabled'] ) || empty( $settings['enabled'] ) ) {
				$is_enabled = false;
			}
		}

		/**
		 * Check excluded pages
		 */
		if( $is_enabled ){
			$exclude_pages = isset( $settings['exclude_pages'] ) ? $settings['exclude_pages'] : false;
			if ( $exclude_pages && isset( $post, $post->ID ) && ! empty( $post->ID ) ) {
				$a = $exclude_pages;
				if ( in_array( "{$post->ID}", $exclude_pages ) ) {
					$this->disabled_by_exclude_page = true;
					$is_enabled                     = false;
				}
			}
		}

		return apply_filters( "search_and_filtering_{$this->type->slug}_is_enabled", $is_enabled, $this );
	}

	/**
	 * Do Action Output
	 *
	 *
	 * @param $action
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public function action_output( $action, $args ){

		$atts = $args && ! empty( $args ) && is_array( $args ) && isset( $args[0] ) && ! empty( $args[0] ) ? $args[0] : array();

		/**
		 * Allow for defining filter_section_id in [jobs]/[resumes] shortcode to output a custom section
		 */
		$custom_filter_section_id = is_array( $atts ) && isset( $atts['filter_section_id'] ) ? $atts['filter_section_id'] : false;
		if( ! empty( $custom_filter_section_id ) ){
			$this->output_section( $custom_filter_section_id, false );
			return true;
		}

		/**
		 * Allow for defining a custom filter action in [jobs]/[resumes] shortcode to match against the Auto Output action
		 * configured in section.
		 */
		$custom_filter_type = is_array( $atts ) && isset( $atts['custom_filter_type'] ) ? $atts['custom_filter_type'] : false;
		$sections = false;

		/**
		 * Check if this is a standard job-filters.php or resume-filters.php action being called,
		 * and see if we need to output a custom type for this specific location being called.
		 */
		if ( ! empty( $custom_filter_type ) && strpos( $action, 'search_and_filtering_filters' ) !== false ) {
			/**
			 * Should result in converting something like search_and_filtering_filters_search_job, to
			 * _search_job
			 */
			$actual_filter_location = str_replace( 'search_and_filtering_filters_', '', $action );
			/**
			 * we then combine it together with `custom_filter_type` to get something like
			 * jobify_widget_map_search_job, where `custom_filter_type` is `jobify_widget_map`
			 *
			 * So in the theme or whatever other custom action, it would be `jobify_widget_map_search_job` and the value passed
			 * in the shortcode for `custom_filter_type` would be `jobify_widget_map`
			 */
			$type_with_location = "{$custom_filter_type}_{$actual_filter_location}";
			$sections = $this->type->sections->get_sections_by_output( $type_with_location );
		} else {
			$sections = $this->type->sections->get_sections_by_output( $action );
		}

		if( $sections ){

			foreach( (array) $sections as $section ){
				$this->output_section( $section['ID'], false );
			}

		}
	}

	/**
	 * Check if a template has an enabled section with auto output
	 *
	 * @param $template_name
	 *
	 * @return bool
	 * @since 1.2.1
	 *
	 */
	public function template_has_enabled_section_auto_output( $template_name ) {

		$ao = $this->type->fields->get_combined_output_locations();
		$has_specific_auto_output = false;
		foreach ( $ao as $ao_item ) {
			if ( isset( $ao_item['template'] ) && $ao_item['template'] === $template_name ) {
				$has_specific_auto_output = $ao_item;
				break;
			}
		}

		if ( $has_specific_auto_output ) {
			$has_section = $this->type->sections->has_enabled_section_by_output( $has_specific_auto_output['value'] );

			return $has_section;
		}

		return true;
	}

	/**
	 * Has Sections by Location
	 *
	 * @param $location
	 *
	 * @return bool
	 * @since 1.1.3
	 *
	 */
	public function has_sections_by_location( $location ) {
		$sections = $this->type->sections->get_sections_by_output( $location );
		return ! empty( $sections );
	}

	/**
	 * Output Sections by Output/Location Value
	 *
	 *
	 * @param      $location
	 * @param bool $return
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public function output_sections_by_location( $location, $return = false ){
		$sections = $this->type->sections->get_sections_by_output( $location );

		if( empty( $sections ) ){
			return false;
		}

		if( $return ){
			ob_start();
		}

		foreach( (array) $sections as $section ){
			$this->output_section( $section['ID'], false );
		}

		if( $return ){
			return ob_get_clean();
		}

		return true;
	}

	/**
	 * Output Section
	 *
	 * @param      $section_id
	 * @param bool $return
	 *
	 * @return string
	 */
	public function output_section( $section_id, $return = true ) {

		if( ! $this->is_enabled() ){
			return '';
		}

		$section = $this->type->sections->get_section( $section_id );

		if( ! $section || $section['post_status'] === 'disabled' || ! apply_filters( 'search_and_filtering_output_section_enabled', true, $section_id, $this ) ){
			return '';
		}

		/**
		 * Check if we're outputting a section that is a form, on the listings page, and setting is enabled to disable the form section type
		 */
		if( isset( $section['disable_form_on_listings_page'] ) && intval( $section['disable_form_on_listings_page'] ) === 1 && isset( $section['is_form'] ) && intval( $section['is_form'] ) === 1 ){
			$page_id = $this->type->get_form_page_id();
			if( ! empty( $page_id ) ){
				if( is_page( $page_id ) ){
					$section['is_form'] = "0";
				}
			}
		}

		$user_can_edit = current_user_can( $this->type->get_capability() ) ? 1 : 0;
		$frontend_script = ! empty( $user_can_edit ) ? 'wpjm-search-filtering-frontend-edit' : 'wpjm-search-filtering-frontend';
		$frontend_script = apply_filters( 'search_and_filtering_output_section_frontend_script', $frontend_script, $user_can_edit, $section, $this );
		$form_url = apply_filters( "search_and_filtering_{$this->type->slug}_form_url", $this->type->get_form_url(), $section_id, $this );
		$section['form_url'] = $form_url;

		/**
		 * We use this check, to make sure to only output the localized values once (as nonce should only be output once)
		 */
		if( ! $this->type->nonce_output ){

			if( ! wp_script_is( $frontend_script, 'registered' ) ){
				WPJMSf()->assets->register_frontend();
			}

			$data_sources = $this->type->fields->assets();

			$default_enable_editing = 0;
			$settings = get_option( $this->type->settings_option, array( 'editing_enabled' => 1, 'reset_hard_clear' => 1 ) );

			wp_localize_script( $frontend_script, "wpjmsf_frontend_nonce", wp_create_nonce( "wpjmsf_frontend_nonce" ) );

			if( $user_can_edit ){
				// Enabled by default if user can edit
				$default_enable_editing = 1;

				if( $settings && isset( $settings['editing_enabled'] ) && empty( $settings['editing_enabled'] ) ){
					$default_enable_editing = 0;
				}
			}

			$emc_enabled = get_option( "job_manager_empty_meta_cleanup_{$this->type->slug}_enable", false );
			$enable_editing = ! empty( $user_can_edit ) ? $default_enable_editing : 0;

			wp_localize_script( $frontend_script, 'wpjmsf_editing_status', array( 'enabled' => $enable_editing, 'can_edit' => $user_can_edit ) );
			wp_localize_script( $frontend_script, "wpjmsf_{$this->type->slug}_values", array(
				'form_url' => $form_url,
				'include_empty' => array(
					'exists' => function_exists( 'WPJM_Empty_Meta_Cleanup' ),
					'enabled' => ! empty( $emc_enabled )
				),
				'reset_hard_clear' => ! empty( $settings['reset_hard_clear'] ),
				'init_core_ajax' => apply_filters( 'search_and_filtering_init_core_ajax_fallback', $this->has_shortcode_enabled && $this->has_shortcode_disabled, $this ),
			) );

			// TODO: check if we need to generate and output these when non-admin
			wp_localize_script( $frontend_script, "wpjmsf_{$this->type->slug}_output_locations", $this->type->fields->get_section_output_locations() );
			wp_localize_script( $frontend_script, "wpjmsf_{$this->type->slug}_output_theme_locations", $this->type->fields->get_section_output_theme_locations() );

			wp_localize_script( $frontend_script, "wpjmsf_init_{$this->type->slug}_values", $this->parse_init_values( $data_sources ) );

			$this->type->nonce_output = true;

			do_action( 'search_and_filtering_section_output_init', $frontend_script, $user_can_edit, $this );
		}

		if ( ! wp_next_scheduled( 'job_manager_check_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'job_manager_check_cleanup' );
		}

		wp_localize_script( $frontend_script, "wpjmsf_section_{$section_id}", $section );

		wp_enqueue_script( $frontend_script );

		$output = "<div id=\"wpjmsf-section-{$section_id}\" class=\"wpjmsf-wrapper wpjmsf-{$this->type->slug}-section\" style=\"width: 100%; height: 100%;\"><search-and-filtering section=\"{$section_id}\" listing-type-slug=\"{$this->type->slug}\"></search-and-filtering></div>";

		if( isset( $section['styles'], $section['styles']['custom'] ) && ! empty( $section['styles']['custom'] ) ){

			$custom_styles = apply_filters( 'search_and_filtering_output_section_custom_styles', $section['styles']['custom'], $section_id, $section, $this );

			$custom_styles = stripslashes( stripslashes( wp_strip_all_tags( $custom_styles ) ) );

			if( ! empty( $custom_styles ) ){
				wp_add_inline_style( $frontend_script, $custom_styles );
			}
		}

		wp_enqueue_style( $frontend_script );

		if( apply_filters( 'search_and_filtering_output_enqueue_select2_style', true, $section, $return, $this ) ){
			wp_enqueue_style( 'select2' );
		}

		$output = apply_filters( 'search_and_filtering_output_section_output', $output, $section_id, $section, $return, $this );

		if( $return ){
			return $output;
		}

		echo $output;
	}

	/**
	 * Parse Initial Values
	 *
	 * This method parses values submitted in $_REQUEST (so either $_GET or $_POST), sanitizes them, and then checks
	 * for any specific taxonomy values that were used with slug instead of term id, converting the value output in JS
	 * to the term ID (as that is required for setting the value)
	 *
	 * @param array $data_sources
	 *
	 * @return array|mixed
	 * @since 1.0.0
	 */
	public function parse_init_values( $data_sources = array() ) {

		if( empty( $_REQUEST ) ){
			return array();
		}

		if( empty( $data_sources ) ){
			$data_sources = $this->type->fields->get_data_sources();
		}

		$raw_request = $_REQUEST;

		if ( isset( $raw_request['wpjmsf_config'] ) ) {
			unset( $raw_request['wpjmsf_config'] );
		}
		if ( isset( $raw_request['wpjmsf_taxonomies'] ) ) {
			unset( $raw_request['wpjmsf_taxonomies'] );
		}

		/**
		 * If page is loading from a Form section type (meaning POST via homepage, etc),
		 * we need to merge values from wpjmsf_fields into $_REQUEST to make sure all
		 * custom field values are also passed.
		 */
		if( isset( $_POST['wpjmsf_form_submit'], $_POST['wpjmsf_fields'] ) ){
			$custom = isset( $_POST['wpjmsf_custom'] ) ? $_POST['wpjmsf_custom'] : array();
			unset( $raw_request['wpjmsf_custom'] );
			unset( $raw_request['wpjmsf_fields'] );
			unset( $raw_request['wpjmsf_form_submit'] );

			$init_values_raw = array_merge( $raw_request, $_POST['wpjmsf_fields'], $custom );
		} else {
			$init_values_raw = $_REQUEST;
		}

		// First make sure to sanitize all request values
		$init_values = wpjmsf_sanitize_value( $init_values_raw );

		$slug_taxonomies = $this->type->get_taxonomy_slug_fields( true );

		$query_param_mappings = $this->type->get_query_param_search_maps();

		foreach( $query_param_mappings as $query_param => $mapped_param ){
			// First check if the query parameter exists
			if( isset( $init_values[ $query_param ] ) && ! empty( $init_values[ $query_param ] ) ){
				// Next make sure the one we're mapping it to is not set
				if( ! isset( $init_values[ $mapped_param ] ) || empty( $init_values[ $mapped_param ] ) ){
					$init_values[ $mapped_param ] = $init_values[ $query_param ];
				}

			}
		}

		$tax_mappings = $this->type->get_search_maps();

		// First check all core taxonomy field types
		foreach( (array) $tax_mappings as $taxonomy => $core_key ){

			// Skip taxonomies that should use slugs
			if( in_array( $taxonomy, $slug_taxonomies ) ){
				continue;
			}

			// If core key value exists, and is not numeric, means it's probably a slug (which is all we support for now)
			if( ! isset( $init_values[ $core_key ] ) || is_numeric( $init_values[ $core_key ] ) ){
				continue;
			}

			$term_object = get_term_by( 'slug', $init_values[ $core_key ], $taxonomy );

			if ( $term_object ) {
				// Set the value to the term_id, not the slug
				$init_values[ $core_key ] = $term_object->term_id;
			}
		}

		// Now check all other non-core taxonomy field types
		foreach( (array) $data_sources['taxonomies']['fields'] as $meta_key => $config ){
			// Skip taxonomies that should use slugs
			if ( in_array( $config['taxonomy'], $slug_taxonomies ) ) {
				continue;
			}

			if( ! isset( $init_values[ $meta_key ] ) || is_numeric( $init_values[ $meta_key ] ) ){
				continue;
			}

			$term_object = get_term_by( 'slug', $init_values[ $meta_key ], $config['taxonomy'] );

			if ( $term_object ) {
				// Set the value to the term_id, not the slug
				$init_values[ $meta_key ] = $term_object->term_id;
			}
		}

		return $init_values;
	}

	/**
	 * Magic Method to handle Action Method calls not defined
	 *
	 * @param $name Name of function/method being called
	 * @param $args Arguments called with function/method
	 *
	 * @since 0.1.1
	 *
	 */
	public function __call( $name, $args ) {

		if ( strpos( $name, 'search_and_filtering_' ) !== false ) {
			$this->action_output( $name, $args );
		}

	}

	/**
	 * Set Custom S&F Template was Triggered
	 *
	 * @since 1.1.3
	 *
	 */
	public function set_template_triggered() {
		$this->template_triggered = true;
	}
}
