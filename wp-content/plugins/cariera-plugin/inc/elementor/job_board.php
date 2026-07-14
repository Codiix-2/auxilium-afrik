<?php
/**
 * ELEMENTOR WIDGET - JOB BOARD
 *
 * @since    1.4.0
 * @version  2.0.0
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Board extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_board';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Job Board', 'cariera-core' );
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
	 * Get job categories. Retrieve the list of categories that the Jobs widget should fetch by default.
	 */
	public function get_wpjm_job_categories() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		$wpjm_categories_options = [];
		$wpjm_categories         = [];

		if ( ! get_option( 'job_manager_enable_categories' ) ) {
			return $wpjm_categories;
		}

		$terms = get_terms(
			[
				'taxonomy'   => 'job_listing_category',
				'hide_empty' => false,
			]
		);

		foreach ( $terms as $term ) {
			$wpjm_categories_options[] = [ $term->slug => $term->name ];
		}

		foreach ( $wpjm_categories_options as $value ) {
			$wpjm_categories += $value;
		}

		return $wpjm_categories;
	}

	/**
	 * Get job categories. Retrieve the list of categories that the Jobs widget should fetch by default.
	 */
	public function get_wpjm_job_types() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		$wpjm_jobtype_options = [];
		$wpjm_types           = [];

		if ( ! get_option( 'job_manager_enable_types' ) ) {
			return $wpjm_types;
		}

		$terms = get_terms(
			[
				'taxonomy'   => 'job_listing_type',
				'hide_empty' => false,
			]
		);

		foreach ( $terms as $term ) {
			$wpjm_jobtype_options[] = [ $term->slug => $term->name ];
		}

		foreach ( $wpjm_jobtype_options as $value ) {
			$wpjm_types += $value;
		}

		return $wpjm_types;
	}

	/**
	 * Get Companies
	 */
	public function get_companies() {
		$options = [];

		if ( ! get_option( 'cariera_company_manager_integration' ) ) {
			return $options;
		}

		$companies = get_posts(
			[
				'post_type'      => 'company',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			]
		);

		if ( ! empty( $companies ) ) {
			foreach ( $companies as $company ) {
				$options[ $company->ID ] = $company->post_title;
			}
		}

		return $options;
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
				'label'       => esc_html__( 'Job Layout', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'list' => esc_html__( 'List', 'cariera-core' ),
					'grid' => esc_html__( 'Grid', 'cariera-core' ),
				],
				'default'     => 'list',
				'description' => esc_html__( 'Choose the layout style for your jobs.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'list_layout',
			[
				'label'       => esc_html__( 'Job List Styles', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'1' => esc_html__( 'Version 1', 'cariera-core' ),
					'2' => esc_html__( 'Version 2', 'cariera-core' ),
					'3' => esc_html__( 'Version 3', 'cariera-core' ),
					'4' => esc_html__( 'Version 4', 'cariera-core' ),
					'5' => esc_html__( 'Version 5', 'cariera-core' ),
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
				'label'       => esc_html__( 'Job Grid Styles', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'1' => esc_html__( 'Version 1', 'cariera-core' ),
					'2' => esc_html__( 'Version 2', 'cariera-core' ),
					'3' => esc_html__( 'Version 3', 'cariera-core' ),
					'4' => esc_html__( 'Version 4', 'cariera-core' ),
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
				'description' => esc_html__( 'How many items to show in the job board.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'orderby',
			[
				'label'       => esc_html__( 'Order by', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'featured'      => esc_html__( 'Featured', 'cariera-core' ),
					'date'          => esc_html__( 'Date', 'cariera-core' ),
					'ID'            => esc_html__( 'ID', 'cariera-core' ),
					'author'        => esc_html__( 'Author', 'cariera-core' ),
					'title'         => esc_html__( 'Title', 'cariera-core' ),
					'modified'      => esc_html__( 'Modified', 'cariera-core' ),
					'rand'          => esc_html__( 'Random', 'cariera-core' ),
					'rand_featured' => esc_html__( 'Random Featured', 'cariera-core' ),
				],
				'default'     => 'featured',
				'description' => '',
			]
		);
		$this->add_control(
			'featured_first',
			[
				'label'        => esc_html__( 'Featured First', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'cariera-core' ),
				'label_off'    => esc_html__( 'Off', 'cariera-core' ),
				'return_value' => 'enable',
				'default'      => '',
				'description'  => '',
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
			'sidebar_search',
			[
				'label'        => esc_html__( 'Sidebar Search', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'cariera-core' ),
				'label_off'    => esc_html__( 'Off', 'cariera-core' ),
				'return_value' => 'enable',
				'default'      => '',
				'description'  => esc_html__( 'Make sure to activate this option if the "show filters" is deactivated and you are using a sidebar search.', 'cariera-core' ),
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
					'{{WRAPPER}} .job_listings nav.job-manager-pagination, {{WRAPPER}} .job_listings .load_more_jobs'    => 'display: none !important',
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
				'description' => esc_html__( 'Set to "Show" to show only featured jobs, "Hide" to hide the featured jobs, or default show both (featured first).', 'cariera-core' ),
			]
		);
		$this->add_control(
			'filled',
			[
				'label'       => esc_html__( 'Filled', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'default' => esc_html__( 'Default', 'cariera-core' ),
					'show'    => esc_html__( 'Show', 'cariera-core' ),
					'hide'    => esc_html__( 'Hide', 'cariera-core' ),
				],
				'default'     => 'default',
				'description' => esc_html__( 'Set to "Show" to show only filled jobs, "Hide" to hide the filled jobs, or default show everything.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'keyword',
			[
				'label'       => esc_html__( 'Keyword', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Enter a default keyword to search', 'cariera-core' ),
			]
		);
		$this->add_control(
			'location',
			[
				'label'       => esc_html__( 'Location', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Enter default location to search', 'cariera-core' ),
			]
		);
		$this->add_control(
			'remote_position',
			[
				'label'       => esc_html__( 'Remote Position', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'default' => esc_html__( 'Default', 'cariera-core' ),
					'show'    => esc_html__( 'Show', 'cariera-core' ),
					'hide'    => esc_html__( 'Hide', 'cariera-core' ),
				],
				'default'     => 'default',
				'description' => esc_html__( 'Set to "Show" to show only remote jobs, "Hide" to hide the filled jobs, or default show everything.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'categories',
			[
				'label'       => esc_html__( 'Categories', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => [],
				'multiple'    => true,
				'options'     => self::get_wpjm_job_categories(),
				'description' => esc_html__( 'Limit the jobs to certain categories', 'cariera-core' ),
			]
		);
		$this->add_control(
			'job_types',
			[
				'label'       => esc_html__( 'Job Types', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => [],
				'multiple'    => true,
				'options'     => self::get_wpjm_job_types(),
				'description' => esc_html__( 'Limit the jobs to certain job types', 'cariera-core' ),
			]
		);
		if ( get_option( 'cariera_company_manager_integration' ) ) {
			$this->add_control(
				'companies',
				[
					'label'       => esc_html__( 'Companies', 'cariera-core' ),
					'type'        => \Elementor\Controls_Manager::SELECT2,
					'default'     => [],
					'options'     => self::get_companies(),
					'multiple'    => true,
					'description' => esc_html__( 'Limit the jobs to certain companies.', 'cariera-core' ),
				]
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Script Dependecy
	 */
	public function get_script_depends() {
		return [ 'wp-job-manager-ajax-filters' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();

		$atts = [];

		// Layout.
		if ( 'grid' === $settings['layout'] ) {
			$atts['jobs_layout'] = 'grid';

			if ( '1' !== $settings['grid_layout'] ) {
				$atts['jobs_grid_version'] = $settings['grid_layout'];
			}
		} elseif ( '1' !== $settings['list_layout'] ) {
			$atts['jobs_list_version'] = $settings['list_layout'];
		}

		// Per Page.
		$atts['per_page'] = ! empty( $settings['per_page'] ) ? $settings['per_page'] : 10;
		$atts['orderby']  = $settings['orderby'] ?? 'featured';
		$atts['order']    = $settings['order'] ?? 'DESC';

		// Flags.
		if ( 'enable' === $settings['featured_first'] ) {
			$atts['featured_first'] = 'true';
		}

		$atts['show_filters'] = ( 'show' === $settings['filters'] ) ? 'true' : 'false';

		if ( 'enable' === $settings['sidebar_search'] ) {
			$atts['sidebar_search'] = 'true';
		}

		if ( isset( $settings['pagination'] ) ) {
			$atts['show_pagination'] = $settings['pagination'];
		}

		// Featured / Filled / Remote.
		if ( 'default' !== $settings['featured'] ) {
			$atts['featured'] = ( 'show' === $settings['featured'] ) ? 'true' : 'false';
		}

		if ( 'default' !== $settings['filled'] ) {
			$atts['filled'] = ( 'show' === $settings['filled'] ) ? 'true' : 'false';
		}

		if ( 'default' !== $settings['remote_position'] ) {
			$atts['remote_position'] = ( 'show' === $settings['remote_position'] ) ? 'true' : 'false';
		}

		// Search.
		if ( ! empty( $settings['keyword'] ) ) {
			$atts['keywords'] = sanitize_text_field( $settings['keyword'] );
		}

		// Location.
		if ( ! empty( $settings['location'] ) ) {
			$atts['location'] = sanitize_text_field( $settings['location'] );
		}

		// Categories.
		if ( ! empty( $settings['categories'] ) ) {
			$atts['categories'] = implode( ',', array_filter( $settings['categories'] ) );
		}

		// Job Types.
		if ( ! empty( $settings['job_types'] ) ) {
			$atts['job_types'] = implode( ',', array_filter( $settings['job_types'] ) );
		}

		// Companies.
		if ( ! empty( $settings['companies'] ) && get_option( 'cariera_company_manager_integration' ) ) {
			$company_ids = array_map( 'intval', $settings['companies'] );
			$company_ids = array_filter( $company_ids );

			if ( ! empty( $company_ids ) ) {
				$atts['companies'] = implode( ',', $company_ids );
			}
		}

		// Build shortcode string safely.
		$shortcode = '[jobs';

		foreach ( $atts as $key => $value ) {
			$shortcode .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		$shortcode .= ']';

		echo do_shortcode( $shortcode );
	}
}
