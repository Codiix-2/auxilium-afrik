<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Sections.
 */
\Cariera\Kirki::add_section(
	'dashboard',
	[
		'title'          => esc_html__( 'Dashboard Options', 'cariera' ),
		'description'    => esc_html__( 'User Dashboard related options', 'cariera' ),
		'panel'          => '', // Not typically needed.
		'priority'       => 18,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// DASHBOARD OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_dashboard_views_statistics',
		'type'        => 'radio',
		'label'       => esc_html__( 'Monthly Views Statistics', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to disable the Graph stats.', 'cariera' ),
		'section'     => 'dashboard',
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
		'settings'        => 'cariera_dashboard_statistics_border',
		'type'            => 'color',
		'label'           => esc_html__( 'Statistics Border Color', 'cariera' ),
		'description'     => esc_html__( 'Change the border color of the statistics chart.', 'cariera' ),
		'section'         => 'dashboard',
		'priority'        => 10,
		'default'         => '#2346f7',
		'active_callback' => [
			[
				'setting'  => 'cariera_dashboard_views_statistics',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_dashboard_statistics_background',
		'type'            => 'color',
		'label'           => esc_html__( 'Statistics Background Color', 'cariera' ),
		'description'     => esc_html__( 'Change the background color of the statistics chart.', 'cariera' ),
		'section'         => 'dashboard',
		'priority'        => 10,
		'default'         => 'rgba(35, 70, 247, .1)',
		'active_callback' => [
			[
				'setting'  => 'cariera_dashboard_views_statistics',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);
