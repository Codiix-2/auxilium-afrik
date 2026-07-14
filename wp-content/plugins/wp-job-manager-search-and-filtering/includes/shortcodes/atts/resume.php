<?php

namespace WPJMSF\Shortcodes\Atts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Resume
 *
 * @package WPJMSF\Shortcodes\Atts
 */
class Resume extends \WPJMSF\Shortcodes\Atts {

	/**
	 * @var \WPJMSF\Resume
	 */
	public $type;

	/**
	 * Resume constructor.
	 *
	 * @param $type \WPJMSF\Resume
	 */
	public function __construct( $type ) {
		add_filter( 'resume_manager_output_resumes_defaults', array( $this, 'shortcode_default_atts' ) );
		parent::__construct( $type, 'resumes' );
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
				'taxonomy'      => 'resume_category',
				'search_source' => 'search_categories'
			),
			'selected_category' => array(
				'multi_value'   => true,
				'default'       => true,
				'taxonomy'      => 'resume_category',
				'search_source' => 'search_categories'
			),
			'featured'           => array(
				'default'       => true,
				'search_source' => 'featured',
				'default_value' => null
			),
			'selected_region' => array(
				'default'       => false,
				'search_source' => 'resume_region'
			)
		);

		return apply_filters( 'search_and_filtering_resumes_shortcode_atts', $atts, $this );
	}
}