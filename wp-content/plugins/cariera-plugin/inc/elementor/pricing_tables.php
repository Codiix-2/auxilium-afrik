<?php
/**
 * ELEMENTOR WIDGET - PRICING TABLES
 *
 * @since   1.4.0
 * @version 1.7.3
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Pricing_Tables extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'pricing_tables';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Pricing Tables', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-price-table';
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
			'version',
			[
				'label'       => esc_html__( 'Pricing Table Layout', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'version1' => esc_html__( 'Layout 1', 'cariera-core' ),
					'version2' => esc_html__( 'Layout 2', 'cariera-core' ),
					'version3' => esc_html__( 'Layout 3', 'cariera-core' ),
				],
				'default'     => 'version1',
				'description' => '',
			]
		);
		$this->add_control(
			'title',
			[
				'label'       => esc_html__( 'Title', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'Basic',
				'description' => '',
			]
		);
		$this->add_control(
			'price',
			[
				'label'       => esc_html__( 'Price', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '$59',
				'description' => '',
			]
		);
		$this->add_control(
			'description',
			[
				'label'       => esc_html__( 'Description', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'This is a basic package',
				'description' => '',
				'condition'   => [
					'version' => 'version2',
				],
			]
		);
		$this->add_control(
			'background_img',
			[
				'label'       => esc_html__( 'Background Image', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'default'     => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'description' => '',
				'condition'   => [
					'version' => 'version2',
				],
			]
		);
		$this->add_control(
			'overlay_color',
			[
				'label'       => esc_html__( 'Pricing Header Overlay Color', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => 'rgba(48, 58, 247, .5)',
				'description' => '',
				'condition'   => [
					'version' => 'version2',
				],
			]
		);
		$this->add_control(
			'content',
			[
				'label'  => esc_html__( 'Pricing Details', 'cariera-core' ),
				'type'   => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name'    => 'detail',
						'label'   => esc_html__( 'Detail', 'cariera-core' ),
						'type'    => \Elementor\Controls_Manager::TEXT,
						'default' => 'List Item',
					],
				],
			]
		);
		$this->add_control(
			'button_text',
			[
				'label'       => esc_html__( 'Button Text', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'Button',
				'description' => '',
			]
		);
		$this->add_control(
			'link',
			[
				'label'         => esc_html__( 'Button Link', 'cariera-core' ),
				'type'          => \Elementor\Controls_Manager::URL,
				'show_external' => true,
				'default'       => [
					'url'         => 'http://',
					'is_external' => '',
					'nofollow'    => '',
				],
				'description'   => '',
			]
		);
		$this->add_control(
			'highlight',
			[
				'label'        => esc_html__( 'Highlight', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera-core' ),
				'label_off'    => esc_html__( 'Disable', 'cariera-core' ),
				'return_value' => 'enable',
				'default'      => '',
				'description'  => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 *
	 * @since 1.7.0
	 */
	public function get_style_depends() {
		return [ 'cariera-pricing-tables' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		wp_enqueue_style( 'cariera-pricing-tables' );

		$settings = $this->get_settings();

		if ( 'enable' !== $settings['highlight'] ) {
			$highlight = '';
		} else {
			$highlight = 'pricing-table-featured';
		}

		// Version 1.
		if ( 'version1' === $settings['version'] ) {
			cariera_get_template(
				'elements/pricing-tables/pricing-table1.php',
				[
					'settings'  => $this->get_settings(),
					'highlight' => $highlight,
				]
			);
		}

		// Version 2.
		if ( 'version2' === $settings['version'] ) {
			cariera_get_template(
				'elements/pricing-tables/pricing-table2.php',
				[
					'settings'  => $this->get_settings(),
					'highlight' => $highlight,
				]
			);
		}

		// Version 3.
		if ( 'version3' === $settings['version'] ) {
			cariera_get_template(
				'elements/pricing-tables/pricing-table3.php',
				[
					'settings'  => $this->get_settings(),
					'highlight' => $highlight,
				]
			);
		}
	}
}
