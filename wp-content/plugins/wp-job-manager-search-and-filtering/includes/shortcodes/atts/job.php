<?php

namespace WPJMSF\Shortcodes\Atts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Job
 *
 * @package WPJMSF\Shortcodes\Atts
 */
class Job extends \WPJMSF\Shortcodes\Atts {

	/**
	 * @var \WPJMSF\Job
	 */
	public $type;

	/**
	 * Job constructor.
	 *
	 * @param $type \WPJMSF\Job
	 */
	public function __construct( $type ) {
		add_filter( 'job_manager_output_jobs_defaults', array( $this, 'shortcode_default_atts' ) );
		parent::__construct( $type, 'jobs' );
	}

	/**
	 * Get Shortcode Attributes
	 *
	 * @return mixed|void
	 * @since 1.0.1
	 *
	 */
	public function get_shortcode_atts() {
		$atts = array(
			'orderby'           => array(
				'default'       => true,
				'search_source' => 'orderby',
				'default_value' => 'featured'
			),
			'order'              => array(
				'default'       => true,
				'search_source' => 'order',
				'default_value' => 'DESC'
			),
			'categories'         => array(
				'limit'         => true,
				'multi_value'   => true,
				'taxonomy'      => 'job_listing_category',
				'search_source' => 'search_categories'
			),
			'job_tag'         => array(
				'limit'         => false,
				'multi_value'   => true,
				'default'       => true,
				'taxonomy'      => 'job_listing_tag',
				'search_source' => 'job_tags'
			),
			'job_types'          => array(
				'limit'         => true,
				'multi_value'   => true,
				'taxonomy'      => 'job_listing_type',
				'search_source' => 'job_types'
			),
			'selected_job_types' => array(
				'multi_value'   => true,
				'default'       => true,
				'taxonomy'      => 'job_listing_type',
				'search_source' => 'job_types'
			),
			'selected_category' => array(
				'multi_value'   => true,
				'default'       => true,
				'taxonomy'      => 'job_listing_category',
				'search_source' => 'search_categories'
			),
			'location'           => array(
				'default'       => true,
				'search_source' => 'search_location'
			),
			'keywords'           => array(
				'default'       => true,
				'search_source' => 'search_keywords'
			),
			'featured'           => array(
				'default'       => true,
				'search_source' => 'featured',
				'default_value' => null
			),
			'filled'            => array(
				'default'       => true,
				'search_source' => 'filled',
				'default_value' => null
			),
			'remote_position'   => array(
				'default'       => true,
				'search_source' => 'remote_position',
				'default_value' => null
			),
			'post_status'        => array(
				'default'       => true,
				'search_source' => 'post_status'
			),
			'selected_region' => array(
				'default' => false,
				'search_source' => 'job_region'
			)
		);

		/**
		 * Remote position field was added in 1.36.0+
		 */
		if( defined( 'JOB_MANAGER_VERSION' ) && version_compare( JOB_MANAGER_VERSION, '1.36.0', '<') ){
			unset( $atts['remote_position'] );
		}

		return apply_filters( 'search_and_filtering_jobs_shortcode_atts', $atts, $this );
	}
}