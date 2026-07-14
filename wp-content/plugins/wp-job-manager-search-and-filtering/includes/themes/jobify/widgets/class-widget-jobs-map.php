<?php

namespace WPJMSF\Themes\Jobify;

class Jobify_Widget_Map {

	public $widget_id = 'jobify_widget_map';

	/**
	 * @var \WPJMSF\Themes\Jobify
	 */
	public $theme;

	/**
	 * Jobify_Widget_Map constructor.
	 *
	 * @param $theme \WPJMSF\Themes\Jobify
	 */
	public function __construct( $theme ) {
		$this->theme = $theme;
	}

	/**
	 * widget function.
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return void
	 * @see    WP_Widget
	 * @access public
	 */
	function widget( $args, $instance ) {

		ob_start();

		extract( $args );

		$wpjm = WPJM();

		if ( method_exists( $wpjm, 'register_select2_assets' ) ) {
			$wpjm::register_select2_assets();
			wp_enqueue_script( 'select2' );
			wp_enqueue_style( 'select2' );
		}

		$filters = true;

		if ( isset( $instance['filters'] ) && '' == $instance['filters'] ) {
			$filters = false;
		}

		if ( isset( $instance['margin'] ) && '' == $instance['margin'] ) {
			$before_widget = str_replace( 'widget--home ', 'widget--home widget--home--no-margin ', $before_widget );
		}

		$before_widget = str_replace( 'jobify_widget_map', ( $filters ? 'filters' : 'no-filters' ) . ' jobify_widget_map', $before_widget );

		echo $before_widget;

		do_action( 'jobify_output_map' );
		echo do_shortcode( '[jobs custom_filter_type="jobify_widget_map"]' );

		echo $after_widget;

		$content = apply_filters( 'jobify_widget_map', ob_get_clean(), $instance, $args );

		echo $content;
	}
}
