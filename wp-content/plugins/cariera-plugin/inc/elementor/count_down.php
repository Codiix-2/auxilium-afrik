<?php
/**
 * ELEMENTOR WIDGET - COUNT DOWN
 *
 * @since   1.4.5
 * @version 1.9.5
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Count_Down extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'count_down';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Count Down', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-countdown';
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
			'countdown_date',
			[
				'label'       => esc_html__( 'Select Date', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::DATE_TIME,
				'default'     => gmdate( 'Y-m-d H:i' ),
				'placeholder' => gmdate( 'Y-m-d H:i' ),
			]
		);
		$this->add_control(
			'countdown_days',
			[
				'label'   => esc_html__( 'Days Text', 'cariera-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Days',
			]
		);
		$this->add_control(
			'countdown_hours',
			[
				'label'   => esc_html__( 'Hours Text', 'cariera-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Hours',
			]
		);
		$this->add_control(
			'countdown_mins',
			[
				'label'   => esc_html__( 'Minutes Text', 'cariera-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Minutes',
			]
		);
		$this->add_control(
			'countdown_secs',
			[
				'label'   => esc_html__( 'Seconds Text', 'cariera-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Seconds',
			]
		);

		$this->end_controls_section();

		// STYLE SECTION.
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Countdown Style', 'cariera-core' ),
			]
		);

		$this->add_responsive_control(
			'flex_direction',
			[
				'label'     => esc_html__( 'Direction', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'row'    => [
						'title' => esc_html__( 'Row', 'cariera-core' ),
						'icon'  => 'eicon-arrow-right',
					],
					'column' => [
						'title' => esc_html__( 'Column', 'cariera-core' ),
						'icon'  => 'eicon-arrow-down',
					],
				],
				'default'   => 'row',
				'toggle'    => true,
				'selectors' => [
					'{{WRAPPER}} .cariera-countdown' => 'display: flex; flex-direction: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'justify_content',
			[
				'label'     => esc_html__( 'Justify Content', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start'    => [
						'title' => esc_html__( 'Start', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-justify-start-h',
					],
					'center'        => [
						'title' => esc_html__( 'Center', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-justify-center-h',
					],
					'flex-end'      => [
						'title' => esc_html__( 'End', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-justify-end-h',
					],
					'space-between' => [
						'title' => esc_html__( 'Space Between', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-justify-space-between-h',
					],
					'space-around'  => [
						'title' => esc_html__( 'Space Around', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-justify-space-around-h',
					],
					'space-evenly'  => [
						'title' => esc_html__( 'Space Evenly', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-justify-space-evenly-h',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .cariera-countdown' => 'justify-content: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'align_items',
			[
				'label'     => esc_html__( 'Align Items', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-align-start-v',
					],
					'center'     => [
						'title' => esc_html__( 'Center', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-align-center-v',
					],
					'flex-end'   => [
						'title' => esc_html__( 'End', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-align-end-v',
					],
					'stretch'    => [
						'title' => esc_html__( 'Stretch', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-align-stretch-v',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .cariera-countdown' => 'align-items: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'gap',
			[
				'label'      => esc_html__( 'Gaps', 'cariera-core' ),
				'type'       => \Elementor\Controls_Manager::GAPS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'default'    => [
					'column' => 80,
					'row'    => 40,
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .cariera-countdown' => 'gap: {{ROW}}{{UNIT}} {{COLUMN}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);
		$this->add_responsive_control(
			'flex_wrap',
			[
				'label'     => esc_html__( 'Wrap', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'nowrap' => [
						'title' => esc_html__( 'No Wrap', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-nowrap',
					],
					'wrap'   => [
						'title' => esc_html__( 'Wrap', 'cariera-core' ),
						'icon'  => 'eicon-flex eicon-wrap',
					],
				],
				'default'   => 'nowrap',
				'toggle'    => true,
				'selectors' => [
					'{{WRAPPER}} .cariera-countdown' => 'flex-wrap: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'text_align',
			[
				'label'     => esc_html__( 'Text Alignment', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => esc_html__( 'Left', 'cariera-core' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'cariera-core' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'cariera-core' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justify', 'cariera-core' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .cariera-countdown' => 'text-align: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);
		$this->add_control(
			'number_heading',
			[
				'label'     => esc_html__( 'Number (Value)', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_control(
			'countdown_color',
			[
				'label'     => esc_html__( 'Number Color', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .cariera-countdown .value' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'           => 'number_typography',
				'label'          => esc_html__( 'Typography', 'cariera-core' ),
				'selector'       => '{{WRAPPER}} .cariera-countdown .value',
				'fields_options' => [
					'typography'  => [ 'default' => 'custom' ],
					'font_size'   => [
						'default' => [
							'unit' => 'rem',
							'size' => 4,
						],
					],
					'line_height' => [
						'default' => [
							'unit' => 'rem',
							'size' => 4,
						],
					],
				],
			]
		);
		$this->add_control(
			'label_heading',
			[
				'label'     => esc_html__( 'Labels (Days, Hours, etc)', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_control(
			'label_color',
			[
				'label'     => esc_html__( 'Label Color', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .cariera-countdown h6' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'label_typography',
				'label'    => esc_html__( 'Typography', 'cariera-core' ),
				'selector' => '{{WRAPPER}} .cariera-countdown h6',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Script Dependecy
	 */
	public function get_script_depends() {
		return [ 'cariera-countdown' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();
		?>

		<div class="cariera-countdown" data-countdown="<?php echo esc_attr( $settings['countdown_date'] ); ?>" data-days="<?php echo esc_attr( $settings['countdown_days'] ); ?>" data-hours="<?php echo esc_attr( $settings['countdown_hours'] ); ?>" data-mins="<?php echo esc_attr( $settings['countdown_mins'] ); ?>" data-secs="<?php echo esc_attr( $settings['countdown_secs'] ); ?>"></div>
		<?php
	}
}
