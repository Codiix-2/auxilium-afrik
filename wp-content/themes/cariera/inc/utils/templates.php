<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if Elementor template exists
 *
 * @param int $template_id
 *
 * @since 1.7.7
 */
function elementor_template_exists( $template_id ) {
	return is_int( $template_id ) && get_post_type( $template_id ) === 'elementor_library' && get_post_status( $template_id ) !== 'trash';
}

/**
 * Print the template
 *
 * @param int $template_id
 *
 * @since 1.7.7
 */
function print_template( $template_id ) {
	if ( ! \Cariera\is_elementor_active() ) {
		return;
	}

	if ( ! \Cariera\is_elementor_preview_mode() ) {
		\Cariera\enqueue_template_css( $template_id );
		wp_print_styles( 'elementor-post-' . $template_id );
	}

	$frontend = \Elementor\Plugin::$instance->frontend;

	echo $frontend->get_builder_content_for_display( $template_id );
}

/**
 * Enqueue Elementor CSS
 *
 * @param int $template_id
 *
 * @since 1.7.7
 */
function enqueue_template_css( $template_id ) {
	if ( ! \Cariera\is_elementor_active() ) {
		return;
	}

	$css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
	$css_file->enqueue();
}

/**
 * Get page Elementor Settings
 *
 * @param string $setting_key
 * @param int    $post_id
 *
 * @since 1.7.7
 */
function get_page_elementor_setting( $setting_key, $post_id = null ) {
	if ( ! \Cariera\is_elementor_active() ) {
		return;
	}

	$page_settings_manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );
	$page_settings_model   = $page_settings_manager->get_model( $post_id ?? get_the_ID() );

	return $page_settings_model->get_settings( $setting_key );
}

/**
 * Print the Header to the frontend
 *
 * @since   1.7.7
 * @version 1.9.3
 */
function print_header() {
	// Add Elementor Pro support for Custom Header.
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
		return;
	}

	// TODO: we have to pass the selected ID from the settings here later on.
	$header = '';

	// If Elementor header exists.
	if ( \Cariera\elementor_template_exists( $header ) ) {
		\Cariera\print_template( $header );
	}

	// Default footer if Elementor header does not exists.
	if ( ! \Cariera\elementor_template_exists( $header ) && get_post_meta( get_the_ID(), 'cariera_show_header', 'true' ) !== 'hide' ) {
		get_template_part( 'templates/header/header-default' );
	}
}

/**
 * Print the Footer to the frontend
 *
 * @since 1.7.7
 */
function print_footer() {
	// Add Elementor Pro support for Custom Footer.
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		return;
	}

	// TODO: here will be the setting that will pull the footer template.
	$footer = '';

	// If Elementor footer exists.
	if ( \Cariera\elementor_template_exists( $footer ) ) {
		\Cariera\print_template( $footer );
	}

	// Default footer if Elementor footer does not exists.
	if ( ! \Cariera\elementor_template_exists( $footer ) ) {
		get_template_part( 'templates/footer/footer' );
	}
}
