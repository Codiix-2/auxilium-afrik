<?php
/**
 * ELEMENTOR WIDGET - JOB RESUME TAB SEARCH
 *
 * @since    1.4.0
 * @version  1.9.9
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Resume_Search extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_resume_search';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Tab Search', 'cariera-core' );
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
		$label_on  = esc_html__( 'Show', 'cariera-core' );
		$label_off = esc_html__( 'Hide', 'cariera-core' );

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
			'job_search',
			[
				'label'        => esc_html__( 'Job Search Form', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => $label_on,
				'label_off'    => $label_off,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'job_tab_label',
			[
				'label'       => esc_html__( 'Jobs Tab Label', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'Jobs',
				'description' => '',
				'condition'   => [
					'job_search' => 'yes',
				],
			]
		);
		$this->add_control(
			'resume_search',
			[
				'label'        => esc_html__( 'Resume Search Form', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => $label_on,
				'label_off'    => $label_off,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'resume_tab_label',
			[
				'label'       => esc_html__( 'Resumes Tab Label', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'Resumes',
				'description' => '',
				'condition'   => [
					'resume_search' => 'yes',
				],
			]
		);
		$this->add_control(
			'company_search',
			[
				'label'        => esc_html__( 'Company Search Form', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => $label_on,
				'label_off'    => $label_off,
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'company_tab_label',
			[
				'label'       => esc_html__( 'Companies Tab Label', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'Companies',
				'description' => '',
				'condition'   => [
					'company_search' => 'yes',
				],
			]
		);
		if ( class_exists( 'Cariera_Events' ) ) {
			$this->add_control(
				'event_search',
				[
					'label'        => esc_html__( 'Event Search Form', 'cariera-core' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => $label_on,
					'label_off'    => $label_off,
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);
			$this->add_control(
				'event_tab_label',
				[
					'label'       => esc_html__( 'Events Tab Label', 'cariera-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => 'Events',
					'description' => '',
					'condition'   => [
						'event_search' => 'yes',
					],
				]
			);
		}

		$this->end_controls_section();

		// SECTION STYLE.
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'cariera-core' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'link_color',
			[
				'label'       => esc_html__( 'Tab Link Color', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => '#fff',
				'description' => '',
				'selectors'   => [ '{{WRAPPER}} .job-resume-tab-search .tabs-nav li:not(.active) a' => 'color: {{VALUE}}' ],
			]
		);
		$this->add_control(
			'body_color',
			[
				'label'       => esc_html__( 'Tab Content Background', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'default'     => '#fff',
				'description' => '',
				'selectors'   => [
					'{{WRAPPER}} .job-resume-tab-search.version-1 ul.tabs-nav li.active, {{WRAPPER}} .job-resume-tab-search .tab-container .tab-content' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .job-resume-tab-search.version-2 .tabs-nav li.active::after' => 'border-bottom-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-wpjm-search-forms' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		\Cariera_Core\Core\Assets::enqueue_location_autocomplete();

		wp_enqueue_style( 'cariera-wpjm-search-forms' );

		$settings = $this->get_settings();
		?>

		<div class="job-resume-tab-search version-<?php echo esc_attr( $settings['version'] ); ?>">
			<ul class="tabs-nav job-resume-search">
				<?php if ( ! empty( $settings['job_search'] ) ) { ?>
					<li class="active">
						<a href="#search-form-tab-jobs">
							<i class="las la-briefcase"></i><?php echo esc_html( $settings['job_tab_label'] ); ?>
						</a>
					</li>
				<?php } ?>

				<?php if ( class_exists( 'WP_Resume_Manager' ) && ! empty( $settings['resume_search'] ) ) { ?>
					<li>
						<a href="#search-form-tab-resumes">
							<i class="las la-graduation-cap"></i><?php echo esc_html( $settings['resume_tab_label'] ); ?>
						</a>
					</li>
				<?php } ?>

				<?php if ( ! empty( $settings['company_search'] ) ) { ?>
					<li>
						<a href="#search-form-tab-company">
							<i class="lar la-building"></i><?php echo esc_html( $settings['company_tab_label'] ); ?>
						</a>
					</li>
				<?php } ?>

				<?php if ( class_exists( 'Cariera_Events' ) && ! empty( $settings['event_search'] ) ) { ?>
					<li>
						<a href="#search-form-tab-event">
							<i class="las la-calendar"></i><?php echo esc_html( $settings['event_tab_label'] ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>

			<div class="tab-container">
				<?php if ( ! empty( $settings['job_search'] ) ) { ?>
					<div class="tab-content" id="search-form-tab-jobs" style="display: none;">
						<?php get_job_manager_template( 'job-resume-search-job-form.php' ); ?>
					</div>
				<?php } ?>

				<?php if ( class_exists( 'WP_Resume_Manager' ) && ! empty( $settings['resume_search'] ) ) { ?>
					<div class="tab-content" id="search-form-tab-resumes" style="display: none;">
						<?php get_job_manager_template( 'job-resume-search-resume-form.php' ); ?>
					</div>
				<?php } ?>

				<?php if ( ! empty( $settings['company_search'] ) ) { ?>
					<div class="tab-content" id="search-form-tab-company" style="display: none;">
						<?php get_job_manager_template( 'job-resume-search-company-form.php' ); ?>
					</div>
				<?php } ?>

				<?php if ( class_exists( 'Cariera_Events' ) && ! empty( $settings['event_search'] ) ) { ?>
					<div class="tab-content" id="search-form-tab-event" style="display: none;">
						<?php get_job_manager_template( 'search/cariera-tab-search.php', [], 'wp-job-manager-events', CARIERA_EVENTS_PATH . '/templates/' ); ?>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php
	}
}
