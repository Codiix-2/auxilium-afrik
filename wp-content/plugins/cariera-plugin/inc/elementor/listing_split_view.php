<?php
/**
 * ELEMENTOR WIDGET - LISTING HALF DETAIL
 *
 * @since   1.8.3
 * @version 1.9.9
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Split_View extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_split_view';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Split-View', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-off-canvas';
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
			'listing_type',
			[
				'label'       => esc_html__( 'Listing Type', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'job_listing' => esc_html__( 'Job Listings', 'cariera-core' ),
					'resume'      => esc_html__( 'Resumes', 'cariera-core' ),
					'company'     => esc_html__( 'Companies', 'cariera-core' ),
				],
				'default'     => 'job_listing',
				'description' => '',
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
				'condition'   => [
					'listing_type' => 'company',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-listing-split-view' ];
	}

	/**
	 * Script Dependecy
	 */
	public function get_script_depends() {
		return [ 'cariera-listing-split-view' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();

		do_action( 'cariera_listing_split_view_before' );

		wp_enqueue_style( 'cariera-listing-half-detail' );
		wp_enqueue_script( 'cariera-listing-half-detail' );

		\Cariera_Core\Core\Assets::enqueue_maps();

		if ( 'job_listing' === $settings['listing_type'] ) {
			wp_enqueue_style( 'cariera-single-job-listing' );
		}

		if ( 'resume' === $settings['listing_type'] ) {
			wp_enqueue_style( 'cariera-single-resume' );
		}

		if ( 'company' === $settings['listing_type'] ) {
			wp_enqueue_style( 'cariera-single-company' );
			wp_enqueue_style( 'cariera-job-listings' );
		}

		if ( ! empty( $settings['per_page'] ) ) {
			$per_page = 'per_page="' . $settings['per_page'] . '"';
		}

		if ( ! empty( $settings['orderby'] ) ) {
			$orderby = 'orderby="' . $settings['orderby'] . '"';
		}

		if ( ! empty( $settings['order'] ) ) {
			$order = 'order="' . $settings['order'] . '"';
		}

		if ( 'default' === $settings['featured'] ) {
			$featured = '';
		} elseif ( 'show' === $settings['featured'] ) {
			$featured = 'featured="true"';
		} else {
			$featured = 'featured="false"';
		}

		if ( 'default' === $settings['active_jobs'] ) {
			$active_jobs = '';
		} elseif ( 'show' === $settings['active_jobs'] ) {
			$active_jobs = 'active_jobs="true"';
		} else {
			$active_jobs = 'active_jobs="false"';
		}

		cariera_get_template(
			'elements/listing-half/main-details.php',
			[
				'settings'        => $settings,
				'per_page'        => $per_page,
				'orderby'         => $orderby,
				'order'           => $order,
				'featured'        => $featured,
				'active_jobs'     => $active_jobs,
				'count_jobs'      => wp_count_posts( 'job_listing', 'readable' ),
				'count_resumes'   => wp_count_posts( 'resume', 'readable' ),
				'count_companies' => wp_count_posts( 'company', 'readable' ),
			]
		);
	}
}
