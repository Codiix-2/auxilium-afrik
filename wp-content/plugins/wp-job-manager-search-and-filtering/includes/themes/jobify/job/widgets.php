<?php

namespace WPJMSF\Themes\Jobify\Job;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widgets extends \WPJMSF\Themes\Jobify\Widgets {

	public function get_widgets() {
		$widgets = array(
			'jobify_widget_search_hero' => array(
				'label'     => __( 'Jobify - Page: Job Search Hero (Widget)', 'wp-job-manager-search-and-filtering' ),
				'class'     => '\WPJMSF\Themes\Jobify\Jobify_Widget_Search_Hero',
				'file'      => 'class-widget-search-hero.php',
				'check'     => array( $this, 'should_override' ),
				'locations' => array(
					array(
						'label' => __( 'Above Form (not recommended)', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_jobify_widget_search_hero_form_above_job'
					),
					array(
						'label' => __( 'Form Top', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_jobify_widget_search_hero_form_top_job'
					),
					array(
						'label' => __( 'Form Inside Search Div (recommended)', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_jobify_widget_search_hero_form_search_job'
					),
					array(
						'label' => __( 'Form Bottom', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_jobify_widget_search_hero_form_bottom_job'
					),
					array(
						'label' => __( 'Below Form (not recommended)', 'wp-job-manager-search-and-filtering' ),
						'value' => 'search_and_filtering_jobify_widget_search_hero_form_below_job'
					),
				)
			),
			'jobify_widget_map'         => array(
				'label'     => __( 'Jobify - Page: Jobs Map (Widget)', 'wp-job-manager-search-and-filtering' ),
				'class'     => '\WPJMSF\Themes\Jobify\Jobify_Widget_Map',
				'file'      => 'class-widget-jobs-map.php',
				'check'     => array( $this, 'should_override' ),
				'locations' => array(
					array(
						'label'       => __( 'Map Overlay', 'wp-job-manager-search-and-filtering' ),
						'value'       => 'jobify_widget_map_search',
						'add_action'  => false, // add_action handled in includes/class-themes.php
						'multi_types' => true
					)
				)
			),
			'jobify_widget_jobs_search' => array(
				'label'     => __( 'Jobify - Page: Jobs Search (Widget)', 'wp-job-manager-search-and-filtering' ),
				'class'     => '\WPJMSF\Themes\Jobify\Jobify_Widget_Jobs_Search',
				'file'      => 'class-widget-jobs-search.php',
				'check'     => array( $this, 'should_override_with_output' ),
				'locations' => array(
					array(
						'label'      => __( 'Widget Output', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'jobify_widget_jobs_search',
						'add_action' => false
					),
				)
			)
		);
		return apply_filters( 'search_and_filtering_jobify_job_get_widgets', $widgets, $this );
	}
}
