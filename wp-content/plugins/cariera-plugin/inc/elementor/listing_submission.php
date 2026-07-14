<?php
/**
 * ELEMENTOR WIDGET - LISTING SUBMISSION
 *
 * @since   1.7.5
 * @version 1.9.3
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Listing_Submission extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'submit_listing';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Listing Submit Form', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
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
			'submission_form',
			[
				'label'       => esc_html__( 'Select Submission Form', 'cariera-core' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'job_listing' => esc_html__( 'Job Submission Form', 'cariera-core' ),
					'resume'      => esc_html__( 'Resume Submission Form', 'cariera-core' ),
					'company'     => esc_html__( 'Company Submission Form', 'cariera-core' ),
				],
				'default'     => 'job_listing',
				'description' => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 */
	public function get_style_depends() {
		return [ 'cariera-wpjm-submissions' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();

		// Job submission form.
		if ( 'job_listing' === $settings['submission_form'] ) {
			echo do_shortcode( '[submit_job_form]' );
		}

		// Resume submission form.
		if ( 'resume' === $settings['submission_form'] ) {
			echo do_shortcode( '[submit_resume_form]' );
		}

		// Company submission form.
		if ( 'company' === $settings['submission_form'] ) {
			echo do_shortcode( '[submit_company]' );
		}
	}
}
