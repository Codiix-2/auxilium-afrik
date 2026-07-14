<?php

namespace WPJMSF\Themes\Listify;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Job
 *
 * @package WPJMSF\Themes\Listify
 *
 * @since   0.1.1
 *
 */
class Job extends \WPJMSF\Theme {

	/**
	 * Class constructor.
	 */
	public function construct() {
		$this->widgets = new Job\Widgets( $this );

		add_filter( 'search_and_filtering_get_job_core_data_sources', array( $this, 'add_sort_options' ) );
		add_filter( 'search_and_filtering_add_meta_queries_skip_job_meta_keys', array( $this, 'skip_meta_query_keys' ) );
	}

	/**
	 * Type Enabled Constructor
	 *
	 * @since 1.0.0
	 *
	 */
	public function construct_enabled() {
		$this->require_file( 'pluggables.php' );
	}

	/**
	 * Custom CSS to Output
	 *
	 *
	 * @return string
	 * @since 0.1.1
	 *
	 */
	public function custom_css() {

		/**
		 * Listify hides job_types class when customizer setting has enabled for categories only .. but users
		 * in this plugin can still remove them manually themselves, so we want to make sure they are not hidden
		 * for any reason.
		 */
		return '.job_types { display: inherit !important; }';
	}

	/**
	 * Initialize Custom Theme JS Handling
   *
   * This method tells SectionGrid.vue to load the theme specific
   * handling, which is passed through wpjmsf_theme_config
	 *
	 * @return array|bool[]
	 * @since 1.0.0
	 *
	 */
	public function theme_config(){
		return array( 'init_handling' => true );
	}

	/**
	 * Add meta keys to skip when generating meta query
	 *
	 *
	 * @param $skip_keys
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function skip_meta_query_keys( $skip_keys ){
		$skip_keys['search_radius' ] = true;
		$skip_keys['search_lat'] = true;
		$skip_keys['search_lng'] = true;
		return $skip_keys;
	}

	/**
	 * Add Custom Listify Field Types
	 *
	 *
	 * @param $groups
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function field_type_groups( $groups ){
		$groups['listify'] = array(
			'label' => __( 'Listify Custom Field Types', 'wp-job-manager-search-and-filtering' ),
			'fields' => array(
				'listify_search_radius_slider' => array(
					'label' => __( 'Search Radius Slider', 'wp-job-manager-search-and-filtering' ),
					'custom' => true,
					'inputs' => array(
						'search_radius',
						'search_lat',
						'search_lng'
					),
					'supports' => array( 'no_search_source' )
				)
			)
		);

		return $groups;
	}

	/**
	 * Add Custom Listify Sort/Order Options to Default Order/Sort Options
	 *
	 *
	 * @param $sources
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function add_sort_options( $sources ) {
		$options = apply_filters( 'listify_filters_sort_by', listify_get_sort_options() );
		// Remove default options that already are set
		if( isset( $options['random'] ) ){
			unset( $options['random'] );
		}
		$built_options = \WPJMSF\Fields::build_options( $options );
		$sources['fields']['orderby']['options'] = array_merge( $sources['fields']['orderby']['options'], $built_options );
		return $sources;
	}

	/**
	 * Listify Fields Configuration
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function fields_config() {

		$radius = isset( $_GET['search_radius'] ) ? absint( $_GET['search_radius'] ) : get_theme_mod( 'map-behavior-search-default', 50 );

		$config = array(
			'select'                       => array(
				'wrapper' => array(
					'class' => array( 'select' )
				)
			),
			'listify_search_radius_slider' => array(
				'radi'          => sprintf( __( 'Radius: <span class="radi">%1$s</span> %2$s', 'listify', 'wp-job-manager-search-and-filtering' ), $radius, listify_results_map_unit() ),
				'search_radius' => isset( $_GET['search_radius'] ) ? absint( $_GET['search_radius'] ) : $radius,
				'search_lat'    => isset( $_GET['search_lat'] ) ? esc_attr( $_GET['search_lat'] ) : 0,
				'search_lng'    => isset( $_GET['search_lng'] ) ? esc_attr( $_GET['search_lng'] ) : 0
			)
		);

		return $config;
	}

	/**
	 * Listify Fields Configuration
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function sources_config(){

		$config = array(
			'search_location' => array(
				'wrapper' => array(
					'class' => array( 'search_location' )
				)
			),
		);

		return $config;
	}

	/**
	 * Listify Specific Output Locations
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function output_locations() {
		$locations = array(
			'listify_job_filters' => array(
				'label' => __( 'Listify - Listings Page', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label' => __( 'Top of Form', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_filters_listify_top',
						'add_filter' => array(
							'hook' => 'search_and_filtering_filters_listify_top',
							'callback' => array( $this, 'search_and_filtering_filters_listify_top' )
						)
					),
					array(
						'label' => __( 'Results Row', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_filters_listify_results',
						'add_filter' => array(
							'hook'     => 'search_and_filtering_filters_listify_results',
							'callback' => array( $this, 'search_and_filtering_filters_listify_results' )
						)
					),
					array(
						'label' => __( 'Bottom of Form', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_filters_listify_bottom'
					)
				)
			)
		);

		return $locations;
	}

	/**
	 * search_and_filtering_filters_listify_top callback
	 *
	 *
	 * @param $atts
	 *
	 * @since 0.1.1
	 *
	 */
	public function search_and_filtering_filters_listify_top( $atts ){
		if( ! $this->type->output->output_sections_by_location( 'search_and_filtering_filters_listify_top' ) ){
			echo \listify_partial_search_filters_archive( false, $atts );
		}
	}

	/**
	 * search_and_filtering_filters_listify_results callback
	 *
	 *
	 * @param $atts
	 *
	 * @since 0.1.1
	 *
	 */
	public function search_and_filtering_filters_listify_results( $atts ){
		if ( ! $this->type->output->output_sections_by_location( 'search_and_filtering_filters_listify_results' ) ) {
			?>
			<h3 class="archive-job_listing-found">
				<span class="results-found"><?php esc_html_e( 'Loading results...', 'listify', 'wp-job-manager-search-and-filtering' ); ?>
					<?php echo \Listify_WP_Job_Manager_Template_Filters::get_sort_filter( $atts ); ?>
			</h3>
			<?php
		}
	}
}