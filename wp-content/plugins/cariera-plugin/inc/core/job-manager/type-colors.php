<?php

namespace Cariera_Core\Core\Job_Manager;

use Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Type_Colors extends Job_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( 0 === absint( get_option( 'job_manager_enable_types' ) ) ) {
			return;
		}

		$this->setup_actions();
	}

	/**
	 * Setup all main actions for the file
	 *
	 * @since 1.7.2
	 */
	private function setup_actions() {
		add_filter( 'job_manager_settings', [ $this, 'job_manager_settings' ] );
		add_action( 'wp_head', [ $this, 'output_colors' ] );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'colorpickers' ] );
			add_action( 'admin_footer', [ $this, 'colorpickersjs' ] );
		}
	}

	/**
	 * Job Manager Settings.
	 *
	 * @since   1.7.2
	 * @version 2.0.0
	 *
	 * @param array $settings
	 */
	public function job_manager_settings( $settings ) {
		$settings['job_colors'] = [
			esc_html__( 'Job Colors', 'cariera-core' ),
			$this->create_options(),
		];

		return $settings;
	}

	/**
	 * Create seperate options for each term.
	 *
	 * @since   1.7.2
	 * @version 1.9.8
	 */
	private function create_options() {
		$terms   = get_terms(
			[
				'taxonomy'   => 'job_listing_type',
				'hide_empty' => false,
			]
		);
		$options = [];

		foreach ( $terms as $term ) {
			$options[] = [
				'name'        => 'job_manager_job_type_' . $term->slug . '_color',
				'std'         => '',
				'placeholder' => '#',
				'label'       => $term->name,
				'desc'        => esc_html__( 'Hex value for the color of this job type.', 'cariera-core' ),
				'attributes'  => [
					'data-default-color' => '',
					'data-type'          => 'colorpicker',
				],
			];
		}

		return $options;
	}

	/**
	 * Outputting the selected colors.
	 *
	 * @since   1.7.2
	 * @version 1.9.9
	 */
	public function output_colors() {
		$terms = get_job_listing_types();

		// Prime options cache - load all job type color options in 1 query.
		$option_names = [];
		foreach ( $terms as $term ) {
			$option_names[] = 'job_manager_job_type_' . $term->slug . '_color';
		}

		if ( function_exists( 'wp_prime_option_caches' ) ) {
			wp_prime_option_caches( $option_names );
		}

		echo "<style id='job_manager_type_colors'>\n";

		foreach ( $terms as $term ) {
			$color = get_option( 'job_manager_job_type_' . $term->slug . '_color', '#fff' );

			if ( ! empty( $color ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				printf( ".job-type.term-%s { background-color: %s !important; } \n", esc_attr( $term->term_id ), esc_html( $color ) );
			}
		}

		echo "</style>\n";
	}

	/**
	 * Color Picker JS
	 *
	 * @since 1.7.2
	 *
	 * @param string $hook
	 */
	public function colorpickers( $hook ) {
		$screen = get_current_screen();

		if ( 'job_listing_page_job-manager-settings' !== $screen->id ) {
			return;
		}

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Add script on Job Manager settings screen only.
	 *
	 * @since 1.7.2
	 */
	public function colorpickersjs() {
		$screen = get_current_screen();

		if ( 'job_listing_page_job-manager-settings' !== $screen->id ) {
			return;
		} ?>

		<script>
			jQuery(document).ready(function($){
				$( 'input[data-type="colorpicker"]' ).wpColorPicker();
			});
		</script>
		<?php
	}
}
