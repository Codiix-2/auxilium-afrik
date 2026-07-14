<?php
namespace Cariera_Addons\Core\Applications\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Settings' ) ) {
	include JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-settings.php';
}

class Settings extends \WP_Job_Manager_Settings {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings_group = 'cariera-addons-applications';
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		// If this setting is enabled, add note on WP Job Manager core's settings page.
		if ( get_option( 'job_application_form_for_url_method', '1' ) ) {
			add_action( 'job_manager_settings', [ $this, 'add_notice_for_url_method' ] );
		}
	}

	/**
	 * Initializes the settings for the plugin.
	 *
	 * @since   0.9.3
	 * @version 0.9.10
	 */
	protected function init_settings() {
		$empty_trash_days = defined( 'EMPTY_TRASH_DAYS ' ) ? EMPTY_TRASH_DAYS : 30;
		if ( empty( $empty_trash_days ) || $empty_trash_days < 0 ) {
			$trash_description = ' ' . esc_html__( 'They will then need to be manually removed from the trash', 'cariera-addons' );
		} else {
			// translators: Placeholder %d is the number of days before items are removed from trash.
			$trash_description = ' ' . sprintf( __( 'They will then be permanently deleted after %d days.', 'cariera-addons' ), $empty_trash_days );
		}

		$prefix = 'job_application_';

		$this->settings = apply_filters(
			'job_manager_applications_settings',
			[
				'application_forms'      => [
					esc_html__( 'Application Forms', 'cariera-addons' ),
					[
						[
							'name'     => $prefix . 'form_for_email_method',
							'std'      => '1',
							'label'    => esc_html__( 'Email Application Method', 'cariera-addons' ),
							'cb_label' => esc_html__( 'Use application form', 'cariera-addons' ),
							'desc'     => esc_html__( 'Show application form for jobs with an email application method. Disable to use the default application functionality, or another form plugin.', 'cariera-addons' ),
							'type'     => 'checkbox',
						],
						[
							'name'     => $prefix . 'form_for_url_method',
							'std'      => '1',
							'label'    => esc_html__( 'Website URL Application Method', 'cariera-addons' ),
							'cb_label' => esc_html__( 'Use application form', 'cariera-addons' ),
							'desc'     => wp_kses_post( __( 'Show application form for jobs with a website URL application method. Disable to use the default application functionality, or another form plugin. <strong>Note: URLs entered for the <em>Application Method</em> will be ignored.</strong>', 'cariera-addons' ) ),
							'type'     => 'checkbox',
						],
						[
							'name'     => $prefix . 'form_require_login',
							'std'      => '0',
							'label'    => esc_html__( 'User Restriction', 'cariera-addons' ),
							'cb_label' => esc_html__( 'Only allow registered users to apply', 'cariera-addons' ),
							'desc'     => wp_kses_post( __( 'If enabled, only logged in users can apply. Non-logged in users will see the contents of the <code>application-form-login.php</code> file instead of a form.', 'cariera-addons' ) ),
							'type'     => 'checkbox',
						],
						[
							'name'     => $prefix . 'prevent_multiple_applications',
							'std'      => '0',
							'label'    => esc_html__( 'Multiple Applications', 'cariera-addons' ),
							'cb_label' => esc_html__( 'Prevent users from applying to the same job multiple times', 'cariera-addons' ),
							'desc'     => esc_html__( 'If enabled, the apply form will be hidden after applying.', 'cariera-addons' ),
							'type'     => 'checkbox',
						],
						[
							'name'       => $prefix . 'show_agreement_application_submission',
							'std'        => '0',
							'label'      => esc_html__( 'Terms and Conditions Checkbox', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Enable required Terms and Conditions checkbox on the form', 'cariera-addons' ),
							'desc'       => sprintf(
								// translators: Placeholder %s is the URL to the page in WP Job Manager's settings to set the pages.
								__( 'Require a Terms and Conditions checkbox to be marked before an application can be submitted. The linked page can be set from the <a href="%s">WP Job Manager\'s settings</a>.', 'cariera-addons' ),
								esc_url( admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings#settings-job_pages' ) )
							),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						'recaptcha' => [
							'name'       => $prefix . 'enable_recaptcha_application_submission',
							'std'        => '0',
							'label'      => esc_html__( 'reCAPTCHA', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Display a reCAPTCHA field on application submission form.', 'cariera-addons' ),
							'desc'       => sprintf(
								// translators: Placeholder %s is the URL to the page in WP Job Manager's settings to make the change.
								__( 'This will help prevent bots from applying for jobs. You must have entered a valid site key and secret key in <a href="%s">WP Job Manager\'s settings</a>.', 'cariera-addons' ),
								esc_url( admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings#settings-recaptcha' ) )
							),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],
				'application_management' => [
					esc_html__( 'Management', 'cariera-addons' ),
					[
						[
							'name'     => $prefix . 'delete_with_job',
							'std'      => '0',
							'label'    => esc_html__( 'Delete with Jobs', 'cariera-addons' ),
							'cb_label' => esc_html__( 'Delete applications when a job is deleted', 'cariera-addons' ),
							'desc'     => esc_html__( 'If enabled, job applications will be deleted when the parent job listing is deleted. Otherwise they will be kept on file and visible in the backend.', 'cariera-addons' ),
							'type'     => 'checkbox',
						],
						[
							'name'        => $prefix . 'purge_days',
							'std'         => '',
							'placeholder' => esc_html__( 'Do not purge data', 'cariera-addons' ),
							'label'       => esc_html__( 'Purge Applications', 'cariera-addons' ),
							'desc'        => esc_html__( 'Purge application data and files after X days. Leave blank to disable.', 'cariera-addons' ),
							'type'        => 'text',
						],
					],
				],
				'application_pages'      => [
					esc_html__( 'Pages', 'cariera-addons' ),
					[
						[
							'name'  => $prefix . 'past_applications_page_id',
							'std'   => '',
							'label' => esc_html__( 'Past Applications Page', 'cariera-addons' ),
							'desc'  => wp_kses_post( __( 'Select the page where you\'ve used the <code>[past_applications]</code> shortcode. This lets the plugin know the location of the page.', 'cariera-addons' ) ),
							'type'  => 'page',
						],
					],
				],
			]
		);
	}

	/**
	 * Add note for application method when URL field will be ignored.
	 *
	 * @since 0.9.3
	 *
	 * @param array $settings Current WPJM core settings.
	 */
	public function add_notice_for_url_method( $settings ) {
		if ( isset( $settings['job_submission'][1] ) ) {
			foreach ( $settings['job_submission'][1] as $index => $setting ) {
				if ( 'job_manager_allowed_application_method' !== $setting['name'] ) {
					continue;
				}

				$warning_text = sprintf(
					// translators: Placeholder is the URL to the settings page in Applications.
					__( 'The <a href="%s">Website URL Application Method</a> setting is enabled in the Applications plugin. URLs entered for the Application Method on the Job Submission form will be ignored unless your theme uses them elsewhere.', 'cariera-addons' ),
					esc_url( admin_url( 'edit.php?post_type=' . \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION . '&page=job-applications-settings#settings-application_forms' ) )
				);

				$settings['job_submission'][1][ $index ]['desc'] .= ' <div class="notice notice-warning inline"><p>' . $warning_text . '</p></div>';
				break;
			}
		}

		return $settings;
	}
}
