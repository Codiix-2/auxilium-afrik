<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'resumes_general',
	[
		'priority'    => 15,
		'title'       => esc_html__( 'Resumes Options', 'cariera' ),
		'description' => esc_html__( 'Resumes related options', 'cariera' ),
	]
);

/**
 * Add Sections.
 */

// RESUME OPTIONS.
\Cariera\Kirki::add_section(
	'resume_options',
	[
		'title'          => esc_html__( 'Resume Options', 'cariera' ),
		'description'    => esc_html__( 'Resume related options', 'cariera' ),
		'panel'          => 'resumes_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// RESUME TAXONOMY OPTIONS.
\Cariera\Kirki::add_section(
	'resume_taxonomy_options',
	[
		'title'          => esc_html__( 'Resume Taxonomy Options', 'cariera' ),
		'description'    => esc_html__( 'Resume Taxonomy view options', 'cariera' ),
		'panel'          => 'resumes_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// SINGLE RESUME OPTIONS.
\Cariera\Kirki::add_section(
	'single_resume_options',
	[
		'title'          => esc_html__( 'Single Resume Options', 'cariera' ),
		'description'    => esc_html__( 'Single resume related options', 'cariera' ),
		'panel'          => 'resumes_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// RESUME OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_resume_search_map',
		'type'        => 'radio',
		'label'       => esc_html__( 'Resume Map', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to remove the map.', 'cariera' ),
		'section'     => 'resume_options',
		'default'     => '',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);

// RESUME TAXONOMY OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_resume_taxonomy_layout',
		'type'        => 'select',
		'label'       => esc_html__( 'Resume Layout', 'cariera' ),
		'description' => esc_html__( 'Choose the layout for the resumes that will be displayed in the taxonomy pages.', 'cariera' ),
		'section'     => 'resume_taxonomy_options',
		'default'     => 'list',
		'priority'    => 10,
		'choices'     => [
			'list' => esc_attr__( 'List Layout', 'cariera' ),
			'grid' => esc_attr__( 'Grid Layout', 'cariera' ),
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_resume_taxonomy_list_version',
		'type'            => 'select',
		'label'           => esc_html__( 'Resume List Layout', 'cariera' ),
		'description'     => esc_html__( 'Choose the list layout for the resumes.', 'cariera' ),
		'section'         => 'resume_taxonomy_options',
		'default'         => '1',
		'priority'        => 10,
		'choices'         => [
			'1' => esc_attr__( 'List Layout 1', 'cariera' ),
			'2' => esc_attr__( 'List Layout 2', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_resume_taxonomy_layout',
				'operator' => '==',
				'value'    => 'list',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_resume_taxonomy_grid_version',
		'type'            => 'select',
		'label'           => esc_html__( 'Resume Grid Layout', 'cariera' ),
		'description'     => esc_html__( 'Choose the grid layout for the resumes.', 'cariera' ),
		'section'         => 'resume_taxonomy_options',
		'default'         => '1',
		'priority'        => 10,
		'choices'         => [
			'1' => esc_attr__( 'Grid Layout 1', 'cariera' ),
			'2' => esc_attr__( 'Grid Layout 2', 'cariera' ),
			'3' => esc_attr__( 'Grid Layout 3', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_resume_taxonomy_layout',
				'operator' => '==',
				'value'    => 'grid',
			],
		],
	]
);

// SINGLE RESUME OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_resume_share',
		'type'        => 'radio',
		'label'       => esc_html__( 'Sharing Options', 'cariera' ),
		'description' => esc_html__( 'Display social sharing on single resume page.', 'cariera' ),
		'section'     => 'single_resume_options',
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
		'settings'    => 'cariera_resume_map',
		'type'        => 'radio',
		'label'       => esc_html__( 'Resume Map', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to remove the map.', 'cariera' ),
		'section'     => 'single_resume_options',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);
