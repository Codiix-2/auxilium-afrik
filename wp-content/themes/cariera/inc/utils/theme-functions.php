<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'cariera_get_option' ) ) {
	/**
	 * Shorthand function for getting setting value from customizer
	 *
	 * @since 1.7.3
	 *
	 * @param string $key
	 */
	function cariera_get_option( $key ) {
		$value = null;

		if ( empty( $key ) ) {
			return;
		}

		$value = \Cariera\Kirki::get_option( 'cariera', $key );

		return apply_filters( 'cariera_get_option', $value, $key );
	}
}
