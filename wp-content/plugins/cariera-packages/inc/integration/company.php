<?php

namespace Cariera_Packages\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Company {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		\Cariera_Packages\Integration\Company\Promotion::instance();
		\Cariera_Packages\Integration\Company\View::instance();
	}
}
