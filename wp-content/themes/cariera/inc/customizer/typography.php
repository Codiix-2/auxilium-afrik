<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'typo_general',
	[
		'priority'    => 11,
		'title'       => esc_html__( 'Typography Options', 'cariera' ),
		'description' => esc_html__( 'Typography related options', 'cariera' ),
	]
);

/**
 * Add Sections.
 */
// BODY - TYPOGRAPHY OPTIONS.
\Cariera\Kirki::add_section(
	'body_typo',
	[
		'title'          => esc_html__( 'Body', 'cariera' ),
		'description'    => '',
		'panel'          => 'typo_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// HEADINGS - TYPOGRAPHY OPTIONS.
\Cariera\Kirki::add_section(
	'headings_typo',
	[
		'title'          => esc_html__( 'Heading', 'cariera' ),
		'description'    => '',
		'panel'          => 'typo_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// MENU - TYPOGRAPHY OPTIONS.
\Cariera\Kirki::add_section(
	'menu_typo',
	[
		'title'          => esc_html__( 'Menu', 'cariera' ),
		'description'    => '',
		'panel'          => 'typo_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// BODY - TYPOGRAPHY OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_body_typo',
		'type'        => 'typography',
		'label'       => esc_html__( 'Body Typography', 'cariera' ),
		'description' => '',
		'section'     => 'body_typo',
		'priority'    => 10,
		'default'     => [
			'font-family'    => 'Poppins',
			'variant'        => '400',
			'font-size'      => '16px',
			'line-height'    => '1.65',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#948a99',
			'text-transform' => 'none',
		],
		'output'      => [
			[
				'element' => 'body',
			],
		],
	]
);

// HEADINGS - TYPOGRAPHY OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_heading1_typo',
		'type'     => 'typography',
		'label'    => esc_html__( 'Heading 1', 'cariera' ),
		'section'  => 'headings_typo',
		'priority' => 10,
		'default'  => [
			'font-family'    => 'Poppins',
			'variant'        => '600',
			'font-size'      => '46px',
			'line-height'    => '1.3',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#333',
			'text-transform' => 'none',
		],
		'output'   => [
			[
				'element' => 'h1',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_heading2_typo',
		'type'     => 'typography',
		'label'    => esc_html__( 'Heading 2', 'cariera' ),
		'section'  => 'headings_typo',
		'priority' => 10,
		'default'  => [
			'font-family'    => 'Poppins',
			'variant'        => '600',
			'font-size'      => '38px',
			'line-height'    => '1.3',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#333',
			'text-transform' => 'none',
		],
		'output'   => [
			[
				'element' => 'h2',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_heading3_typo',
		'type'     => 'typography',
		'label'    => esc_html__( 'Heading 3', 'cariera' ),
		'section'  => 'headings_typo',
		'priority' => 10,
		'default'  => [
			'font-family'    => 'Poppins',
			'variant'        => '600',
			'font-size'      => '30px',
			'line-height'    => '1.3',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#333',
			'text-transform' => 'none',
		],
		'output'   => [
			[
				'element' => 'h3',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_heading4_typo',
		'type'     => 'typography',
		'label'    => esc_html__( 'Heading 4', 'cariera' ),
		'section'  => 'headings_typo',
		'priority' => 10,
		'default'  => [
			'font-family'    => 'Poppins',
			'variant'        => '500',
			'font-size'      => '24px',
			'line-height'    => '1.3',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#333',
			'text-transform' => 'none',
		],
		'output'   => [
			[
				'element' => 'h4',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_heading5_typo',
		'type'     => 'typography',
		'label'    => esc_html__( 'Heading 5', 'cariera' ),
		'section'  => 'headings_typo',
		'priority' => 10,
		'default'  => [
			'font-family'    => 'Poppins',
			'variant'        => '500',
			'font-size'      => '20px',
			'line-height'    => '1.3',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#333',
			'text-transform' => 'none',
		],
		'output'   => [
			[
				'element' => 'h5',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_heading6_typo',
		'type'     => 'typography',
		'label'    => esc_html__( 'Heading 6', 'cariera' ),
		'section'  => 'headings_typo',
		'priority' => 10,
		'default'  => [
			'font-family'    => 'Poppins',
			'variant'        => '500',
			'font-size'      => '18px',
			'line-height'    => '1.3',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#333',
			'text-transform' => 'none',
		],
		'output'   => [
			[
				'element' => 'h6',
			],
		],
	]
);

// MENU - TYPOGRAPHY OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_menu_typo',
		'type'     => 'typography',
		'label'    => esc_html__( 'Menu', 'cariera' ),
		'section'  => 'menu_typo',
		'priority' => 10,
		'default'  => [
			'font-family'    => 'Poppins',
			'variant'        => '500',
			'font-size'      => '14px',
			'line-height'    => '1.4',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#666',
			'text-transform' => 'capitalize',
		],
		'output'   => [
			[
				'element' => 'ul.main-nav .menu-item a, header.main-header .extra-menu-item > a',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_submenu_typo',
		'type'     => 'typography',
		'label'    => esc_html__( 'Submenu Item', 'cariera' ),
		'section'  => 'menu_typo',
		'priority' => 10,
		'default'  => [
			'font-family'    => 'Poppins',
			'variant'        => '500',
			'font-size'      => '14px',
			'line-height'    => '1.4',
			'letter-spacing' => '0',
			'subsets'        => '',
			'color'          => '#666',
			'text-transform' => 'capitalize',
		],
		'output'   => [
			[
				'element' => 'ul.main-nav .menu-item.dropdown .dropdown-menu > li > a, ul.main-nav .mega-menu .dropdown-menu .mega-menu-inner .menu-item-mega .sub-menu a',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_menu_hover_color',
		'type'        => 'color',
		'label'       => esc_html__( 'Menu Items Hover Color', 'cariera' ),
		'description' => esc_html__( 'Color for any menu item when hovering.', 'cariera' ),
		'section'     => 'menu_typo',
		'default'     => '#303af7',
		'priority'    => 10,
	]
);
