<?php
/**
 * ELEMENTOR WIDGET - LISTING MAP
 *
 * @since   1.4.0
 * @version 1.9.9
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Map extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_map';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Cariera Map', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-google-maps';
	}

	/**
	 * Get widget's categories.
	 */
	public function get_categories() {
		return [ 'cariera-elements' ];
	}

	/**
	 * Register the controls for the widget
	 */
	protected function register_controls() {

		// SECTION.
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'cariera-core' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'height',
			[
				'label'       => esc_html__( 'Map Height', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '450px',
				'description' => esc_html__( 'Insert the height of the map. For example: 450px.', 'cariera-core' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-maps' ];
	}

	/**
	 * Script Dependecy
	 */
	public function get_script_depends() {
		return [ 'cariera-maps' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();
		$attrs    = '';

		$output = do_shortcode( '[cariera-map height="' . $settings['height'] . '"]' );

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
