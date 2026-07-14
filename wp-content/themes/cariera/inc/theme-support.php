<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Theme_Support {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Filters.
		add_filter( 'post_thumbnail_html', [ $this, 'vertical_featured_image' ], 10, 5 );
		add_filter( 'woocommerce_create_pages', [ $this, 'disable_wc_page_creation' ] );
		add_filter( 'wp_kses_allowed_html', [ $this, 'allow_svg_tags' ], 10, 2 );
		add_filter( 'kirki/config', [ $this, 'kirki_config' ], 999 );

		// Actions.
		add_action( 'wp_logout', [ $this, 'logout_redirect' ] );
		add_action( 'wp_footer', [ $this, 'back_to_top' ] );
		add_action( 'wp_footer', [ $this, 'cookie_bar' ] );
		add_action( 'wp_footer', [ $this, 'login_register_modal' ] );

		// AJAX.
		add_action( 'wp_ajax_cariera_fonticonpicker', [ $this, 'icon_picker_ajax' ] );
	}

	/**
	 *  Add support for Vertical Featured Images.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed $html
	 * @param int   $post_id
	 * @param int   $post_thumbnail_id
	 * @param mixed $size
	 * @param array $attr
	 */
	public function vertical_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		$image_data = wp_get_attachment_image_src( $post_thumbnail_id, 'large' );

		// Get the image width and height from the data provided by wp_get_attachment_image_src().
		$width  = $image_data[1];
		$height = $image_data[2];

		if ( $height > $width ) {
			$html = str_replace( 'attachment-', 'vertical-image attachment-', $html );
		}
		return $html;
	}

	/**
	 * Redirect on logout.
	 *
	 * @since 1.2.7
	 */
	public function logout_redirect() {
		wp_safe_redirect( home_url() );

		exit;
	}

	/**
	 * Back to top template
	 *
	 * @since   1.7.7
	 * @version 1.7.7
	 */
	public function back_to_top() {
		if ( ! cariera_get_option( 'cariera_back_top' ) ) {
			return;
		}

		get_template_part( 'templates/extra/back-to-top' );
	}

	/**
	 * Cookie Law Info
	 *
	 * @since   1.3.0
	 * @version 1.7.7
	 */
	public function cookie_bar() {
		if ( ! cariera_get_option( 'cariera_cookie_notice' ) ) {
			return;
		}

		get_template_part( 'templates/extra/cookie-bar' );
	}

	/**
	 * Disable WooCommerce page creation on first activate
	 *
	 * @since 1.5.0
	 */
	public function disable_wc_page_creation() {
		$pages = [];

		return $pages;
	}

	/**
	 * Login Register modal
	 *
	 * @since   1.4.0
	 * @version 1.7.0
	 */
	public function login_register_modal() {
		$login_registration = get_option( 'cariera_login_register_layout' );

		if ( is_user_logged_in() || 'page' === $login_registration || is_page_template( 'templates/login-register.php' ) ) {
			return;
		}

		get_template_part( 'templates/popups/login-register' );
	}

	/**
	 * AJAX Font Icon Picker
	 *
	 * @since 1.8.4
	 */
	public function icon_picker_ajax() {
		$icon_sources = [
			'lineawesome'     => \Cariera\Utils\Icons\Line_Awesome::get(),
			'fontawesome'     => get_option( 'cariera_fonticon_fontawesome' ) ? \Cariera\Utils\Icons\Font_Awesome::get() : [],
			'simplelineicons' => get_option( 'cariera_fonticon_simplelineicons' ) ? \Cariera\Utils\Icons\Simpleline::get() : [],
			'iconsmind'       => get_option( 'cariera_fonticon_iconsmind' ) ? \Cariera\Utils\Icons\Iconsmind::get() : [],
		];

		// Prepare icons for JSON response.
		$icons = [];

		foreach ( $icon_sources as $type => $icon_list ) {
			if ( ! empty( $icon_list ) ) {
				$icons[ $type ] = $icon_list;
			}
		}

		// Send the icons list as a JSON response.
		wp_send_json_success( $icons );
	}

	/**
	 * Allow SVG and PATH tags with specific attributes in post content.
	 *
	 * This method extends the list of allowed HTML tags for `wp_kses` when the context is 'post',
	 * enabling inline SVG support for safe attributes.
	 *
	 * @since 1.9.1
	 *
	 * @param array  $tags    The existing array of allowed HTML tags and attributes.
	 * @param string $context The context for which HTML is being filtered (e.g., 'post').
	 *
	 * @return array Modified array with additional SVG-related tags and attributes.
	 */
	public function allow_svg_tags( $tags, $context ) {
		if ( 'post' === $context ) {
			$tags['svg']  = [
				'xmlns'       => true,
				'viewbox'     => true,
				'fill'        => true,
				'class'       => true,
				'width'       => true,
				'height'      => true,
				'aria-hidden' => true,
				'role'        => true,
			];
			$tags['path'] = [
				'd' => true,
			];
		}
		return $tags;
	}

	/**
	 * Customize Kirki configuration support for PHP 8.2+.
	 *
	 * @since 1.9.1
	 *
	 * @param array $config Kirki config array.
	 */
	public function kirki_config( $config ) {
		if ( isset( $config['compiler'] ) ) {
			unset( $config['compiler'] );
		}
		return $config;
	}
}
