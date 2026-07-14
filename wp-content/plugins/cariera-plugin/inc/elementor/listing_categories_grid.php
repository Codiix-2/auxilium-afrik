<?php
/**
 * ELEMENTOR WIDGET - LISTING CATEGORIES GRID
 *
 * @since    1.4.5
 * @version  1.8.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Categories_Grid extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_categories_grid';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Categories Grid', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
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
			'listing',
			[
				'label'       => esc_html__( 'Listing', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $this->get_listing_types(),
				'default'     => 'job_listing',
				'description' => '',
			]
		);
		$this->add_control(
			'category_layout',
			[
				'label'       => esc_html__( 'Category Grid Layout', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'layout1' => esc_html__( 'Layout 1', 'cariera-core' ),
					'layout2' => esc_html__( 'Layout 2', 'cariera-core' ),
					'layout3' => esc_html__( 'Layout 3', 'cariera-core' ),
					'layout4' => esc_html__( 'Layout 4', 'cariera-core' ),
				],
				'default'     => 'layout1',
				'description' => '',
			]
		);
		$this->add_control(
			'icon',
			[
				'label'        => esc_html__( 'Category Icon', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
				'condition'    => [
					'category_layout' => [ 'layout1', 'layout2' ],
				],
			]
		);
		$this->add_control(
			'background',
			[
				'label'        => esc_html__( 'Category Background', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
				'condition'    => [
					'category_layout' => 'layout1',
				],
			]
		);
		$this->add_control(
			'job_counter',
			[
				'label'        => esc_html__( 'Job Counter', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => esc_html__( 'Show number of jobs inside of category', 'cariera-core' ),
				'condition'    => [
					'listing' => 'job_listing',
				],
			]
		);
		$this->add_control(
			'resume_counter',
			[
				'label'        => esc_html__( 'Resume Counter', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => esc_html__( 'Show number of resumes inside of the category.', 'cariera-core' ),
				'condition'    => [
					'listing' => 'resume',
				],
			]
		);
		$this->add_control(
			'company_counter',
			[
				'label'        => esc_html__( 'Company Counter', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => esc_html__( 'Show number of companies inside of the category.', 'cariera-core' ),
				'condition'    => [
					'listing' => 'company',
				],
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
				'description'  => esc_html__( 'Hides categories that doesn\'t have any listings.', 'cariera-core' ),
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
				'description' => esc_html__( 'Set max limit for items (limited to 1000).', 'cariera-core' ),
			]
		);
		$this->add_control(
			'exclude_job_listing',
			[
				'label'     => esc_html__( 'Exclude Job Categories', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => [],
				'options'   => $this->get_terms( 'job_listing_category' ),
				'condition' => [
					'listing' => 'job_listing',
				],
			]
		);
		$this->add_control(
			'exclude_resume',
			[
				'label'     => esc_html__( 'Exclude Resume Categories', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => [],
				'options'   => $this->get_terms( 'resume_category' ),
				'condition' => [
					'listing' => 'resume',
				],
			]
		);
		$this->add_control(
			'exclude_company',
			[
				'label'     => esc_html__( 'Exclude Company Categories', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => [],
				'options'   => $this->get_terms( 'company_category' ),
				'condition' => [
					'listing' => 'company',
				],
			]
		);
		$this->add_control(
			'exclude_cariera_event',
			[
				'label'     => esc_html__( 'Exclude Event Categories', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => [],
				'options'   => $this->get_terms( 'cariera_event_category' ),
				'condition' => [
					'listing' => 'cariera_event',
				],
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

		$settings = $this->get_settings();
		$listing  = $settings['listing'];

		$categories = get_terms(
			[
				'taxonomy'   => $settings['listing'] . '_category',
				'orderby'    => $settings['orderby'],
				'order'      => $settings['order'],
				'hide_empty' => $settings['hide_empty'],
				'number'     => $settings['items'],
				'exclude'    => $settings[ 'exclude_' . $listing ],
			]
		);

		if ( is_wp_error( $categories ) ) {
			return;
		}

		$chunks = cariera_partition( $categories, 1 );

		cariera_get_template(
			'elements/listing-category/categories-grid.php',
			[
				'settings' => $settings,
				'chunks'   => $chunks,
			]
		);
	}

	/**
	 * Get Terms of a taxonomy
	 *
	 * @param mixed $taxonomy
	 */
	protected function get_terms( $taxonomy ) {
		$taxonomies = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		);

		$options = [ '' => '' ];

		if ( is_array( $taxonomies ) && ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$options[ $taxonomy->term_id ] = $taxonomy->name;
			}
		}

		return $options;
	}

	/**
	 * Get listing types.
	 */
	protected function get_listing_types() {
		$listing_types = [
			'job_listing' => esc_html__( 'Job Listing', 'cariera-core' ),
			'resume'      => esc_html__( 'Resume', 'cariera-core' ),
			'company'     => esc_html__( 'Company', 'cariera-core' ),
		];

		if ( class_exists( 'Cariera_Events' ) ) {
			$listing_types['cariera_event'] = cariera_events_get_listing_singular_label();
		}

		return $listing_types;
	}
}
