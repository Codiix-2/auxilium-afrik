<?php

namespace Cariera_Addons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Settings option prefix.
	 *
	 * @var string
	 */
	const SETTINGS_PREFIX = 'cariera_addons_';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'cariera_settings', [ $this, 'settings' ] );
	}

	/**
	 * Cariera Addons settings.
	 *
	 * @since   0.9.0
	 * @version 1.1.0
	 *
	 * @param array $settings
	 */
	public function settings( $settings ) {
		$prefix        = self::SETTINGS_PREFIX;
		$core_features = \Cariera_Addons\Helpers::get_core_features();

		if ( ! isset( $settings['general'][1] ) || ! is_array( $settings['general'][1] ) ) {
			return $settings;
		}

		// Generate default values with all options enabled.
		$default_features = array_fill_keys( array_keys( $core_features ), true );

		// Append new addon settings to the general tab.
		$settings['general'][1][] = [
			'id'            => $prefix . 'heading',
			'label'         => '',
			// translators: %s: Cariera Addons documentation link.
			'description'   => wp_kses_post( sprintf( __( 'Configure the core functionalities for Cariera Addons. Check full %s', 'cariera-addons' ), '<a href="https://docs.cariera.cc/kb/cariera-addons/" target="_blank" rel="noopener noreferrer">' . __( 'documentation.', 'cariera-addons' ) . '</a>' ) ),
			'type'          => 'title',
			'title'         => esc_html__( 'Cariera Addons Settings', 'cariera-addons' ),
			'version'       => CARIERA_ADDONS_VERSION,
			'class_wrapper' => '',
			'attributes'    => [],
		];

		$settings['general'][1][] = [
			'id'            => $prefix . 'core_features',
			'label'         => esc_html__( 'Core Functionality', 'cariera-addons' ),
			'description'   => esc_html__( 'Choose which core features you want to enable or disable.', 'cariera-addons' ),
			'type'          => 'multi_switch',
			'options'       => $core_features,
			'default'       => $default_features,
			'class_wrapper' => '',
			'attributes'    => [],
		];

		return $settings;
	}
}
