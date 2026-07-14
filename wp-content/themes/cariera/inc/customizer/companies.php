<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'company_general',
	[
		'priority'    => 14,
		'title'       => esc_html__( 'Company Options', 'cariera' ),
		'description' => esc_html__( 'Company related options', 'cariera' ),
	]
);

/**
 * Add Sections.
 */

// COMPANY OPTIONS.
\Cariera\Kirki::add_section(
	'company_options',
	[
		'title'          => esc_html__( 'Company Options', 'cariera' ),
		'description'    => esc_html__( 'Company related options', 'cariera' ),
		'panel'          => 'company_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// SINGLE COMPANY OPTIONS.
\Cariera\Kirki::add_section(
	'single_company_options',
	[
		'title'          => esc_html__( 'Single Company Options', 'cariera' ),
		'description'    => esc_html__( 'Single company related options', 'cariera' ),
		'panel'          => 'company_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// GENERAL COMPANY OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_company_search_map',
		'type'        => 'radio',
		'label'       => esc_html__( 'General Company Search Map', 'cariera' ),
		'description' => esc_html__( 'Select "disable" if you want to disable the company search map.', 'cariera' ),
		'section'     => 'company_options',
		'default'     => '',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);

// SINGLE COMPANY OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_company_share',
		'type'        => 'radio',
		'label'       => esc_html__( 'Sharing Options', 'cariera' ),
		'description' => esc_html__( 'Display social sharing on single company page.', 'cariera' ),
		'section'     => 'single_company_options',
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
		'settings'    => 'cariera_company_map',
		'type'        => 'radio',
		'label'       => esc_html__( 'Company Map', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to remove the map.', 'cariera' ),
		'section'     => 'single_company_options',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);
