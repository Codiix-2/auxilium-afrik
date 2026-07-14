<?php

namespace WPJMSF\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera {

	/**
	 * @var \WPJMSF\Themes
	 */
	public $themes;

	/**
	 * Cariera constructor.
	 *
	 * @param $themes \WPJMSF\Themes
	 */
	public function __construct( $themes ) {
		$this->themes = $themes;
	}

}
