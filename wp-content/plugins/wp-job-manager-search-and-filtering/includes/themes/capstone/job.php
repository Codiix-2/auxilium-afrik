<?php

namespace WPJMSF\Themes\Capstone;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job extends \WPJMSF\Theme {

	/**
	 * Theme constructor.
	 */
	public function construct() {
	}

	/**
	 * Capstone Custom Field Types
	 *
	 *
	 * @param $groups
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function field_type_groups( $groups ) {

		$groups['capstone'] = array(
			'label'  => __( 'Capstone Custom Field Types', 'wp-job-manager-search-and-filtering' ),
			'fields' => array(
				'capstone_text_field' => array(
					'label'    => __( 'Capstone - Text Field', 'wp-job-manager-search-and-filtering' ),
					'custom'   => true,
					'supports' => $groups['input']['fields']['text']['supports']
				),
				'capstone_location_field' => array(
					'label'    => __( 'Capstone - Location Field', 'wp-job-manager-search-and-filtering' ),
					'custom'   => true,
					'supports' => $groups['input']['fields']['text']['supports']
				)
			)
		);

		return $groups;
	}

	public function theme_config() {

		$geo_method = get_theme_mod( 'capstone_geolocation_method', 'html' );
		$geo_html = $geo_method == 'html' && get_option( 'job_manager_google_maps_api_key' );
		$geo_ip = $geo_method == 'ip' && get_theme_mod( 'capstone_geolocation_api_key' );

		return array(
			'ip_address' => shortcode_exists( 'capstone_get_user_ip' ) ? esc_attr( do_shortcode( '[capstone_get_user_ip]' ) ) : '',
			'geolocation_api' => esc_attr( get_theme_mod( 'capstone_geolocation_api_key' ) ),
			'locate_me' => $geo_html || $geo_ip,
			'gps_svg' => esc_url( get_template_directory_uri() . '/images/gps.svg' ),
			'geo_method' => $geo_method
		);
	}

	/**
	 * Capstone Specific Output Locations
	 *
	 *
	 * @return array
	 * @since 0.1.1
	 *
	 */
	public function output_locations() {

		$locations = array(
			'capstone_job_homepage_hero' => array(
				'label'     => __( 'Capstone - Homepage Hero', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label'      => __( 'Hero Search (Recommended)', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_capstone_form_top_homepage_job'
					),
					array(
						'label'      => __( 'Hero Search Bottom', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_capstone_form_bottom_homepage_job'
					),
				)
			),
			'capstone_job_listings' => array(
				'label'     => __( 'Capstone - Job Listings Page', 'wp-job-manager-search-and-filtering' ),
				'locations' => array(
					array(
						'label'      => __( 'Search Module (Recommended)', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_capstone_form_top_job'
					),
					array(
						'label'      => __( 'Search Module Bottom', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_capstone_form_bottom_job'
					),
					array(
						'label'      => __( 'Filters Module (Recommended)', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_capstone_filters_top_job'
					),
					array(
						'label'      => __( 'Filters Module Bottom', 'wp-job-manager-search-and-filtering' ),
						'value'      => 'search_and_filtering_capstone_filters_bottom_job'
					),
				)
			)
		);

		return $locations;
	}
}
