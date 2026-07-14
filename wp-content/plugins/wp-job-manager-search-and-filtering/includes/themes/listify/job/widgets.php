<?php

namespace WPJMSF\Themes\Listify\Job;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widgets extends \WPJMSF\Themes\Widgets {

	public function get_widgets() {

		$widgets = array(
			'listify_widget_search_listings' => array(
				'label'     => __( 'Listify - Page: Search Listings', 'wp-job-manager-search-and-filtering' ),
				'class'     => '\WPJMSF\Themes\Listify\Listify_Widget_Search_Listings',
				'file'      => 'class-widget-home-search-listings.php',
				'check'     => array( $this, 'should_override_with_output' ),
				'locations' => array(
					array(
						'label'      => __( 'Homepage Top Hero Search', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'listify_widget_search_listings_homepage_hero',
						'add_action' => false
//						'add_filter' => array(
//							'hook' => 'the_widget',
//							'callback' => array( $this, 'the_widget' ),
//							'priority' => 10,
//							'args' => 3
//						),
					),
					array(
						'label'      => __( 'Search Listings (Widget)', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'listify_widget_search_listings',
						'add_action' => false
					)
				)
			)
		);

		return apply_filters( 'search_and_filtering_listify_job_get_widgets', $widgets, $this );
	}

	public function should_override_with_output( $instance, $that, $args, $theme_widget ) {
		global $wp_version;

		if( version_compare( $wp_version, 5.3, '<' ) && isset( $args['widget_id'] ) && $args['widget_id'] === "search-12391" ){
			return false;
		}

		return parent::should_override_with_output( $instance, $that, $args, $theme_widget );
	}

	public function the_widget( $widget, $instance, $args ){
		// Not actually needed anymore
//		$a = $widget;
//		$b = $instance;
//
//		// Homepage hero top area has widget_id defined as search-12391
//		if( isset( $args['widget_id'] ) && $args['widget_id'] === "search-12391" ){
//			$this->set_pending_widget( 'listify_widget_search_listings_homepage_hero' );
//		}
	}
}
