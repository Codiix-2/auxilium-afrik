<?php
/**
 * ELEMENTOR WIDGET - BUTTON
 *
 * @since    1.4.5
 * @version  1.7.9
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Button extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'cariera_button';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Button', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-button';
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
			'text',
			[
				'label'       => esc_html__( 'Text', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'Click Here',
				'description' => '',
			]
		);
		$this->add_control(
			'url',
			[
				'label'       => esc_html__( 'URL', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'default'     => [
					'url'         => '#',
					'is_external' => '',
					'nofollow'    => '',
				],
				'description' => '',
			]
		);
		$this->add_control(
			'style',
			[
				'label'       => esc_html__( 'Button Style', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					''           => esc_html__( 'Default', 'cariera-core' ),
					'btn-round'  => esc_html__( 'Round', 'cariera-core' ),
					'btn-border' => esc_html__( 'Bordered', 'cariera-core' ),
				],
				'default'     => '',
				'description' => '',
			]
		);
		$this->add_control(
			'color',
			[
				'label'       => esc_html__( 'Button Preset Color', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'btn-main'      => esc_html__( 'Main Color', 'cariera-core' ),
					'btn-secondary' => esc_html__( 'Secondary Color', 'cariera-core' ),
				],
				'default'     => 'btn-main',
				'description' => '',
			]
		);
		$this->add_control(
			'effect',
			[
				'label'        => esc_html__( 'Ripple Effect', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera-core' ),
				'label_off'    => esc_html__( 'Disable', 'cariera-core' ),
				'return_value' => 'btn-effect',
				'default'      => 'btn-effect',
				'description'  => '',
				'condition'    => [
					'style' => [ '', 'btn-round' ],
				],
			]
		);
		$this->add_control(
			'fullwidth',
			[
				'label'        => esc_html__( 'Full Width', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera-core' ),
				'label_off'    => esc_html__( 'Disable', 'cariera-core' ),
				'return_value' => 'btn-block',
				'default'      => '',
				'description'  => '',
			]
		);
		$this->add_control(
			'align',
			[
				'label'   => esc_html__( 'Align', 'cariera-core' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [
						'title' => esc_html__( 'Left', 'cariera-core' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'cariera-core' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'cariera-core' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default' => 'left',
			]
		);
		$this->add_control(
			'custom_class',
			[
				'label'       => esc_html__( 'Custom Class', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();

		// Main button classes.
		$btn_class = [ 'btn' ];

		if ( ! empty( $settings['color'] ) ) {
			$btn_class[] = $settings['color'];
		}

		if ( ! empty( $settings['effect'] ) ) {
			$btn_class[] = 'btn-effect';
		}

		if ( ! empty( $settings['style'] ) ) {
			$btn_class[] = $settings['style'];
		}

		if ( ! empty( $settings['fullwidth'] ) ) {
			$btn_class[] = 'btn-block';
		}

		if ( ! empty( $settings['custom_class'] ) ) {
			$btn_class[] = $settings['custom_class'];
		}

		$url    = $settings['url']['url'];
		$target = $settings['url']['is_external'] ? 'target="_blank"' : '';
		$follow = $settings['url']['nofollow'] ? 'rel="nofollow"' : '';
		$align  = $settings['align'];

		echo '<div class="text-' . esc_attr( $align ) . '">';
			echo '<a href="' . esc_url( $url ) . '" ' . esc_attr( $target ) . ' class="' . esc_attr( join( ' ', $btn_class ) ) . '" ' . esc_attr( $follow ) . '>' . esc_html( $settings['text'] ) . '</a>';
		echo '</div>';
	}
}
