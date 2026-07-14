<?php
/**
 * ELEMENTOR WIDGET - JOB CATEGORIES SLIDER
 *
 * @since    1.4.0
 * @version  1.9.5
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Categories_Slider extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_categories_slider';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Job Categories Slider', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-slider-3d';
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
			'icon',
			[
				'label'        => esc_html__( 'Category Icon', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
			]
		);
		$this->add_control(
			'columns',
			[
				'label'       => esc_html__( 'Visible Items per Slide', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '5',
				'min'         => '1',
				'max'         => '10',
				'description' => esc_html__( 'This will change how many categories will be visible per slide.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'hide_empty',
			[
				'label'        => esc_html__( 'Hide Empty', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'true',
				'default'      => '',
			]
		);
		$this->add_control(
			'orderby',
			[
				'label'       => esc_html__( 'Order by', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'name'  => esc_html__( 'Name', 'cariera-core' ),
					'ID'    => esc_html__( 'ID', 'cariera-core' ),
					'count' => esc_html__( 'Count', 'cariera-core' ),
					'slug'  => esc_html__( 'Slug', 'cariera-core' ),
					'none'  => esc_html__( 'None', 'cariera-core' ),
				],
				'default'     => 'count',
				'description' => '',
			]
		);
		$this->add_control(
			'order',
			[
				'label'       => esc_html__( 'Order', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'DESC' => esc_html__( 'Descending', 'cariera-core' ),
					'ASC'  => esc_html__( 'Ascending', 'cariera-core' ),
				],
				'default'     => 'DESC',
				'description' => '',
			]
		);
		$this->add_control(
			'items',
			[
				'label'       => esc_html__( 'Total Items', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '10',
				'min'         => '1',
				'description' => esc_html__( 'Set max limit for items (limited to 1000).', 'cariera-core' ),
			]
		);

		$this->add_control(
			'arrows',
			[
				'label'        => esc_html__( 'Slider Arrows', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'cariera-core' ),
				'label_off'    => esc_html__( 'Off', 'cariera-core' ),
				'return_value' => 'true',
				'default'      => '',
			]
		);
		$this->add_control(
			'autoplay',
			[
				'label'        => esc_html__( 'Autoplay', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'cariera-core' ),
				'label_off'    => esc_html__( 'Off', 'cariera-core' ),
				'return_value' => 'true',
				'default'      => '',
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

		// SECTION STYLE.
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'cariera-core' ),
			]
		);
		$this->add_control(
			'text_color',
			[
				'label'       => esc_html__( 'Text Color', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => '#fff',
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .listing-term-slider .term-item' => 'color: {{VALUE}}' ],
			]
		);
		$this->add_control(
			'bg_color',
			[
				'label'       => esc_html__( 'Background Color', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => '#072e44',
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .listing-term-slider .term-item' => 'background-color: {{VALUE}}' ],
			]
		);
		$this->add_responsive_control(
			'item_width',
			[
				'label'       => esc_html__( 'Item Width', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'default'     => [
					'size' => 130,
					'unit' => 'px',
				],
				'description' => '',
				'size_units'  => [ 'px', '%', 'custom' ],
				'range'       => [
					'px' => [
						'min'  => 50,
						'max'  => 500,
						'step' => 1,
					],
				],
				'selectors'   => [ '{{WRAPPER}} .listing-term-slider .term-item' => 'max-width: {{SIZE}}{{UNIT}}' ],
			]
		);
		$this->add_responsive_control(
			'item_height',
			[
				'label'       => esc_html__( 'Item Height', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'default'     => [
					'size' => 130,
					'unit' => 'px',
				],
				'description' => '',
				'size_units'  => [ 'px', '%', 'custom' ],
				'range'       => [
					'px' => [
						'min'  => 50,
						'max'  => 500,
						'step' => 1,
					],
				],
				'selectors'   => [ '{{WRAPPER}} .listing-term-slider .term-item' => 'min-height: {{SIZE}}{{UNIT}}; max-height: {{SIZE}}{{UNIT}}' ],
			]
		);
		$this->add_responsive_control(
			'border_radius',
			[
				'label'       => esc_html__( 'Item Border Radius', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'default'     => [
					'size' => 5,
					'unit' => 'px',
				],
				'description' => '',
				'size_units'  => [ 'px', '%', 'custom' ],
				'selectors'   => [ '{{WRAPPER}} .listing-term-slider .term-item' => 'border-radius: {{SIZE}}{{UNIT}}' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-listing-categories' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		wp_enqueue_style( 'cariera-listing-categories' );

		$settings   = $this->get_settings();
		$categories = get_terms(
			[
				'taxonomy'   => 'job_listing_category',
				'orderby'    => $settings['orderby'],
				'order'      => $settings['order'],
				'hide_empty' => $settings['hide_empty'],
				'number'     => $settings['items'],
			]
		);

		if ( is_wp_error( $categories ) ) {
			return;
		}

		// Load template.
		cariera_get_template(
			'elements/listing-category/job-category-slider.php',
			[
				'settings'   => $settings,
				'categories' => $categories,
			]
		);
	}
}
