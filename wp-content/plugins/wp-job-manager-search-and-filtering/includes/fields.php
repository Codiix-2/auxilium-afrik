<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Fields
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Fields {
	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * @var array Array of object cached output locations combined (theme and default)
	 */
	public $output_locations = array();

	/**
	 * Fields constructor.
	 *
	 * @param $type
	 */
	public function __construct( $type ) {
		$this->type = $type;
//		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );
	}

	/**
	 * Build Options into Required value/label array format
	 *
	 *
	 * @param $arr_data
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public static function build_options( $arr_data ) {
		$default_options = array();
		foreach ( (array) $arr_data as $arr_data_val => $arr_data_label ) {

			/**
			 * Make sure any options from Field Editor that signify "disabled"
			 * are not added as an available option
			 */
			if( strpos( $arr_data_val, '~' ) !== false ){
				continue;
			}

			/**
			 * Make sure any options from Field Editor that signify "default"
			 * are removed from the value (to prevent value mangling)
			 */
			if( strpos( $arr_data_val, '*' ) ){
				$arr_data_val = str_replace( '*', '', $arr_data_val );
			}

			$default_options[] = array(
				'label' => $arr_data_label,
				'value' => $arr_data_val
			);
		}
		return $default_options;
	}

	/**
	 * Register/Enqueue/Localize Assets
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	public function assets() {
		$data_sources = $this->get_data_sources();
		$field_type_groups = $this->get_field_type_groups();

		wp_localize_script( 'wpjm-search-filtering-frontend', "wpjmsf_{$this->type->slug}_field_types", $field_type_groups );
		wp_localize_script( 'wpjm-search-filtering-frontend-edit', "wpjmsf_{$this->type->slug}_field_types", $field_type_groups );
		wp_localize_script( 'wpjm-search-filtering-frontend', "wpjmsf_{$this->type->slug}_data_sources", $data_sources );
		wp_localize_script( 'wpjm-search-filtering-frontend-edit', "wpjmsf_{$this->type->slug}_data_sources", $data_sources );
		return $data_sources;
	}

	/**
	 * Get Section Output Hooks
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_section_output_locations(){

		$locations = array(
			array(
				'label' => __( 'Do not automatically output', 'wp-job-manager-search-and-filtering' ),
				'value' => ''
			),
			array(
				'label' => __( 'Filters Top (above Main Filters)', 'wp-job-manager-search-and-filtering' ),
				'value' => 'search_and_filtering_filters_start'
			),
			array(
				'label' => __( 'Main Filters Area (below filters top)', 'wp-job-manager-search-and-filtering' ),
				'value' => 'search_and_filtering_filters_search'
			),
			array(
				'label' => __( 'Filters Bottom (below search bottom)', 'wp-job-manager-search-and-filtering' ),
				'value' => 'search_and_filtering_filters_end'
			),
			array(
				'label' => __( 'Below Listings (below listing results)', 'wp-job-manager-search-and-filtering' ),
				'value' => 'search_and_filtering_listings_end_after'
			),
		);

		return apply_filters( 'search_and_filtering_section_output_locations', $locations, $this );
	}

	/**
	 * Get Combined Auto Output Locations
	 *
	 * @param $force
	 *
	 * @return array|null
	 * @since 1.2.1
	 *
	 */
	public function get_combined_output_locations( $force = false ) {
		if( empty( $this->output_locations ) || $force ){
			$theme_locations = array();
			$theme = $this->get_section_output_theme_locations();
			foreach( $theme as $theme_location ){
				if( $theme_location['locations'] ) {
					$theme_locations = array_merge( $theme_locations, $theme_location['locations'] );
				}
			}

			$this->output_locations = array_merge( $this->get_section_output_locations(), $theme_locations );
		}
		return $this->output_locations;
	}

	/**
	 * Get Section Theme Output Hooks
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_section_output_theme_locations(){
		$locations = $this->type->theme && method_exists( $this->type->theme, 'get_output_locations' ) ? $this->type->theme->get_output_locations() : array();
		return apply_filters( 'search_and_filtering_section_output_theme_locations', $locations, $this );
	}

	/**
	 * Get Map Display Types
	 *
	 * @since 1.1.0
	 *
	 */
	public function get_map_display_types() {

		$zoom_levels = array(
			'1'  => __( '1x', 'wp-job-manager-search-and-filtering' ),
			'2'  => __( '2x', 'wp-job-manager-search-and-filtering' ),
			'3'  => __( '3x', 'wp-job-manager-search-and-filtering' ),
			'4'  => __( '4x', 'wp-job-manager-search-and-filtering' ),
			'5'  => __( '5x', 'wp-job-manager-search-and-filtering' ),
			'6'  => __( '6x', 'wp-job-manager-search-and-filtering' ),
			'7'  => __( '7x', 'wp-job-manager-search-and-filtering' ),
			'8'  => __( '8x', 'wp-job-manager-search-and-filtering' ),
			'9'  => __( '9x', 'wp-job-manager-search-and-filtering' ),
			'10' => __( '10x', 'wp-job-manager-search-and-filtering' ),
			'11' => __( '11x', 'wp-job-manager-search-and-filtering' ),
			'12' => __( '12x', 'wp-job-manager-search-and-filtering' ),
			'13' => __( '13x', 'wp-job-manager-search-and-filtering' ),
			'14' => __( '14x', 'wp-job-manager-search-and-filtering' ),
			'15' => __( '15x', 'wp-job-manager-search-and-filtering' ),
			'16' => __( '16x', 'wp-job-manager-search-and-filtering' ),
			'17' => __( '17x', 'wp-job-manager-search-and-filtering' ),
			'18' => __( '18x (Default Max)', 'wp-job-manager-search-and-filtering' ),
			'19' => __( '19x (may not show anything)', 'wp-job-manager-search-and-filtering' ),
			'20' => __( '20x (may not show anything)', 'wp-job-manager-search-and-filtering' ),
			'21' => __( '21x (may not show anything)', 'wp-job-manager-search-and-filtering' )
		);

		$general_map_settings = array(
//			array(
//				'label' => __( 'Zoom Level' ),
//				'prop'  => 'zoom',
//				'type'  => 'select',
//				'placeholder' => __( 'Auto (default/recommended)' ),
//				'options' => array_merge( array( 'auto' => __( 'Auto (recommended)' ) ), $zoom_levels ),
//				'desc' => __( 'The default zoom level when the map is updated with listings.  It is recommended to leave this at auto to allow the plugin to auto zoom based on listings displayed on the page.' )
//			),
			array(
				'label' => __( 'Min Zoom', 'wp-job-manager-search-and-filtering' ),
				'prop'  => 'minZoom',
				'type'  => 'select',
				'placeholder' => __( '0 (default)', 'wp-job-manager-search-and-filtering' ),
				'options' => array( '0' => __( '0 (No Zoom)', 'wp-job-manager-search-and-filtering' ) ) + $zoom_levels,
				'desc' => __( 'The minimum amount of zoom that the map will show.  By default this is 0, meaning no zoom, showing the entire world.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label' => __( 'Max Zoom', 'wp-job-manager-search-and-filtering' ),
				'prop'  => 'maxZoom',
				'type'  => 'select',
				'placeholder' => __( '18 (default)', 'wp-job-manager-search-and-filtering' ),
				'options' => $zoom_levels,
				'desc' => __( 'The maximum amount that the map can be zoomed in.  By default this is 18x, as anything over 18x potentially will not show anything.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label' => __( 'Default Zoom', 'wp-job-manager-search-and-filtering' ),
				'prop' => 'default_zoom',
				'type' => 'select',
				'placeholder' => __( '5x (default)', 'wp-job-manager-search-and-filtering' ),
				'options' => $zoom_levels,
				'desc' => __( 'Default zoom when the page initially loads (before any listings are loaded).', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label' => __( 'Center Point', 'wp-job-manager-search-and-filtering' ),
				'prop' => 'center_point',
				'type' => 'text',
				'desc' => __( 'Latitude and Longitude, separated by comma, to use for center point on map when initially loaded (example: 28.5383,-81.3792)', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'    => __( 'Move Map', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'move_map',
				'type'     => 'checkbox',
				'cb_label' => __( 'Yes, move the map outside the section grid', 'wp-job-manager-search-and-filtering' ),
				'checked'   => array(
					'hide' => array(),
					'show' => array( 'move_to_element', 'move_to_element_height' )
				),
				'unchecked' => array(
					'hide' => array( 'move_to_element', 'move_to_element_height' ),
					'show' => array()
				),
			),
			array(
				'label'       => __( 'Move to Element', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'move_to_element',
				'type'        => 'text',
				'placeholder' => '#some-other-html-element',
				'desc'        => __( 'In the situation you want to move the map somewhere else on the page (other than inside the grid), enter the HTML Element selector here.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Map Height', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'move_to_element_height',
				'type'        => 'text',
				'placeholder' => '300px',
				'desc'        => __( 'If you move the map outside the section grid you MUST specify a height otherwise it will not show.  Enter a valid CSS height (can NOT be %).  300px will be used by default.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Touch Text', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'gesture_touch_text',
				'type'        => 'text',
				'placeholder' => 'Use two fingers to move the map',
				'desc'        => __( 'When someone attempts to move the map on a smaller device (like mobile), this is text shown when not using "two fingers" to move the map.  Language should be auto detected, but you can set a static value to use here if you wish. Leave blank to use default. If any of these are set, you must set the other values as well (or defaults will be used).', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Scroll Text', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'gesture_scroll_text',
				'type'        => 'text',
				'placeholder' => 'Use ctrl + scroll to zoom the map',
				'desc'        => __( 'When someone attempts to use scroll button on the map, this is text shown when NOT holding CTRL (Windows) to zoom in or out. Language should be auto detected, but you can set a static value to use here if you wish. Leave blank to use default. If any of these are set, you must set the other values as well (or defaults will be used).', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Scroll Text (Mac)', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'gesture_scroll_mac_text',
				'type'        => 'text',
				'placeholder' => 'Use ⌘ + scroll to zoom the map',
				'desc'        => __( 'When someone attempts to use scroll button on the map, this is text shown when NOT holding ⌘ (Mac/OSX) to zoom in or out. Language should be auto detected, but you can set a static value to use here if you wish. Leave blank to use default. If any of these are set, you must set the other values as well (or defaults will be used).', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Gesture Duration', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'gesture_duration',
				'type'        => 'text',
				'placeholder' => '1000',
				'desc'        => __( 'The amount of time to show the gesture (touch/scroll) overlay in milliseconds (1 second = 1000 milliseconds).  Leave blank for default (1 second = 1000 milliseconds)', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Hover Icon Size', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'hover_icon_size',
				'type'        => 'text',
				'placeholder' => '1',
				'desc'        => __( 'When a listing is hovered over in the results/list, multiple the size of the associated map icon by this value. Default is 1 (does nothing). This only works for single markers and does nothing for clustering icon.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'    => __( 'Clustering', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'disable_clustering',
				'type'     => 'select',
				'placeholder' => __( 'Auto', 'wp-job-manager-search-and-filtering' ),
				'desc'     => __( 'Set what zoom level clustering (grouping of markers into single marker with count) should be disabled and single markers shown. If set, at this zoom level and below, markers will not be clustered', 'wp-job-manager-search-and-filtering' ),
				'options'  => array( 'auto' => __( 'Auto (default)', 'wp-job-manager-search-and-filtering' ), 'disabled' => __( 'Disable Clustering', 'wp-job-manager-search-and-filtering' ) ) + $zoom_levels
			),
			array(
				'label'       => __( 'Cluster Max Radius', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'maxClusterRadius',
				'type'        => 'text',
				'placeholder' => '80',
				'desc'        => __( 'The maximum radius that a cluster will cover from the central marker (in pixels). Default 80. Decreasing will make more, smaller clusters. ', 'wp-job-manager-search-and-filtering' )
			)
		);

		$types = array(
			'google_leaflet_map'   => array(
				'label'    => __( 'Google Map', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'no_search_source', 'type_config', 'no_auto_size', 'no_checkbox' ),
				'type_config' => array_merge( array(
					                              array(
						                              'label'    => __( 'Google API Key', 'wp-job-manager-search-and-filtering' ),
						                              'prop'     => 'google_api_key',
						                              'type'     => 'text',
						                              'required' => true,
						                              'desc'     => sprintf( __( 'Enter the client side API key to use for the GeoLocation.  This key should be different from any server-side keys you use.  Aquire an API key from the <a href="%s">Google Maps API Developer Site</a>.', 'wp-job-manager-search-and-filtering' ), 'https://developers.google.com/maps/documentation/geocoding/get-api-key' ),
					                              ),
					                              array(
						                              'label' => __( 'Custom Language', 'wp-job-manager-search-and-filtering' ),
						                              'prop'  => 'language',
						                              'type'  => 'text',
						                              'desc'  => sprintf( __( 'By default, the Maps JavaScript API uses the user\'s preferred language setting as specified in the browser (if this field is blank), to set a specific language, enter a valid language code from <a href="%s" target="_blank">Google Maps Supported Language Codes</a>.', 'wp-job-manager-search-and-filtering' ), 'https://developers.google.com/maps/documentation/javascript/localization' ),
					                              ),
					                              array(
						                              'label' => __( 'Custom Region', 'wp-job-manager-search-and-filtering' ),
						                              'prop'  => 'region',
						                              'type'  => 'text',
						                              'desc'  => sprintf( __( 'To set a custom region, enter a valid region code from <a href="%s" target="_blank">Google Maps Supported Region</a>.', 'wp-job-manager-search-and-filtering' ), 'https://developers.google.com/maps/documentation/javascript/localization#Region' ),
					                              ),
				                              ), $general_map_settings )
			),
			'osm_leaflet_map'   => array(
				'label'    => __( 'OpenStreetMap Map', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'no_search_source', 'type_config', 'no_auto_size', 'no_checkbox' ),
				'type_config' => array_merge( array(), $general_map_settings )
			),
			'mapbox_leaflet_map'   => array(
				'label'    => __( 'MapBox Map', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'no_search_source', 'type_config', 'no_auto_size', 'no_checkbox' ),
				'type_config' => array_merge( array(
					                              array(
						                              'label'    => __( 'Mapbox Token', 'wp-job-manager-search-and-filtering' ),
						                              'prop'     => 'mapbox_token',
						                              'type'     => 'text',
						                              'required' => true,
						                              'desc'     => sprintf( __( 'Enter the mapbox token to use for the GeoLocation.  Obtain this from the <a href="%s">Mapbox website</a>.', 'wp-job-manager-search-and-filtering' ), 'https://mapbox.com' ),
					                              ),
				                              ), $general_map_settings )
			)
		);

		return apply_filters( 'search_and_filtering_map_field_types', $types, $this );
	}

	/**
	 * Get Field Types (Input/Design/Etc)
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_field_type_groups(){
		$inputs = new Fields\Input( $this->type );

		$groups = array(
			'input' => array(
				'label' => __( 'Input Field Types', 'wp-job-manager-search-and-filtering' ),
				'fields' => $inputs->get_field_types()
			),
			'design' => array(
				'label' => __( 'Design Field Types', 'wp-job-manager-search-and-filtering' ),
				'fields' => $this->get_design_field_types()
			),
			'maps' => array(
				'label' => __( 'Map Display Types', 'wp-job-manager-search-and-filtering' ),
				'fields' => $this->get_map_display_types()
			),
			'other' => array(
				'label' => __( 'Other Field Types', 'wp-job-manager-search-and-filtering' ),
				'fields' => $this->get_other_field_types()
			),
			'wp' => array(
				'label' => __( 'WordPress Field Types', 'wp-job-manager-search-and-filtering' ),
				'fields' => $this->get_wp_field_types()
			)
		);

		return apply_filters( 'search_and_filtering_field_type_groups', $groups, $this );
	}

	/**
	 * Get Other Field Types
	 *
	 *
	 * @return mixed|void
	 * @since 1.1.0
	 *
	 */
	public function get_other_field_types() {

		$types = array(
			'hidden'   => array(
				'label'    => __( 'Hidden', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'default', 'multiple_values', 'no_auto_size', 'no_checkbox', 'no_wrapper_classes', 'no_label', 'no_source_trigger' ),
			)
		);

		return apply_filters( 'search_and_filtering_design_field_types', $types, $this );
	}

	/**
	 * Get Design Field Types
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_design_field_types(){

		$types = array(
			'html' => array(
				'label' => __( 'HTML Content', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'html', 'no_search_source' )
			),
			'spacer' => array(
				'label' => __( 'Spacer', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'no_search_source' )
			)
		);

		return apply_filters( 'search_and_filtering_design_field_types', $types, $this );
	}

	/**
	 * Get WordPress Field Types
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_wp_field_types() {

		$types = array(
			'tag_cloud' => array(
				'label'       => __( 'Tag Cloud', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'only_taxonomies', 'add_to_url', 'default', 'compare_relation', 'auto_update', 'input_classes', 'auto_hide', 'multiple_values' ),
				'type_config' => array(
					array(
						'label'       => __( 'Smallest Font Size', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'smallest',
						'type'        => 'text',
						'placeholder' => '8',
						'desc'        => __( 'Smallest font size used to display tags. Paired with the value of unit, to determine CSS text size unit. Default 8 (pt).', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'       => __( 'Largest Font Size', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'largest',
						'type'        => 'text',
						'placeholder' => '22',
						'desc'        => __( 'Largest font size used to display tags. Paired with the value of unit, to determine CSS text size unit. Default 22 (pt).', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'       => __( 'Unit', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'unit',
						'type'        => 'text',
						'placeholder' => 'pt',
						'desc'        => __( 'CSS text size unit to use with the $smallest and $largest values. Accepts any valid CSS text size unit. Default "pt"', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'       => __( 'Number', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'number',
						'type'        => 'text',
						'placeholder' => '0',
						'desc'        => __( 'The max number of tags to output. Accepts any positive integer or zero to return all. Default 0.', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'       => __( 'Format', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'format',
						'type'        => 'select',
						'placeholder' => __( 'Flat (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'flat' => __( 'Flat (default)', 'wp-job-manager-search-and-filtering' ),
							'list' => __( 'List', 'wp-job-manager-search-and-filtering' )
						),
						'desc'        => __( 'Format to display the tag cloud in. Flat (tags separated with spaces), or "list" (tags displayed in an unordered list)', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'       => __( 'Separator', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'separator',
						'type'        => 'textarea_html',
						'placeholder' => '\n ' . __( '(new line)', 'wp-job-manager-search-and-filtering' ),
						'desc'        => __( 'HTML or text to separate the tags. Default "\n" (newline)', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'       => __( 'Order By', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'orderby',
						'type'        => 'select',
						'placeholder' => __( 'Name (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'name'  => __( 'Name (default)', 'wp-job-manager-search-and-filtering' ),
							'count' => __( 'Count', 'wp-job-manager-search-and-filtering' )
						),
						'desc'        => __( 'Value to order tags by', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'       => __( 'Order', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'order',
						'type'        => 'select',
						'placeholder' => __( 'Ascending (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'ASC'  => __( 'Ascending (default)', 'wp-job-manager-search-and-filtering' ),
							'DESC' => __( 'Descending', 'wp-job-manager-search-and-filtering' ),
							'RAND' => __( 'Random', 'wp-job-manager-search-and-filtering' ),
						),
						'desc'        => __( 'How to order the tags', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'    => __( 'Show Count', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'show_count',
						'type'     => 'checkbox',
						'cb_label' => __( 'Yes, show the tag count with tag', 'wp-job-manager-search-and-filtering' ),
						'desc'     => __( 'Whether or not to show the tag count with the tag (default no/unchecked)', 'wp-job-manager-search-and-filtering' ),
						'default'  => false
					),
				)
			),
		);

		return apply_filters( 'search_and_filtering_wp_field_types', $types, $this );
	}

	/**
	 * Get Formatted Data Options for Taxonomies
	 *
	 *
	 * @param $taxonomy
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function get_taxonomy_data_options( $taxonomy ){
		$slug_fields = $this->type->get_taxonomy_slug_fields( true );
		$is_slug_taxonomy = in_array( $taxonomy, $slug_fields );

		$tax = new Fields\Taxonomies( $taxonomy, $is_slug_taxonomy, $this->type );
		$options = $tax->get_data_options();

		return apply_filters( 'search_and_filtering_taxonomy_data_options', $options, $this );
	}

	/**
	 * Get Taxonomies (with data) from Fields
	 *
	 * This method extracts taxonomy field types from passed meta fields, removing them from the passed
	 * fields array, and returning them, so they can be output in a separate taxonomies section
	 *
	 *
	 * @param &$fields
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function get_taxonomy_data_sources( &$fields ) {

		$taxes = array();
		$tax_text_fields = $this->type->get_taxonomy_text_fields();

		foreach ( (array) $fields as $meta_key => $config ) {

			$is_tax_field = false;

			/**
			 * This is required as some taxonomy fields do not actually have the taxonomy
			 * set on them in the configuration.
			 */
			if( array_key_exists( $meta_key, $tax_text_fields ) ){
				$config['taxonomy'] = $tax_text_fields[ $meta_key ]['taxonomy'];
				$is_tax_field = true;
			}

			/**
			 * We need to check for taxonomy since some fields may have old stale
			 * taxonomy key set on them for some reason
			 */
			if ( isset( $config['taxonomy'], $config['type'] ) ) {

				$is_tax_field_type = in_array( $config['type'], array( 'term-select', 'term-multiselect', 'term-checklist' ) );

				$is_taxonomy_field = apply_filters( 'search_and_filtering_get_taxonomy_data_sources_is_taxonomy_field', $is_tax_field_type || $is_tax_field, $meta_key, $config, $fields );

				if ( $is_taxonomy_field ) {
					// Add to taxonomies
					$taxes[ $meta_key ] = $config;
					$taxes[ $meta_key ]['options'] = $this->get_taxonomy_data_options( $config['taxonomy'] );
					// Remove from list of fields
					unset( $fields[ $meta_key ] );
				}

			}

		}

		return $taxes;
	}

	/**
	 * Get Listing Meta Fields
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_field_data_sources() {
		$fields = $this->type->get_fields();

		$multi_value_field_types = apply_filters( 'search_and_filtering_get_field_data_sources_multi_value_field_types', array( 'multiselect', 'checklist' ), $this );

		foreach( (array) $fields as $meta_key => $field ){
			if( array_key_exists( 'options', $field ) ){
				$fields[ $meta_key ][ 'options' ] = self::build_options( $fields[ $meta_key ]['options'] );
			}

			/**
			 * Add wpjmsf_multi_value to field configuration, so when the user selects this meta field,
			 * we know the value will be a "multi" value (saved as a serialized array)
			 */
			if( isset( $field['type'] ) && in_array( $field['type'], $multi_value_field_types ) ){
				$fields[ $meta_key ]['wpjmsf_multi_value'] = true;
			}
		}

		return $fields;
	}

	/**
	 * Get Custom Search Sources
	 *
	 * Custom search sources are sources that are not related to any specific meta key or taxonomy, and handled
	 * specifically by custom code either in this plugin, or other extending plugins or themes (must add own handling).
	 *
	 * These field types will not be added to the normal search query that is sent, they will be sent in the `wpjmsf_custom`
	 * POST variable (so you should look there if adding custom ones) and then adjust the query accordingly.
	 *
	 * These "keys" should be as unique as possible to prevent conflict with existing meta keys or fields, in a general sense
	 * any added through this plugin will be prepended with c_ before them, to signify a custom (non-field related value)
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 *
	 */
	public function get_custom_sources() {

		/**
		 * not_supports is specifically for TypeConfig field values
		 */

		$sources = array(
			'c_search_radius' => array(
				'label' => __( 'Location Search Radius', 'wp-job-manager-search-and-filtering' ),
				'not_supports' => array( 'auto_min_max', 'include_empty' ),
				'supports' => array( 'no_include_empty', 'search_compare' ),
				'type_config' => array(
					array(
						'label'       => __( 'Radius Unit', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'radius_unit',
						'type'        => 'select',
						'send_config' => true,
						'placeholder' => __( 'Miles (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'mi'  => __( 'Miles (default)', 'wp-job-manager-search-and-filtering' ),
							'km' => __( 'Kilometres', 'wp-job-manager-search-and-filtering' ),
						),
						'desc'        => __( 'The unit of measurement to use for location search radius. If your users are expecting the slider to represent kilometers, make sure to change to kilometers otherwise results will be incorrect.  This value is used to determine the radius of the earth when doing calculations.  If you create a field for the user to select radius unit, this will be used as the default value when page loads, and a user selected value will always override this value.', 'wp-job-manager-search-and-filtering' ),
					),
				)
			),
			'c_radius_unit' => array(
				'label' => __( 'Location Search Radius Unit (Miles/Kilometres)', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'no_include_empty' ),
				'not_supports' => array( 'include_empty' ),
				'search_compare_opt' => array( '=', '!=', '>', '>=', '<', '<=' ),
				'search_compare_2_opt' => array( '=', '!=', '>', '>=', '<', '<=' ),
				'options' => array(
					array(
						'value' => 'mi',
						'label' => __( 'Miles', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'value' => 'km',
						'label' => __( 'Kilometres', 'wp-job-manager-search-and-filtering' )
					)
				)
			)
		);

		return apply_filters( 'search_and_filtering_get_custom_sources', $sources, $this );
	}

	/**
	 * Get Geolocation Data Sources
	 *
	 * @return array
	 * @since 1.2.1
	 *
	 */
	public function get_geolocation_data_sources() {
		$fields = array(
			'_geolocation_city' => array(
				'label' => __( 'City', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_country_long' => array(
				'label' => __( 'Country Long', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_country_short' => array(
				'label' => __( 'Country Short', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_formatted_address' => array(
				'label' => __( 'Formatted Address', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_state_long' => array(
				'label' => __( 'State Long', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_state_short' => array(
				'label' => __( 'State Short', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_street' => array(
				'label' => __( 'Street', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_street_number' => array(
				'label' => __( 'Street Number', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_zipcode' => array(
				'label' => __( 'Zip Code', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_postcode' => array(
				'label' => __( 'Postcode', 'wp-job-manager-search-and-filtering' ),
			),
			'_geolocation_lat' => array(
				'label' => __( 'Latitude', 'wp-job-manager-search-and-filtering' ),
				'not_supports' => array( 'auto_min_max', 'include_empty' ),
				'supports' => array( 'no_include_empty', 'search_compare' ),
				'search_compare_opt' => array( '=', '!=', '>', '>=', '<', '<=' ),
				'search_compare_2_opt' => array( '=', '!=', '>', '>=', '<', '<=' ),
			),
			'_geolocation_long' => array(
				'label' => __( 'Longitude', 'wp-job-manager-search-and-filtering' ),
				'not_supports' => array( 'auto_min_max', 'include_empty' ),
				'supports' => array( 'no_include_empty', 'search_compare' ),
				'search_compare_opt' => array( '=', '!=', '>', '>=', '<', '<=' ),
				'search_compare_2_opt' => array( '=', '!=', '>', '>=', '<', '<=' ),
			),
		);

		return apply_filters( 'search_and_filtering_get_geolocation_sources', $fields, $this );
	}

	/**
	 * Get Search/Filter Data Sources
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_data_sources() {

		$fields      = $this->get_field_data_sources();
		$custom      = $this->get_custom_sources();
		$taxonomies  = $this->get_taxonomy_data_sources( $fields );
		$core        = $this->type->get_core_data_sources();

		// Add taxonomy 'options' to core field types
		if( is_array( $core ) && isset( $core['fields'] ) ){
			foreach ( (array) $core['fields'] as $core_field_slug => $core_field_config ) {
				if( array_key_exists( 'taxonomy', $core_field_config ) ){
					$core['fields'][ $core_field_slug ]['options'] = $this->get_taxonomy_data_options( $core_field_config['taxonomy'] );
				}
			}
		}

		$sources = array(
			'core'       => $core,
			'taxonomies' => array(
				'label'  => __( 'Listing Taxonomies', 'wp-job-manager-search-and-filtering' ),
				'fields' => $taxonomies
			),
			'meta'       => array(
				'label'  => __( 'Listing Meta Fields', 'wp-job-manager-search-and-filtering' ),
				'fields' => $fields
			),
			'custom'       => array(
				'label'  => __( 'Custom', 'wp-job-manager-search-and-filtering' ),
				'fields' => $custom
			),
		);

		if( $this->type->geolocation_enabled() ){
			$sources['geolocation'] = array(
				'label'  => __( 'Geolocation', 'wp-job-manager-search-and-filtering' ),
				'fields' => $this->get_geolocation_data_sources()
			);
		}

		return apply_filters( 'search_and_filtering_get_data_sources', $sources, $fields, $taxonomies, $core, $this );
	}

}