<?php

namespace Cariera_Packages\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Event {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		\Cariera_Packages\Integration\Event\Promotion::instance();
		\Cariera_Packages\Integration\Event\View::instance();
	}
}
