<?php
/**
 * ELEMENTOR WIDGET - LISTING DASHBOARD
 *
 * @since   1.7.5
 * @version 1.9.3
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Dashboard extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_dashboard';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Dashboard', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-post-list';
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
			'listing_dashboard',
			[
				'label'       => esc_html__( 'Select Dashboard', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'job_listing' => esc_html__( 'Job Dashboard', 'cariera-core' ),
					'resume'      => esc_html__( 'Resume Dashboard', 'cariera-core' ),
					'company'     => esc_html__( 'Company Dashboard', 'cariera-core' ),
				],
				'default'     => 'job_listing',
				'description' => '',
			]
		);
		$this->add_control(
			'posts_per_page',
			[
				'label'       => esc_html__( 'Posts Per Page', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 25,
				'min'         => 1,
				'description' => esc_html__( 'Set how many items to show per page.', 'cariera-core' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-wpjm-dashboards' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings       = $this->get_settings();
		$posts_per_page = ! empty( $settings['posts_per_page'] ) ? absint( $settings['posts_per_page'] ) : 25;

		$dashboards = [
			'job_listing' => 'job_dashboard',
			'resume'      => 'candidate_dashboard',
			'company'     => 'company_dashboard',
		];

		$dashboard = $dashboards[ $settings['listing_dashboard'] ] ?? null;

		if ( $dashboard ) {
			echo do_shortcode( sprintf( '[%s posts_per_page="%d"]', esc_attr( $dashboard ), $posts_per_page ) );
		}
	}
}
