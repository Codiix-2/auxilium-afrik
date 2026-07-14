<?php
/**
 * ELEMENTOR WIDGET - JOB SLIDER
 *
 * @since    1.4.0
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Slider extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_slider';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Job Slider', 'cariera-core' );
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
			'per_page',
			[
				'label'       => esc_html__( 'Total Jobs', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Leave it blank to display all featured jobs.', 'cariera-core' ),
			]
		);
		$this->add_control(
			'columns',
			[
				'label'       => esc_html__( 'Visible Jobs', 'cariera-core' ),
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
					'true'  => esc_html__( 'Show only featured', 'cariera-core' ),
					'false' => esc_html__( 'Hide featured', 'cariera-core' ),
					'null'  => esc_html__( 'Show all', 'cariera-core' ),
				],
				'default'     => 'null',
				'description' => '',
			]
		);
		$this->add_control(
			'filled',
			[
				'label'       => esc_html__( 'Filled', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'null'  => esc_html__( 'Show all', 'cariera-core' ),
					'true'  => esc_html__( 'Show only filled', 'cariera-core' ),
					'false' => esc_html__( 'Hide filled', 'cariera-core' ),
				],
				'default'     => 'null',
				'description' => '',
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
		return [ 'cariera-job-listings' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		wp_enqueue_style( 'cariera-job-listings' );

		$settings = $this->get_settings();

		if ( 'null' !== $settings['featured'] ) {
			$featured = ( is_bool( $settings['featured'] ) && $settings['featured'] ) || in_array( $settings['featured'], [ '1', 'true', 'yes' ], true ) ? true : false;
		} else {
			$featured = null;
		}

		if ( 'null' !== $settings['filled'] ) {
			$filled = ( is_bool( $settings['filled'] ) && $settings['filled'] ) || in_array( $settings['filled'], [ '1', 'true', 'yes' ], true ) ? true : false;
		} else {
			$filled = null;
		}

		// Get jobs.
		$jobs = get_job_listings(
			[
				'orderby'        => $settings['orderby'],
				'order'          => $settings['order'],
				'posts_per_page' => $settings['per_page'],
				'featured'       => $featured,
				'filled'         => $filled,
			]
		);

		if ( 'enable' === $settings['autoplay'] ) {
			$autoplay = '1';
		} else {
			$autoplay = '0';
		}

		// Loop.
		if ( $jobs->have_posts() ) { ?>
			<div class="job-carousel <?php echo esc_attr( $settings['custom_class'] ); ?>" data-columns="<?php echo esc_attr( $settings['columns'] ); ?>" data-autoplay="<?php echo esc_attr( $autoplay ); ?>">
				<?php
				while ( $jobs->have_posts() ) :
					$jobs->the_post();

					get_job_manager_template_part( 'job-templates/job', 'carousel' );

					endwhile;
				?>
			</div>
			<?php
		}

		wp_reset_postdata();
	}
}
