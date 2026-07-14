<?php

namespace WPJMSF\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capstone {
	/**
	 * @var \WPJMSF\Themes
	 */
	public $themes;

	/**
	 * Capstone constructor.
	 *
	 * @param $themes \WPJMSF\Themes
	 */
	public function __construct( $themes ) {
		$this->themes = $themes;
	}
}
