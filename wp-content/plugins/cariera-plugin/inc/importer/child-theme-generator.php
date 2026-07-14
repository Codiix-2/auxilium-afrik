<?php

namespace Cariera_Core\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Child_Theme_Generator {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Parent theme slug
	 *
	 * @var string
	 */
	private $parent_theme = 'cariera';

	/**
	 * Child theme slug
	 *
	 * @var string
	 */
	private $child_theme = 'cariera-child';

	/**
	 * Check if child theme is already active
	 *
	 * @since 1.9.8
	 */
	public function is_child_theme_active() {
		$current_theme = wp_get_theme();
		return ( $current_theme->get_template() === $this->parent_theme && $current_theme->get_stylesheet() === $this->child_theme );
	}

	/**
	 * Check if child theme exists
	 *
	 * @since 1.9.8
	 */
	public function child_theme_exists() {
		$theme_root = get_theme_root();
		return is_dir( $theme_root . '/' . $this->child_theme );
	}

	/**
	 * Generate child theme
	 *
	 * @since 1.9.8
	 */
	public function generate() {
		global $wp_filesystem;

		// Initialize filesystem.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$theme_root      = get_theme_root();
		$child_theme_dir = $theme_root . '/' . $this->child_theme;

		// Check if child theme already exists.
		if ( $this->child_theme_exists() ) {
			return new \WP_Error(
				'child_theme_exists',
				esc_html__( 'Child theme already exists.', 'cariera-core' )
			);
		}

		// Check if directory is writable.
		if ( ! $wp_filesystem->is_writable( $theme_root ) ) {
			return new \WP_Error(
				'directory_not_writable',
				sprintf(
					// translators: %s: theme root directory.
					esc_html__( 'The directory %s is not writable.', 'cariera-core' ),
					$theme_root
				)
			);
		}

		// Create child theme directory.
		if ( ! wp_mkdir_p( $child_theme_dir ) ) {
			return new \WP_Error(
				'mkdir_failed',
				esc_html__( 'Could not create child theme directory.', 'cariera-core' )
			);
		}

		// Create style.css.
		$style_css = $this->get_style_css_content();
		if ( ! $wp_filesystem->put_contents( $child_theme_dir . '/style.css', $style_css, FS_CHMOD_FILE ) ) {
			return new \WP_Error(
				'style_creation_failed',
				esc_html__( 'Could not create style.css file.', 'cariera-core' )
			);
		}

		// Create functions.php.
		$functions_php = $this->get_functions_php_content();
		if ( ! $wp_filesystem->put_contents( $child_theme_dir . '/functions.php', $functions_php, FS_CHMOD_FILE ) ) {
			return new \WP_Error(
				'functions_creation_failed',
				esc_html__( 'Could not create functions.php file.', 'cariera-core' )
			);
		}

		// Create screenshot.jpg.
		$parent_screenshot = get_template_directory() . '/assets/images/screenshot-child.jpg';
		if ( file_exists( $parent_screenshot ) ) {
			$wp_filesystem->copy( $parent_screenshot, $child_theme_dir . '/screenshot.jpg' );
		}

		return [
			'success' => true,
			'message' => esc_html__( 'Child theme created successfully.', 'cariera-core' ),
		];
	}

	/**
	 * Activate child theme
	 *
	 * @since 1.9.8
	 */
	public function activate() {
		if ( ! $this->child_theme_exists() ) {
			return new \WP_Error(
				'child_theme_not_found',
				esc_html__( 'Child theme does not exist.', 'cariera-core' )
			);
		}

		// Switch to child theme.
		switch_theme( $this->child_theme );

		// Verify theme was switched.
		if ( get_stylesheet() !== $this->child_theme ) {
			return new \WP_Error(
				'activation_failed',
				esc_html__( 'Could not activate child theme.', 'cariera-core' )
			);
		}

		return true;
	}

	/**
	 * Generate the default content for the child theme's style.css file.
	 *
	 * @since 1.9.8
	 */
	private function get_style_css_content() {
		return implode(
			"\n",
			[
				'/*',
				'Theme Name: Cariera Child',
				'Theme URI: https://1.envato.market/cariera',
				"Template: {$this->parent_theme}",
				'Description: Cariera Child Theme.',
				'Author: Gnodesign',
				'Author URI: https://1.envato.market/gnodesign',
				'Version: 1.0.0',
				'License: ThemeForest',
				'License URI: http://themeforest.net/licenses',
				'Text Domain: cariera',
				'Domain Path: /lang/',
				'*/',
			]
		);
	}

	/**
	 * Generate the default content for the child theme's functions.php file.
	 *
	 * @since 1.9.8
	 */
	private function get_functions_php_content() {
		return <<<'PHP'
    <?php

    add_action( 'wp_enqueue_scripts', 'cariera_child_enqueue_scripts', 20 );

    function cariera_child_enqueue_scripts() {
        wp_enqueue_style( 'cariera-child-style', get_stylesheet_uri() );
    }

    PHP;
	}
}
