<?php
/**
 * ELEMENTOR WIDGET - COMPANY LIST
 *
 * @since   1.4.0
 * @version 2.0.0
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Company_List extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'company_list';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Company List', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return ' eicon-post-list';
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

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-companies-list' ];
	}

	/**
	 * Script Dependecy
	 */
	public function get_script_depends() {
		return [ 'cariera-companies-list' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();
		$attrs    = '';

		$output = '[cariera_companies_list]';

		echo do_shortcode( $output );
	}
}
