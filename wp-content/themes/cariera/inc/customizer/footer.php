<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Sections.
 */
\Cariera\Kirki::add_section(
	'footer',
	[
		'title'          => esc_html__( 'Footer Options', 'cariera' ),
		'description'    => esc_html__( 'Footer related options', 'cariera' ),
		'panel'          => '', // Not typically needed.
		'priority'       => 12,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// FOOTER OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_footer_bg',
		'type'        => 'color',
		'label'       => esc_html__( 'Footer Background Color', 'cariera' ),
		'description' => esc_html__( 'Select the background color for your footer.', 'cariera' ),
		'section'     => 'footer',
		'default'     => '#1e1f21',
		'priority'    => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_footer_title_color',
		'type'        => 'color',
		'label'       => esc_html__( 'Footer Widget Title Color', 'cariera' ),
		'description' => esc_html__( 'Select the color for the widget titles in the footer.', 'cariera' ),
		'section'     => 'footer',
		'default'     => '#fff',
		'priority'    => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_footer_text_color',
		'type'        => 'color',
		'label'       => esc_html__( 'Footer Text Color', 'cariera' ),
		'description' => esc_html__( 'Select the color for the text in the footer.', 'cariera' ),
		'section'     => 'footer',
		'default'     => '#948a99',
		'priority'    => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_footer_custom2',
		'type'     => 'custom',
		'section'  => 'footer',
		'default'  => '<hr>',
		'priority' => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_footer_info',
		'type'        => 'radio',
		'label'       => esc_html__( 'Footer Info Section', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to remove the footer info section. ', 'cariera' ),
		'section'     => 'footer',
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
		'settings'        => 'cariera_footer_sidebar_1',
		'type'            => 'select',
		'label'           => esc_html__( 'Footer Sidebar Column 1', 'cariera' ),
		'description'     => '',
		'section'         => 'footer',
		'default'         => 'col-sm-6 col-xs-6',
		'priority'        => 10,
		'choices'         => [
			'col-sm-12 col-xs-12'        => esc_html__( 'Full Width', 'cariera' ),
			'col-sm-9 col-xs-9'          => esc_html__( '3/4', 'cariera' ),
			'col-sm-8 col-xs-8'          => esc_html__( '2/3', 'cariera' ),
			'col-sm-6 col-xs-6'          => esc_html__( '1/2', 'cariera' ),
			'col-sm-4 col-xs-4'          => esc_html__( '1/3', 'cariera' ),
			'col-md-3 col-sm-6 col-xs-6' => esc_html__( '1/4', 'cariera' ),
			'col-md-2 col-sm-6 col-xs-6' => esc_html__( '1/6', 'cariera' ),
			'disabled'                   => esc_html__( 'disabled', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_footer_info',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_footer_sidebar_2',
		'type'            => 'select',
		'label'           => esc_html__( 'Footer Sidebar Column 2', 'cariera' ),
		'description'     => '',
		'section'         => 'footer',
		'default'         => 'col-md-2 col-sm-6 col-xs-6',
		'priority'        => 10,
		'choices'         => [
			'col-sm-12 col-xs-12'        => esc_html__( 'Full Width', 'cariera' ),
			'col-sm-9 col-xs-9'          => esc_html__( '3/4', 'cariera' ),
			'col-sm-8 col-xs-8'          => esc_html__( '2/3', 'cariera' ),
			'col-sm-6 col-xs-6'          => esc_html__( '1/2', 'cariera' ),
			'col-sm-4 col-xs-4'          => esc_html__( '1/3', 'cariera' ),
			'col-md-3 col-sm-6 col-xs-6' => esc_html__( '1/4', 'cariera' ),
			'col-md-2 col-sm-6 col-xs-6' => esc_html__( '1/6', 'cariera' ),
			'disabled'                   => esc_html__( 'disabled', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_footer_info',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_footer_sidebar_3',
		'type'            => 'select',
		'label'           => esc_html__( 'Footer Sidebar Column 3', 'cariera' ),
		'description'     => '',
		'section'         => 'footer',
		'default'         => 'col-md-2 col-sm-6 col-xs-6',
		'priority'        => 10,
		'choices'         => [
			'col-sm-12 col-xs-12'        => esc_html__( 'Full Width', 'cariera' ),
			'col-sm-9 col-xs-9'          => esc_html__( '3/4', 'cariera' ),
			'col-sm-8 col-xs-8'          => esc_html__( '2/3', 'cariera' ),
			'col-sm-6 col-xs-6'          => esc_html__( '1/2', 'cariera' ),
			'col-sm-4 col-xs-4'          => esc_html__( '1/3', 'cariera' ),
			'col-md-3 col-sm-6 col-xs-6' => esc_html__( '1/4', 'cariera' ),
			'col-md-2 col-sm-6 col-xs-6' => esc_html__( '1/6', 'cariera' ),
			'disabled'                   => esc_html__( 'disabled', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_footer_info',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_footer_sidebar_4',
		'type'            => 'select',
		'label'           => esc_html__( 'Footer Sidebar Column 4', 'cariera' ),
		'description'     => '',
		'section'         => 'footer',
		'default'         => 'col-md-2 col-sm-6 col-xs-6',
		'priority'        => 10,
		'choices'         => [
			'col-sm-12 col-xs-12'        => esc_html__( 'Full Width', 'cariera' ),
			'col-sm-9 col-xs-9'          => esc_html__( '3/4', 'cariera' ),
			'col-sm-8 col-xs-8'          => esc_html__( '2/3', 'cariera' ),
			'col-sm-6 col-xs-6'          => esc_html__( '1/2', 'cariera' ),
			'col-sm-4 col-xs-4'          => esc_html__( '1/3', 'cariera' ),
			'col-md-3 col-sm-6 col-xs-6' => esc_html__( '1/4', 'cariera' ),
			'col-md-2 col-sm-6 col-xs-6' => esc_html__( '1/6', 'cariera' ),
			'disabled'                   => esc_html__( 'disabled', 'cariera' ),
		],
		'active_callback' => [
			[
				'setting'  => 'cariera_footer_info',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings' => 'cariera_footer_custom3',
		'type'     => 'custom',
		'section'  => 'footer',
		'default'  => '<hr>',
		'priority' => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_copyrights',
		'type'        => 'textarea',
		'label'       => esc_html__( 'Copyrights text', 'cariera' ),
		'description' => esc_html__( 'Enter your Copyright Text (HTML allowed).', 'cariera' ),
		'default'     => 'Copyright &copy; Cariera. Developed by <a href="https://1.envato.market/gnodesign" target="_blank">Gnodesign</a>',
		'section'     => 'footer',
		'priority'    => 10,
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_footer_socials',
		'type'        => 'repeater',
		'label'       => esc_html__( 'Social Media', 'cariera' ),
		'description' => esc_html__( 'Choose the social media that you want to be displayed in the footer.', 'cariera' ),
		'section'     => 'footer',
		'priority'    => 10,
		'default'     => '',
		'fields'      => [
			'social_type' => [
				'type'        => 'select',
				'label'       => esc_html__( 'Social Media Type', 'cariera' ),
				'description' => esc_html__( 'Choose your social media type.', 'cariera' ),
				'default'     => '',
				'priority'    => 10,
				'choices'     => \Cariera\footer_social_media(),
			],
			'link_url'    => [
				'type'        => 'text',
				'label'       => esc_html__( 'Social URL', 'cariera' ),
				'description' => esc_html__( 'Enter the URL for this social', 'cariera' ),
				'default'     => '',
			],
		],
	]
);
