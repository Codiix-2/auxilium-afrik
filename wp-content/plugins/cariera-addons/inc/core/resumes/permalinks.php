<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Permalinks {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Permalinks Settings.
	 *
	 * @var array
	 */
	private $permalinks = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->setup_fields();
		$this->settings_save();
		$this->permalinks = \Cariera_Addons\Core\Resumes\Post_Types::get_permalink_structure();
	}

	/**
	 * Add setting fields related to permalinks.
	 *
	 * @since 0.9.10
	 */
	public function setup_fields() {
		add_settings_field(
			'cariera_addons_resume_base_slug',
			esc_html__( 'Resume base', 'cariera-addons' ),
			[ $this, 'resume_base_slug_input' ],
			'permalink',
			'optional'
		);

		add_settings_field(
			'cariera_addons_resume_archive_slug',
			esc_html__( 'Resume archive page', 'cariera-addons' ),
			[ $this, 'resumes_archive_slug_input' ],
			'permalink',
			'optional'
		);

		if ( get_option( 'resume_manager_enable_categories' ) ) {
			add_settings_field(
				'cariera_addons_resume_category_slug',
				esc_html__( 'Resume category base', 'cariera-addons' ),
				[ $this, 'resume_category_slug_input' ],
				'permalink',
				'optional'
			);
		}

		if ( get_option( 'resume_manager_enable_skills' ) ) {
			add_settings_field(
				'cariera_addons_resume_skill_slug',
				esc_html__( 'Resume skill base', 'cariera-addons' ),
				[ $this, 'resume_skill_slug_input' ],
				'permalink',
				'optional'
			);
		}
	}

	/**
	 * Show a slug input box for resume post type slug.
	 *
	 * @since 0.9.10
	 */
	public function resume_base_slug_input() {
		?>
		<input name="cariera_addons_resume_base_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['cariera_addons_resume_base'] ); ?>" placeholder="<?php echo esc_attr_x( 'resume', 'Resume base permalink - resave permalinks after changing this', 'cariera-addons' ); ?>" />
		<?php
	}

	/**
	 * Show a slug input box for resume archive slug.
	 *
	 * @since 0.9.10
	 */
	public function resumes_archive_slug_input() {
		?>
		<input name="cariera_addons_resume_archive_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['cariera_addons_resume_archive'] ); ?>" placeholder="<?php echo esc_attr_x( 'resumes', 'Resumes archive permalink - resave permalinks after changing this', 'cariera-addons' ); ?>" />
		<?php
	}

	/**
	 * Show a slug input box for resume category slug.
	 *
	 * @since 0.9.10
	 */
	public function resume_category_slug_input() {
		?>
		<input name="cariera_addons_resume_category_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['cariera_addons_resume_category'] ); ?>" placeholder="<?php echo esc_attr_x( 'resume-category', 'Resume category slug - resave permalinks after changing this', 'cariera-addons' ); ?>" />
		<?php
	}

	/**
	 * Show a slug input box for resume skill slug.
	 *
	 * @since 0.9.10
	 */
	public function resume_skill_slug_input() {
		?>
		<input name="cariera_addons_resume_skill_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['cariera_addons_resume_skill'] ); ?>" placeholder="<?php echo esc_attr_x( 'resume-skill', 'Resume skill slug - resave permalinks after changing this', 'cariera-addons' ); ?>" />
		<?php
	}

	/**
	 * Save the settings.
	 *
	 * @since   0.9.10
	 * @version 1.0.2
	 */
	public function settings_save() {
		if ( ! is_admin() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WP core handles nonce check for settings save.
		if ( ! isset( $_POST['permalink_structure'] ) ) {
			// We must not be saving permalinks.
			return;
		}

		if ( function_exists( 'switch_to_locale' ) ) {
			switch_to_locale( get_locale() );
		}

		$permalink_settings = \Cariera_Addons\Core\Resumes\Post_Types::get_raw_permalink_settings();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WP core handles nonce check for settings save.
		$permalink_settings['cariera_addons_resume_base']     = isset( $_POST['cariera_addons_resume_base_slug'] ) ? sanitize_title_with_dashes( wp_unslash( $_POST['cariera_addons_resume_base_slug'] ) ) : '';
		$permalink_settings['cariera_addons_resume_archive']  = isset( $_POST['cariera_addons_resume_archive_slug'] ) ? sanitize_title_with_dashes( wp_unslash( $_POST['cariera_addons_resume_archive_slug'] ) ) : '';
		$permalink_settings['cariera_addons_resume_category'] = isset( $_POST['cariera_addons_resume_category_slug'] ) ? sanitize_title_with_dashes( wp_unslash( $_POST['cariera_addons_resume_category_slug'] ) ) : '';
		$permalink_settings['cariera_addons_resume_skill']    = isset( $_POST['cariera_addons_resume_skill_slug'] ) ? sanitize_title_with_dashes( wp_unslash( $_POST['cariera_addons_resume_skill_slug'] ) ) : '';

		update_option( \Cariera_Addons\Core\Resumes\Post_Types::PERMALINK_OPTION_NAME, $permalink_settings );

		if ( function_exists( 'restore_current_locale' ) ) {
			restore_current_locale();
		}

		flush_rewrite_rules();
	}
}
