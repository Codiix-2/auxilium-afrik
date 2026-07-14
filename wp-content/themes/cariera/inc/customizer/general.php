<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'general',
	[
		'priority'    => 10,
		'title'       => esc_html__( 'General Options', 'cariera' ),
		'description' => esc_html__( 'General options', 'cariera' ),
	]
);

/**
 * Add Sections.
 */

// LAYOUT OPTIONS.
\Cariera\Kirki::add_section(
	'layout',
	[
		'title'          => esc_html__( 'Layout Options', 'cariera' ),
		'description'    => esc_html__( 'General layout options', 'cariera' ),
		'panel'          => 'general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// COLORS OPTIONS.
\Cariera\Kirki::add_section(
	'colors',
	[
		'title'          => esc_html__( 'Color Options', 'cariera' ),
		'description'    => '',
		'panel'          => 'general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// GENERAL - LAYOUT OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_preloader',
		'type'        => 'radio',
		'label'       => esc_html__( 'Preloader', 'cariera' ),
		'description' => esc_html__( 'Turn the switch "ON" to enable the website preloader.', 'cariera' ),
		'section'     => 'layout',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_preloader_version',
		'type'            => 'select',
		'label'           => esc_html__( 'Preloader style', 'cariera' ),
		'description'     => '',
		'section'         => 'layout',
		'default'         => 'preloader4',
		'priority'        => 10,
		'choices'         => [
			'preloader1' => esc_html__( 'Preloader Version 1', 'cariera' ),
			'preloader2' => esc_html__( 'Preloader Version 2', 'cariera' ),
			'preloader3' => esc_html__( 'Preloader Version 3', 'cariera' ),
			'preloader4' => esc_html__( 'Preloader Version 4', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_preloader',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_back_top',
		'type'        => 'radio',
		'label'       => esc_html__( 'Back to Top Button', 'cariera' ),
		'description' => esc_html__( 'Turn the switch "OFF" to disable the back to top button.', 'cariera' ),
		'section'     => 'layout',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_breadcrumbs',
		'type'        => 'radio',
		'label'       => esc_html__( 'Breadcrumbs', 'cariera' ),
		'description' => esc_html__( 'Turn the switch "OFF" to disable all breadcrumbs.', 'cariera' ),
		'section'     => 'layout',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);

// GENERAL - COLORS OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_wrapper_color',
		'type'        => 'color',
		'label'       => esc_html__( 'Select body wrapper color', 'cariera' ),
		'description' => '',
		'section'     => 'colors',
		'default'     => '#fff',
		'priority'    => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_main_color',
		'type'        => 'color',
		'label'       => esc_html__( 'Select main theme color', 'cariera' ),
		'description' => '',
		'section'     => 'colors',
		'default'     => '#303af7',
		'priority'    => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_secondary_color',
		'type'        => 'color',
		'label'       => esc_html__( 'Select secondary theme color', 'cariera' ),
		'description' => '',
		'section'     => 'colors',
		'default'     => '#443088',
		'priority'    => 10,
	]
);
