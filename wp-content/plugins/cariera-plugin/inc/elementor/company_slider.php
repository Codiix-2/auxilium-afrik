<?php
/**
 * ELEMENTOR WIDGET - COMPANY SLIDER
 *
 * @since    1.4.0
 * @version  1.8.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Company_Slider extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'company_slider';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Company Slider', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-post-slider';
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
			'version',
			[
				'label'       => esc_html__( 'Layout Version', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'1' => esc_html__( 'Version 1', 'cariera-core' ),
					'2' => esc_html__( 'Version 2', 'cariera-core' ),
				],
				'default'     => '1',
				'description' => '',
			]
		);
		$this->add_control(
			'per_page',
			[
				'label'       => esc_html__( 'Total Companies', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Leave it blank to display all companies.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'columns',
			[
				'label'       => esc_html__( 'Visible Companies', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '1',
				'min'         => '1',
				'max'         => '10',
				'description' => esc_html__( 'This will change how many jobs will be visible per slide.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'autoplay',
			[
				'label'        => esc_html__( 'Autoplay', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera-core' ),
				'label_off'    => esc_html__( 'Disable', 'cariera-core' ),
				'return_value' => 'enable',
				'default'      => '',
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
					'ID'       => esc_html__( 'ID', 'cariera-core' ),
					'author'   => esc_html__( 'Author', 'cariera-core' ),
					'title'    => esc_html__( 'Title', 'cariera-core' ),
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
		return [ 'cariera-company-listings' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		wp_enqueue_style( 'cariera-company-listings' );

		$settings = $this->get_settings();
		global $post;

		// Featured value.
		if ( 'default' === $settings['featured'] ) {
			$featured = null;
		} elseif ( 'show' === $settings['featured'] ) {
			$featured = 'true';
		} else {
			$featured = '';
		}

		// Active jobs value.
		if ( 'default' === $settings['active_jobs'] ) {
			$active_jobs = null;
		} elseif ( 'show' === $settings['active_jobs'] ) {
			$active_jobs = 'true';
		} else {
			$active_jobs = '';
		}

		$companies = cariera_get_companies(
			[
				'orderby'        => $settings['orderby'],
				'order'          => $settings['order'],
				'posts_per_page' => $settings['per_page'],
				'featured'       => $featured,
				'active_jobs'    => $active_jobs,
			]
		);

		if ( 'enable' === $settings['autoplay'] ) {
			$autoplay = '1';
		} else {
			$autoplay = '0';
		}

		if ( $companies->have_posts() ) { ?>
			<div class="company-carousel company-carousel-<?php echo esc_attr( $settings['version'] ) . ' ' . esc_attr( $settings['custom_class'] ); ?>" data-columns="<?php echo esc_attr( $settings['columns'] ); ?>" data-autoplay="<?php echo esc_attr( $autoplay ); ?>">
				<?php
				while ( $companies->have_posts() ) :
					$companies->the_post();

					get_job_manager_template(
						'company-templates/company-carousel.php',
						[
							'settings' => $settings,
						],
						'wp-job-manager-companies'
					);

					endwhile;
				?>
			</div>
			<?php
		}

		wp_reset_postdata();
	}
}
