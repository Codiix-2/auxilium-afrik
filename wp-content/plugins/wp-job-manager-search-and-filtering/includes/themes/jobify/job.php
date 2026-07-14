<?php

namespace WPJMSF\Themes\Jobify;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job extends \WPJMSF\Theme {

	/**
	 * Theme constructor.
	 */
	public function construct() {
		$this->widgets = new Job\Widgets( $this );
	}

	public function fields_config() {
		// Jobify select2 issues when admin bar, add downdown parent to .section-wrapper
		$config = array(
			'select' => array(
				'input' => array(
					'class' => array( 'feedFormField' )
				)
			)
		);

		return $config;
	}
}
