<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'extra_general',
	[
		'priority'    => 20,
		'title'       => esc_html__( 'Extra Options', 'cariera' ),
		'description' => '',
	]
);

/**
 * Add Sections.
 */

// COOKIE BAR OPTIONS.
\Cariera\Kirki::add_section(
	'cookie_bar',
	[
		'title'          => esc_html__( 'Cookie Notice Options', 'cariera' ),
		'description'    => '',
		'panel'          => 'extra_general', // Not typically needed.
		'priority'       => 12,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// COOKIE NOTICE OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_cookie_notice',
		'type'        => 'radio',
		'label'       => esc_html__( 'Cookie Notice', 'cariera' ),
		'description' => esc_html__( 'If enabled, a cookie notice will show at the bottom of the page on load.', 'cariera' ),
		'section'     => 'cookie_bar',
		'default'     => '',
		'priority'    => 20,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_notice_message',
		'type'            => 'textarea',
		'label'           => esc_html__( 'Cookie Text Message', 'cariera' ),
		'description'     => esc_html__( 'Write the message that you want to show up in the Cookie Notice.', 'cariera' ),
		'section'         => 'cookie_bar',
		'default'         => esc_html__( 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies.', 'cariera' ),
		'priority'        => 20,
		'active_callback' => [
			[
				'setting'  => 'cariera_cookie_notice',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'        => 'cariera_policy_page',
		'type'            => 'dropdown-pages',
		'label'           => esc_html__( 'Cookie Details Page', 'cariera' ),
		'description'     => esc_html__( 'Choose page that will contain detailed information about your Privacy Policy.', 'cariera' ),
		'section'         => 'cookie_bar',
		'default'         => '',
		'priority'        => 20,
		'active_callback' => [
			[
				'setting'  => 'cariera_cookie_notice',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);
