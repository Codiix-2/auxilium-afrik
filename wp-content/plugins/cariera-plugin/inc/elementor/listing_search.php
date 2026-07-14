<?php
/**
 * ELEMENTOR WIDGET - LISTING SEARCH
 *
 * @since   1.7.5
 * @version 1.8.4
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Search extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_search';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Search Form', 'cariera-core' );
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
	 * Get value options.
	 */
	protected function get_value_options() {
		$value_options = [
			'job_listing' => esc_html__( 'Job Search Form', 'cariera-core' ),
			'resume'      => esc_html__( 'Resume Search Form', 'cariera-core' ),
			'company'     => esc_html__( 'Company Search Form', 'cariera-core' ),
		];

		$value_options = apply_filters( 'cariera_listing_search_form_types', $value_options );

		return $value_options;
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
				'options'     => $this->get_value_options(),
				'default'     => 'job_listing',
				'description' => '',
			]
		);
		$this->add_control(
			'search_style',
			[
				'label'       => esc_html__( 'Search Layout', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'style-1' => esc_html__( 'Style 1', 'cariera-core' ),
					'style-2' => esc_html__( 'Style 2', 'cariera-core' ),
				],
				'default'     => 'style-1',
				'description' => esc_html__( 'Choose the layout version that you want your search to have.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'keyword_autocomple',
			[
				'label'        => esc_html__( 'Keyword Autocomplete', 'cariera-core' ),
				'description'  => esc_html__( 'Disable to turn off keyword autocomplete for listing search.', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera-core' ),
				'label_off'    => esc_html__( 'Disable', 'cariera-core' ),
				'return_value' => 'true',
				'default'      => 'true',
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
		$settings = $this->get_settings();

		// Job search form.
		if ( 'job_listing' === $settings['listing_search'] ) {
			cariera_get_template(
				'elements/listing-search/job-search.php',
				[
					'settings' => $settings,
				]
			);
		}

		// Resume search form.
		if ( 'resume' === $settings['listing_search'] ) {
			cariera_get_template(
				'elements/listing-search/resume-search.php',
				[
					'settings' => $settings,
				]
			);
		}

		// Company search form.
		if ( 'company' === $settings['listing_search'] ) {
			cariera_get_template(
				'elements/listing-search/company-search.php',
				[
					'settings' => $settings,
				]
			);
		}

		do_action( 'cariera_listing_search_form_rendering', $settings );
	}
}
