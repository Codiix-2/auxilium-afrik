<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once get_template_directory() . '/inc/onboarding/plugins/class-tgm-plugin-activation.php';

/**
 * Register the required plugins for this theme.
 */
function cariera_register_required_plugins() {
	$plugins_url = \Cariera\Onboarding\License::instance()->core_bundle_url();

	$plugins = [
		[
			'name'             => 'Cariera Core',
			'slug'             => 'cariera-plugin',
			'source'           => $plugins_url . '/cariera-plugin.zip',
			'required'         => true,
			'version'          => '2.0.0',
			'force_activation' => false,
		],
		[
			'name'             => 'Cariera Addons',
			'slug'             => 'cariera-addons',
			'source'           => $plugins_url . '/cariera-addons.zip',
			'required'         => true,
			'version'          => '1.1.0',
			'force_activation' => false,
		],
		[
			'name'     => 'Elementor',
			'slug'     => 'elementor',
			'required' => true,
		],
		[
			'name'     => 'Kirki Framework',
			'slug'     => 'kirki',
			'required' => true,
		],
		[
			'name'     => 'WP Job Manager',
			'slug'     => 'wp-job-manager',
			'required' => true,
		],
		[
			'name'     => 'WooCommerce',
			'slug'     => 'woocommerce',
			'required' => false,
		],
		[
			'name'     => 'Contact Form 7',
			'slug'     => 'contact-form-7',
			'required' => false,
		],
	];

	// TODO: Delete this once Cariera Packages is fully working.
	$native_packages_plugin = (bool) get_option( 'cariera_packages_addon', true );

	if ( $native_packages_plugin ) {
		$plugins[] = [
			'name'             => 'Cariera Packages',
			'slug'             => 'cariera-packages',
			'source'           => $plugins_url . '/cariera-packages.zip',
			'required'         => true,
			'version'          => '1.0.0',
			'force_activation' => false,
		];
	} else {
		$plugins[] = [
			'name'     => 'WP Job Manager - WC Paid Listings',
			'slug'     => 'wp-job-manager-wc-paid-listings',
			'source'   => $plugins_url . '/wp-job-manager-wc-paid-listings.zip',
			'required' => false,
			'version'  => '3.0.3',
		];
	}

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = [
		'id'           => 'cariera',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'install-required-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
	];

	tgmpa( $plugins, $config );
}

add_action( 'tgmpa_register', 'cariera_register_required_plugins' );
