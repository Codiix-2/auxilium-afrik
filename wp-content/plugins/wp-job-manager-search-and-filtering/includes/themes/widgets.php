<?php

namespace WPJMSF\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widgets {
	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var \WPJMSF\Theme
	 */
	public $theme;
	/**
	 * @var string Directory to where widget include files are stored, would be /includes/themes/THEMENAME/widgets/
	 */
	public $widget_includes_directory = '';

	/**
	 * Widgets constructor.
	 *
	 * @param $theme \WPJMSF\Theme
	 */
	public function __construct( $theme ) {
		$this->type = $theme->type;
		$this->theme = $theme;
		$this->widget_includes_directory = untrailingslashit( $theme->includes_directory ) . '/widgets/';

		if ( $this->type->output->is_enabled() ) {
			add_filter( 'widget_display_callback', array( $this, 'widget_display_callback' ), 99999, 3 );
		}

	}

	/**
	 * Get All Location Values from Specific Widget
	 *
	 *
	 * @param $widget
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function get_location_values_from_widget( $widget ){
		if( ! is_array( $widget ) || empty( $widget ) || ! array_key_exists( 'locations', $widget ) ){
			return array();
		}
		return array_column( $widget['locations'], 'value' );
	}

	/**
	 * Get Output Locations from Widget Config
	 *
	 *
	 * @param bool $keep_hooks
	 *
	 * @return array
	 * @since 0.1.1
	 */
	public function get_locations_from_widgets( $keep_hooks = false ){

		$widgets = $this->get_widgets();
		$widget_locations = array();

		foreach( ( array ) $widgets as $widget_slug => $widget ){

			if( ! array_key_exists( 'locations', $widget ) ){
				continue;
			}

			if( ! $keep_hooks ){
				foreach ( (array) $widget['locations'] as $loc_index => $loc_config ) {
					if ( array_key_exists( 'add_filter', $loc_config ) ) {
						unset( $widget['locations'][ $loc_index ]['add_filter'] );
					}
					if ( array_key_exists( 'add_action', $loc_config ) ) {
						unset( $widget['locations'][ $loc_index ]['add_action'] );
					}
				}
			}

			$widget_locations[ $widget_slug ] = array(
				'label' => $widget['label'],
				'locations' => $widget['locations']
			);
		}

		return $widget_locations;
	}

	/**
	 * Check if we need to override default Widget callback output
	 *
	 * NOTE: the_widget does not call this in versions older than 5.3, patch added to be included
	 * in WordPress 5.3+ ( https://core.trac.wordpress.org/ticket/34226 )
	 *
	 *
	 * @param $instance array
	 * @param $that \WP_Widget
	 * @param $args array
	 *
	 * @return bool|array
	 * @since 0.1.1
	 *
	 */
	public function widget_display_callback( $instance, $that, $args ) {
		$theme_widgets = $this->get_widgets();

		if( empty( $theme_widgets ) || ! array_key_exists( $that->id_base, $theme_widgets ) ){
			return $instance;
		}

		$theme_widget = $theme_widgets[ $that->id_base ];

		if( array_key_exists( 'check', $theme_widget ) ){
			$check_fn = $theme_widget['check'];
			$should_override = call_user_func( $check_fn, $instance, $that, $args, $theme_widget );
			if( ! $should_override ){
				return $instance;
			}
		}

		$theme_widget_file = $this->widget_includes_directory . $theme_widget['file'];

		if( ! file_exists( $theme_widget_file ) ){
			return $instance;
		}

		require_once $theme_widget_file;
		$theme_widget_class = new $theme_widget['class']( $this->theme );

		$was_cache_addition_suspended = wp_suspend_cache_addition();
		if ( $that->is_preview() && ! $was_cache_addition_suspended ) {
			wp_suspend_cache_addition( true );
		}

		$theme_widget_class->widget( $args, $instance );

		if ( $that->is_preview() ) {
			wp_suspend_cache_addition( $was_cache_addition_suspended );
		}

		return false;
	}

	/**
	 * Standard Should Override Widget Check
	 *
	 * This is standard override check just to make sure type section configuration is enabled.
	 * Realistically this should be overridden in extending class with more specific checks.
	 *
	 *
	 * @param $instance
	 * @param $that
	 * @param $args
	 * @param $theme_widget
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public function should_override( $instance, $that, $args, $theme_widget ) {
		return $this->type->output->is_enabled();
	}

	/**
	 * Standard Should Override Widget with Output Check
	 *
	 * This method checks first standard (or extending class version) of should_override, then also checks if there
	 * is a section enabled with this specific output location.
	 *
	 *
	 * @param $instance
	 * @param $that
	 * @param $args
	 * @param $theme_widget
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	public function should_override_with_output( $instance, $that, $args, $theme_widget ) {
		$outputs = $this->get_location_values_from_widget( $theme_widget );
		return $this->should_override( $instance, $that, $args, $theme_widget ) && $this->type->sections->has_enabled_section_by_outputs( $outputs );
	}

	/**
	 * get_widgets() placeholder, should be overridden in extending class
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function get_widgets() {
		return array();
	}
}
