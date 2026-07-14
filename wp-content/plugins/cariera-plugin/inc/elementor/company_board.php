<?php
/**
 * ELEMENTOR WIDGET - COMPANY BOARD
 *
 * @since    1.4.0
 * @version  1.9.7
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Company_Board extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'company_board';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Company Board', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-posts-justified';
	}

	/**
	 * Get widget's categories.
	 */
	public function get_categories() {
		return [ 'cariera-elements' ];
	}

	/**
	 * Get Company categories. Retrieve the list of categories that the Company widget should fetch by default.
	 */
	public function get_wpjm_company_categories() {
		$wpjm_categories_options = [];

		$terms = get_terms(
			[
				'taxonomy'   => 'company_category',
				'hide_empty' => false,
			]
		);

		foreach ( $terms as $term ) {
			$wpjm_categories_options[] = [ $term->slug => $term->name ];
		}

		$wpjm_categories = [];

		foreach ( $wpjm_categories_options as $value ) {
			$wpjm_categories += $value;
		}

		return $wpjm_categories;
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
			'layout',
			[
				'label'       => esc_html__( 'Company Layout', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'list' => esc_html__( 'List', 'cariera-core' ),
					'grid' => esc_html__( 'Grid', 'cariera-core' ),
				],
				'default'     => 'list',
				'description' => esc_html__( 'Choose the layout style for your companies.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'list_layout',
			[
				'label'       => esc_html__( 'Company List Styles', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'1' => esc_html__( 'Version 1', 'cariera-core' ),
					'2' => esc_html__( 'Version 2', 'cariera-core' ),
				],
				'default'     => '1',
				'description' => '',
				'condition'   => [
					'layout' => 'list',
				],
			]
		);
		$this->add_control(
			'grid_layout',
			[
				'label'       => esc_html__( 'Company Grid Styles', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'1' => esc_html__( 'Version 1', 'cariera-core' ),
					'2' => esc_html__( 'Version 2', 'cariera-core' ),
					'3' => esc_html__( 'Version 3', 'cariera-core' ),
				],
				'default'     => '1',
				'description' => '',
				'condition'   => [
					'layout' => 'grid',
				],
			]
		);
		$this->add_control(
			'per_page',
			[
				'label'       => esc_html__( 'Items per Page', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '10',
				'description' => esc_html__( 'How many items to show in the company board.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'orderby',
			[
				'label'       => esc_html__( 'Order by', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'featured' => esc_html__( 'Featured', 'cariera-core' ),
					'date'     => esc_html__( 'Date', 'cariera-core' ),
					'title'    => esc_html__( 'Title', 'cariera-core' ),
					'ID'       => esc_html__( 'ID', 'cariera-core' ),
					'name'     => esc_html__( 'Name', 'cariera-core' ),
					'modified' => esc_html__( 'Modified', 'cariera-core' ),
					'rand'     => esc_html__( 'Random', 'cariera-core' ),
				],
				'default'     => 'featured',
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
			'filters',
			[
				'label'        => esc_html__( 'Show Filters', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
			]
		);
		$this->add_control(
			'hide_pagination',
			[
				'label'        => esc_html__( 'Hide Pagination', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Hide', 'cariera-core' ),
				'label_off'    => esc_html__( 'Show', 'cariera-core' ),
				'return_value' => 'true',
				'default'      => '',
				'description'  => '',
				'selectors'    => [
					'{{WRAPPER}} .company_listings nav.company-manager-pagination, {{WRAPPER}} .company_listings .load_more_companies'   => 'display: none !important',
				],
			]
		);
		$this->add_control(
			'pagination',
			[
				'label'       => esc_html__( 'Pagination Style', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'false' => esc_html__( 'Load More', 'cariera-core' ),
					'true'  => esc_html__( 'Numeric', 'cariera-core' ),
				],
				'default'     => 'false',
				'description' => '',
				'condition'   => [
					'hide_pagination' => '',
				],
			]
		);
		$this->add_control(
			'featured',
			[
				'label'       => esc_html__( 'Featured', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'default' => esc_html__( 'Default', 'cariera-core' ),
					'show'    => esc_html__( 'Show', 'cariera-core' ),
					'hide'    => esc_html__( 'Hide', 'cariera-core' ),
				],
				'default'     => 'default',
				'description' => esc_html__( 'Set to "Show" to show only featured companies, "Hide" to hide the featured companies, or default show both (featured first).', 'cariera-core' ),
			]
		);
		$this->add_control(
			'active_jobs',
			[
				'label'       => esc_html__( 'Active Jobs', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'default' => esc_html__( 'Default', 'cariera-core' ),
					'show'    => esc_html__( 'Show', 'cariera-core' ),
					'hide'    => esc_html__( 'Hide', 'cariera-core' ),
				],
				'default'     => 'default',
				'description' => esc_html__( 'Set to "Show" to show only companies with jobs, "Hide" to hide companies with jobs, or default show both.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'categories',
			[
				'label'       => esc_html__( 'Categories', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => [],
				'multiple'    => true,
				'options'     => self::get_wpjm_company_categories(),
				'description' => esc_html__( 'Limit the companies to certain categories', 'cariera-core' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Script Dependecy
	 */
	public function get_script_depends() {
		return [ 'cariera-company-ajax-filters' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();

		$atts = [];

		// Layout.
		if ( 'grid' === $settings['layout'] ) {
			$atts['companies_layout'] = 'grid';

			if ( '1' !== $settings['grid_layout'] ) {
				$atts['companies_grid_version'] = $settings['grid_layout'];
			}
		} elseif ( '1' !== $settings['list_layout'] ) {
			$atts['companies_list_version'] = $settings['list_layout'];
		}

		// Per page.
		if ( ! empty( $settings['per_page'] ) ) {
			$atts['per_page'] = $settings['per_page'];
		}

		if ( ! empty( $settings['orderby'] ) ) {
			$atts['orderby'] = $settings['orderby'];
		}

		if ( ! empty( $settings['order'] ) ) {
			$atts['order'] = $settings['order'];
		}

		// Filters.
		$atts['show_filters'] = ( 'show' === $settings['filters'] ) ? 'true' : 'false';

		// Pagination.
		if ( isset( $settings['pagination'] ) ) {
			$atts['show_pagination'] = $settings['pagination'];
		}

		// Featured.
		if ( 'default' !== $settings['featured'] ) {
			$atts['featured'] = ( 'show' === $settings['featured'] ) ? 'true' : 'false';
		}

		// Active jobs.
		if ( 'default' !== $settings['active_jobs'] ) {
			$atts['active_jobs'] = ( 'show' === $settings['active_jobs'] ) ? 'true' : 'false';
		}

		// Categories.
		if ( ! empty( $settings['categories'] ) ) {
			$atts['categories'] = implode( ',', array_filter( $settings['categories'] ) );
		}

		// Build shortcode safely.
		$shortcode = '[companies';

		foreach ( $atts as $key => $value ) {
			$shortcode .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		$shortcode .= ']';

		echo do_shortcode( $shortcode );
	}
}
