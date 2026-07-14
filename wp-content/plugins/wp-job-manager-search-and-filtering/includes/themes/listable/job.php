<?php

namespace WPJMSF\Themes\Listable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Job
 *
 * @package WPJMSF\Themes\Listable
 *
 * @since  1.0.0
 *
 */
class Job extends \WPJMSF\Theme {

	/**
	 * Class constructor.
	 */
	public function construct() {

	}

	/**
	 * Initialize Custom Theme JS Handling
	 *
	 * This method tells SectionGrid.vue to load the theme specific
	 * handling, which is passed through wpjmsf_theme_config
	 *
	 * @return array|bool[]
	 * @since 1.0.0
	 *
	 */
	public function theme_config() {
		return array( 'init_handling' => true );
	}
}