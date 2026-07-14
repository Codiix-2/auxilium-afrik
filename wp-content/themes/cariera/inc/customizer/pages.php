<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'pages_general',
	[
		'priority'    => 19,
		'title'       => esc_html__( 'Pages Options', 'cariera' ),
		'description' => esc_html__( 'Pages related options', 'cariera' ),
	]
);

/**
 * Add Sections.
 */

// LOGIN PAGE OPTIONS.
\Cariera\Kirki::add_section(
	'login_page',
	[
		'title'          => esc_html__( 'Login & Register Page Options', 'cariera' ),
		'description'    => esc_html__( 'Login & Register related options', 'cariera' ),
		'panel'          => 'pages_general',
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// LOGIN & REGISTER PAGE OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'login_page_image',
		'type'        => 'image',
		'label'       => esc_html__( 'Login Page Background Image', 'cariera' ),
		'description' => esc_html__( 'Background image for the Login page', 'cariera' ),
		'section'     => 'login_page',
		'priority'    => 60,
		'default'     => '',
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'login_page_text',
		'type'        => 'textarea',
		'label'       => esc_html__( 'Login Page Intro Text', 'cariera' ),
		'description' => esc_html__( 'Edit the text that will be shown on the login page at the left section.', 'cariera' ),
		'section'     => 'login_page',
		'priority'    => 60,
		'default'     => 'Welcome to Cariera',
	]
);
