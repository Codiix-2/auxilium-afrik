<?php
/**
 * ELEMENTOR WIDGET - TESTIMONIALS
 *
 * @since    1.4.5
 * @version  1.8.1
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Testimonials extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'testimonials';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Testimonials', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-slider-device';
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
			'layout_style',
			[
				'label'       => esc_html__( 'Testimonial Style', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'layout1' => esc_html__( 'Layout 1', 'cariera-core' ),
					'layout2' => esc_html__( 'Layout 2', 'cariera-core' ),
					'layout3' => esc_html__( 'Layout 3', 'cariera-core' ),
				],
				'default'     => 'layout1',
				'description' => '',
			]
		);
		$this->add_control(
			'show_testimonials',
			[
				'label'       => esc_html__( 'Show Testimonials', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'random'   => esc_html__( 'Random', 'cariera-core' ),
					'latest'   => esc_html__( 'Latest', 'cariera-core' ),
					'selected' => esc_html__( 'Selected IDs', 'cariera-core' ),
				],
				'default'     => 'random',
				'description' => '',
			]
		);
		$this->add_control(
			'ids',
			[
				'label'       => esc_html__( 'Enter Post IDs', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter Post ID\'s separated by a comma. Leave empty to show all.', 'cariera-core' ),
				'condition'   => [
					'show_testimonials' => 'selected',
				],
			]
		);
		$this->add_control(
			'posts_per_page',
			[
				'label'       => esc_html__( 'Posts to show', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '5',
				'description' => esc_html__( 'Number of testimonials to show (-1 for all).', 'cariera-core' ),
				'condition'   => [
					'show_testimonials' => [ 'random', 'latest' ],
				],
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

		// CONTROLS.
		$this->add_control(
			'icon_color',
			[
				'label'       => esc_html__( 'Title Color', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => '',
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .testimonial-item .testimonial .customer .title' => 'color: {{VALUE}}' ],
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
		return [ 'cariera-testimonials' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		wp_enqueue_style( 'cariera-testimonials' );

		$settings = $this->get_settings();

		if ( 'layout1' === $settings['layout_style'] ) {
			$layout_class = 'testimonials-carousel-style1';
		} elseif ( 'layout2' === $settings['layout_style'] ) {
			$layout_class = 'testimonials-carousel-style2';
		} elseif ( 'layout3' === $settings['layout_style'] ) {
			$layout_class = 'testimonials-carousel-style3';
		} elseif ( 'layout4' === $settings['layout_style'] ) {
			$layout_class = 'testimonials-carousel-style4';
		}

		if ( 'selected' === $settings['show_testimonials'] ) {
			$show_only_ids = explode( ',', $settings['ids'] );
			$args          = [
				'post_type' => 'testimonial',
				'post__in'  => $show_only_ids,
			];
		} else {
			$args = [
				'post_type'      => 'testimonial',
				'posts_per_page' => $settings['posts_per_page'],
			];
		}

		echo '<div class="testimonials-carousel ' . esc_attr( $layout_class ) . '">';

		$posts_query = new \WP_Query( $args );
		if ( $posts_query->have_posts() ) {
			while ( $posts_query->have_posts() ) :
				$posts_query->the_post();
				global $post;
				echo '<div class="testimonial-item">';

					// TESTIMONIALS LAYOUT 1.
				if ( 'layout1' === $settings['layout_style'] ) {
					get_template_part( '/templates/content/content-testimonial1' );
				}

					// TESTIMONIALS LAYOUT 2.
				elseif ( 'layout2' === $settings['layout_style'] ) {
					get_template_part( '/templates/content/content-testimonial2' );
				}

					// TESTIMONIALS LAYOUT 3.
				elseif ( 'layout3' === $settings['layout_style'] ) {
					get_template_part( '/templates/content/content-testimonial3' );
				}

				echo '</div>';

			endwhile;
		}

		echo '</div>';

		wp_reset_postdata();
	}
}
