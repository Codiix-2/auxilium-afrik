<?php
/**
 * ELEMENTOR WIDGET - LISTING SEARCH
 *
 * @since   1.7.5
 * @version 1.7.5
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Search_Box extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_search_box';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Search Box Form', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-search';
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
			'listing_search',
			[
				'label'       => esc_html__( 'Listing Search Form', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'job_listing' => esc_html__( 'Job Search Form', 'cariera-core' ),
					'resume'      => esc_html__( 'Resume Search Form', 'cariera-core' ),
					'company'     => esc_html__( 'Company Search Form', 'cariera-core' ),
				],
				'default'     => 'job_listing',
				'description' => '',
			]
		);
		$this->add_control(
			'title',
			[
				'label'   => esc_html__( 'Search Box Title', 'cariera-core' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Search Title',
			]
		);
		$this->add_control(
			'keywords',
			[
				'label'        => esc_html__( 'Keywords', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'location',
			[
				'label'        => esc_html__( 'Location', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		if ( class_exists( 'Astoundify_Job_Manager_Regions' ) ) {
			$this->add_control(
				'region',
				[
					'label'        => esc_html__( 'Region', 'cariera-core' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Show', 'cariera-core' ),
					'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => [
						'listing_search' => [ 'job_listing', 'resume' ],
					],
				]
			);
		}
		$this->add_control(
			'categories',
			[
				'label'        => esc_html__( 'Categories', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera-core' ),
				'label_off'    => esc_html__( 'Hide', 'cariera-core' ),
				'return_value' => 'yes',
				'default'      => 'yes',
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
		// Listing search form.
		cariera_get_template(
			'elements/listing-search/listing-searchbox.php',
			[
				'settings' => $this->get_settings(),
			]
		);
	}
}
