<?php

namespace Cariera_Addons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Register Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );

		// Enqueue Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ], 20 );
	}

	/**
	 * Register assets.
	 *
	 * @since   0.9.0
	 * @version 1.0.5
	 */
	public function register_assets() {
		$version = function_exists( '\Cariera\is_dev_mode' ) && \Cariera\is_dev_mode() ? wp_rand( 1, 1e4 ) : CARIERA_ADDONS_VERSION;
		$suffix  = is_rtl() ? '.rtl' : '';

		// Job Alerts.
		wp_register_script( 'cariera-addons-job-alerts', CARIERA_ADDONS_URL . '/assets/dist/js/job-alerts.js', [ 'jquery', 'select2' ], $version, true );
		wp_localize_script(
			'cariera-addons-job-alerts',
			'cariera_addons_job_alerts',
			[
				'i18n_confirm_delete' => esc_html__( 'Are you sure you want to delete this alert?', 'cariera-addons' ),
				'is_rtl'              => is_rtl(),
			]
		);

		// Bookmarks.
		wp_register_style( 'cariera-addons-bookmarks', CARIERA_ADDONS_URL . '/assets/dist/css/bookmarks' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-addons-bookmarks', CARIERA_ADDONS_URL . '/assets/dist/js/bookmarks.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-addons-bookmarks',
			'cariera_addons_bookmarks',
			[
				'i18n_confirm_delete'  => esc_html__( 'Are you sure you want to delete this bookmark?', 'cariera-addons' ),
				'i18n_add_bookmark'    => esc_html__( 'Add Bookmark', 'cariera-addons' ),
				'i18n_update_bookmark' => esc_html__( 'Update Bookmark', 'cariera-addons' ),
				'spinner_url'          => includes_url( 'images/spinner.gif' ),
			]
		);

		// Bookmarks Dashboard.
		wp_register_style( 'cariera-addons-bookmarks-dashboard', CARIERA_ADDONS_URL . '/assets/dist/css/bookmarks-dashboard' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-addons-bookmarks-dashboard', CARIERA_ADDONS_URL . '/assets/dist/js/bookmarks-dashboard.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-addons-bookmarks-dashboard',
			'cariera_addons_bookmarks_dashboard',
			[
				'i18n_confirm_delete' => esc_html__( 'Are you sure you want to delete this bookmark?', 'cariera-addons' ),
				'spinner_url'         => includes_url( 'images/spinner.gif' ),
			]
		);

		// Tags.
		wp_register_script( 'cariera-addons-tag-filters', CARIERA_ADDONS_URL . '/assets/dist/js/tag-filters.js', [ 'jquery' ], $version, true );

		// Applications.
		wp_register_script( 'cariera-addons-applications', CARIERA_ADDONS_URL . '/assets/dist/js/applications.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-addons-applications',
			'cariera_addons_applications',
			[
				// translators: %s placeholder is the field name.
				'i18n_required' => esc_html__( '"%s" is a required field', 'cariera-addons' ),
			]
		);

		// Past Applications.
		wp_register_style( 'cariera-addons-past-applications', CARIERA_ADDONS_URL . '/assets/dist/css/past-applications' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-addons-past-applications', CARIERA_ADDONS_URL . '/assets/dist/js/past-applications.js', [], $version, true );

		// Applications Dashboard.
		wp_register_script( 'cariera-addons-applications-dashboard', CARIERA_ADDONS_URL . '/assets/dist/js/application-dashboard.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-addons-applications-dashboard',
			'cariera_addons_application',
			[
				'i18n_confirm_delete'         => esc_html__( 'Are you sure you want to delete this? There is no undo.', 'cariera-addons' ),
				'i18n_toggle_content'         => esc_html__( 'Details', 'cariera-addons' ),
				'i18n_toggle_notes'           => esc_html__( 'Notes', 'cariera-addons' ),
				'i18n_hide'                   => esc_html__( 'Hide', 'cariera-addons' ),
				'ajax_url'                    => admin_url( 'admin-ajax.php' ),
				'job_application_notes_nonce' => wp_create_nonce( 'job-application-notes' ),
			]
		);

		// Applications Backend.
		wp_register_style( 'cariera-addons-admin-applications', CARIERA_ADDONS_URL . '/assets/dist/css/admin/applications' . $suffix . '.css', [ 'dashicons' ], $version );
		wp_register_script( 'cariera-addons-admin-applications', CARIERA_ADDONS_URL . '/assets/dist/js/admin/applications.js', [ 'jquery', 'select2' ], $version, true );
		wp_localize_script(
			'cariera-addons-admin-applications',
			'cariera_addons_admin_applications',
			[

				'ajax_url' => admin_url( 'admin-ajax.php' ),
			]
		);

		// Applications Form Editor.
		$form_editor_deps = [ 'jquery', 'jquery-ui-sortable' ];
		if ( wp_script_is( 'select2', 'registered' ) ) {
			$form_editor_deps[] = 'select2';
		}

		wp_register_style( 'cariera-addons-admin-applications-form-editor', CARIERA_ADDONS_URL . '/assets/dist/css/admin/applications-form-editor' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-addons-admin-applications-form-editor', CARIERA_ADDONS_URL . '/assets/dist/js/admin/applications-form-editor.js', $form_editor_deps, $version, true );
		wp_localize_script(
			'cariera-addons-admin-applications-form-editor',
			'cariera_addons_admin_applications_form_editor',
			[
				'confirm_delete_i18n' => esc_html__( 'Are you sure you want to delete this row?', 'cariera-addons' ),
				'confirm_reset_i18n'  => esc_html__( 'Are you sure you want to reset your changes? This cannot be undone.', 'cariera-addons' ),
				'is_rtl'              => is_rtl() ? 1 : 0,
			]
		);

		// Resume Backend.
		wp_register_style( 'cariera-addons-admin-resumes', CARIERA_ADDONS_URL . '/assets/dist/css/admin/resumes' . $suffix . '.css', [ 'dashicons' ], $version );
		wp_register_script( 'cariera-addons-admin-resumes', CARIERA_ADDONS_URL . '/assets/dist/js/admin/resumes.js', [ 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable' ], $version, true );

		// Resume AJAX Filters.
		$ajax_url = admin_url( 'admin-ajax.php', 'relative' );
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$ajax_url = add_query_arg( 'lang', ICL_LANGUAGE_CODE, $ajax_url );
		}

		wp_register_script( 'cariera-addons-resume-ajax-filters', CARIERA_ADDONS_URL . '/assets/dist/js/resume-ajax-filters.js', [ 'jquery', 'jquery-deserialize', 'select2' ], $version, true );
		wp_localize_script(
			'cariera-addons-resume-ajax-filters',
			'cariera_addons_resume_ajax_filters',
			[
				'ajax_url' => \WP_Job_Manager_Ajax::get_endpoint(),
				'is_rtl'   => is_rtl() ? 1 : 0,
				'lang'     => apply_filters( 'cariera_addons_lang', null ),
			]
		);

		// Resume Dashboard.
		wp_register_script( 'cariera-addons-resume-dashboard', CARIERA_ADDONS_URL . '/assets/dist/js/resume-dashboard.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-addons-resume-dashboard',
			'cariera_addons_resume_dashboard',
			[
				'i18n_confirm_delete' => esc_html__( 'Are you sure you want to delete this resume?', 'cariera-addons' ),
			]
		);

		// Resume Submission.
		wp_register_script( 'cariera-addons-resume-submission', CARIERA_ADDONS_URL . '/assets/dist/js/resume-submission.js', [ 'jquery', 'jquery-ui-sortable' ], $version, true );
		wp_localize_script(
			'cariera-addons-resume-submission',
			'cariera_addons_resume_submission',
			[
				'i18n_navigate'       => esc_html__( 'If you wish to edit the posted details use the "edit resume" button instead, otherwise changes may be lost.', 'cariera-addons' ),
				'i18n_confirm_remove' => esc_html__( 'Are you sure you want to remove this item?', 'cariera-addons' ),
				'i18n_remove'         => esc_html__( 'remove', 'cariera-addons' ),
			]
		);
	}

	/**
	 * Enqueue Frontend assets.
	 *
	 * @since 0.9.0
	 */
	public function enqueue_assets() {
		$version = function_exists( '\Cariera\is_dev_mode' ) && \Cariera\is_dev_mode() ? wp_rand( 1, 1e4 ) : CARIERA_CORE_VERSION;
	}

	/**
	 * Backend - Enqueue assets.
	 *
	 * @since   0.9.0
	 * @version 1.0.3
	 */
	public function enqueue_admin_assets() {
		global $wp_scripts, $post_type;
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		// Applications Admin Pages.
		$application_post_types = [
			\Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION,
		];

		$allowed_application_screens = [];
		foreach ( $application_post_types as $pt ) {
			$allowed_application_screens[] = "edit-{$pt}";
			$allowed_application_screens[] = $pt;
			$allowed_application_screens[] = "{$pt}_page_job-applications-settings";
		}

		if ( in_array( $screen->id, $allowed_application_screens, true ) ) {
			wp_enqueue_style( 'cariera-addons-admin-applications' );
			wp_enqueue_script( 'cariera-addons-admin-applications' );
		}

		// Application Form Editor Admin Page.
		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION_FORM === $screen->id ) {
			// Layout option has only 1 column.
			add_screen_option(
				'layout_columns',
				[
					'max'     => 1,
					'default' => 1,
				]
			);

			if ( wp_script_is( 'select2', 'registered' ) ) {
				wp_enqueue_style( 'select2' );
			}

			wp_enqueue_style( 'cariera-addons-admin-applications-form-editor' );
			wp_enqueue_script( 'cariera-addons-admin-applications-form-editor' );
		}

		// Resume Admin Pages.
		if ( 'resume_page_cariera_addons_resumes_settings' === $screen->id || \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME === $post_type ) {
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
			$jquery_version = preg_replace( '/-wp/', '', $jquery_version );
			wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'cariera-addons-admin-resumes' );
			wp_enqueue_script( 'cariera-addons-admin-resumes' );
		}
	}
}
