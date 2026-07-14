<?php
/**
 * ELEMENTOR WIDGET - LISTING HALF MAP
 *
 * @since   1.9.9
 * @version 2.0.0
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Half_Map extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'listing_half_map';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Half Map', 'cariera-core' );
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
			'listings_position',
			[
				'label'       => esc_html__( 'Listings Position', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'left-side',
				'description' => esc_html__( 'Choose which side the listings appear on.', 'cariera-core' ),
				'options'     => [
					'left-side'  => esc_html__( 'Left Side', 'cariera-core' ),
					'right-side' => esc_html__( 'Right Side', 'cariera-core' ),
				],
			]
		);
		$this->add_control(
			'search_form',
			[
				'label'       => esc_html__( 'Search Form', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'top',
				'description' => esc_html__( 'Choose your listing search form position.', 'cariera-core' ),
				'options'     => [
					'none'      => esc_html__( 'None', 'cariera-core' ),
					'top'       => esc_html__( 'Top', 'cariera-core' ),
					'offcanvas' => esc_html__( 'Off-Canvas', 'cariera-core' ),
				],
			]
		);
		$this->add_control(
			'move_map',
			[
				'label'        => esc_html__( 'Move Map Search', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera-core' ),
				'label_off'    => esc_html__( 'Disable', 'cariera-core' ),
				'return_value' => 'true',
				'default'      => '',
				'description'  => esc_html__( 'Enable to show the "Search as I move the map" option in the map.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'per_page',
			[
				'label'       => esc_html__( 'Items per Page', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '10',
				'placeholder' => '10',
				'description' => esc_html__( 'How many listing will be shown.', 'cariera-core' ),
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
			]
		);
		// Jobs.
		$this->add_control(
			'job_listing_title',
			[
				'label'       => esc_html__( 'Job Title', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Your career starts now!', 'cariera-core' ),
				'description' => esc_html__( 'Enter the heading text that will appear above the job search.', 'cariera-core' ),
				'condition'   => [
					'listing_type' => 'job_listing',
				],
			]
		);
		$this->add_control(
			'job_layout',
			[
				'label'     => esc_html__( 'Job Listings Layout', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'list4',
				'options'   => [
					'list1' => esc_html__( 'List Layout 1', 'cariera-core' ),
					'list2' => esc_html__( 'List Layout 2', 'cariera-core' ),
					'list3' => esc_html__( 'List Layout 3', 'cariera-core' ),
					'list4' => esc_html__( 'List Layout 4', 'cariera-core' ),
					'list5' => esc_html__( 'List Layout 5', 'cariera-core' ),
					'grid1' => esc_html__( 'Grid Layout 1', 'cariera-core' ),
					'grid2' => esc_html__( 'Grid Layout 2', 'cariera-core' ),
					'grid3' => esc_html__( 'Grid Layout 3', 'cariera-core' ),
					'grid4' => esc_html__( 'Grid Layout 4', 'cariera-core' ),
				],
				'condition' => [
					'listing_type' => 'job_listing',
				],
			]
		);
		// Resumes.
		$this->add_control(
			'resume_listing_title',
			[
				'label'       => esc_html__( 'Resume Title', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Find the right Candidate for your business!', 'cariera-core' ),
				'description' => esc_html__( 'Enter the heading text that will appear above the resume search.', 'cariera-core' ),
				'condition'   => [
					'listing_type' => 'resume',
				],
			]
		);
		$this->add_control(
			'resume_layout',
			[
				'label'     => esc_html__( 'Resumes Layout', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'list1',
				'options'   => [
					'list1' => esc_html__( 'List Layout 1', 'cariera-core' ),
					'list2' => esc_html__( 'List Layout 2', 'cariera-core' ),
					'grid1' => esc_html__( 'Grid Layout 1', 'cariera-core' ),
					'grid2' => esc_html__( 'Grid Layout 2', 'cariera-core' ),
					'grid3' => esc_html__( 'Grid Layout 3', 'cariera-core' ),
				],
				'condition' => [
					'listing_type' => 'resume',
				],
			]
		);
		// Companies.
		$this->add_control(
			'company_listing_title',
			[
				'label'       => esc_html__( 'Company Title', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Find the perfect Company for you!', 'cariera-core' ),
				'description' => esc_html__( 'Enter the heading text that will appear above the company search.', 'cariera-core' ),
				'condition'   => [
					'listing_type' => 'company',
				],
			]
		);
		$this->add_control(
			'company_layout',
			[
				'label'     => esc_html__( 'Companies Layout', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'list1',
				'options'   => [
					'list1' => esc_html__( 'List Layout 1', 'cariera-core' ),
					'list2' => esc_html__( 'List Layout 2', 'cariera-core' ),
					'grid1' => esc_html__( 'Grid Layout 1', 'cariera-core' ),
					'grid2' => esc_html__( 'Grid Layout 2', 'cariera-core' ),
					'grid3' => esc_html__( 'Grid Layout 3', 'cariera-core' ),
				],
				'condition' => [
					'listing_type' => 'company',
				],
			]
		);

		$this->end_controls_section();

		// STYLE SECTION.
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'cariera-core' ),
			]
		);

		// Controls.
		$this->add_control(
			'cluster_bg_color',
			[
				'label'     => esc_html__( 'Cluster Background Color', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .marker-cluster span, {{WRAPPER}} .marker-cluster span::after' => 'background: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'cluster_color',
			[
				'label'     => esc_html__( 'Cluster Color', 'cariera-core' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .marker-cluster span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-listing-half-map' ];
	}

	/**
	 * Script Dependecy
	 */
	public function get_script_depends() {
		return [ 'cariera-listing-half-map' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings     = $this->get_settings();
		$listing_type = $settings['listing_type'];

		// Listings position.
		$listings_position = isset( $settings['listings_position'] ) ? $settings['listings_position'] : 'left-side';
		$map_side          = ( 'left-side' === $listings_position ) ? 'map-wrapper-right' : 'map-wrapper-left';
		$list_side         = ( 'left-side' === $listings_position ) ? 'listing-wrapper-left' : 'listing-wrapper-right';

		// Titles.
		$job_title     = isset( $settings['job_listing_title'] ) ? $settings['job_listing_title'] : '';
		$resume_title  = isset( $settings['resume_listing_title'] ) ? $settings['resume_listing_title'] : '';
		$company_title = isset( $settings['company_listing_title'] ) ? $settings['company_listing_title'] : '';

		// Layouts.
		$job_layout     = isset( $settings['job_layout'] ) ? $settings['job_layout'] : 'list4';
		$resume_layout  = isset( $settings['resume_layout'] ) ? $settings['resume_layout'] : 'list1';
		$company_layout = isset( $settings['company_layout'] ) ? $settings['company_layout'] : 'list1';

		// Whether to suppress the shortcode's built-in filters.
		$search_form_position = isset( $settings['search_form'] ) ? $settings['search_form'] : 'top';
		$hide_filters         = in_array( $search_form_position, [ 'none', 'offcanvas' ], true );
		$filters_attr         = $hide_filters ? ' show_filters="false"' : '';

		// Search as I move the map.
		$move_map = isset( $settings['move_map'] ) ? $settings['move_map'] : '';

		// Per page.
		$per_page      = isset( $settings['per_page'] ) ? intval( $settings['per_page'] ) : '';
		$per_page_attr = $per_page ? ' per_page="' . $per_page . '"' : '';

		// Pagination.
		$pagination      = isset( $settings['pagination'] ) ? $settings['pagination'] : 'false';
		$pagination_attr = ' show_pagination="' . esc_attr( $pagination ) . '"';

		wp_enqueue_script( 'cariera-listing-half-map' );
		wp_enqueue_style( 'cariera-listing-half-map' );

		// Determine shortcode based on listing type.
		switch ( $listing_type ) {
			case 'resume':
				$title = $resume_title;

				$resume_map = [
					'list1' => '[resumes' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'list2' => '[resumes resumes_list_version="2"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid1' => '[resumes resumes_layout="grid" resumes_grid_version="1"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid2' => '[resumes resumes_layout="grid" resumes_grid_version="2"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid3' => '[resumes resumes_layout="grid" resumes_grid_version="3"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
				];

				$listings_shortcode = isset( $resume_map[ $resume_layout ] ) ? $resume_map[ $resume_layout ] : '[resumes' . $pagination_attr . $filters_attr . $per_page_attr . ']';
				break;

			case 'company':
				$title = $company_title;

				$company_map = [
					'list1' => '[companies' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'list2' => '[companies companies_list_version="2"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid1' => '[companies companies_layout="grid" companies_grid_version="1"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid2' => '[companies companies_layout="grid" companies_grid_version="2"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid3' => '[companies companies_layout="grid" companies_grid_version="3"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
				];

				$listings_shortcode = isset( $company_map[ $company_layout ] ) ? $company_map[ $company_layout ] : '[companies' . $pagination_attr . $filters_attr . $per_page_attr . ']';
				break;

			case 'job_listing':
			default:
				$title = $job_title;

				$job_map = [
					'list1' => '[jobs' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'list2' => '[jobs jobs_list_version="2"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'list3' => '[jobs jobs_list_version="3"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'list4' => '[jobs jobs_list_version="4"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'list5' => '[jobs jobs_list_version="5"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid1' => '[jobs jobs_layout="grid" jobs_grid_version="1"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid2' => '[jobs jobs_layout="grid" jobs_grid_version="2"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid3' => '[jobs jobs_layout="grid" jobs_grid_version="3"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
					'grid4' => '[jobs jobs_layout="grid" jobs_grid_version="4"' . $pagination_attr . $filters_attr . $per_page_attr . ']',
				];

				$listings_shortcode = isset( $job_map[ $job_layout ] ) ? $job_map[ $job_layout ] : '[jobs jobs_list_version="4"' . $filters_attr . $per_page_attr . ']';
				break;
		}
		?>

		<main class="half-map-wrapper listings-half-map">
			<div class="responsive-nav">
				<ul class="nav nav-tabs">
					<li class="show-results active">
						<a href="#" class="list-view">
							<i class="las la-list"></i>
							<?php esc_html_e( 'List view', 'cariera-core' ); ?>
						</a>
					</li>
					<li class="show-map">
						<a href="#" class="map-view">
							<i class="las la-map"></i>
							<?php esc_html_e( 'Map view', 'cariera-core' ); ?>
						</a>
					</li>
				</ul>
			</div>

			<div class="map-wrapper <?php echo esc_attr( $map_side ); ?>">
				<?php if ( $move_map ) { ?>
					<div class="move-map-search">
						<div class="checkbox">
							<input type="checkbox" id="move-map-checkbox" />
							<label for="move-map-checkbox"><?php esc_html_e( 'Search as I move the map', 'cariera-core' ); ?></label>
						</div>
					</div>
				<?php } ?>

				<?php echo do_shortcode( '[cariera-map height="100%"]' ); ?>
			</div>

			<?php if ( 'offcanvas' === $search_form_position ) { ?>
				<div class="offcanvas-listing-search hidden">
					<div class="close-search"><i class="las la-times"></i></div>
					<?php do_action( 'cariera_listing_split_view_search', $listing_type ); ?>
				</div>
			<?php } ?>

			<div class="listing-wrapper <?php echo esc_attr( $list_side ); ?> cariera-scroll">
				<div class="info">
					<?php if ( ! empty( $title ) ) { ?>
						<h3 class="title"><?php echo esc_html( $title ); ?></h3>
					<?php } ?>

					<?php if ( 'offcanvas' === $search_form_position ) { ?>
						<a href="#" class="filters-btn"><i class="las la-sliders-h"></i><i class="las la-times"></i></a>
					<?php } ?>
				</div>

				<?php echo do_shortcode( $listings_shortcode ); ?>
			</div>
		</main>
		<?php
	}
}
