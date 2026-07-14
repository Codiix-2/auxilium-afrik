<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Resume_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Init Classes.
		if ( ! $this->cariera_addons_resumes_is_active() ) {
			new \Cariera_Core\Core\Resume_Manager\Resumes_Extender();
		}
		new \Cariera_Core\Core\Resume_Manager\Fields();
		new \Cariera_Core\Core\Resume_Manager\Search();
		new \Cariera_Core\Core\Resume_Manager\Settings();
		new \Cariera_Core\Core\Resume_Manager\Taxonomy();
	}

	/**
	 * Check if the Cariera Addons Resumes is active.
	 *
	 * @since   1.9.3
	 * @version 1.9.5
	 */
	private function cariera_addons_resumes_is_active(): bool {
		$core_features   = get_option( 'cariera_addons_core_features', [] );
		$resumes_feature = $core_features['resumes'] ?? null;

		// Cariera Addons is active, but resumes feature is disabled.
		if ( class_exists( 'Cariera_Addons\Core\Resumes\Resumes' ) ) {
			return ! ( empty( $resumes_feature ) || 0 === $resumes_feature );
		}

		return false;
	}
}
