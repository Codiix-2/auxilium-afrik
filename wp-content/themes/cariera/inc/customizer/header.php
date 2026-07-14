<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'header_general',
	[
		'priority'    => 12,
		'title'       => esc_html__( 'Header Options', 'cariera' ),
		'description' => esc_html__( 'Header related options', 'cariera' ),
	]
);

/**
 * Add Sections.
 */

// LOGO OPTIONS.
\Cariera\Kirki::add_section(
	'logo',
	[
		'title'          => esc_html__( 'Logo', 'cariera' ),
		'description'    => '',
		'panel'          => 'header_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// MAIN HEADER OPTIONS.
\Cariera\Kirki::add_section(
	'header',
	[
		'title'          => esc_html__( 'Header Options', 'cariera' ),
		'description'    => esc_html__( 'Header related options', 'cariera' ),
		'panel'          => 'header_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// LOGO OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'logo',
		'type'        => 'image',
		'label'       => esc_html__( 'Logo', 'cariera' ),
		'description' => esc_html__( 'This logo is used for all site.', 'cariera' ),
		'section'     => 'logo',
		'default'     => '',
		'priority'    => 20,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'logo-white',
		'type'        => 'image',
		'label'       => esc_html__( 'Logo White', 'cariera' ),
		'description' => esc_html__( 'This white version of the logo can be used for transparent header.', 'cariera' ),
		'section'     => 'logo',
		'default'     => '',
		'priority'    => 20,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'logo_text',
		'type'        => 'text',
		'label'       => esc_html__( 'Text Logo', 'cariera' ),
		'description' => '',
		'section'     => 'logo',
		'default'     => '',
		'priority'    => 20,
		[
			'setting'  => 'logo',
			'operator' => '!=',
			'value'    => '',
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'logo_width',
		'type'        => 'text',
		'label'       => esc_html__( 'Logo Width(px)', 'cariera' ),
		'description' => '',
		'section'     => 'logo',
		'priority'    => 20,
		'default'     => '150',
		[
			'setting'  => 'logo',
			'operator' => '!=',
			'value'    => '',
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'logo_height',
		'type'        => 'text',
		'label'       => esc_html__( 'Logo Height(px)', 'cariera' ),
		'description' => '',
		'section'     => 'logo',
		'priority'    => 20,
		'default'     => '',
		[
			'setting'  => 'logo',
			'operator' => '!=',
			'value'    => '',
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'logo_margins',
		'type'        => 'spacing',
		'label'       => esc_html__( 'Logo Margin', 'cariera' ),
		'description' => '',
		'section'     => 'logo',
		'priority'    => 20,
		'default'     => [
			'top'    => '0px',
			'bottom' => '0px',
			'left'   => '0px',
			'right'  => '0px',
		],
		[
			'setting'  => 'logo',
			'operator' => '!=',
			'value'    => '',
		],
	]
);

// MAIN HEADER OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_navbar_bg',
		'type'        => 'color',
		'label'       => esc_html__( 'Header Background Color', 'cariera' ),
		'description' => esc_html__( 'Select the background color for your header.', 'cariera' ),
		'section'     => 'header',
		'default'     => '#fff',
		'priority'    => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_fullwidth_header',
		'type'        => 'radio',
		'label'       => esc_html__( 'Full Width Header', 'cariera' ),
		'description' => esc_html__( 'Select "enable" to enable Full Width Header.', 'cariera' ),
		'section'     => 'header',
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
		'settings'    => 'cariera_sticky_header',
		'type'        => 'radio',
		'label'       => esc_html__( 'Sticky Header', 'cariera' ),
		'description' => esc_html__( 'Select "enable" to enable Sticky Header.', 'cariera' ),
		'section'     => 'header',
		'default'     => '',
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
		'settings'    => 'cariera_sticky_mobile_header',
		'type'        => 'radio',
		'label'       => esc_html__( 'Sticky Responsive Header', 'cariera' ),
		'description' => esc_html__( 'Select "enable" to enable Sticky Header for responsive mode.', 'cariera' ),
		'section'     => 'header',
		'default'     => '',
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
		'settings' => 'cariera_header_custom',
		'type'     => 'custom',
		'section'  => 'header',
		'default'  => '<hr>',
		'priority' => 10,
	]
);


\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_header_extra',
		'type'        => 'radio',
		'label'       => esc_html__( 'Header Extra', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to disable all links in the header extra section.', 'cariera' ),
		'section'     => 'header',
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
		'settings'        => 'header_cart',
		'type'            => 'radio',
		'label'           => esc_html__( 'Header Cart', 'cariera' ),
		'description'     => esc_html__( 'select "disable" to disable Header Cart.', 'cariera' ),
		'section'         => 'header',
		'default'         => 'true',
		'priority'        => 10,
		'choices'         => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_header_extra',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'header_account',
		'type'            => 'radio',
		'label'           => esc_html__( 'Header Login/Account', 'cariera' ),
		'description'     => esc_html__( 'Select "disable" to disable Header Login/Account.', 'cariera' ),
		'section'         => 'header',
		'default'         => 'true',
		'priority'        => 10,
		'choices'         => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_header_extra',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'header_cta',
		'type'            => 'radio',
		'label'           => esc_html__( 'Header CTA', 'cariera' ),
		'description'     => esc_html__( 'Select "disable" to disable Header CTA.', 'cariera' ),
		'section'         => 'header',
		'default'         => 'true',
		'priority'        => 10,
		'choices'         => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_header_extra',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);
