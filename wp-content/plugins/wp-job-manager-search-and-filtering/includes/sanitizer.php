<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sanitizer
 *
 * @package WPJMSF
 */
class Sanitizer {

	private $sanitizer = 'sanitize_text_field';

	/**
	 * @var array|mixed Safe values are exact values that a field can be, for it to skip sanitation handling
	 */
	private $safe_values = array();

	/**
	 * @var array Array keys that are allowed to have HTML in them (and will be sanitized with `wp_kses_post`)
	 */
	private $html_array_keys = array();

	/**
	 * Sanitizer constructor.
	 *
	 * @param string $custom_sanitizer
	 * @param array  $safe_values
	 */
	public function __construct( $custom_sanitizer = 'sanitize_text_field', $safe_values = array() ) {
		$this->sanitizer = $custom_sanitizer;
		$this->safe_values = $safe_values;
	}

	/**
	 * Set Array Keys to Allow HTML Data
	 *
	 * @param $fields
	 *
	 * @since 1.0.0
	 *
	 */
	public function set_html_array_keys( $fields ){
		$this->html_array_keys = $fields;
	}

	/**
	 * Sanitize Data
	 *
	 * @param mixed $data
	 * @param string $data_key
	 *
	 * @return array|false|mixed
	 * @since 1.0.0
	 *
	 */
	public function sanitize( $data, $data_key = '' ) {

		if ( is_array( $data ) ) {

			$sanitized_values = array();

			foreach ( (array) $data as $dk => $dv ) {
				$sanitized_values[ $dk ] = $this->sanitize( $dv, $dk );
			}

			return $sanitized_values;

		}

		/**
		 * If value exactly matches a value from "safe values" we skip sanitation
		 */
		if ( ! empty( $this->safe_values ) && in_array( $data, $this->safe_values, true ) ) {
			return $data;
		}

		$sanitizer = function_exists( $this->sanitizer ) ? $this->sanitizer : 'sanitize_text_field';

		if( ! empty( $data_key ) && in_array( $data_key, $this->html_array_keys ) ){
			$sanitizer = 'wp_kses_post';
		}

		return call_user_func( $sanitizer, $data );
	}

	public function get_sanitizer(){
		return $this->sanitizer;
	}
}

if( ! function_exists( 'wpjmsf_sanitize_value' ) ){

	function wpjmsf_sanitize_value( $value, $sanitizer = 'sanitize_text_field', $safe_values = array( '<', '<=', '>', '>=', '=', '!=', 'LIKE', 'NOT LIKE', 'NOT EXISTS', 'EXISTS' ) ) {

		$sanitizer = function_exists( $sanitizer ) ? $sanitizer : 'sanitize_text_field';

		if ( is_array( $value ) ) {
			$sanitized_value = array_map( array( new Sanitizer( $sanitizer, $safe_values ), 'sanitize' ), $value );
		} else {

			if ( ! empty( $safe_values ) && in_array( $value, $safe_values, true ) ) {
				$sanitized_value = $value;
			} else {
				$sanitized_value = call_user_func( $sanitizer, $value );
			}

		}

		// Strip whitespace from beginning and end of value
		$trim_whitespace = apply_filters( 'wpjmsf_sanitize_value_trim_whitespace', true, $value, $sanitized_value, $sanitizer, $safe_values );
		if( $trim_whitespace ){
			$sanitized_value = wpjmsf_trim_whitespace( $sanitized_value );
		}

		return $sanitized_value;
	}
}