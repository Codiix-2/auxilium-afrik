<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 *
 * @package WPJMSF
 */
class Plugin {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * @var string
	 */
	public $plugin_class = 'Some_Plugin_Class_Placeholder';

	/**
	 * Is Plugin Active?
	 *
	 * @return bool
	 * @since 1.1.0
	 *
	 */
	public function is_active() {
		return class_exists( $this->plugin_class );
	}
}