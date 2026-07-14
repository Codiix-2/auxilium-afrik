<?php

namespace Cariera_Addons\Core\Resumes\Admin;

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
		$this->settings_group = 'cariera-addons-resumes';
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Register settings for the resumes.
	 *
	 * @since   0.9.5
	 * @version 1.0.6
	 */
	protected function init_settings() {
		// Prepare roles option.
		$roles         = get_editable_roles();
		$account_roles = [];

		foreach ( $roles as $key => $role ) {
			if ( 'administrator' === $key ) {
				continue;
			}
			$account_roles[ $key ] = $role['name'];
		}

		$empty_trash_days = defined( 'EMPTY_TRASH_DAYS ' ) ? EMPTY_TRASH_DAYS : 30;
		if ( empty( $empty_trash_days ) || $empty_trash_days < 0 ) {
			$trash_description = esc_html__( 'They will then need to be manually removed from the trash', 'cariera-addons' );
		} else {
			// translators: Placeholder %d is the number of days before items are removed from trash.
			$trash_description = sprintf( __( 'They will then be permanently deleted after %d days.', 'cariera-addons' ), $empty_trash_days );
		}

		$prefix        = 'resume_manager_';
		$addons_prefix = 'cariera_addons_';

		$this->settings = apply_filters(
			'resume_manager_settings',
			[
				'resume_listings'    => [
					esc_html__( 'Resume Listings', 'cariera-addons' ),
					[
						[
							'name'        => $prefix . 'per_page',
							'std'         => '10',
							'placeholder' => '',
							'label'       => esc_html__( 'Resumes Per Page', 'cariera-addons' ),
							'desc'        => esc_html__( 'How many resumes should be shown per page by default?', 'cariera-addons' ),
							'attributes'  => [],
						],
						[
							'name'       => $prefix . 'enable_categories',
							'std'        => '0',
							'label'      => esc_html__( 'Categories', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Enable resume categories', 'cariera-addons' ),
							'desc'       => esc_html__( 'Choose whether to enable resume categories. Categories must be setup by an admin for users to choose during resume submission.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'enable_default_category_multiselect',
							'std'        => '0',
							'label'      => esc_html__( 'Multi-select Categories', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Enable category multiselect by default', 'cariera-addons' ),
							'desc'       => esc_html__( 'If enabled, the category select box will default to a multiselect on the [resumes] shortcode.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'    => $prefix . 'category_filter_type',
							'std'     => 'any',
							'label'   => esc_html__( 'Category Filter Type', 'cariera-addons' ),
							'desc'    => esc_html__( 'Choose how to filter resumes when Multi-select Categories option is enabled.', 'cariera-addons' ),
							'type'    => 'select',
							'options' => [
								'any' => esc_html__( 'Resumes will be shown if within ANY selected category', 'cariera-addons' ),
								'all' => esc_html__( 'Resumes will be shown if within ALL selected categories', 'cariera-addons' ),
							],
						],
						[
							'name'       => $prefix . 'enable_skills',
							'std'        => '0',
							'label'      => esc_html__( 'Skills', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Enable candidate skills', 'cariera-addons' ),
							'desc'       => esc_html__( 'Choose whether to enable the candidate skills field. Skills can be added by users during resume submission.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'        => $prefix . 'max_skills',
							'std'         => '',
							'label'       => esc_html__( 'Maximum Skills', 'cariera-addons' ),
							'placeholder' => esc_html__( 'Unlimited', 'cariera-addons' ),
							'desc'        => esc_html__( 'Enter the number of skills per resume submission you wish to allow, or leave blank for unlimited skills.', 'cariera-addons' ),
							'type'        => 'input',
						],
						[
							'name'       => $prefix . 'enable_resume_upload',
							'std'        => '0',
							'label'      => esc_html__( 'Resume Upload', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Enable resume upload', 'cariera-addons' ),
							'desc'       => esc_html__( 'Choose whether to allow candidates to upload a resume file.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'delete_files_on_resume_deletion',
							'std'        => '0',
							'label'      => esc_html__( 'Delete uploaded files', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Delete uploaded files when a resume is deleted', 'cariera-addons' ),
							'desc'       => esc_html__( 'Choose whether to deleted uploaded files when a resume is deleted and removed from the trash.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],
				'single_resume'      => [ esc_html__( 'Single Resume', 'cariera-addons' ), [] ],
				'resume_submission'  => [
					esc_html__( 'Resume Submission', 'cariera-addons' ),
					[
						[
							'name'       => $prefix . 'user_requires_account',
							'std'        => '1',
							'label'      => esc_html__( 'Account Required', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Submitting listings requires an account', 'cariera-addons' ),
							'desc'       => esc_html__( 'If disabled, non-logged in users will be able to submit listings without creating an account. Please note that this will prevent non-registered users from being able to edit their listings at a later date.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'enable_registration',
							'std'        => '1',
							'label'      => esc_html__( 'Account Creation', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Allow account creation', 'cariera-addons' ),
							'desc'       => esc_html__( 'If enabled, non-logged in users will be able to create an account by entering their email address on the resume submission form.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'generate_username_from_email',
							'std'        => '1',
							'label'      => esc_html__( 'Account Username', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Automatically Generate Username from Email Address', 'cariera-addons' ),
							'desc'       => esc_html__( 'If enabled, a username will be generated from the first part of the user email address. Otherwise, a username field will be shown.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'use_standard_password_setup_email',
							'std'        => '1',
							'label'      => esc_html__( 'Account Password', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Use WordPress\' default behavior and email new users link to set a password', 'cariera-addons' ),
							'desc'       => esc_html__( 'If enabled, an email will be sent to the user with their username and a link to set their password. Otherwise, a password field will be shown and their email address won\'t be verified.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'    => $prefix . 'registration_role',
							'std'     => 'candidate',
							'label'   => esc_html__( 'Account Role', 'cariera-addons' ),
							'desc'    => esc_html__( 'If you enable registration on your submission form, choose a role for the new user.', 'cariera-addons' ),
							'type'    => 'select',
							'options' => $account_roles,
						],
						[
							'name'       => $prefix . 'submission_requires_approval',
							'std'        => '1',
							'label'      => esc_html__( 'Approval Required', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'New submissions require admin approval', 'cariera-addons' ),
							'desc'       => esc_html__( 'If enabled, new submissions will be inactive, pending admin approval.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'user_can_edit_pending_submissions',
							'std'        => '0',
							'label'      => esc_html__( 'Allow Pending Edits', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Allow editing of pending resumes', 'cariera-addons' ),
							'desc'       => esc_html__( 'Users can continue to edit pending resumes until they are approved by an admin.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'user_edit_published_submissions',
							'std'        => 'yes',
							'label'      => esc_html__( 'Allow Published Edits', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Allow editing of published resumes', 'cariera-addons' ),
							'desc'       => esc_html__( 'Choose whether published resumes can be edited and if edits require admin approval. When moderation is required, the original resume will be unpublished while edits await admin approval.', 'cariera-addons' ),
							'type'       => 'radio',
							'options'    => [
								'no'            => esc_html__( 'Users cannot edit', 'cariera-addons' ),
								'yes'           => esc_html__( 'Users can edit without admin approval', 'cariera-addons' ),
								'yes_moderated' => esc_html__( 'Users can edit, but edits require admin approval', 'cariera-addons' ),
							],
							'attributes' => [],
						],
						[
							'name'        => $prefix . 'submission_duration',
							'std'         => '',
							'label'       => esc_html__( 'Listing Duration', 'cariera-addons' ),
							'desc'        => wp_kses_post( __( 'How many <strong>days</strong> listings are live before expiring. Can be left blank to never expire. Expired listings must be relisted to become visible.', 'cariera-addons' ) ),
							'attributes'  => [],
							'placeholder' => esc_html__( 'Never expire', 'cariera-addons' ),
						],
						[
							'name'        => $prefix . 'autohide',
							'std'         => '',
							'label'       => esc_html__( 'Auto-hide Resumes', 'cariera-addons' ),
							'desc'        => wp_kses_post( __( 'How many <strong>days</strong> un-modified resumes should be published before being hidden. Can be left blank to never hide resumes automatically. Candidates can re-publish hidden resumes form their dashboard.', 'cariera-addons' ) ),
							'attributes'  => [],
							'placeholder' => esc_html__( 'Never auto-hide', 'cariera-addons' ),
						],
						[
							'name'        => $prefix . 'submission_limit',
							'std'         => '',
							'label'       => esc_html__( 'Listing Limit', 'cariera-addons' ),
							'desc'        => esc_html__( 'How many listings are users allowed to post. Can be left blank to allow unlimited listings per account.', 'cariera-addons' ),
							'attributes'  => [],
							'placeholder' => esc_html__( 'No limit', 'cariera-addons' ),
						],
						[
							'name'       => $prefix . 'show_agreement_resume_submission',
							'std'        => '0',
							'label'      => esc_html__( 'Terms and Conditions Checkbox', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Enable required Terms and Conditions checkbox on the form', 'cariera-addons' ),
							'desc'       => sprintf(
								// translators: Placeholder %s is the URL to the page in WP Job Manager's settings to set the pages.
								__( 'Require a Terms and Conditions checkbox to be marked before a resume can be submitted. The linked page can be set from the <a href="%s">WP Job Manager\'s settings</a>.', 'cariera-addons' ),
								esc_url( admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings#settings-job_pages' ) )
							),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						'recaptcha' => [
							'name'       => $prefix . 'enable_recaptcha_resume_submission',
							'std'        => '0',
							'label'      => esc_html__( 'reCAPTCHA', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Display a reCAPTCHA field on resume submission form.', 'cariera-addons' ),
							'desc'       => sprintf(
								// translators: Placeholder %s is the URL to the page in WP Job Manager's settings to make the change.
								__( 'This will help prevent bots from submitting resumes. You must have entered a valid site key and secret key in <a href="%s">WP Job Manager\'s settings</a>.', 'cariera-addons' ),
								esc_url( admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings#settings-recaptcha' ) )
							),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],
				'resume_application' => [
					esc_html__( 'Apply with Resume', 'cariera-addons' ),
					[
						[
							'name'     => $prefix . 'enable_application',
							'std'      => '1',
							'label'    => esc_html__( 'Email Based Applications', 'cariera-addons' ),
							'cb_label' => esc_html__( 'Allow candidates to apply to jobs which use the email application method using their online resume', 'cariera-addons' ),
							'desc'     => sprintf(
								// translators: Placeholder is link to settings tab which includes email Notifications.
								__( 'The employer will be mailed their message and a private link to the resume. Manage notification from the <a href="%s" class="nav-internal">Email Notifications</a> settings tab.', 'cariera-addons' ),
								'#settings-email_notifications'
							),
							'type'     => 'checkbox',
						],
						[
							'name'     => $prefix . 'enable_application_for_url_method',
							'std'      => '1',
							'label'    => esc_html__( 'Website Based Applications', 'cariera-addons' ),
							'cb_label' => esc_html__( 'Allow candidates to apply to jobs which use the the website URL application method using their online resume', 'cariera-addons' ),
							'desc'     => esc_html__( 'The application will be stored in the database.', 'cariera-addons' ),
							'type'     => 'checkbox',
						],
						[
							'name'       => $prefix . 'force_resume',
							'std'        => '0',
							'label'      => esc_html__( 'Force Resume Creation', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Force candidates to create an online resume before applying to a job', 'cariera-addons' ),
							'desc'       => esc_html__( 'Candidates without a resume on file will be taken through the resume submission process. Other details, such as the application email address or application forms, will be hidden.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'force_application',
							'std'        => '0',
							'label'      => esc_html__( 'Force Apply with Resume', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Force candidates to apply through Resume Manager', 'cariera-addons' ),
							'desc'       => esc_html__( 'If the apply forms are enabled above, they must be used to apply. All other application methods will be hidden.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],
				'resume_pages'       => [
					esc_html__( 'Pages', 'cariera-addons' ),
					[
						[
							'name'  => $prefix . 'submit_resume_form_page_id',
							'std'   => '',
							'label' => esc_html__( 'Submit Resume Page', 'cariera-addons' ),
							'desc'  => esc_html__( 'Select the page where you have placed the [submit_resume_form] shortcode. This lets the plugin know where the form is located.', 'cariera-addons' ),
							'type'  => 'page',
						],
						[
							'name'  => $prefix . 'candidate_dashboard_page_id',
							'std'   => '',
							'label' => esc_html__( 'Candidate Dashboard Page', 'cariera-addons' ),
							'desc'  => esc_html__( 'Select the page where you have placed the [candidate_dashboard] shortcode. This lets the plugin know where the dashboard is located.', 'cariera-addons' ),
							'type'  => 'page',
						],
						[
							'name'  => $prefix . 'resumes_page_id',
							'std'   => '',
							'label' => esc_html__( 'Resume Listings Page', 'cariera-addons' ),
							'desc'  => sprintf(
								// translators: Placeholder is link to settings tab which includes resume visibility settings.
								__( 'Select the page where you have placed the [resumes] shortcode. This lets the plugin know where the resume listings page is located. Manage access to this page and resumes from the <a href="%s" class="nav-internal">Resume Visibility</a> tab.', 'cariera-addons' ),
								'#settings-resume_visibility'
							),
							'type'  => 'page',
						],
					],
				],
				'resume_visibility'  => [
					esc_html__( 'Resume Visibility', 'cariera-addons' ),
					[
						[
							'name'              => $addons_prefix . 'view_resume_name_capability',
							'std'               => [],
							'label'             => esc_html__( 'View Resume name Capability', 'cariera-addons' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter which <a href="%s">roles or capabilities</a> allow visitors to view resumes names. If no value is selected, everyone (including logged out guests) will be able view candidates full name.', 'cariera-addons' ), 'http://codex.wordpress.org/Roles_and_Capabilities' ),
						],
						[
							'name'              => $addons_prefix . 'browse_resume_capability',
							'std'               => [],
							'label'             => esc_html__( 'Browse Resume Capability', 'cariera-addons' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter which <a href="%s">roles or capabilities</a> allow visitors to browse resumes. If no value is selected, everyone (including logged out guests) will be able to browse resumes..', 'cariera-addons' ), 'http://codex.wordpress.org/Roles_and_Capabilities' ),
						],
						[
							'name'              => $addons_prefix . 'view_resume_capability',
							'std'               => [],
							'label'             => esc_html__( 'View Resume Capability', 'cariera-addons' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter which <a href="%s">roles or capabilities</a> allow visitors to view a single resume. If no value is selected, everyone (including logged out guests) will be able to view resumes.', 'cariera-addons' ), 'http://codex.wordpress.org/Roles_and_Capabilities' ),
						],
						[
							'name'              => $addons_prefix . 'submit_resume_capability',
							'std'               => [],
							'label'             => esc_html__( 'Submit Capability', 'cariera-addons' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter which <a href="%s">roles or capabilities</a> allow visitors to submit a resume. If no value is selected, everyone (including logged out guests) will be able to submit resumes.', 'cariera-addons' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ),
						],
						[
							'name'              => $addons_prefix . 'contact_resume_capability',
							'std'               => [],
							'label'             => esc_html__( 'Contact Details Capability', 'cariera-addons' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter which <a href="%s">roles or capabilities</a> allow visitors to view contact details on a resume. If no value is selected, contact details will be publicly available.', 'cariera-addons' ), 'http://codex.wordpress.org/Roles_and_Capabilities' ),
						],
						[
							'name'       => $prefix . 'discourage_resume_search_indexing',
							'std'        => '1',
							'label'      => esc_html__( 'Search Engine Visibility', 'cariera-addons' ),
							'cb_label'   => esc_html__( 'Discourage search engines from indexing resume listings', 'cariera-addons' ),
							'desc'       => esc_html__( 'Search engines choose whether to honor this request.', 'cariera-addons' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],
				'resume_other'       => [
					esc_html__( 'Other', 'cariera-addons' ),
					[
						[
							'name'        => $addons_prefix . 'resume_cpt_singular_label',
							'std'         => '',
							'placeholder' => esc_html__( 'Resume', 'cariera-addons' ),
							'label'       => esc_html__( 'Singular Label', 'cariera-addons' ),
							'desc'        => esc_html__( 'You can change the singular label and use a custom label instead of "Resume".', 'cariera-addons' ),
							'attributes'  => [],
						],
						[
							'name'        => $addons_prefix . 'resume_cpt_plural_label',
							'std'         => '',
							'placeholder' => esc_html__( 'Resumes', 'cariera-addons' ),
							'label'       => esc_html__( 'Plural Label', 'cariera-addons' ),
							'desc'        => esc_html__( 'You can change the plural label and use a custom label instead of "Resumes".', 'cariera-addons' ),
							'attributes'  => [],
						],
					],
				],
			]
		);

		if ( ! defined( 'JOB_MANAGER_VERSION' ) || version_compare( '1.30.0', JOB_MANAGER_VERSION, '>' ) ) {
			unset( $this->settings['resume_submission'][1]['recaptcha'] );
		}

		if ( ! class_exists( 'WP_Job_Manager_Applications' ) || ! class_exists( 'Cariera_Addons\Core\Applications\Applications' ) ) {
			unset( $this->settings['resume_application'][1][1] );
		}
	}
}
