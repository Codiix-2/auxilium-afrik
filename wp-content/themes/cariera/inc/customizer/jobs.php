<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'jobs_general',
	[
		'priority'    => 13,
		'title'       => esc_html__( 'Job Options', 'cariera' ),
		'description' => esc_html__( 'Job related options', 'cariera' ),
	]
);

/**
 * Add Sections.
 */

// JOB OPTIONS.
\Cariera\Kirki::add_section(
	'job_options',
	[
		'title'          => esc_html__( 'Job Options', 'cariera' ),
		'description'    => esc_html__( 'Job related options', 'cariera' ),
		'panel'          => 'jobs_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// JOB TAXONOMY OPTIONS.
\Cariera\Kirki::add_section(
	'job_taxonomy_options',
	[
		'title'          => esc_html__( 'Job Taxonomy Options', 'cariera' ),
		'description'    => esc_html__( 'Job Taxonomy view options', 'cariera' ),
		'panel'          => 'jobs_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// SINGLE JOB OPTIONS.
\Cariera\Kirki::add_section(
	'single_job_option',
	[
		'title'          => esc_html__( 'Single Job Options', 'cariera' ),
		'description'    => esc_html__( 'Single job related options', 'cariera' ),
		'panel'          => 'jobs_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// GENERAL JOB OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_job_search_map',
		'type'        => 'radio',
		'label'       => esc_html__( 'General Job Search Map', 'cariera' ),
		'description' => esc_html__( 'Select "disable" if you want to disable the job search map.', 'cariera' ),
		'section'     => 'job_options',
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
		'settings' => 'cariera_job_category_custom',
		'type'     => 'custom',
		'section'  => 'job_options',
		'default'  => '<hr>',
		'priority' => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_job_category_bg',
		'type'        => 'radio',
		'label'       => esc_html__( 'Job Category Background', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to hide the category background on single category pages.', 'cariera' ),
		'section'     => 'job_options',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);

// JOB TAXONOMY OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_job_taxonomy_layout',
		'type'        => 'select',
		'label'       => esc_html__( 'Job Layout', 'cariera' ),
		'description' => esc_html__( 'Choose the layout for the jobs that will be displayed in the taxonomy pages.', 'cariera' ),
		'section'     => 'job_taxonomy_options',
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
		'settings'        => 'cariera_job_taxonomy_list_version',
		'type'            => 'select',
		'label'           => esc_html__( 'Job List Layout', 'cariera' ),
		'description'     => esc_html__( 'Choose the list layout for the jobs.', 'cariera' ),
		'section'         => 'job_taxonomy_options',
		'default'         => '1',
		'priority'        => 10,
		'choices'         => [
			'1' => esc_attr__( 'List Layout 1', 'cariera' ),
			'2' => esc_attr__( 'List Layout 2', 'cariera' ),
			'3' => esc_attr__( 'List Layout 3', 'cariera' ),
			'4' => esc_attr__( 'List Layout 4', 'cariera' ),
			'5' => esc_attr__( 'List Layout 5', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_job_taxonomy_layout',
				'operator' => '==',
				'value'    => 'list',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_job_taxonomy_grid_version',
		'type'            => 'select',
		'label'           => esc_html__( 'Job Grid Layout', 'cariera' ),
		'description'     => esc_html__( 'Choose the grid layout for the jobs.', 'cariera' ),
		'section'         => 'job_taxonomy_options',
		'default'         => '1',
		'priority'        => 10,
		'choices'         => [
			'1' => esc_attr__( 'Grid Layout 1', 'cariera' ),
			'2' => esc_attr__( 'Grid Layout 2', 'cariera' ),
			'3' => esc_attr__( 'Grid Layout 3', 'cariera' ),
			'4' => esc_attr__( 'Grid Layout 4', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_job_taxonomy_layout',
				'operator' => '==',
				'value'    => 'grid',
			],
		],
	]
);

// SINGLE JOB OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_job_share',
		'type'        => 'radio',
		'label'       => esc_html__( 'Sharing Options', 'cariera' ),
		'description' => esc_html__( 'Display social sharing on single job page.', 'cariera' ),
		'section'     => 'single_job_option',
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
		'settings'    => 'cariera_job_map',
		'type'        => 'radio',
		'label'       => esc_html__( 'Job Map', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to remove the map.', 'cariera' ),
		'section'     => 'single_job_option',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);
