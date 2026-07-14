<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	public function __construct( $type ) {
		$this->type = $type;
		add_shortcode( "{$type->slug}_search_filters", array( $this, 'output_shortcode' ) );
	}

	/**
	 * Output Shortcode
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function output_shortcode( $atts, $content = '' ) {
		$sectionId = $atts['section'];
		$content .= $this->type->output->output_section( $sectionId, true );
		return $content;
	}
}
