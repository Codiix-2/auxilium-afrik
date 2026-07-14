<?php

namespace WPJMSF\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Input
 *
 * @package WPJMSF
 *
 * @since   1.0.1
 *
 */
class Input {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

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
	 * Get Locate Me Types
	 *
	 * @return array
	 * @since 1.0.1
	 *
	 */
	public function get_locate_result_types() {
		$locate_result_types = array(
			'street_address'              => __( 'Street Address', 'wp-job-manager-search-and-filtering' ),
			'route'                       => __( 'Nearby Address Range (Route)', 'wp-job-manager-search-and-filtering' ),
			'intersection'                => __( 'Nearby Major Intersection', 'wp-job-manager-search-and-filtering' ),
			'political'                   => __( 'Political', 'wp-job-manager-search-and-filtering' ),
			'country'                     => __( 'Country', 'wp-job-manager-search-and-filtering' ),
			'administrative_area_level_1' => __( 'Administrative Level 1 (State)', 'wp-job-manager-search-and-filtering' ),
			'administrative_area_level_2' => __( 'Administrative Level 2 (County)', 'wp-job-manager-search-and-filtering' ),
			'administrative_area_level_3' => __( 'Administrative Level 3', 'wp-job-manager-search-and-filtering' ),
			'administrative_area_level_4' => __( 'Administrative Level 4', 'wp-job-manager-search-and-filtering' ),
			'administrative_area_level_5' => __( 'Administrative Level 5', 'wp-job-manager-search-and-filtering' ),
			'locality'                    => __( 'City/Town (Locality)', 'wp-job-manager-search-and-filtering' ),
			'sublocality'                 => __( 'Sub Locality', 'wp-job-manager-search-and-filtering' ),
			'neighborhood'                => __( 'Neighborhood', 'wp-job-manager-search-and-filtering' ),
			'premise'                     => __( 'Street Address/Named Location (premise)', 'wp-job-manager-search-and-filtering' ),
			'subpremise'                  => __( 'Sub Premise', 'wp-job-manager-search-and-filtering' ),
			'plus_code'                   => __( 'Plus Code', 'wp-job-manager-search-and-filtering' ),
			'postal_code'                 => __( 'Postal Code', 'wp-job-manager-search-and-filtering' ),
			'natural_feature'             => __( 'Natural Feature', 'wp-job-manager-search-and-filtering' ),
			'airport'                     => __( 'Airport', 'wp-job-manager-search-and-filtering' ),
			'park'                        => __( 'Park', 'wp-job-manager-search-and-filtering' ),
			'point_of_interest'           => __( 'Point of Interest', 'wp-job-manager-search-and-filtering' ),
		);
		return $locate_result_types;
	}

