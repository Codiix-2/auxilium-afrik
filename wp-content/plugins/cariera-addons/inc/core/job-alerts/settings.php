<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	use \Cariera_Addons\Src\Traits\Singleton;

	public const OPTION_ACCOUNT_REQUIRED        = 'job_manager_alerts_account_required';
	public const OPTION_BRAND_COLOR             = 'job_manager_alerts_brand_color';
	public const DEFAULT_BRAND_COLOR            = '#0453EB';
	public const OPTION_FORM_FIELDS             = 'job_manager_alerts_form_fields';
	public const OPTION_EMAIL_TEMPLATE          = 'job_manager_alerts_email_template';
	public const OPTION_JOB_DETAILS_VISIBLE     = 'job_manager_job_details_visible';
	public const DEFAULT_JOB_DETAILS_VISIBLE    = [ 'fields' => [ 'company_name', 'company_logo', 'location' ] ];
	public const OPTION_JOB_ALERTS_AUTO_DISABLE = 'job_manager_alerts_auto_disable';
	public const OPTION_JOB_MATCHES_ONLY        = 'job_manager_alerts_matches_only';
	public const OPTION_JOB_ALERTS_PAGE_ID      = 'job_manager_alerts_page_id';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'job_manager_settings', [ $this, 'settings' ] );
	}

	/**
	 * Add Settings
	 *
	 * @since 0.9.2
	 *
	 * @param array $settings
	 */
	public function settings( $settings = [] ) {
		if ( ! get_option( self::OPTION_EMAIL_TEMPLATE ) ) {
			delete_option( self::OPTION_EMAIL_TEMPLATE );
		}

		$settings['job_alerts'] = [
			esc_html__( 'Job Alerts', 'cariera-addons' ),
			apply_filters(
				'wp_job_manager_alerts_settings',
				[
					[
						'name'     => self::OPTION_ACCOUNT_REQUIRED,
						'std'      => '1',
						'label'    => esc_html__( 'Account Required', 'cariera-addons' ),
						'cb_label' => esc_html__( 'Require an account to create job alerts', 'cariera-addons' ),
						'desc'     => esc_html__( 'Limit alert creation to registered, logged-in users.', 'cariera-addons' ),
						'type'     => 'checkbox',
						'track'    => 'bool',
					],
					[
						'name'  => self::OPTION_BRAND_COLOR,
						'label' => esc_html__( 'Brand Color', 'cariera-addons' ),
						'std'   => self::DEFAULT_BRAND_COLOR,
						'type'  => 'color',
						'desc'  => esc_html__( 'Set the color used for links and buttons in e-mails and shortcodes.', 'cariera-addons' ),
						'track' => 'is-default',
					],
					[
						'name'    => self::OPTION_FORM_FIELDS,
						'label'   => esc_html__( 'Alert Form Fields', 'cariera-addons' ),
						'type'    => 'multi_checkbox',
						'desc'    => 'Select what fields are displayed on the Add Alert form.',
						'options' => Alert_Form_Fields::get_default_fields(),
						'std'     => [
							'fields' => array_keys( Alert_Form_Fields::get_default_fields() ),
						],
						'track'   => 'is-default',
					],
					[
						'name'     => self::OPTION_EMAIL_TEMPLATE,
						'class'    => 'job-manager-alerts-email-template',
						'std'      => self::get_default_email(),
						'label'    => esc_html__( 'Alert Email Content', 'cariera-addons' ),
						'desc'     => esc_html__( 'Enter the content for your email alerts or leave it blank to use the default message. The following tags can be used to insert data dynamically:', 'cariera-addons' ) . '<br/><br/>' .
							'<code>{alert_name}</code> - ' . esc_html__( 'The name of the alert being sent', 'cariera-addons' ) . '<br/>' .
							'<code>{jobs}</code> - ' . esc_html__( 'The jobs found matching your alert', 'cariera-addons' ) . '<br/>' .
							'<code>{alert_next_date}</code> - ' . esc_html__( 'The next date this alert will be sent', 'cariera-addons' ) . '<br/>' .
							'<code>{alert_expiry}</code> - ' . esc_html__( 'When this job alert expires', 'cariera-addons' ) . '<br/>' .
							'<code>{display_name}</code> - ' . esc_html__( 'The user WordPress username', 'cariera-addons' ) . '<br/>' .
							'<br>' .
							'<strong>Note: </strong> {display_name}' . esc_html__( ' is not available when accounts are not required.', 'cariera-addons' ),
						'type'     => 'textarea',
						'required' => true,
						'track'    => 'is-default',
					],
					[
						'name'    => self::OPTION_JOB_DETAILS_VISIBLE,
						'class'   => 'job_details_visible',
						'label'   => esc_html__( 'Job Details Visible', 'cariera-addons' ),
						'type'    => 'multi_checkbox',
						'options' => [
							'company_name' => esc_html__( 'Company Name', 'cariera-addons' ),
							'company_logo' => esc_html__( 'Company Logo', 'cariera-addons' ),
							'location'     => esc_html__( 'Location', 'cariera-addons' ),
						],
						'std'     => self::DEFAULT_JOB_DETAILS_VISIBLE,
						'track'   => 'is-default',
					],
					[
						'name'  => self::OPTION_JOB_ALERTS_AUTO_DISABLE,
						'std'   => '90',
						'label' => esc_html__( 'Alert Duration', 'cariera-addons' ),
						'desc'  => esc_html__( 'Enter the number of days before alerts are automatically disabled, or leave blank to disable this feature. By default, alerts will be turned off for a search after 90 days.', 'cariera-addons' ),
						'type'  => 'input',
						'track' => 'value',
					],
					[
						'name'     => self::OPTION_JOB_MATCHES_ONLY,
						'std'      => '0',
						'label'    => esc_html__( 'Alert Matches', 'cariera-addons' ),
						'cb_label' => esc_html__( 'Send alerts with matches only', 'cariera-addons' ),
						'desc'     => esc_html__( 'Only send an alert when jobs are found matching its criteria. When disabled, an alert is sent regardless.', 'cariera-addons' ),
						'type'     => 'checkbox',
						'track'    => 'bool',
					],
					[
						'name'  => self::OPTION_JOB_ALERTS_PAGE_ID,
						'std'   => '',
						'label' => esc_html__( 'Alerts Page ID', 'cariera-addons' ),
						'desc'  => esc_html__( 'So that the plugin knows where to link users to view their alerts, you must select the page where you have placed the [job_alerts] shortcode.', 'cariera-addons' ),
						'type'  => 'page',
						'track' => 'bool',
					],
				]
			),
		];
		return $settings;
	}

	/**
	 * Whether signing in is required to create an alert.
	 *
	 * @since   0.9.2
	 * @version 0.9.11
	 */
	public static function is_account_required() {
		return (bool) get_option( self::OPTION_ACCOUNT_REQUIRED );
	}

	/**
	 * Get the page with the [job_alerts] shortcode.
	 *
	 * @since   0.9.2
	 * @version 0.9.11
	 */
	public static function get_alerts_page() {
		return get_option( self::OPTION_JOB_ALERTS_PAGE_ID );
	}

	/**
	 * Return the default email content for alerts
	 *
	 * @since 0.9.2
	 */
	public function get_default_email() {
		return __(
			'Hello {display_name},

The following jobs were found matching your "{alert_name}" job alert.

{jobs}

Your next alert for this search will be sent {alert_next_date}.

{alert_expiry}
',
			'cariera-addons'
		);
	}

	/**
	 * Get job details to show in alert emails.
	 *
	 * @since 0.9.2
	 */
	public function get_visible_email_fields() {
		$option = get_option( self::OPTION_JOB_DETAILS_VISIBLE, self::DEFAULT_JOB_DETAILS_VISIBLE );
		return $option['fields'] ?? [];
	}

	/**
	 * Get the brand color.
	 *
	 * @since 0.9.2
	 */
	public function get_the_brand_color() {
		return get_option( self::OPTION_BRAND_COLOR, self::DEFAULT_BRAND_COLOR );
	}

	/**
	 * Get the alert consent message.
	 *
	 * @since 0.9.2
	 *
	 * @param bool $with_checkbox whether the form has a checkbox or not.
	 */
	public static function get_alert_consent_message( $with_checkbox = false ) {
		$privacy_policy_url = get_privacy_policy_url();
		$main_text          = $with_checkbox ? esc_html__( 'I agree to receiving job alert e-mails', 'cariera-addons' ) : esc_html__( 'By subscribing, you agree to receive job alert e-mails', 'cariera-addons' );

		if ( ! empty( $privacy_policy_url ) ) {
			$message = sprintf(
				/* Translators: 1: beginning text, 2: opening anchor tag, 3: closing anchor tag. */
				esc_html__( '%1$s and accept the %2$sPrivacy Policy%3$s.', 'cariera-addons' ),
				$main_text,
				'<a href="' . esc_url( $privacy_policy_url ) . '">',
				'</a>'
			);
		} else {
			// Translators: %s: alert consent message when no privacy policy is set.
			$message = sprintf( esc_html__( '%s.', 'cariera-addons' ), $main_text );
		}

		/**
		 * Filters the alert consent message.
		 *
		 * @since 0.9.2
		 *
		 * @param string $message The alert consent message.
		 */
		return apply_filters(
			'cariera_addons_alerts_permission_checkbox_label',
			$message
		);
	}
}
