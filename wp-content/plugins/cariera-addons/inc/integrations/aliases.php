<?php
namespace Cariera_Addons\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Aliases {

	/**
	 * Load plugin aliases
	 *
	 * @since   0.9.5
	 * @version 1.0.6
	 */
	public static function load_aliases() {
		$features = get_option( 'cariera_addons_core_features', [] );

		// Define alias groups by feature.
		$alias_groups = [
			'applications' => [
				'Cariera_Addons\Core\Applications\Applications' => 'WP_Job_Manager_Applications',
			],
			'resumes'      => [
				'Cariera_Addons\Core\Resumes\Resumes' => 'WP_Resume_Manager',
				'Cariera_Addons\Core\Resumes\Forms\Submit_Resume' => 'WP_Resume_Manager_Form_Submit_Resume',
				'Cariera_Addons\Core\Resumes\Forms\Edit_Resume' => 'WP_Resume_Manager_Form_Edit_Resume',
			],
		];

		// Loop through enabled features and load aliases.
		foreach ( $features as $feature => $enabled ) {
			if ( $enabled && isset( $alias_groups[ $feature ] ) ) {
				foreach ( $alias_groups[ $feature ] as $custom => $original ) {
					if ( ! class_exists( $original ) && class_exists( $custom ) ) {
						class_alias( $custom, $original );
					}
				}
			}
		}
	}
}
