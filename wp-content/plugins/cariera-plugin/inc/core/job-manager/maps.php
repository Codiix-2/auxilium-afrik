<?php

namespace Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Maps {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'cariera-map', [ $this, 'show_map' ] );
	}

	/**
	 * Show map function
	 *
	 * @since   1.2.0
	 * @version 1.9.9
	 *
	 * @param array $atts
	 */
	public function show_map( $atts ) {
		if ( 'google' === get_option( 'cariera_map_provider' ) ) {
			\Cariera_Core\Core\Assets::enqueue_google_maps( true );
		}
		\Cariera_Core\Core\Assets::enqueue_maps();

		$atts = shortcode_atts(
			[
				'class'  => '',
				'height' => '',
			],
			$atts
		);

		if ( empty( $atts['height'] ) ) {
			$map_height = '450px';
		} else {
			$map_height = $atts['height'];
		}

		$output = '<div id="map-container" class="' . esc_attr( $atts['class'] ) . '"><div id="cariera-map" style="height:' . esc_attr( $map_height ) . '" ></div></div>';

		return $output;
	}
}
