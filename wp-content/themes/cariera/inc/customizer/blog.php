<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Panel.
 */
\Cariera\Kirki::add_panel(
	'blog_general',
	[
		'priority'    => 16,
		'title'       => esc_html__( 'Blog Options', 'cariera' ),
		'description' => esc_html__( 'Blog related options', 'cariera' ),
	]
);

/**
 * Add Sections.
 */

// PAGE HEADER BLOG OPTIONS.
\Cariera\Kirki::add_section(
	'page_header_blog',
	[
		'title'          => esc_html__( 'Page Header Blog Options', 'cariera' ),
		'description'    => esc_html__( 'Page header related options', 'cariera' ),
		'panel'          => 'blog_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// GENERAL BLOG OPTIONS.
\Cariera\Kirki::add_section(
	'blog',
	[
		'title'          => esc_html__( 'General Blog Options', 'cariera' ),
		'description'    => esc_html__( 'Blog related options', 'cariera' ),
		'panel'          => 'blog_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

// SINGLE BLOG POST OPTIONS.
\Cariera\Kirki::add_section(
	'single_blog_post',
	[
		'title'          => esc_html__( 'Single Blog Post Options', 'cariera' ),
		'description'    => esc_html__( 'Single blog post related options', 'cariera' ),
		'panel'          => 'blog_general', // Not typically needed.
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */

// PAGE HEADER BLOG OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_blog_page_header',
		'type'        => 'radio',
		'label'       => esc_html__( 'Page Header', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to remove the page header section on the blog pages.', 'cariera' ),
		'section'     => 'page_header_blog',
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
		'settings'        => 'cariera_blog_title',
		'type'            => 'text',
		'label'           => esc_html__( 'Blog Title', 'cariera' ),
		'default'         => esc_html__( 'Our Blog', 'cariera' ),
		'section'         => 'page_header_blog',
		'priority'        => 10,
		'active_callback' => [
			[
				'setting'  => 'cariera_blog_page_header',
				'operator' => '==',
				'value'    => 'true',
			],
		],
	]
);

// GENERAL BLOG OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_blog_layout',
		'type'        => 'select',
		'label'       => esc_html__( 'Blog Layout', 'cariera' ),
		'description' => esc_html__( 'Choose the sidebar side for your blog.', 'cariera' ),
		'section'     => 'blog',
		'default'     => 'right-sidebar',
		'priority'    => 10,
		'choices'     => [
			'left-sidebar'  => esc_attr__( 'Left Sidebar', 'cariera' ),
			'right-sidebar' => esc_attr__( 'Right Sidebar', 'cariera' ),
			'fullwidth'     => esc_attr__( 'No Sidebar', 'cariera' ),
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_blog_meta',
		'type'        => 'multicheck',
		'label'       => esc_html__( 'Meta Informations on Blog Posts', 'cariera' ),
		'description' => esc_html__( 'Set which elements of posts meta data you want to display on blog and archive pages.', 'cariera' ),
		'section'     => 'blog',
		'default'     => [ 'author', 'date', 'cat' ],
		'priority'    => 10,
		'choices'     => [
			'author' => esc_html__( 'Author', 'cariera' ),
			'date'   => esc_html__( 'Date', 'cariera' ),
			'cat'    => esc_html__( 'Categories', 'cariera' ),
			'com'    => esc_html__( 'Comments', 'cariera' ),
		],
	]
);

// SINGLE BLOG POST OPTIONS.
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_meta_single',
		'type'        => 'multicheck',
		'label'       => esc_html__( 'Meta Informations', 'cariera' ),
		'description' => esc_html__( 'Set which elements of posts meta data you want to display on a single post.', 'cariera' ),
		'section'     => 'single_blog_post',
		'default'     => [ 'author', 'date', 'cat' ],
		'priority'    => 10,
		'choices'     => [
			'author' => esc_html__( 'Author', 'cariera' ),
			'date'   => esc_html__( 'Date', 'cariera' ),
			'cat'    => esc_html__( 'Categories', 'cariera' ),
			'com'    => esc_html__( 'Comments', 'cariera' ),
		],
	]
);

\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_post_share',
		'type'        => 'radio',
		'label'       => esc_html__( 'Show Sharing Icons', 'cariera' ),
		'description' => esc_html__( 'Display social sharing icons on single post', 'cariera' ),
		'section'     => 'single_blog_post',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);
