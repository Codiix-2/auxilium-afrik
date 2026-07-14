<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Sections.
 */

// WOOCOMMERCE OPTIONS.
\Cariera\Kirki::add_section(
	'woocommerce_options',
	[
		'title'          => esc_html__( 'General Options', 'cariera' ),
		'description'    => esc_html__( 'Woocommerce related options', 'cariera' ),
		'panel'          => 'woocommerce', // Not typically needed.
		'priority'       => 1,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '', // Rarely needed.
	]
);

/**
 * Add Fields.
 */
\Cariera\Kirki::add_field(
	'cariera',
	[
		'settings'    => 'cariera_shop_layout',
		'type'        => 'select',
		'label'       => esc_html__( 'Shop Layout', 'cariera' ),
		'description' => esc_html__( 'Choose the sidebar side for your shop.', 'cariera' ),
		'section'     => 'woocommerce_options',
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
		'settings'    => 'cariera_product_share',
		'type'        => 'radio',
		'label'       => esc_html__( 'Show Sharing Icons', 'cariera' ),
		'description' => esc_html__( 'Display social sharing icons on single product', 'cariera' ),
		'section'     => 'woocommerce_options',
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
		'settings'    => 'cariera_related_products',
		'type'        => 'radio',
		'label'       => esc_html__( 'Related Products', 'cariera' ),
		'description' => esc_html__( 'Select "disable" to remove the related products on single product page.', 'cariera' ),
		'section'     => 'woocommerce_options',
		'default'     => 'true',
		'priority'    => 10,
		'choices'     => [
			'true' => esc_html__( 'Enable', 'cariera' ),
			''     => esc_html__( 'Disable', 'cariera' ),
		],
	]
);
