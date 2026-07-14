<?php

namespace WPJMSF\Sections;

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

	private $sections;

	/**
	 * Fields constructor.
	 *
	 * @param $sections \WPJMSF\Sections
	 */
	public function __construct( $sections ) {
		$this->sections = $sections;
	}

	public function add_section_id( $fields_array, $section_id, $nested_keys = array() ) {

		$default_nested_keys = array( 'spacing', 'styles' );
		$nested_keys         = array_merge( $default_nested_keys, $nested_keys );

		foreach ( (array) $fields_array as $index => $field_option ) {

			foreach ( (array) $field_option as $fo_key => $fo_val ) {

				if ( ! in_array( $fo_key, $nested_keys ) ) {
					continue;
				}

				$fields_array[ $index ][ $fo_key ]['field_slug'] = $fields_array[ $index ]['slug'];
				$fields_array[ $index ][ $fo_key ]['section_id'] = $section_id;
			}
		}

		return $fields_array;
	}

	private function get_default_section_post_fields() {
		$default = array( 'output', 'is_form', 'disable_form_on_listings_page' );
		return apply_filters( 'search_and_filtering_get_default_section_post_fields', $default, $this );
	}

	private function get_all_section_post_fields() {

		$base       = $this->get_default_section_post_fields();
		$additional = array( 'spacing' => array( 'default' => array() ), 'styles', 'fields', 'grids' );
		$all        = array_merge( $base, $additional );

		return apply_filters( 'search_and_filtering_get_all_section_post_fields', $all, $this );
	}

	function add_section_array_data( $section_array, $post ) {

		return $section_array;
	}

	function add_additional_section_fields( $post, $section_array, $fields ) {

		foreach ( (array) $fields as $field ) {
			$section_array[ $field ] = $post->$field;
		}

		return $section_array;
	}
}