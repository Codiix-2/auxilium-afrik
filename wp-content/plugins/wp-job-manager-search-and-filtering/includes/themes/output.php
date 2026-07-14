<?php

namespace WPJMSF\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Output {
	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var \WPJMSF\Theme
	 */
	public $theme = null;

	/**
	 * Themes constructor.
	 *
	 * This only gets called when the actual type is enabled to be output, otherwise this class will not be initialized
	 *
	 * @param $type \WPJMSF\Job|\WPJMSF\Resume
	 * @param $theme
	 */
	public function __construct( $type, $theme ) {
		$this->theme = $theme;
		$this->type = $type;
		$this->init();
	}

	/**
	 * Initialize Theme Outputs
	 *
	 *
	 * @since 0.1.1
	 *
	 */
	function init() {

		if ( ! $this->theme || ! method_exists( $this->theme, 'get_output_locations' ) ) {
			return;
		}

		$output_sections = $this->theme->get_output_locations( true );

		foreach ( (array) $output_sections as $output_section ) {

			foreach ( (array) $output_section['locations'] as $output_location ) {

				/**
				 * Allow for defining custom add_action or add_filter in location configuration, to add custom action or filter
				 * that can handle theme specific functionality for dealing with outputting sections.
				 *
				 * This is only ever called if output is ENABLED for the type (job/resume)
				 *
				 * We use add_filter here since even calls for add_action are basically ran to add_filter
				 */
				$custom_add_filter = isset( $output_location['add_filter'] ) && is_array( $output_location['add_filter'] ) ? $output_location['add_filter'] : false;
				if( $custom_add_filter ){
					$custom_priority = isset( $custom_add_filter['priority'] ) ? $custom_add_filter['priority'] : 10;
					$custom_args = isset( $custom_add_filter['args'] ) ? $custom_add_filter['args'] : 1;
					// add_action is the same as add_filter, it's just a wrapper function for add_filter
					add_filter( $custom_add_filter['hook'], $custom_add_filter['callback'], $custom_priority, $custom_args );
					continue;
				}

				/**
				 * If action value is empty (has no value), or has config set for `add_action` set to false, we don't want to (or need to) add
				 * the action and callback.  `add_action` should be set to false when the action defined is specifically for using in
				 * the shortcode as `custom_filter_action` attribute.
				 */
				if ( empty( $output_location['value'] ) || ( isset( $output_location['add_action'] ) && empty( $output_location['add_action'] ) ) ) {
					continue;
				}

//				$output_action = "{$output_location['value']}_{$this->type->slug}";

				$output_action = $output_location['value'];
				if ( ! has_action( $output_action, array( $this->type->output, $output_location['value'] ) ) ) {
					add_action( $output_action, array( $this->type->output, $output_location['value'] ) );
				}

			}

		}
	}
}
