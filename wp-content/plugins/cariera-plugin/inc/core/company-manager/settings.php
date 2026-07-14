<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Settings' ) ) {
	include JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-settings.php';
}

class Settings extends \WP_Job_Manager_Settings {

	/**
	 * Constructor function.
	 */
	public function __construct() {
		$this->settings_group = 'cariera_company_manager';

		// Register settings.
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		// Add settings page to menu.
		add_action( 'admin_menu', [ $this, 'add_menu_item' ], 12 );
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @since   1.4.4
	 * @version 1.5.2
	 */
	public function add_menu_item() {
		add_submenu_page( 'edit.php?post_type=company', esc_html__( 'Settings', 'cariera-core' ), esc_html__( 'Settings', 'cariera-core' ), 'manage_options', 'cariera_company_manager_settings', [ $this, 'settings_output' ] );
	}

	/**
	 * Output settings page
	 *
	 * @since   1.5.2
	 * @version 1.8.5
	 */
	public function settings_output() {
		if ( ! cariera_activation_status() ) { ?>
			<div class="activate-theme-alert">
				<p><?php esc_html_e( 'Please activate the theme to be able to edit the core settings.', 'cariera-core' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cariera_theme' ) ); ?>" class="cariera-btn"><?php echo esc_html( 'Activate Cariera' ); ?></a>
			</div>

			<?php
			return;
		}

		$this->output();
	}

	/**
	 * Initializes the configuration for the plugin's setting fields.
	 *
	 * @since   1.4.4
	 * @version 2.0.0
	 */
	protected function init_settings() {
		// Prepare roles option.
		$roles         = get_editable_roles();
		$account_roles = [];

		$singular = cariera_get_company_manager_singular_label();
		$plural   = cariera_get_company_manager_plural_label();

		foreach ( $roles as $key => $role ) {
			if ( 'administrator' === $key ) {
				continue;
			}
			$account_roles[ $key ] = $role['name'];
		}

		$prefix = 'cariera_';

		$this->settings = apply_filters(
			'cariera_company_manager_settings',
			[

				/* COMPANY LISTINGS OPTIONS */
				'company_listings'            => [
					esc_html__( 'Company Listings', 'cariera-core' ),
					[
						[
							'name'        => $prefix . 'companies_per_page',
							'std'         => '10',
							'placeholder' => '',
							'label'       => esc_html__( 'Listings Per Page', 'cariera-core' ),
							'desc'        => esc_html__( 'Number of company listings to display per page.', 'cariera-core' ),
							'attributes'  => [],
						],
						[
							'name'     => $prefix . 'company_category',
							'std'      => '1',
							'label'    => esc_html__( 'Company Category', 'cariera-core' ),
							'cb_label' => esc_html__( 'Enable Company Category', 'cariera-core' ),
							'desc'     => esc_html__( 'Enabling this option will show the Company Categories in sidebar, job posting and in the backend.', 'cariera-core' ),
							'type'     => 'checkbox',
						],
						[
							'name'       => $prefix . 'company_category_multiselect',
							'std'        => '0',
							'label'      => esc_html__( 'Multi-select Categories', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Enable category multiselect by default', 'cariera-core' ),
							'desc'       => esc_html__( 'If enabled, the category select box will default to a multiselect on the [companies] shortcode.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'     => $prefix . 'company_team_size',
							'std'      => '1',
							'label'    => esc_html__( 'Team Size', 'cariera-core' ),
							'cb_label' => esc_html__( 'Enable Team Size', 'cariera-core' ),
							'desc'     => esc_html__( 'Enabling this option will show the Team Size in sidebar, job posting and in the backend.', 'cariera-core' ),
							'type'     => 'checkbox',
						],
					],
				],

				/* SINGLE COMPANY PAGE OPTIONS */
				'company_page'                => [
					esc_html__( 'Company Single Page', 'cariera-core' ),
					[
						[
							'name'    => $prefix . 'single_company_layout',
							'std'     => 'v1',
							'label'   => esc_html__( 'Single Company Layout', 'cariera-core' ),
							'desc'    => esc_html__( 'Select the default layout version for your single company page.', 'cariera-core' ),
							'type'    => 'select',
							'options' => [
								'v1' => esc_html__( 'Version 1', 'cariera-core' ),
								'v2' => esc_html__( 'Version 2', 'cariera-core' ),
								'v3' => esc_html__( 'Version 3', 'cariera-core' ),
							],
						],
						[
							'name'    => $prefix . 'single_company_contact_form',
							'std'     => '',
							'label'   => esc_html__( 'Single Company Contact Form', 'cariera-core' ),
							'desc'    => esc_html__( 'Select the form for single company contact form. This lets the plugin know the contact form of single company. The contact form will show only if the private messages are disabled for companies.', 'cariera-core' ),
							'type'    => 'select',
							'options' => function_exists( 'cariera_get_forms' ) ? cariera_get_forms() : [ 0 => esc_html__( 'Please select a form', 'cariera-core' ) ],
						],
						[
							'name'       => $prefix . 'single_company_active_jobs',
							'std'        => '1',
							'label'      => esc_html__( 'Active Jobs', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Display active Jobs', 'cariera-core' ),
							// translators: %s placeholder is the singular lable of company cpt.
							'desc'       => sprintf( esc_html__( 'If the %s has active Jobs, a list will be output at the bottom of the page.', 'cariera-core' ), $singular ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'     => $prefix . 'company_related_companies',
							'std'      => '1',
							'label'    => esc_html__( 'Related Companies', 'cariera-core' ),
							'cb_label' => esc_html__( 'Enable related listings', 'cariera-core' ),
							'desc'     => esc_html__( 'Show related listings in single listing page.', 'cariera-core' ),
							'type'     => 'checkbox',
						],
						[
							'name'       => $prefix . 'single_company_featured_companies',
							'std'        => '1',
							// translators: %s placeholder is the plural lable of company cpt.
							'label'      => sprintf( esc_html__( 'Featured %s', 'cariera-core' ), $plural ),
							// translators: %s placeholder is the plural lable of company cpt.
							'cb_label'   => sprintf( esc_html__( 'Enable featured %s', 'cariera-core' ), $plural ),
							// translators: %1$s placeholder is the plural lable of company cpt and %2$s the singular lable of company cpt.
							'desc'       => sprintf( esc_html__( 'Show featured %1$s in single %2$s page.', 'cariera-core' ), $plural, $singular ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* COMPANY SUBMISSION OPTIONS */
				'company_submission'          => [
					esc_html__( 'Company Submission', 'cariera-core' ),
					[
						[
							'name'       => $prefix . 'company_user_requires_account',
							'std'        => '1',
							'label'      => esc_html__( 'Account Required', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Submitting listings requires an account', 'cariera-core' ),
							'desc'       => esc_html__( 'Limits company submissions to registered, logged-in users.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'enable_company_registration',
							'std'        => '1',
							'label'      => esc_html__( 'Account Creation', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Allow account creation', 'cariera-core' ),
							'desc'       => esc_html__( 'Includes account creation on the company submission form, to allow non-registered users to create an account and submit a company simultaneously.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'name'       => $prefix . 'company_generate_username_from_email',
							'std'        => '1',
							'label'      => esc_html__( 'Account Username', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Generate usernames from email addresses', 'cariera-core' ),
							'desc'       => esc_html__( 'Automatically generates usernames for new accounts from the registrant\'s email address. If this is not enabled, a "username" field will display instead.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [
								'data-toggleon' => 'cariera_enable_company_registration',
							],
						],
						[
							'name'       => $prefix . 'company_use_standard_password_setup_email',
							'std'        => '1',
							'label'      => esc_html__( 'Account Password', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Email new users a link to set a password', 'cariera-core' ),
							'desc'       => esc_html__( 'Sends an email to the user with their username and a link to set their password. If this is not enabled, a "password" field will display instead, and their email address won\'t be verified.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [
								'data-toggleon' => 'cariera_enable_company_registration',
							],
						],
						[
							'name'    => $prefix . 'company_registration_role',
							'std'     => 'employer',
							'label'   => esc_html__( 'Account Role', 'cariera-core' ),
							'desc'    => esc_html__( 'If you enable registration on your submission form, choose a role for the new user.', 'cariera-core' ),
							'type'    => 'select',
							'options' => $account_roles,
						],
						[
							'name'       => $prefix . 'company_submission_requires_approval',
							'std'        => '1',
							'label'      => esc_html__( 'Moderate New Listings', 'cariera-core' ),
							'cb_label'   => esc_html__( 'New submissions require admin approval', 'cariera-core' ),
							'desc'       => esc_html__( 'Sets all new submissions to "pending." They will not appear on your site until an admin approves them.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'company_user_can_edit_pending_submissions',
							'std'        => '0',
							'label'      => esc_html__( 'Allow Pending Edits', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Allow editing of pending listings', 'cariera-core' ),
							'desc'       => esc_html__( 'Users can continue to edit pending listings until they are approved by an admin.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						[
							'name'       => $prefix . 'company_user_edit_published_submissions',
							'std'        => 'yes',
							'label'      => esc_html__( 'Allow Published Edits', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Allow editing of published listings', 'cariera-core' ),
							'desc'       => esc_html__( 'Choose whether published company listings can be edited and if edits require admin approval. When moderation is required, the original company listings will be unpublished while edits await admin approval.', 'cariera-core' ),
							'type'       => 'radio',
							'options'    => [
								'no'            => esc_html__( 'Users cannot edit', 'cariera-core' ),
								'yes'           => esc_html__( 'Users can edit without admin approval', 'cariera-core' ),
								'yes_moderated' => esc_html__( 'Users can edit, but edits require admin approval', 'cariera-core' ),
							],
							'attributes' => [],
						],
						[
							'name'        => $prefix . 'company_submission_duration',
							'std'         => '',
							'label'       => esc_html__( 'Listing Duration', 'cariera-core' ),
							'desc'        => wp_kses_post( __( 'How many <strong>days</strong> listings are live before expiring. Can be left blank to never expire. Expired listings must be relisted to become visible.', 'cariera-core' ) ),
							'attributes'  => [],
							'placeholder' => esc_html__( 'Never expire', 'cariera-core' ),
						],
						[
							'name'        => $prefix . 'company_submission_limit',
							'std'         => '',
							'label'       => esc_html__( 'Listing Limit', 'cariera-core' ),
							// translators: %s placeholder is the plural lable of company cpt.
							'desc'        => sprintf( esc_html__( 'Limit users submission by adding a max number. Can be left blank to allow unlimited %s per account.', 'cariera-core' ), $plural ),
							'attributes'  => [],
							'placeholder' => esc_html__( 'No limit', 'cariera-core' ),
						],
						[
							'name'       => $prefix . 'user_specific_company',
							'std'        => '1',
							'label'      => esc_html__( 'User Specific Companies', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Enable User Specific Companies', 'cariera-core' ),
							'desc'       => esc_html__( 'If enabled the user will be able to see only the companies created by the user under the "existing company". If disabled all companies will be visible, even for non logged in users.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'name'       => $prefix . 'hr_company_users',
							'std'        => '0',
							'label'      => esc_html__( 'HR Company Users', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Enable HR Company Users', 'cariera-core' ),
							'desc'       => esc_html__( 'When enabled, admins can assign HR users to a company, allowing them to post jobs on its behalf.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [
								'data-toggleon' => 'cariera_user_specific_company',
							],
						],
						[
							'name'       => $prefix . 'show_agreement_company_submission',
							'std'        => '0',
							'label'      => esc_html__( 'Terms and Conditions Checkbox', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Enable required Terms and Conditions checkbox on the form', 'cariera-core' ),
							'desc'       => sprintf(
								// translators: Placeholder %s is the URL to the page in WP Job Manager's settings to set the pages.
								__( 'Require a Terms and Conditions checkbox to be marked before a company can be submitted. The linked page can be set from the <a href="%s">WP Job Manager\'s settings</a>.', 'cariera-core' ),
								esc_url( admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings#settings-job_pages' ) )
							),
							'type'       => 'checkbox',
							'attributes' => [],
						],
						'recaptcha' => [
							'name'       => $prefix . 'enable_recaptcha_company_submission',
							'std'        => '0',
							'label'      => esc_html__( 'reCAPTCHA', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Display a reCAPTCHA field on company submission form.', 'cariera-core' ),
							'desc'       => sprintf(
								// translators: Placeholder %s is the URL to the page in WP Job Manager's settings to make the change.
								__( 'This will help prevent bots from submitting companies. You must have entered a valid site key and secret key in <a href="%s">WP Job Manager\'s settings</a>.', 'cariera-core' ),
								esc_url( admin_url( 'edit.php?post_type=job_listing&page=job-manager-settings#settings-recaptcha' ) )
							),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* WPJM INTEGRATION OPTIONS */
				'company_integration'         => [
					esc_html__( 'WPJM Integration', 'cariera-core' ),
					[
						[
							'name'       => $prefix . 'company_manager_integration',
							'std'        => '1',
							'label'      => esc_html__( 'Cariera Company Manager', 'cariera-core' ),
							'cb_label'   => esc_html__( 'WPJM Integration', 'cariera-core' ),
							// translators: %s placeholder is the singular lable of company cpt.
							'desc'       => sprintf( esc_html__( 'Replace all the default %s fields from WP Job Manager with the main Cariera Company Manager fields.', 'cariera-core' ), $singular ),
							'type'       => 'checkbox',
							'attributes' => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'name'       => $prefix . 'job_submit_company_submission',
							'std'        => '1',
							// translators: %s placeholder is the singular lable of company cpt.
							'label'      => sprintf( esc_html__( '%s Submission', 'cariera-core' ), $singular ),
							// translators: %s placeholder is the singular lable of company cpt.
							'cb_label'   => sprintf( esc_html__( 'Require the user to select or add a new %s.', 'cariera-core' ), $singular ),
							// translators: %s placeholder is the singular lable of company cpt.
							'desc'       => sprintf( esc_html__( 'When enabled, users will be able to submit a new %s or select an existing company in the job submission page.', 'cariera-core' ), $singular, $singular ),
							'type'       => 'checkbox',
							'attributes' => [
								'data-toggleon' => 'cariera_company_manager_integration',
							],
						],
						[
							'name'       => $prefix . 'add_new_company',
							'std'        => '1',
							// translators: %s placeholder is the singular lable of company cpt.
							'label'      => sprintf( esc_html__( 'Add New %s', 'cariera-core' ), $singular ),
							// translators: %s placeholder is the singular lable of company cpt.
							'cb_label'   => sprintf( esc_html__( 'Enable new %s submission', 'cariera-core' ), $singular ),
							// translators: %s placeholder is the singular lable of company cpt.
							'desc'       => sprintf( esc_html__( 'If disabled you will not be able to post a new %s on job submission.', 'cariera-core' ), $singular ),
							'type'       => 'checkbox',
							'attributes' => [
								'data-toggleon' => 'cariera_company_manager_integration',
							],
						],
						[
							'name'       => $prefix . 'job_submit_company_required',
							'std'        => '1',
							// translators: %s placeholder is the singular lable of company cpt.
							'label'      => sprintf( esc_html__( '%s Required', 'cariera-core' ), $singular ),
							// translators: %s placeholder is the singular lable of company cpt.
							'cb_label'   => sprintf( esc_html__( 'Require the user to select an existing %s, or add a new one when submitting a new job.', 'cariera-core' ), $singular ),
							// translators: %1$s & %2$s are placeholders for the singular lable of company cpt.
							'desc'       => sprintf( esc_html__( 'When enabled, this will set the dropdown to select existing %1$s from (or add new one) as a required field, so the user will have to select an existing %2$s or add a new one.', 'cariera-core' ), $singular, $singular ),
							'type'       => 'checkbox',
							'attributes' => [
								'data-toggleon' => 'cariera_company_manager_integration',
							],
						],
					],
				],

				/* PAGES OPTIONS */
				'company_pages'               => [
					esc_html__( 'Pages', 'cariera-core' ),
					[
						[
							'name'  => $prefix . 'submit_company_page',
							'std'   => '',
							'label' => esc_html__( 'Submit Company Page', 'cariera-core' ),
							'desc'  => esc_html__( 'Select the page where you have placed the [submit_company] shortcode. This lets the plugin know the location of the company submission page.', 'cariera-core' ),
							'type'  => 'page',
						],
						[
							'name'  => $prefix . 'company_dashboard_page',
							'std'   => '',
							'label' => esc_html__( 'Company Dashboard Page', 'cariera-core' ),
							'desc'  => esc_html__( 'Select the page where you have placed the [company_dashboard] shortcode. This lets the plugin know the location of the company dashboard page.', 'cariera-core' ),
							'type'  => 'page',
						],
						[
							'name'  => $prefix . 'companies_page',
							'std'   => '',
							'label' => esc_html__( 'Company Listings Page', 'cariera-core' ),
							'desc'  => esc_html__( 'Select the page where you have placed the [companies] shortcode or companies element via Elementor. This lets the plugin know the location of the company listings page.', 'cariera-core' ),
							'type'  => 'page',
						],
					],
				],

				/* VISIBILITY OPTIONS */
				'company_visibility'          => [
					// translators: %s placeholder is the singular lable of company cpt.
					sprintf( esc_html__( '%s Visibility', 'cariera-core' ), $singular ),
					[
						[
							'name'  => $prefix . 'company_manager_view_name_capability',
							'std'   => [],
							'label' => esc_html__( 'View Company name Capability', 'cariera-core' ),
							'type'  => 'capabilities',
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'  => sprintf( __( 'Enter which <a href="%s">roles or capabilities</a> allow visitors to view company names. If no value is selected, everyone (including logged out guests) will be able view company\'s full name.', 'cariera-core' ), 'http://codex.wordpress.org/Roles_and_Capabilities' ),
						],
						[
							'name'              => $prefix . 'company_manager_browse_company_capability',
							'std'               => [],
							'label'             => esc_html__( 'Browse Capability', 'cariera-core' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to browse. If no value is selected, everyone (including logged out guests) will be able to browse companies.', 'cariera-core' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ),
						],
						[
							'name'              => $prefix . 'company_manager_view_company_capability',
							'std'               => [],
							'label'             => esc_html__( 'View Capability', 'cariera-core' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view a single company. If no value is selected, everyone (including logged out guests) will be able to view companies.', 'cariera-core' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ),
						],
						[
							'name'              => $prefix . 'company_manager_submit_company_capability',
							'std'               => [],
							'label'             => esc_html__( 'Submit Capability', 'cariera-core' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter which <a href="%s">roles or capabilities</a> allow visitors to submit a company. If no value is selected, everyone (including logged out guests) will be able to submit companies.', 'cariera-core' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ),
						],
						[
							'name'              => $prefix . 'company_manager_contact_company_capability',
							'std'               => [],
							'label'             => esc_html__( 'Contact Capability', 'cariera-core' ),
							'type'              => 'capabilities',
							'sanitize_callback' => [ $this, 'sanitize_capabilities' ],
							// translators: Placeholder %s is the url to the WordPress core documentation for capabilities and roles.
							'desc'              => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view a contact details on a single company. If no value is selected, contact details will be publicly available.', 'cariera-core' ), 'https://wordpress.org/support/article/roles-and-capabilities/' ),
						],
						[
							'name'       => $prefix . 'company_manager_discourage_company_search_indexing',
							'std'        => '0',
							'label'      => esc_html__( 'Search Engine Visibility', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Discourage search engines from indexing company listings', 'cariera-core' ),
							'desc'       => esc_html__( 'Search engines choose whether to honor this request.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* EMAIL NOTIFICATION OPTIONS */
				'company_email_notifications' => [
					esc_html__( 'Email Notifications', 'cariera-core' ),
					[
						[
							'name'       => $prefix . 'company_submission_notification',
							'std'        => '1',
							'label'      => esc_html__( 'New Submission', 'cariera-core' ),
							'cb_label'   => esc_html__( 'Admin Notice on New Listing', 'cariera-core' ),
							'desc'       => esc_html__( 'Send a notice to the site administrator when a new company listing is submitted on the frontend.', 'cariera-core' ),
							'type'       => 'checkbox',
							'attributes' => [],
						],
					],
				],

				/* OTHER OPTIONS */
				'company_other'               => [
					esc_html__( 'Other', 'cariera-core' ),
					[
						[
							'name'        => $prefix . 'company_manager_cpt_singular_label',
							'std'         => esc_html__( 'Company', 'cariera-core' ),
							'placeholder' => esc_html__( 'Company', 'cariera-core' ),
							'label'       => esc_html__( 'Singular Label', 'cariera-core' ),
							'desc'        => esc_html__( 'You can change the singular label and use a custom label instead of "Company".', 'cariera-core' ),
							'attributes'  => [],
						],
						[
							'name'        => $prefix . 'company_manager_cpt_plural_label',
							'std'         => esc_html__( 'Companies', 'cariera-core' ),
							'placeholder' => esc_html__( 'Companies', 'cariera-core' ),
							'label'       => esc_html__( 'Plural Label', 'cariera-core' ),
							'desc'        => esc_html__( 'You can change the plural label and use a custom label instead of "Companies".', 'cariera-core' ),
							'attributes'  => [],
						],
					],
				],

			]
		);

		// Remove Contact Form field if Contact Form 7 is not active.
		if ( ! class_exists( 'WPCF7' ) ) {
			foreach ( $this->settings['company_page'][1] as $i => $field ) {
				if ( isset( $field['name'] ) && $field['name'] === $prefix . 'single_company_contact_form' ) {
					unset( $this->settings['company_page'][1][ $i ] );
				}
			}
			// Reindex the array to avoid gaps.
			$this->settings['company_page'][1] = array_values( $this->settings['company_page'][1] );
		}
	}
}