	/**
	 * Get Google Places AutoSuggest TypeConfig Options
	 *
	 * @param bool $include_api_field
	 *
	 * @return array[]
	 * @since 1.1.0
	 */
	public function get_places_autosuggest_type_config( $include_api_field = false ) {
		$fields = array(
			array(
				'label'       => __( 'Result Type', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'autocomplete_types',
				'placeholder' => __( 'All (default)', 'wp-job-manager-search-and-filtering' ),
				'type'        => 'select',
				'options'     => array(
					'geocode'       => __( 'Geocode Results', 'wp-job-manager-search-and-filtering' ),
					'address'       => __( 'Address', 'wp-job-manager-search-and-filtering' ),
					'establishment' => __( 'Business Results (establishment)', 'wp-job-manager-search-and-filtering' ),
					'(regions)'     => __( 'State/County/Country/City (regions)', 'wp-job-manager-search-and-filtering' ),
					'(cities)'      => __( 'City/Town (Locality)', 'wp-job-manager-search-and-filtering' )
				),
				'desc'        => __( 'If you only want specific types to be returned, select it from this list and only those results will show in the dropdown.  Default is all.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Countries', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'autocomplete_countries',
				'placeholder' => __( 'All (default)', 'wp-job-manager-search-and-filtering' ),
				'type'        => 'text',
				'desc'        => sprintf( __( 'If you want to restrict results to only specific countries, enter the two-character ISO 3166-1 Alpha-2 compatible country code.  Max is 5 countries, separated with a comma.  List of codes can be found on <a href="%s" target="_blank">Wikipedia</a>.', 'wp-job-manager-search-and-filtering' ), 'https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes' )
			),
			array(
				'label'    => __( 'Strip Country', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'autocomplete_strip_country',
				'type'     => 'checkbox',
				'cb_label' => __( 'Yes, strip the country from the value when not by itself', 'wp-job-manager-search-and-filtering' ),
				'desc'     => __( 'When specific result types are chosen (ie State/County/Country/City), if the country is not the only value selected, when this option is enabled, it will be stripped from the value sent to the server.  This requires value(s) to be set in Countries field above to work correctly.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'    => __( 'API Region', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'autocomplete_region',
				'type'     => 'text',
				'cb_label' => __( 'Yes, strip the country from the value when not by itself', 'wp-job-manager-search-and-filtering' ),
				'desc'     => sprintf( __( "When you load the Maps JavaScript API it applies a default bias for application behavior towards the United States. Enter a value here to override this. The region parameter accepts Unicode region subtag identifiers which (generally) have a one-to-one mapping to country code Top-Level Domains (ccTLDs). Most Unicode region identifiers are identical to <a href=\"%s\" target=\"_blank\">ISO 3166-1 codes</a>, with some notable exceptions. For example, Great Britain's ccTLD is “uk” (corresponding to the domain .co.uk) while its region identifier is GB.  NOTE: this only works if some a theme or plugin has not already loaded places/maps API.", 'wp-job-manager-search-and-filtering' ), 'https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes' )
			),
			array(
				'label'       => __( 'Disable Radius', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'autocomplete_disable_radius',
				'type'        => 'select_multiple',
				'placeholder' => __( 'Do not disable radius or radius unit', 'wp-job-manager-search-and-filtering' ),
				'options'     => $this->get_locate_result_types(),
				'desc'        => __( 'When a user selects any of the result types selected here, the radius and radius unit (if exists) will automatically be disabled and hidden. This will most likely be needed for State and Country selections to work correctly.', 'wp-job-manager-search-and-filtering' ),
			)
		);

		if( $include_api_field ){
			$fields[] = array(
				'label'    => __( 'Google API key', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'api_key',
				'type'     => 'text',
				'required' => true,
				'desc'     => sprintf( __( 'Enter the client side API key to use for the GeoLocation.  This key should be different from any server-side keys you use.  Aquire an API key from the <a href="%s">Google Maps API Developer Site</a>.', 'wp-job-manager-search-and-filtering' ), 'https://developers.google.com/maps/documentation/geocoding/get-api-key' ),
			);
		}

		return $fields;
	}

	/**
	 * Get Slider Type Config
	 *
	 * @return array[]
	 * @since 1.0.1
	 *
	 */
	public function get_slider_type_config() {
		$slider_type_config = array(
			array(
				'label'     => __( 'Auto Min/Max', 'wp-job-manager-search-and-filtering' ),
				'cb_label'  => __( 'Yes, automatically detect min and max', 'wp-job-manager-search-and-filtering' ),
				'prop'      => 'auto_min_max',
				'type'      => 'checkbox',
				'checked'   => array(
					'hide' => array( 'min', 'max' ),
					'show' => array()
				),
				'unchecked' => array(
					'hide' => array(),
					'show' => array( 'min', 'max' )
				),
				'desc'      => __( 'Instead of using a defined mix/max, this will automatically detect the min and max based on the values from all existing listings.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label' => __( 'Min', 'wp-job-manager-search-and-filtering' ),
				'prop'  => 'min',
				'type'  => 'number',
				'desc'  => __( 'Minimum value that can be selected (default 0)', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label' => __( 'Max', 'wp-job-manager-search-and-filtering' ),
				'prop'  => 'max',
				'type'  => 'number',
				'desc'  => __( 'Maximum value that can be selected (default 100)', 'wp-job-manager-search-and-filtering' )
			),
//			array(
//				'label'     => __( 'Filtered Listings' ),
//				'cb_label'  => __( 'Yes, automatically update min/max based on filtered/shown listings only.' ),
//				'prop'      => 'auto_min_max_filtered',
//				'type'      => 'checkbox',
//				'desc'      => __( 'By default the auto min/max feature does this for all listings on your site.  Enable this setting, to automatically update the min/max values based on ONLY the filtered or shown listings.' )
//			),
			array(
				'label'       => __( 'Interval', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'interval',
				'type'        => 'number',
				'placeholder' => '1',
				'desc'        => __( 'The interval between two values. The value must be greater than 0 and MUST be divisible by (max - min)', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Tooltip', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'tooltip',
				'type'        => 'select',
				'placeholder' => __( 'Active (default)', 'wp-job-manager-search-and-filtering' ),
				'options'     => array(
					'none'   => __( 'None', 'wp-job-manager-search-and-filtering' ),
					'always' => __( 'Always', 'wp-job-manager-search-and-filtering' ),
					'focus'  => __( 'Focus', 'wp-job-manager-search-and-filtering' ),
					'hover'  => __( 'Hover', 'wp-job-manager-search-and-filtering' ),
					'active' => __( 'Active (default)', 'wp-job-manager-search-and-filtering' )
				),
				'desc'        => __( 'When to show tooltip (if enabled)', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Tooltip Placement', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'tooltipPlacement',
				'type'        => 'select',
				'placeholder' => __( 'Top (default)', 'wp-job-manager-search-and-filtering' ),
				'options'     => array(
					'top'    => __( 'Top (default)', 'wp-job-manager-search-and-filtering' ),
					'bottom' => __( 'Bottom', 'wp-job-manager-search-and-filtering' ),
					'left'   => __( 'Left', 'wp-job-manager-search-and-filtering' ),
					'right'  => __( 'Right', 'wp-job-manager-search-and-filtering' )
				),
				'desc'        => __( 'The placement of the tooltip.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label' => __( 'Prepend', 'wp-job-manager-search-and-filtering' ),
				'prop'  => 'prepend',
				'type'  => 'text',
				'desc'  => __( 'Any specific value you want to prepend on the value display output (before value).  If you want to use a space before or after any kind of string, you must use the string below, otherwise sanitizer will strip spaces before or after.', 'wp-job-manager-search-and-filtering' ),
				'codes' => array(
					'space' => '&nbsp;'
				)
			),
			array(
				'label' => __( 'Append', 'wp-job-manager-search-and-filtering' ),
				'prop'  => 'append',
				'type'  => 'text',
				'desc'  => __( 'Any specific value you want to append on the value display output (after value).  If you want to use a space before or after any kind of string, you must use the string below, otherwise sanitizer will strip spaces before or after.', 'wp-job-manager-search-and-filtering' ),
				'codes' => array(
					'space' => '&nbsp;'
				)
			),
			array(
				'label'     => __( 'Format', 'wp-job-manager-search-and-filtering' ),
				'prop'      => 'format',
				'el_option' => false,
				'type'      => 'checkbox',
				'cb_label'  => __( 'Yes, format numbers when displayed', 'wp-job-manager-search-and-filtering' ),
				'desc'      => __( 'Uses JavaScript NumberFormat to output formatted number values (ie 3000 will show as 3,000)', 'wp-job-manager-search-and-filtering' )
			)
		);
		return $slider_type_config;
	}

	/**
	 * Get Element UI Select Type Config
	 *
	 * @return array[]
	 * @since 1.0.1
	 *
	 */
	public function get_eui_select_type_config() {
		$eui_select_config = array(
			array(
				'label'    => __( 'Clearable', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'clearable',
				'type'     => 'checkbox',
				'cb_label' => __( 'Yes, make selection clearable', 'wp-job-manager-search-and-filtering' ),
			),
			array(
				'label'    => __( 'Filterable', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'filterable',
				'type'     => 'checkbox',
				'cb_label' => __( 'Yes, make selection filterable (searchable)', 'wp-job-manager-search-and-filtering' ),
				'checked'   => array(
					'hide' => array(),
					'show' => array( 'allow_create' )
				),
				'unchecked' => array(
					'hide' => array( 'allow_create' ),
					'show' => array()
				),
			),
			array(
				'label'    => __( 'Allow Create', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'allow_create',
				'type'     => 'checkbox',
				'cb_label' => __( 'Yes, allow custom values (filterable must be enabled)', 'wp-job-manager-search-and-filtering' ),
				'desc' => __( 'By default when a user searches for a value when filterable is enabled, if this setting is enabled, the search value they entered will show in the dropdown for them to add as a custom search value.', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label' => __( 'No Match Text', 'wp-job-manager-search-and-filtering' ),
				'prop'  => 'no_match_text',
				'type'  => 'text',
				'desc'  => __( 'When filterable is enabled, text to show when no results are found', 'wp-job-manager-search-and-filtering' )
			),
			array(
				'label'       => __( 'Size', 'wp-job-manager-search-and-filtering' ),
				'prop'        => 'size',
				'type'        => 'select',
				'placeholder' => __( 'Large (default)', 'wp-job-manager-search-and-filtering' ),
				'desc'        => __( 'Size of the input. Three default sizes are included that can be used: mini, small, or large.', 'wp-job-manager-search-and-filtering' ),
				'options'     => array(
					'mini'  => __( 'Mini', 'wp-job-manager-search-and-filtering' ),
					'small' => __( 'Small', 'wp-job-manager-search-and-filtering' ),
					'large' => __( 'Large', 'wp-job-manager-search-and-filtering' ),
				)
			),
			array(
				'label'    => __( 'Show Count', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'show_count',
				'type'     => 'checkbox',
				'cb_label' => __( 'Yes, show the count in parenthesis next to the label', 'wp-job-manager-search-and-filtering' ),
				'desc'     => __( 'Enable this setting to show the total number of listings that have the taxonomy source for this field, in parenthesis, next to the label.  For example: Temporary (5).  Please note this value does NOT automatically update based on other field value selections (yet).', 'wp-job-manager-search-and-filtering' )
			),
		);
		return $eui_select_config;
	}

	/**
	 * Get Select2 Type Config
	 *
	 * @return array[]
	 * @since 1.0.1
	 *
	 */
	public function get_select2_type_config() {
		$select2_type_config = array(
			array(
				'label'   => __( 'Search Min', 'wp-job-manager-search-and-filtering' ),
				'prop'    => 'minimumResultsForSearch',
				'type'    => 'number',
				'desc'    => __( 'Minimum number of option values that must in the dropdown to show the Search box.  Use -1 to disable search box.', 'wp-job-manager-search-and-filtering' ),
				'default' => 10
			),
			array(
				'label'   => __( 'Dropdown Parent', 'wp-job-manager-search-and-filtering' ),
				'prop'    => 'dropdownParent',
				'type'    => 'text',
				'desc'    => __( 'Sometimes the dropdown will need to be attached to a different element to work correctly.  Enter the selector here to attach to something different than the body element.  ONLY enter the selector, for example ".some-class" or "#some-id"', 'wp-job-manager-search-and-filtering' ),
				'default' => ''
			),
			array(
				'label'   => __( 'Allow Clear', 'wp-job-manager-search-and-filtering' ),
				'prop'    => 'allowClear',
				'type'    => 'checkbox',
				'desc'    => __( 'Allow user to clear selection. Will show a small X in the dropdown to click to remove selection. Default is FALSE', 'wp-job-manager-search-and-filtering' ),
				'default' => false
			),
			array(
				'label'   => __( 'Close on Select', 'wp-job-manager-search-and-filtering' ),
				'prop'    => 'closeOnSelect',
				'type'    => 'checkbox',
				'desc'    => __( 'Controls whether the dropdown is closed after a selection is made. Default is TRUE', 'wp-job-manager-search-and-filtering' ),
				'default' => false
			),
			//			array(
			//				'label'   => __( 'Search Matching' ),
			//				'prop'    => 'search_matching',
			//				'type'    => 'select',
			//				'desc'    => __( 'When using search in a dropdown, the default Select2 handling only matches on the exact typed input.' ),
			//				'options' => array(
			//					'default' => __( 'Exact Match (default)' ),
			//					'any_word' => __( 'Any Word Matches' )
			//				)
			//			),
			array(
				'label'    => __( 'Show Count', 'wp-job-manager-search-and-filtering' ),
				'prop'     => 'show_count',
				'type'     => 'checkbox',
				'cb_label' => __( 'Yes, show the count in parenthesis next to the label', 'wp-job-manager-search-and-filtering' ),
				'desc'     => __( 'Enable this setting to show the total number of listings that have the taxonomy source for this field, in parenthesis, next to the label.  For example: Temporary (5)', 'wp-job-manager-search-and-filtering' )
			),
		);
		return $select2_type_config;
	}

	/**
	 * Get Input Field Types
	 *
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_field_types() {
		$types = array(
			'text' => array(
				'label'       => __( 'Standard Text', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'placeholder', 'default', 'input_classes', 'debounce', 'add_to_url' ),
				'type_config' => array(
					array(
						'label'       => __( 'Icon Action', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'icon_action',
						'type'        => 'select',
						'desc'        => __( 'Selecting a value from here will show an icon in the right corner of the text box, and will trigger the associated action.', 'wp-job-manager-search-and-filtering' ),
						'placeholder' => __( 'No Action (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'search' => __( 'Search (magnifying glass icon)', 'wp-job-manager-search-and-filtering' ),
							'clear'  => __( 'Clear Value (round X icon)', 'wp-job-manager-search-and-filtering' ),
						),
					)
				)
			),
			'text_locate'     => array(
				'label'       => __( 'Standard Text (Locate Me)', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'placeholder', 'default', 'input_classes', 'debounce', 'add_to_url' ),
				'type_config' => array_merge( array(
					array(
						'label'    => __( 'Google API key', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'api_key',
						'type'     => 'text',
						'required' => true,
						'desc'     => sprintf( __( 'Enter the client side API key to use for the GeoLocation.  This key should be different from any server-side keys you use.  Aquire an API key from the <a href="%s">Google Maps API Developer Site</a>.', 'wp-job-manager-search-and-filtering' ), 'https://developers.google.com/maps/documentation/geocoding/get-api-key' ),
					),
					array(
						'label'       => __( 'Result Type(s)', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'result_type',
						'type'        => 'select_multiple',
						'placeholder' => __( 'Best Match', 'wp-job-manager-search-and-filtering' ),
						'options'     => $this->get_locate_result_types(),
						'desc'        => sprintf( __( 'Specific GeoCoding result types to limit for populating the field with. Specific details can be found on <a href="%s" target="_blank">Google Reverse GeoCoding Documentation</a>.  If you set one of these values, it is recommended to select at least 4 of them, and set a preference in setting below.', 'wp-job-manager-search-and-filtering' ), 'https://developers.google.com/maps/documentation/geocoding/overview#geocoding' ),
					),
					array(
						'label'       => __( 'Type Preference', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'result_type_pref',
						'type'        => 'select',
						'placeholder' => __( 'Best Match', 'wp-job-manager-search-and-filtering' ),
						'options'     => $this->get_locate_result_types(),
						'desc'        => __( 'When Google returns results for GeoLocate, it returns them in order of best to least matches.  When this value is not set, the first (best match) will be used. Set a value here to prefer a specific type, if one is available (but not best match) it will be used, otherwise the best match will be used.', 'wp-job-manager-search-and-filtering' ),
					),
					array(
						'label' => __( 'Language', 'wp-job-manager-search-and-filtering' ),
						'prop'  => 'language',
						'type'  => 'text',
						'desc'  => sprintf( __( 'To return the result in a specific language, specify the language code from <a href="%s" target="_blank">Google Maps Supported Language Codes</a>.  If not specified, Google will attempt to automatically detect the language based on the native language of this site.', 'wp-job-manager-search-and-filtering' ), 'https://developers.google.com/maps/faq#languagesupport' ),
					),
					array(
						'label'    => __( 'Lat/Lng Fallback', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'lat_lng_fallback',
						'type'     => 'checkbox',
						'cb_label' => __( 'Yes, fallback to latitude/longitude if no results from Google', 'wp-job-manager-search-and-filtering' ),
						'desc'     => __( 'If for some reason Google does not return any results (or the query errors out), the latitude/longitude value can be populated into the field instead', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'type' => 'divider',
						'label' => __( 'Google Map Places AutoComplete', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'     => __( 'AutoComplete', 'wp-job-manager-search-and-filtering' ),
						'prop'      => 'enable_places_autocomplete',
						'type'      => 'checkbox',
						'cb_label'  => __( 'Yes, enable Google Places Autocomplete on this field', 'wp-job-manager-search-and-filtering' ),
						'checked'   => array(
							'hide' => array(),
							'show' => array( 'autocomplete_types', 'autocomplete_countries' )
						),
						'unchecked' => array(
							'hide' => array( 'autocomplete_types', 'autocomplete_countries' ),
							'show' => array()
						),
					),
				), $this->get_places_autosuggest_type_config() )
			),
			'text_google_places' => array(
				'label'       => __( 'Standard Text (Google Places Autocomplete)', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'placeholder', 'default', 'input_classes', 'debounce', 'add_to_url' ),
				'type_config' => array_merge( array(
					                              array(
						                              'label'       => __( 'Icon Action', 'wp-job-manager-search-and-filtering' ),
						                              'prop'        => 'icon_action',
						                              'type'        => 'select',
						                              'desc'        => __( 'Selecting a value from here will show an icon in the right corner of the text box, and will trigger the associated action.', 'wp-job-manager-search-and-filtering' ),
						                              'placeholder' => __( 'No Action (default)', 'wp-job-manager-search-and-filtering' ),
						                              'options'     => array(
							                              'search' => __( 'Search (magnifying glass icon)', 'wp-job-manager-search-and-filtering' ),
							                              'clear'  => __( 'Clear Value (round X icon)', 'wp-job-manager-search-and-filtering' ),
						                              ),
					                              ),
					                              array(
						                              'type'  => 'divider',
						                              'label' => __( 'Google Map Places AutoComplete', 'wp-job-manager-search-and-filtering' )
					                              )
				                              ), $this->get_places_autosuggest_type_config( true ) )
			),
			'checkbox'        => array(
				'label'       => __( 'Checkbox', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'default', 'input_classes', 'caption', 'placeholder', 'add_to_url' ),
				'type_config' => array(
					array(
						'label'       => __( 'Label Placement', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'label_placement',
						'type'        => 'select',
						'placeholder' => __( 'Wrap Input (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'wrap'  => __( 'Wrap Input (default)', 'wp-job-manager-search-and-filtering' ),
							'above' => __( 'Above Input', 'wp-job-manager-search-and-filtering' ),
							'below' => __( 'Below Input', 'wp-job-manager-search-and-filtering' )
						),
						'desc'        => __( 'Labels and Checkbox Input placement all vary based on themes and opinions.  Some themes expect the label to be below/above the input, whereas the standard is to wrap the input with the label. If you\'re unsure which to use, try each one to see which one looks best for your theme.', 'wp-job-manager-search-and-filtering' ),
					),
					array(
						'label'       => __( 'Label Element', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'label_element',
						'type'        => 'text',
						'placeholder' => __( 'label', 'wp-job-manager-search-and-filtering' ),
						'desc'        => __( 'Type of HTML element to use for the checkbox label.  Almost always this should be \'label\', but can also be any other kind of HTML element (span, div, etc)', 'wp-job-manager-search-and-filtering' ),
					),
				)
			),
			'select'          => array(
				'label'       => __( 'Single Select Dropdown (Select2)', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'placeholder', 'default', 'data', 'options', 'input_classes', 'add_to_url' ),
				'type_config' => array_merge( $this->get_select2_type_config(), array() ),
				'styles'      => array(
					'alert' => array(
						'message' => __( 'Due to the selected field type (which uses Select2), Input CSS will only be applied after Saving.', 'wp-job-manager-search-and-filtering' ),
						'type'    => 'info'
					)
				),
			),
			'eui_select'      => array(
				'label'       => __( 'Single Select Dropdown (Element UI)', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'placeholder', 'default', 'data', 'options', 'input_classes', 'add_to_url' ),
				'type_config' => array_merge( $this->get_eui_select_type_config(), array() ),
			),
			'multiselect'     => array(
				'label'       => __( 'Multi Select Dropdown (Select 2)', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'placeholder', 'default', 'data', 'options', 'input_classes', 'add_to_url', 'multiple_values' ),
				'styles'      => array(
					'warning' => __( 'Due to the selected field type (which uses Select2), Input CSS will only be applied after Saving.', 'wp-job-manager-search-and-filtering' )
				),
				'type_config' => array_merge( $this->get_select2_type_config(), array(
					array(
						'label'   => __( 'Max Selections', 'wp-job-manager-search-and-filtering' ),
						'prop'    => 'maximumSelectionLength',
						'type'    => 'number',
						'desc'    => __( 'Maximum number of selections that can be made.  If less than 1, selections will not be limited.', 'wp-job-manager-search-and-filtering' ),
						'default' => 0
					),
					array(
						'label'       => __( 'Overflow', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'overflow',
						'type'        => 'select',
						'desc'        => __( 'What to do when the selected options would overflow or be outside of the normal size of the multiselect box.  Default is to expand/resize the box to fit the selected options.', 'wp-job-manager-search-and-filtering' ),
						'placeholder' => __( 'Expand (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'expand' => __( 'Expand (default)', 'wp-job-manager-search-and-filtering' ),
							'scroll' => __( 'Scroll', 'wp-job-manager-search-and-filtering' )
						)
					),
				) ),
			),
			'eui_multiselect' => array(
				'label'       => __( 'Multi Select Dropdown (Element UI)', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'placeholder', 'default', 'data', 'options', 'input_classes', 'add_to_url', 'multiple_values' ),
				'type_config' => array_merge( $this->get_eui_select_type_config(), array(
					                                                array(
						                                                'label'   => __( 'Max Selections', 'wp-job-manager-search-and-filtering' ),
						                                                'prop'    => 'multiple_limit',
						                                                'type'    => 'number',
						                                                'desc'    => __( 'Maximum number of selections that can be made.  If less than 1, selections will not be limited.', 'wp-job-manager-search-and-filtering' ),
						                                                'default' => 0
					                                                ),
					                                                array(
						                                                'label'    => __( 'Collapse Tags', 'wp-job-manager-search-and-filtering' ),
						                                                'prop'     => 'collapse_tags',
						                                                'type'     => 'checkbox',
						                                                'cb_label' => __( 'Yes, collapse tags when multiple selected', 'wp-job-manager-search-and-filtering' ),
						                                                'desc'     => __( 'By default when multiple tags are selected, each will show, collapsing them will only show the first one, and then another one with count of how many are selected.', 'wp-job-manager-search-and-filtering' )
					                                                ),
					                                                array(
						                                                'label'       => __( 'Select All Checkbox', 'wp-job-manager-search-and-filtering' ),
						                                                'prop'        => 'select_all_option',
						                                                'type'        => 'text',
						                                                'placeholder' => '',
						                                                'desc'        => __( 'Enter a value here to show as a "Select All" checkbox option in the dropdown.  Leave blank to disable adding a "Select All" checkbox option', 'wp-job-manager-search-and-filtering' ),
					                                                ),
				                                                )
				),
			),
			'radio'           => array(
				'label'    => __( 'Radio Buttons (Single Select)', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'data', 'options', 'input_classes', 'wrapper_elements', 'default', 'add_to_url' ),
				'type_config' => array(
					array(
						'label'       => __( 'Label Placement', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'label_placement',
						'type'        => 'select',
						'placeholder' => __( 'Below Input (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'wrap'  => __( 'Wrap Input', 'wp-job-manager-search-and-filtering' ),
							'above' => __( 'Above Input', 'wp-job-manager-search-and-filtering' ),
							'below' => __( 'Below Input (default)', 'wp-job-manager-search-and-filtering' )
						),
						'desc'        => __( 'Labels and Checkbox Input placement all vary based on themes and opinions.  Some themes expect the label to be below/above the input, whereas the standard is to wrap the input with the label. If you\'re unsure which to use, try each one to see which one looks best for your theme.', 'wp-job-manager-search-and-filtering' ),
					),
					array(
						'label'    => __( 'Show Count', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'show_count',
						'type'     => 'checkbox',
						'cb_label' => __( 'Yes, show the count in parenthesis next to the label', 'wp-job-manager-search-and-filtering' ),
						'desc'     => __( 'Enable this setting to show the total number of listings that have the taxonomy source for this field, in parenthesis, next to the label.  For example: Temporary (5).  Please note this value does NOT automatically update based on other field value selections (yet).', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'    => __( 'Indent Children', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'indent_children',
						'type'     => 'checkbox',
						'cb_label' => __( 'Yes, indent children taxonomy values', 'wp-job-manager-search-and-filtering' ),
						'desc'     => __( 'By default for checklist taxonomy fields, children are NOT automatically indented under their parent (for dropdowns they are).', 'wp-job-manager-search-and-filtering' )
					),
				)
			),
			'slider'          => array(
				'label'       => __( 'Slider', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'input_classes', 'default', 'add_to_url' ),
				'type_config' => array_merge( $this->get_slider_type_config(), array(
					array(
						'label'    => __( 'Slider Indicator', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'range_indicator',
						'type'     => 'checkbox',
						'cb_label' => __( 'Yes, show slider indicator separately from slider', 'wp-job-manager-search-and-filtering' ),
						'desc'     => __( 'Enabling this setting will show a separate area that outputs the slider value above the slider.  You should probably set tooltip to none if you enable this setting.', 'wp-job-manager-search-and-filtering' )
					),
				) )
			),
			'range'           => array(
				'label'            => __( 'Range Slider', 'wp-job-manager-search-and-filtering' ),
				'supports'         => array( 'input_classes', 'search_source_2', 'search_compare', 'search_compare_2', 'add_to_url', 'default' ),
				'type_config'      => array_merge( $this->get_slider_type_config(), array(
					array(
						'label'    => __( 'Range Indicator', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'range_indicator',
						'type'     => 'checkbox',
						'cb_label' => __( 'Yes, show range indicator separately from range slider', 'wp-job-manager-search-and-filtering' ),
						'desc'     => __( 'Enabling this setting will show a separate area that outputs the range values above the slider.  You should probably set tooltip to none if you enable this setting.', 'wp-job-manager-search-and-filtering' )
					),
				) ),
				'default'          => array(
					'description' => __( '(Format: MIN-MAX) To set default values for range fields, you MUST specify the minimum value and maximum value, separated by a hyphen.  Example of 1 as min and 10 as max would be 1-10', 'wp-job-manager-search-and-filtering' )
				),
				'search_source'    => array(
					'label'       => __( 'Min Source', 'wp-job-manager-search-and-filtering' ),
					'description' => __( 'The source field to use as the minimum value search in the range slider', 'wp-job-manager-search-and-filtering' )
				),
				'search_source_2'  => array(
					'label'       => __( 'Max Source', 'wp-job-manager-search-and-filtering' ),
					'description' => __( 'The source field to use as the maximum value search in the range slider. This can be set to the same exact field as above if you only want to use one field.', 'wp-job-manager-search-and-filtering' ),
					'required'    => true
				),
				'search_compare'   => array(
					'label'       => __( 'Min Comparison', 'wp-job-manager-search-and-filtering' ),
					'description' => __( 'Comparison operator to use for the Minimum Value source.  This should almost always be Greater Than or Equal To. Do not change this unless you know what you are doing.', 'wp-job-manager-search-and-filtering' ),
					'default'     => '>='
				),
				'search_compare_2' => array(
					'label'       => __( 'Max Comparison', 'wp-job-manager-search-and-filtering' ),
					'description' => __( 'Comparison operator to use for the Maximum Value source.  This should almost always be Less Than or Equal To.  Do not change this unless you know what you are doing.', 'wp-job-manager-search-and-filtering' ),
					'default'     => '<='
				)
			),
			'checklist'       => array(
				'label'       => __( 'Checklist', 'wp-job-manager-search-and-filtering' ),
				'supports'    => array( 'data', 'options', 'input_classes', 'wrapper_elements', 'default', 'add_to_url', 'multiple_values' ),
				'type_config' => array(
					array(
						'label'       => __( 'Label Placement', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'label_placement',
						'type'        => 'select',
						'placeholder' => __( 'Wrap Input (default)', 'wp-job-manager-search-and-filtering' ),
						'options'     => array(
							'wrap'  => __( 'Wrap Input (default)', 'wp-job-manager-search-and-filtering' ),
							'above' => __( 'Above Input', 'wp-job-manager-search-and-filtering' ),
							'below' => __( 'Below Input', 'wp-job-manager-search-and-filtering' )
						),
						'desc'        => __( 'Labels and Checkbox Input placement all vary based on themes and opinions.  Some themes expect the label to be below/above the input, whereas the standard is to wrap the input with the label. If you\'re unsure which to use, try each one to see which one looks best for your theme.', 'wp-job-manager-search-and-filtering' ),
					),
					array(
						'label'       => __( 'Label Element', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'label_element',
						'type'        => 'text',
						'placeholder' => __( 'label', 'wp-job-manager-search-and-filtering' ),
						'desc'        => __( 'Type of HTML element to use for the checkbox label.  Almost always this should be \'label\', but can also be any other kind of HTML element (span, div, etc)', 'wp-job-manager-search-and-filtering' ),
					),
					array(
						'label'       => __( 'Label Classes', 'wp-job-manager-search-and-filtering' ),
						'prop'        => 'label_classes',
						'type'        => 'text',
						'desc'        => __( 'Any custom classes (separated by spaces) to add to the label element used for the checklist (not the main label for entire field)', 'wp-job-manager-search-and-filtering' ),
					),
					array(
						'label'    => __( 'Show Count', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'show_count',
						'type'     => 'checkbox',
						'cb_label' => __( 'Yes, show the count in parenthesis next to the label', 'wp-job-manager-search-and-filtering' ),
						'desc'     => __( 'Enable this setting to show the total number of listings that have the taxonomy source for this field, in parenthesis, next to the label.  For example: Temporary (5).  Please note this value does NOT automatically update based on other field value selections (yet).', 'wp-job-manager-search-and-filtering' )
					),
					array(
						'label'    => __( 'Indent Children', 'wp-job-manager-search-and-filtering' ),
						'prop'     => 'indent_children',
						'type'     => 'checkbox',
						'cb_label' => __( 'Yes, indent children taxonomy values', 'wp-job-manager-search-and-filtering' ),
						'desc'     => __( 'By default for checklist taxonomy fields, children are NOT automatically indented under their parent (for dropdowns they are).', 'wp-job-manager-search-and-filtering' )
					),
				)
			),
			'button'          => array(
				'label'    => __( 'Button', 'wp-job-manager-search-and-filtering' ),
				'supports' => array( 'button_type', 'caption', 'no_search_source', 'input_classes' ),
			)
		);

		return apply_filters( 'search_and_filtering_input_field_types', $types, $this );
	}
}