<?php

namespace WPJMSF\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Atts
 *
 * @package WPJMSF\Shortcodes
 */
class Atts {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var array Local object cache storage of shortcode attributes, required because the filter
	 *            to output jobs from shortcode does not pass the atts in the filter.
	 */
	public $atts = array();
	/**
	 * @var string Actual shortcode we're targeting, without the open/close brackets
	 */
	public $shortcode = 'jobs';
	/**
	 * @var array Default shortcode attribute values (passed in filter before merging with actual args)
	 */
	public $default_atts = array();
	/**
	 * @var array Local object cache of taxonomy fields that use slugs on frontend for values
	 */
	public $taxonomy_slug_fields = null;

	/**
	 * Atts constructor.
	 *
	 * @param        $type
	 * @param string $shortcode
	 */
	public function __construct( $type, $shortcode = 'jobs' ) {
		$this->type = $type;
		$this->shortcode = $shortcode;
		add_filter( 'do_shortcode_tag', array( $this, 'check_shortcode' ), 99, 3 );
		add_filter( 'pre_do_shortcode_tag', array( $this, 'check_pre_shortcode' ), 99, 3 );
	}

	/**
	 * Get Attributes Before Shortcode Function is Triggered
	 *
	 * @param $false
	 * @param $tag
	 * @param $attr
	 *
	 * @return mixed
	 * @since 1.1.3
	 *
	 */
	public function check_pre_shortcode( $false, $tag, $attr ) {

		if ( $tag !== $this->shortcode ) {
			return $false;
		}

		$this->atts = $attr;
		return $false;
	}

	/**
	 * Check show_sf_filters on Shortcode Output
	 *
	 * This method is used to check if output is disabled via the actual shortcode output attribute.
	 *
	 * @param bool $attr
	 * @param bool $also_check_show_filters
	 *
	 * @return bool
	 * @since 1.1.3
	 *
	 */
	public function show_sf_filters( $attr = false, $also_check_show_filters = false ) {
		$attr = $attr ? $attr : $this->atts;
		$show_sf_filters = ! isset( $attr['show_sf_filters'] ) || ( $attr['show_sf_filters'] !== 'false' && $attr['show_sf_filters'] !== false );
		$show_filters = ! isset( $attr['show_filters'] ) || ( $attr['show_filters'] !== 'false' && $attr['show_filters'] !== false );
		return $also_check_show_filters ? $show_sf_filters && $show_filters : $show_sf_filters;
	}

	/**
	 * Check Shortcode Being Output
	 *
	 * This method checks if the shortcode being output is the one we're targeting,
	 * and if so, parse the attributes and localize the output.
	 *
	 * @param $output
	 * @param $tag
	 * @param $attr
	 *
	 * @return mixed
	 * @since 1.0.1
	 *
	 */
	public function check_shortcode( $output, $tag, $attr ) {
		if( $tag !== $this->shortcode ){
			return $output;
		}

		if( $this->show_sf_filters( $attr ) &&  $this->type->output->is_enabled() ){
			$this->atts = $attr;
			$this->localize();
		}

		return $output;
	}

	/**
	 * Localize Output
	 *
	 * @since 1.0.1
	 *
	 */
	public function localize() {
		$shortcode_atts = $this->parse_shortcode_atts();
		wp_localize_script( 'wpjm-search-filtering-frontend', "wpjmsf_{$this->type->slug}_shortcode_atts", $shortcode_atts );
		wp_localize_script( 'wpjm-search-filtering-frontend-edit', "wpjmsf_{$this->type->slug}_shortcode_atts", $shortcode_atts );
	}

	/**
	 * Add [jobs]/[resumes] available/default args/attributes
	 *
	 *
	 * @param $atts
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function shortcode_default_atts( $atts ) {

		// $this->atts can't be set here as this is just for default values
		$custom_atts = array( 'filter_section_id', 'custom_filter_type', 'show_sf_filters' );

		foreach ( (array) $custom_atts as $custom_att ) {
			if ( ! isset( $atts[ $custom_att ] ) ) {
				$atts[ $custom_att ] = '';
			}
		}

		$this->default_atts = $atts;

		return $atts;
	}

	/**
	 * Parse Shortcode Attributes and Build Expected Array
	 *
	 * @return array
	 * @since 1.0.1
	 *
	 */
	public function parse_shortcode_atts() {

		$parsed_atts = array(
			'actual'  => $this->atts,
			'default' => array(),
			'limit'   => array()
		);

		$type_atts = $this->get_shortcode_atts();

		// $this->atts will only be the actual attributes defined on the shortcode (not including default values)
		foreach ( (array) $this->atts as $att => $att_value ) {
			// Unknown or not-defined attribute (in type extending class)
			if ( ! array_key_exists( $att, $type_atts ) ) {
				continue;
			}

			/**
			 * Trim whitespace and check for invalid value (mainly from Elementor for WP Job Manager), which passes an empty
			 * string with only a comma.  If this is the case, set the value to empty string.
			 */
			if ( trim( $this->atts[ $att ] ) === ',' ) {
				$this->atts[ $att ] = '';
			}

			$config        = $type_atts[ $att ];
			$search_source = $config['search_source'];

			if ( array_key_exists( 'default', $config ) ) {

				$parsed_atts['default'][ $search_source ] = array(
					'value'       => $this->get_frontend_value( $search_source, $this->atts[ $att ], $config ),
					'multi_value' => array_key_exists( 'multi_value', $config ) && $config['multi_value']
				);

			}

			if ( array_key_exists( 'limit', $config ) ) {

				$parsed_atts['limit'][ $search_source ] = array(
					'value'       => $this->get_frontend_value( $search_source, $this->atts[ $att ], $config ),
					'multi_value' => array_key_exists( 'multi_value', $config ) && $config['multi_value']
				);

			}
		}

		return $parsed_atts;
	}

	/**
	 * Get Frontend Value
	 *
	 * @param string $search_source
	 * @param string $shortcode_value
	 * @param array  $config
	 *
	 * @return string
	 * @since 1.0.1
	 *
	 */
	public function get_frontend_value( $search_source, $shortcode_value, $config = array() ) {

		$parsed_value = $shortcode_value;

		if ( isset( $config['taxonomy'] ) ) {

			if ( is_null( $this->taxonomy_slug_fields ) ) {
				$this->taxonomy_slug_fields = $this->type->get_taxonomy_slug_fields( true );
			}

			/**
			 * If not a slug value field, we need to convert the slugs to term id's
			 */
			if ( ! in_array( $config['taxonomy'], $this->taxonomy_slug_fields ) ) {
				$parsed_value = array();
				$term_slugs   = explode( ',', $shortcode_value );

				foreach ( (array) $term_slugs as $term_slug ) {

					// If already a term id, just add it to the array
					if ( is_numeric( $term_slug ) ) {
						$parsed_value[] = $term_slug;
						continue;
					}

					if ( $term_object = get_term_by( 'slug', $term_slug, $config['taxonomy'] ) ) {
						$parsed_value[] = $term_object->term_id;
					}
				}

				$parsed_value = implode( ',', $parsed_value );
			}
		}

		return apply_filters( "search_and_filtering_{$this->type->slug}_shortcode_atts_get_frontend_value", $parsed_value, $search_source, $shortcode_value, $config, $this );
	}

	/**
	 * Get Shortcode Attributes Placeholder
	 *
	 * @return array
	 * @since 1.0.1
	 *
	 */
	public function get_shortcode_atts() {}

	/**
	 * Add Data Attribute
	 *
	 * This method adds a data attribute to the listings main div (for shortcode output), to signify
	 * that this plugin is active for this specific type
	 *
	 * @param $attributes
	 *
	 * @return mixed
	 * @since 1.1.0
	 *
	 */
	public function add_data_attribute( $attributes ) {
		if( $this->show_sf_filters() ){
			$attributes['wpjmsf_enabled'] = true;
		} else {
			$attributes['wpjmsf_disabled'] = true;
		}

		return $attributes;
	}
}