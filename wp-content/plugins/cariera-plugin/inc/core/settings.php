<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Settings title.
	 *
	 * @var string
	 */
	protected $settings_title = 'Cariera Core Settings';

	/**
	 * Available settings for plugin.
	 *
	 * @var array
	 */
	protected $settings_name = 'Cariera Theme:';

	/**
	 * Available settings for plugin.
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Settings group.
	 *
	 * @var string
	 */
	protected $settings_group;

	/**
	 * Settings prefix.
	 *
	 * @var string
	 */
	protected $settings_prefix = 'cariera_';

	/**
	 * Cariera theme version.
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * Updating is available from the settings.
	 *
	 * @var boolean
	 */
	protected $update = true;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings_group = 'cariera';

		if ( defined( 'CARIERA_VERSION' ) ) {
			$this->version = CARIERA_VERSION;
		}

		// Register plugin settings.
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		// Save settings via ajax.
		add_action( 'wp_ajax_cariera_save_core_settings', [ $this, 'save_settings' ] );

		// AJAX load section settings.
		add_action( 'wp_ajax_cariera_load_settings_section', [ $this, 'load_settings_section' ] );
	}

	/**
	 * Get Cariera Settings
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 */
	public function get_settings() {
		if ( 0 === count( $this->settings ) ) {
			$this->init_settings();
		}
		return $this->settings;
	}

	/**
	 * Initializes the configuration for the plugin's setting fields.
	 *
	 * @since   1.4.8
	 * @version 2.0.0
	 */
	protected function init_settings() {
		$prefix = $this->settings_prefix;

		$this->settings = apply_filters(
			'cariera_settings',
			[
				/* GENERAL OPTIONS */
				'general'          => [
					esc_html__( 'General', 'cariera-core' ),
					[
						[
							'id'            => $prefix . 'header_emp_cta_link',
							'label'         => esc_html__( 'Main Header CTA Link', 'cariera-core' ),
							'description'   => esc_html__( 'This link will be added to the Header CTA for non loggedin, employers and admins.', 'cariera-core' ),
							'type'          => 'page',
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [],

						],
						[
							'id'            => $prefix . 'header_candidate_cta_link',
							'label'         => esc_html__( 'Candidate Header CTA Link', 'cariera-core' ),
							'description'   => esc_html__( 'This link will be added to the Header CTA for loggedin Candidate users.', 'cariera-core' ),
							'type'          => 'page',
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'currency_position',
							'label'         => esc_html__( 'Currency Symbol positon', 'cariera-core' ),
							'description'   => esc_html__( 'Choose the position of the currency symbol (before or after the number).', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								'before' => esc_html__( 'Before Number', 'cariera-core' ),
								'after'  => esc_html__( 'After Number', 'cariera-core' ),
							],
							'default'       => 'before',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'currency_setting',
							'label'         => esc_html__( 'Currency Symbol', 'cariera-core' ),
							'description'   => esc_html__( 'Choose the currency symbol to be displayed across all listings.', 'cariera-core' ),
							'type'          => 'select',
							'options'       => $this->currencies(),
							'default'       => 'USD',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'fonticon_library_title',
							'label'         => '',
							'description'   => esc_html__( 'You can enable/disable any of the additional font icon libraries from below.', 'cariera-core' ),
							'type'          => 'title',
							'title'         => esc_html__( 'Font Icon Libraries', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'fonticon_fontawesome',
							'label'         => esc_html__( 'Fontawesome', 'cariera-core' ),
							'description'   => esc_html__( 'You can enable/disable Fontawesome icon library by turning the switch on/off if you do not use any icons from this library to improve performance.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'fonticon_simplelineicons',
							'label'         => esc_html__( 'Simple Line Icons', 'cariera-core' ),
							'description'   => esc_html__( 'You can enable/disable Simple Line Icons icon library by turning the switch on/off if you do not use any icons from this library to improve performance.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'fonticon_iconsmind',
							'label'         => esc_html__( 'Iconsmind', 'cariera-core' ),
							'description'   => esc_html__( 'You can enable/disable Iconsmind font icon library by turning the switch on/off if you do not use any icons from this library to improve performance.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'other_title',
							'label'         => '',
							'description'   => esc_html__( 'You can enable/disable any of the additional options below.', 'cariera-core' ),
							'type'          => 'title',
							'title'         => esc_html__( 'Other', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'block_editor',
							'label'         => esc_html__( 'Block Editor', 'cariera-core' ),
							'description'   => esc_html__( 'Enable this option to use the WordPress Block Editor (Gutenberg) instead of the Classic Editor.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'debug',
							'label'         => esc_html__( 'Debug Log', 'cariera-core' ),
							'description'   => esc_html__( 'You can enable this option if you want to view your debug log in the backend (Cariera -> Debug Log).', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'migrations',
							'label'         => esc_html__( 'Migrations', 'cariera-core' ),
							'description'   => esc_html__( 'You can enable this option if you want to access the migration features.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'mobile_app',
							'label'         => esc_html__( 'Mobile App Integration', 'cariera-core' ),
							// translators: %s is link url.
							'description'   => sprintf( __( 'If you are using the <a href="%s" target="_blank">Cariera Flutter Mobile App</a> enable this setting to allow the theme to send various data to the app via REST API.', 'cariera-core' ), 'https://1.envato.market/cariera-flutter' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'packages_addon',
							'label'         => esc_html__( 'Cariera Packages', 'cariera-core' ),
							'description'   => esc_html__( 'Enable this option to use the new native Cariera Packages plugin instead of relying on the WC Paid Listings plugin.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'envato_api_token',
							'label'         => esc_html__( 'Envato API Token', 'cariera-core' ),
							// translators: %s is link url.
							'description'   => sprintf( __( 'To access theme updates, please enter your Envato API Personal Token. You can generate your API Token by visiting the following link: <a href="%s" target="_blank">Create Envato API Token</a>', 'cariera-core' ), 'https://build.envato.com/create-token/?default=t&purchase:download=t&purchase:list=t&purchase:verify=t' ),
							'type'          => 'ajax',
							'ajax_action'   => 'cariera_envato_api_connection',
							'btn_label'     => esc_html__( 'Test Connection', 'cariera-core' ),
							'input_field'   => true,
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [],
						],
					],
				],

				/********** PRIVATE MESSAGES OPTIONS */
				'private_messages' => [
					esc_html__( 'Private Messages', 'cariera-core' ),
					[
						[
							'id'            => $prefix . 'private_messages',
							'label'         => esc_html__( 'Private Messaging System', 'cariera-core' ),
							'description'   => esc_html__( 'You can disable the private messaging system by disabling the option.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'clear_messages_db',
							'label'         => esc_html__( 'Delete All Messages', 'cariera-core' ),
							'description'   => '',
							'type'          => 'ajax',
							'ajax_action'   => 'cariera_delete_all_messages',
							'btn_label'     => esc_html__( 'Delete all messages', 'cariera-core' ),
							'input_field'   => false,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_job_listings',
							'label'         => esc_html__( 'Job Listings - Private Messages', 'cariera-core' ),
							'description'   => esc_html__( 'You can disable the private messages on job listings.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_companies',
							'label'         => esc_html__( 'Companies - Private Messages', 'cariera-core' ),
							'description'   => esc_html__( 'You can disable the private messages on companies.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_resumes',
							'label'         => esc_html__( 'Resumes - Private Messages', 'cariera-core' ),
							'description'   => esc_html__( 'You can disable the private messages on resumes.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_autoload_interval',
							'label'         => esc_html__( 'Autoload Interval', 'cariera-core' ),
							'description'   => esc_html__( 'Number of milliseconds to autoload messages and conversations. If you have a slower server you might want to increase the value. If left blank the default value will be 10000 (e.g 10000 is 10 sec).', 'cariera-core' ),
							'type'          => 'text',
							'default'       => '30000',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_compose',
							'label'         => esc_html__( 'Compose Messages', 'cariera-core' ),
							'description'   => esc_html__( 'You can disable compose message on the messages popup.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],
						[
							'id'            => $prefix . 'private_messages_attachmets',
							'label'         => esc_html__( 'Attachment Uploads', 'cariera-core' ),
							'description'   => esc_html__( 'You can disable this option if you want to prevent users from uploading and sending attachments to each other.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages',
							],
						],

					],
				],

				/********** NOTIFICATIONS OPTIONS */
				'notifications'    => [
					esc_html__( 'Notifications', 'cariera-core' ),
					[
						[
							'id'            => $prefix . 'notifications',
							'label'         => esc_html__( 'In-Site Notifications', 'cariera-core' ),
							'description'   => esc_html__( 'Notifications will be globally disabled if this option is turned off.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'clear_notification_db',
							'label'         => esc_html__( 'Delete All Notifications', 'cariera-core' ),
							'description'   => '',
							'type'          => 'ajax',
							'title'         => esc_html__( 'Delete All Notifications', 'cariera-core' ),
							'ajax_action'   => 'cariera_delete_all_notifications',
							'btn_label'     => esc_html__( 'Delete all notifications', 'cariera-core' ),
							'input_field'   => false,
							'default'       => '',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_status_title',
							'label'         => '',
							'description'   => esc_html__( 'When a listing\'s status changes, e.g pending to published.', 'cariera-core' ),
							'type'          => 'title',
							'title'         => esc_html__( 'Listing Status', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_status',
							'label'         => esc_html__( 'Notification', 'cariera-core' ),
							'description'   => esc_html__( '"Listing Status" notification will be disabled if this option is turned off.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_status_webhook',
							'label'         => esc_html__( 'Webhook', 'cariera-core' ),
							'description'   => esc_html__( 'Enable to be able to use webhooks', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						// Published Listings - Webhook.
						[
							'id'            => $prefix . 'notification_webhook_url_listing_created',
							'label'         => esc_html__( 'Webhook URL - Published', 'cariera-core' ),
							'description'   => esc_html__( 'This Webhook URL is used for "Published Listings"', 'cariera-core' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_listing_created_trigger',
							'label'         => ' ',
							'description'   => '',
							'type'          => 'button',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_listing_created',
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						// Approved Listings - Webhook.
						[
							'id'            => $prefix . 'notification_webhook_url_listing_approved',
							'label'         => esc_html__( 'Webhook URL - Approved', 'cariera-core' ),
							'description'   => esc_html__( 'This Webhook URL is used for "Approved Listings"', 'cariera-core' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_listing_approved_trigger',
							'label'         => ' ',
							'description'   => '',
							'type'          => 'button',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_listing_approved',
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						// Expired Listings - Webhook.
						[
							'id'            => $prefix . 'notification_webhook_url_listing_expired',
							'label'         => esc_html__( 'Webhook URL - Expired', 'cariera-core' ),
							'description'   => esc_html__( 'This Webhook URL is used for "Expired Listings"', 'cariera-core' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_listing_expired_trigger',
							'label'         => ' ',
							'description'   => '',
							'type'          => 'button',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_listing_expired',
								'data-toggleon' => 'cariera_notification_listing_status_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_application_title',
							'label'         => '',
							'description'   => '',
							'type'          => 'title',
							'title'         => esc_html__( 'New Job Application', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_application',
							'label'         => esc_html__( 'Notification', 'cariera-core' ),
							'description'   => esc_html__( '"New Application" notification will be disabled if this option is turned off.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_application_webhook',
							'label'         => esc_html__( 'Webhook', 'cariera-core' ),
							'description'   => esc_html__( 'Enable to be able to use webhooks', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_job_application',
							'label'         => esc_html__( 'Webhook URL', 'cariera-core' ),
							'description'   => '',
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_application_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_job_application_trigger',
							'label'         => ' ',
							'description'   => '',
							'type'          => 'button',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_job_application',
								'data-toggleon' => 'cariera_notification_application_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_listing_promotion_title',
							'label'         => '',
							'description'   => '',
							'type'          => 'title',
							'title'         => esc_html__( 'Listing Promotion', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_promotion',
							'label'         => esc_html__( 'Notification - Promo Started', 'cariera-core' ),
							'description'   => esc_html__( '"Listing Promoted" notification will be disabled if this option is turned off.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_promotion_ended',
							'label'         => esc_html__( 'Notification - Promo Expired', 'cariera-core' ),
							'description'   => esc_html__( '"Promotion Expired" notification will be disabled if this option is turned off.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'notification_listing_promotion_webhook',
							'label'         => esc_html__( 'Webhook', 'cariera-core' ),
							'description'   => esc_html__( 'Enable to be able to use webhooks', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 0,
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_promotion_expired',
							'label'         => esc_html__( 'Webhook URL - Expired', 'cariera-core' ),
							'description'   => esc_html__( 'This Webhook URL is used for "Expired Promotions"', 'cariera-core' ),
							'type'          => 'text',
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-toggleon' => 'cariera_notification_listing_promotion_webhook',
							],
						],
						[
							'id'            => $prefix . 'notification_webhook_url_promotion_expired_trigger',
							'label'         => ' ',
							'description'   => '',
							'type'          => 'button',
							'title'         => esc_html__( 'Trigger Webhook', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [
								'data-action'   => 'webhook-trigger',
								'data-webhook'  => 'cariera_notification_webhook_url_promotion_expired',
								'data-toggleon' => 'cariera_notification_listing_promotion_webhook',
							],
						],

					],
				],

				/* CAPTCHA OPTIONS */
				'captcha'          => [
					esc_html__( 'Captcha', 'cariera-core' ),
					[
						[
							'id'            => $prefix . 'captcha_provider',
							'label'         => esc_html__( 'Captcha Provider', 'cariera-core' ),
							'description'   => esc_html__( 'Select the captcha provider you want to use.', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								''          => esc_html__( 'none', 'cariera-core' ),
								'recaptcha' => esc_html__( 'reCaptcha', 'cariera-core' ),
								'hcaptcha'  => esc_html__( 'hCaptcha', 'cariera-core' ),
								'turnstile' => esc_html__( 'Cloudflare Turnstile', 'cariera-core' ),
							],
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						// reCAPTCHA.
						[
							'id'            => $prefix . 'recaptcha_sitekey',
							'label'         => esc_html__( 'reCAPTCHA Site Key', 'cariera-core' ),
							// translators: %s is link url.
							'description'   => sprintf( __( 'Get the sitekey from <a href="%s" target="_blank">Google\'s reCAPTCHA admin dashboard</a> - use reCAPTCHA v2', 'cariera-core' ), 'https://www.google.com/recaptcha/admin#list' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_captcha_provider',
								'data-toggle-value' => 'recaptcha',
							],
						],
						[
							'id'            => $prefix . 'recaptcha_secretkey',
							'label'         => esc_html__( 'reCAPTCHA Secret Key', 'cariera-core' ),
							// translators: %s is link url.
							'description'   => sprintf( __( 'Get the secret key from <a href="%s" target="_blank">Google\'s reCAPTCHA admin dashboard</a> - use reCAPTCHA v2', 'cariera-core' ), 'https://www.google.com/recaptcha/admin#list' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_captcha_provider',
								'data-toggle-value' => 'recaptcha',
							],
						],
						// hCaptcha.
						[
							'id'            => $prefix . 'hcaptcha_sitekey',
							'label'         => esc_html__( 'hCaptcha Site Key', 'cariera-core' ),
							// translators: %s is link url.
							'description'   => sprintf( __( 'Get the site key from <a href="%s" target="_blank">your hCaptcha dashboard</a>.', 'cariera-core' ), 'https://dashboard.hcaptcha.com/' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_captcha_provider',
								'data-toggle-value' => 'hcaptcha',
							],
						],
						[
							'id'            => $prefix . 'hcaptcha_secretkey',
							'label'         => esc_html__( 'hCaptcha Secret Key', 'cariera-core' ),
							// translators: %s is link url.
							'description'   => sprintf( __( 'Get the secret key from <a href="%s" target="_blank">your hCaptcha dashboard</a> under Settings → Secret Key.', 'cariera-core' ), 'https://dashboard.hcaptcha.com/' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_captcha_provider',
								'data-toggle-value' => 'hcaptcha',
							],
						],
						// Cloudflare Turnstile.
						[
							'id'            => $prefix . 'turnstile_sitekey',
							'label'         => esc_html__( 'Turnstile Site Key', 'cariera-core' ),
							// translators: %s is link url.
							'description'   => sprintf( __( 'Get the site key from the <a href="%s" target="_blank">Cloudflare Turnstile dashboard</a>.', 'cariera-core' ), 'https://dash.cloudflare.com/?to=/:account/turnstile' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_captcha_provider',
								'data-toggle-value' => 'turnstile',
							],
						],
						[
							'id'            => $prefix . 'turnstile_secretkey',
							'label'         => esc_html__( 'Turnstile Secret Key', 'cariera-core' ),
							// translators: %s is link url.
							'description'   => sprintf( __( 'Get the secret key from the <a href="%s" target="_blank">Cloudflare Turnstile dashboard</a>.', 'cariera-core' ), 'https://dash.cloudflare.com/?to=/:account/turnstile' ),
							'type'          => 'text',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_captcha_provider',
								'data-toggle-value' => 'turnstile',
							],
						],
						[
							'id'            => $prefix . 'captcha_options_title',
							'label'         => '',
							'description'   => esc_html__( 'Choose which forms should include CAPTCHA verification. Make sure your CAPTCHA is configured correctly above.', 'cariera-core' ),
							'type'          => 'title',
							'title'         => esc_html__( 'Captcha Display Settings', 'cariera-core' ),
							'class_wrapper' => 'cariera-notifications',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'captcha_login',
							'label'         => esc_html__( 'Login Form', 'cariera-core' ),
							'description'   => esc_html__( 'Display CAPTCHA field in the login form. You must have entered a valid site key and secret key above.', 'cariera-core' ),
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'captcha_register',
							'label'         => esc_html__( 'Registration Form', 'cariera-core' ),
							'description'   => esc_html__( 'Display CAPTCHA field in the registration form. You must have entered a valid site key and secret key above.', 'cariera-core' ),
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'captcha_forgotpass',
							'label'         => esc_html__( 'Forgot Password Form', 'cariera-core' ),
							'description'   => esc_html__( 'Display CAPTCHA field in the forgot password form. You must have entered a valid site key and secret key above.', 'cariera-core' ),
							'type'          => 'switch',
							'class_wrapper' => '',
							'attributes'    => [],
						],

					],
				],

				/* REGISTRATION OPTIONS */
				'registration'     => [
					esc_html__( 'Login & Register', 'cariera-core' ),
					[
						[
							'id'            => $prefix . 'login_register_layout',
							'label'         => esc_html__( 'Login & Registration Layout', 'cariera-core' ),
							'description'   => esc_html__( 'You can set your login & register to "popup" or to redirect to a "custom page".', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								'popup' => esc_html__( 'Popup', 'cariera-core' ),
								'page'  => esc_html__( 'Custom Page', 'cariera-core' ),
							],
							'default'       => 'popup',
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'login_register_page',
							'label'         => esc_html__( 'Login & Registration Custom Page', 'cariera-core' ),
							'description'   => esc_html__( 'Choose page that uses "Page Template Login".', 'cariera-core' ),
							'type'          => 'page',
							'default'       => '',
							'class_wrapper' => 'login-page',
							'attributes'    => [
								'data-toggleon'     => 'cariera_login_register_layout',
								'data-toggle-value' => 'page',
							],
						],
						[
							'id'            => $prefix . 'login_redirection',
							'label'         => esc_html__( 'Login Redirection', 'cariera-core' ),
							'description'   => esc_html__( 'Select your prefered login redirection method.', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								'dashboard'      => esc_html__( 'Dashboard', 'cariera-core' ),
								'home'           => esc_html__( 'Home Page', 'cariera-core' ),
								'custom_page'    => esc_html__( 'Custom Page', 'cariera-core' ),
								'no_redirection' => esc_html__( 'No Redirection', 'cariera-core' ),
							],
							'default'       => 'dashboard',
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'login_redirection_page',
							'label'         => esc_html__( 'Custom Redirection Page', 'cariera-core' ),
							'description'   => esc_html__( 'Choose the custom page to redirect users on login.', 'cariera-core' ),
							'type'          => 'page',
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_login_redirection',
								'data-toggle-value' => 'custom_page',
							],
						],
						[
							'id'            => $prefix . 'login_redirection_candidate',
							'label'         => esc_html__( 'Candidate Login Redirection', 'cariera-core' ),
							'description'   => esc_html__( 'Select your prefered login redirection method for candidate users.', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								'dashboard'      => esc_html__( 'Dashboard', 'cariera-core' ),
								'home'           => esc_html__( 'Home Page', 'cariera-core' ),
								'custom_page'    => esc_html__( 'Custom Page', 'cariera-core' ),
								'no_redirection' => esc_html__( 'No Redirection', 'cariera-core' ),
							],
							'default'       => 'dashboard',
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'login_candi_redirection_page',
							'label'         => esc_html__( 'Custom Redirection Page', 'cariera-core' ),
							'description'   => esc_html__( 'Choose the custom page to redirect candidates on login.', 'cariera-core' ),
							'type'          => 'page',
							'default'       => '',
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon'     => 'cariera_login_redirection_candidate',
								'data-toggle-value' => 'custom_page',
							],
						],

						/***** WELCOME USER */
						[
							'id'            => $prefix . 'header_registration',
							'label'         => '',
							'description'   => '',
							'type'          => 'title',
							'title'         => esc_html__( 'Registration', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'registration',
							'label'         => esc_html__( 'Registration', 'cariera-core' ),
							'description'   => esc_html__( 'Turn the switch "off" if you want to disable registration.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_role_candidate',
							'label'         => esc_html__( 'Candidate Role Selection', 'cariera-core' ),
							'description'   => esc_html__( 'Turn the switch "off" if you want to disable the "Candidate" role from the registration form.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_role_employer',
							'label'         => esc_html__( 'Employer Role Selection', 'cariera-core' ),
							'description'   => esc_html__( 'Turn the switch "off" if you want to disable the "Employer" role from the registration form.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'default_registration_user_role',
							'label'         => esc_html__( 'Default User Role', 'cariera-core' ),
							'description'   => esc_html__( 'Set the default user role pre-selected in the registration form.', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								'none'      => esc_html__( 'None', 'cariera-core' ),
								'employer'  => esc_html__( 'Employer', 'cariera-core' ),
								'candidate' => esc_html__( 'Candidate', 'cariera-core' ),
							],
							'default'       => 'employer',
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'moderate_new_user',
							'label'         => esc_html__( 'Moderate New User', 'cariera-core' ),
							'description'   => esc_html__( 'Users are automatically approved once registered and there is no need to activate their account. You can setup so that they can activate their account by email or that admin has to approve them manually.', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								'auto'  => esc_html__( 'Auto Approval', 'cariera-core' ),
								'email' => esc_html__( 'Email Approval', 'cariera-core' ),
								'admin' => esc_html__( 'Admin Approval', 'cariera-core' ),
							],
							'default'       => 'auto',
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'moderate_new_user_page',
							'label'         => esc_html__( 'Approve User Page', 'cariera-core' ),
							'description'   => esc_html__( 'Approve pending user page. The page needs to have [cariera_approve_user] shortcode.', 'cariera-core' ),
							'type'          => 'page',
							'default'       => '',
							'class_wrapper' => 'cariera-registration approve-user-page',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'auto_login',
							'label'         => esc_html__( 'Auto Login after Registration', 'cariera-core' ),
							'description'   => esc_html__( 'If enabled the user will automatically login after registration.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'data-toggleon'     => 'cariera_moderate_new_user',
								'data-toggle-value' => 'auto',
							],
						],
						[
							'id'            => $prefix . 'register_hide_username',
							'label'         => esc_html__( 'Hide Username Field', 'cariera-core' ),
							'description'   => esc_html__( 'If enabled the username will be generated from the email address.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => '',
							'class_wrapper' => 'cariera-registration',
						],
						[
							'id'            => $prefix . 'register_privacy_policy',
							'label'         => esc_html__( 'Privacy Policy', 'cariera-core' ),
							'description'   => esc_html__( 'Turn the switch to "off" if you want to disable privacy policy checkbox.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'register_privacy_policy_text',
							'label'         => esc_html__( 'Privacy Policy Text', 'cariera-core' ),
							'description'   => esc_html__( 'Make sure to add "{gdpr_link}" in the input below if you want to add a link. The {gdpr_link} will get replaced with the page set on the next option.', 'cariera-core' ),
							'type'          => 'text',
							'default'       => esc_html__( 'By signing up, you agree to our {gdpr_link}.', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'data-toggleon' => 'cariera_register_privacy_policy',
							],
						],
						[
							'id'            => $prefix . 'register_privacy_policy_page',
							'label'         => esc_html__( 'Privacy Policy Page', 'cariera-core' ),
							'description'   => esc_html__( 'Choose page that will contain detailed information about the Privacy Policy of your website.', 'cariera-core' ),
							'type'          => 'page',
							'default'       => '',
							'class_wrapper' => 'cariera-registration',
							'attributes'    => [
								'data-toggleon' => 'cariera_register_privacy_policy',
							],
						],
						[
							'id'            => $prefix . 'account_role_change',
							'label'         => esc_html__( 'Switch User Role', 'cariera-core' ),
							'description'   => esc_html__( 'Allows Employers and Candidates to change their user role on "My Profile" page.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration',
						],
						[
							'id'            => $prefix . 'register_password_length',
							'label'         => esc_html__( 'Minimun password length', 'cariera-core' ),
							'description'   => esc_html__( 'Select the minimun length of your password.', 'cariera-core' ),
							'type'          => 'number',
							'default'       => '4',
							'class_wrapper' => 'cariera-registration',
						],

						/***** WELCOME USER */
						[
							'id'            => $prefix . 'header_user_welcome',
							'label'         => '',
							'description'   => esc_html__( 'Available tags are: ', 'cariera-core' ) . '<strong>{user_name}, {user_mail}, {site_name}, {password}</strong>',
							'type'          => 'title',
							'title'         => esc_html__( 'New User Welcome Email - Auto Approve', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_welcome_email',
							'label'         => esc_html__( 'User Welcome Email', 'cariera-core' ),
							'description'   => esc_html__( 'Enable/Disable email notification when a user registers on the website.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_welcome_email_admin',
							'label'         => esc_html__( 'Admin Notification - New User', 'cariera-core' ),
							'description'   => esc_html__( 'Enable/Disable email notification to notify the admin that a new user has registered.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_welcome_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera-core' ),
							'type'          => 'text',
							'default'       => esc_html__( 'Welcome to {site_name}', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_welcome_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera-core' ),
							'type'          => 'editor',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
Welcome and thank you for signing up. You can login to your account with the details below:<br>
<ul>
<li>Username: {user_name}</li>
<li>Email: {user_mail}</li>
<li>Password: {password}</li>
</ul>'
								)
							),
							'class_wrapper' => 'cariera-registration no-approval-required',
							'attributes'    => [],
						],

						/***** APPROVAL EMAIL */
						[
							'id'            => $prefix . 'header_new_user_approve',
							'label'         => '',
							'description'   => esc_html__( 'This email will be sent to the user or the admin depending on your approval settings. Available tags are: ', 'cariera-core' ) . '<strong>{user_name}, {user_mail}, {site_name}, {password}, {approval_url}</strong>',
							'type'          => 'title',
							'title'         => esc_html__( 'Approve new registered User', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_approve_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera-core' ),
							'type'          => 'text',
							'default'       => esc_html__( 'Approve new Registered user: {user_name}', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_approve_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera-core' ),
							'type'          => 'editor',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									"Hi {user_name},<br>
Welcome and thank you for signing up. You can verify your account by clicking the link below:<br>
<a href='{approval_url}' target='_blank'>Verify Account</a>"
								)
							),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],

						/***** USER APPROVED EMAIL */
						[
							'id'            => $prefix . 'header_new_user_approved',
							'label'         => '',
							'description'   => esc_html__( 'This email will be sent to the user once their user status changes to "Approved"', 'cariera-core' ),
							'type'          => 'title',
							'title'         => esc_html__( 'User Approved', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_approved_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera-core' ),
							'type'          => 'text',
							'default'       => esc_html__( 'Your Account has been Approved', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_approved_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera-core' ),
							'type'          => 'editor',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									"Hi {user_name},<br>
Your account has been approved. You can login via the link below:<br>
<a href='{site_url}' target='_blank'>Login</a>"
								)
							),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],

						/***** USER DENIED EMAIL */
						[
							'id'            => $prefix . 'header_new_user_denied',
							'label'         => '',
							'description'   => esc_html__( 'This email will be sent to the user once their user status changes to "Denied"', 'cariera-core' ),
							'type'          => 'title',
							'title'         => esc_html__( 'User Denied', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_denied_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera-core' ),
							'type'          => 'text',
							'default'       => esc_html__( 'Your Account has been Denied', 'cariera-core' ),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'new_user_denied_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera-core' ),
							'type'          => 'editor',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
We are sorry to say but your account has been denied.'
								)
							),
							'class_wrapper' => 'cariera-registration approval-required',
							'attributes'    => [],
						],

					],
				],

				/* PAGES OPTIONS */
				'pages'            => [
					esc_html__( 'Pages', 'cariera-core' ),
					[
						[
							'id'            => $prefix . 'dashboard_page',
							'label'         => esc_html__( 'Dashboard Page', 'cariera-core' ),
							'description'   => esc_html__( 'Main User Dashboard page. The page needs to have [cariera_dashboard] shortcode (optional).', 'cariera-core' ),
							'type'          => 'page',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'bookmarks_page',
							'label'         => esc_html__( 'Bookmarks Page', 'cariera-core' ),
							'description'   => esc_html__( 'The page needs to have [my_bookmarks] shortcode.', 'cariera-core' ),
							'type'          => 'page',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'past_applications_page',
							'label'         => esc_html__( 'Applied Jobs Page', 'cariera-core' ),
							'description'   => esc_html__( 'The page needs to have [past_applications] shortcode.', 'cariera-core' ),
							'type'          => 'page',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'user_packages_page',
							'label'         => esc_html__( 'User Packages Page', 'cariera-core' ),
							'description'   => esc_html__( 'The page needs to have [cariera_user_packages] shortcode.', 'cariera-core' ),
							'type'          => 'page',
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'dashboard_profile_page',
							'label'         => esc_html__( 'My Profile Page', 'cariera-core' ),
							'description'   => esc_html__( 'Profile customization page. The page needs to have [cariera_my_account] shortcode.', 'cariera-core' ),
							'type'          => 'page',
							'class_wrapper' => '',
							'attributes'    => [],
						],

					],
				],

				/* EMAILS OPTIONS */
				'maps'             => [
					esc_html__( 'Maps', 'cariera-core' ),
					[
						[
							'id'            => $prefix . 'map_provider',
							'label'         => esc_html__( 'Maps Provider', 'cariera-core' ),
							'description'   => esc_html__( 'Select the map provider for your maps.', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								'none'   => 'None',
								'osm'    => 'Open Street Maps',
								'mapbox' => 'MapBox',
								'google' => 'Google Maps',
							],
							'default'       => 'none',
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-select-field',
							],
						],
						[
							'id'            => $prefix . 'mapbox_access_token',
							'label'         => esc_html__( 'Access Token', 'cariera-core' ),
							'description'   => esc_html__( 'Enter your Mapbox access token here.', 'cariera-core' ),
							'type'          => 'text',
							'default'       => '',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'data-toggleon'     => 'cariera_map_provider',
								'data-toggle-value' => 'mapbox',
							],
						],
						[
							'id'            => $prefix . 'gmap_api_key',
							'label'         => esc_html__( 'API Key', 'cariera-core' ),
							'description'   => esc_html__( 'Enter your Google Maps API key here.', 'cariera-core' ),
							'type'          => 'text',
							'default'       => '',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'data-toggleon'     => 'cariera_map_provider',
								'data-toggle-value' => 'google',
							],
						],
						[
							'id'            => $prefix . 'gmap_language',
							'label'         => esc_html__( 'Language', 'cariera-core' ),
							'description'   => esc_html__( 'Select the language for Google Maps services.', 'cariera-core' ),
							'type'          => 'text',
							'default'       => 'en',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'data-toggleon'     => 'cariera_map_provider',
								'data-toggle-value' => 'google',
							],
						],
						[
							'id'            => $prefix . 'maps_type',
							'label'         => esc_html__( 'Select the map type.', 'cariera-core' ),
							'description'   => '',
							'type'          => 'select',
							'options'       => [
								'roadmap'   => 'Roadmap',
								'hybrid'    => 'Hybrid',
								'satellite' => 'Satellite',
								'terrain'   => 'Terrain',
							],
							'default'       => 'roadmap',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'data-toggleon'     => 'cariera_map_provider',
								'data-toggle-value' => 'google',
							],
						],
						[
							'id'            => $prefix . 'location_autocomplete',
							'label'         => esc_html__( 'Location Autocomplete', 'cariera-core' ),
							'description'   => esc_html__( 'Enable location suggestions while typing in the search field.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'location_autocomplete_restriction',
							'label'         => esc_html__( 'Restrict Search Result', 'cariera-core' ),
							'description'   => esc_html__( 'To restrict search results to specific countries, enter the ISO 3166-1 Alpha-2 country code. Google maps can support up to five countries (e.g., de,uk).', 'cariera-core' ),
							'type'          => 'text',
							'default'       => '',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'data-toggleon' => 'cariera_location_autocomplete',
							],
						],
						[
							'id'            => $prefix . 'auto_geolocate',
							'label'         => esc_html__( 'Geolocate', 'cariera-core' ),
							'description'   => esc_html__( 'Allow users to automatically detect their location using the map provider\'s geolocation feature.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => '',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'search_radius',
							'label'         => esc_html__( 'Search Radius', 'cariera-core' ),
							'description'   => esc_html__( 'Enable users to filter results within a specified radius from their chosen location.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => '',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'search_radius_default',
							'label'         => esc_html__( 'Default Radius Value', 'cariera-core' ),
							'description'   => esc_html__( 'Set the default preselected search radius value.', 'cariera-core' ),
							'type'          => 'text',
							'default'       => '',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'data-toggleon' => 'cariera_search_radius',
							],
						],
						[
							'id'            => $prefix . 'search_radius_max',
							'label'         => esc_html__( 'Max Radius Value', 'cariera-core' ),
							'description'   => esc_html__( 'Set the maximum allowable search radius.', 'cariera-core' ),
							'type'          => 'text',
							'default'       => '100',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'data-toggleon' => 'cariera_search_radius',
							],
						],
						[
							'id'            => $prefix . 'search_radius_unit',
							'label'         => esc_html__( 'Search Radius Unit', 'cariera-core' ),
							'description'   => esc_html__( 'Choose the measurement unit for the search radius.', 'cariera-core' ),
							'type'          => 'select',
							'options'       => [
								'km'    => 'KM',
								'miles' => 'Miles',
							],
							'default'       => 'km',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [
								'data-toggleon' => 'cariera_search_radius',
							],
						],
						[
							'id'            => $prefix . 'map_autofit',
							'label'         => esc_html__( 'Autofit Markers in the Map', 'cariera-core' ),
							'description'   => esc_html__( 'Automatically adjust the map view to fit all markers when displaying search results.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'map_center',
							'label'         => esc_html__( 'Custom Center point', 'cariera-core' ),
							'description'   => esc_html__( 'Enter latitude and longitude coordinates, separated by a comma (e.g., 37.9838, 23.7275).', 'cariera-core' ),
							'type'          => 'text',
							'default'       => '37.9838, 23.7275',
							'class_wrapper' => 'cariera-maps',
							'attributes'    => [],
						],
					],
				],

				/* EMAILS OPTIONS */
				'emails'           => [
					esc_html__( 'Emails', 'cariera-core' ),
					[
						[
							'id'            => $prefix . 'emails_name',
							'label'         => esc_html__( '"From name" in email', 'cariera-core' ),
							'description'   => esc_html__( 'The name from who the email is received, by default it is your site name.', 'cariera-core' ),
							'type'          => 'text',
							'default'       => get_bloginfo( 'name' ),
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'emails_from_email',
							'label'         => esc_html__( '"From" email ', 'cariera-core' ),
							'description'   => esc_html__( 'This will act as the "from" and "reply-to" address. This emails should match your domain address', 'cariera-core' ),
							'type'          => 'text',
							'default'       => get_bloginfo( 'admin_email' ),
							'class_wrapper' => '',
							'attributes'    => [],
						],

						/***** ACCOUNT DELETED */
						[
							'id'            => $prefix . 'header_delete_account',
							'label'         => '',
							'description'   => esc_html__( 'Available tags are: ', 'cariera-core' ) . '<strong>{user_name}, {user_mail}, {first_name}, {last_name}</strong>',
							'type'          => 'title',
							'title'         => esc_html__( 'Delete Account Email', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'delete_account_email',
							'label'         => esc_html__( 'Delete Account Notification', 'cariera-core' ),
							'description'   => esc_html__( 'Enable/Disable email notification when a user deletes their account.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'delete_account_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera-core' ),
							'description'   => '',
							'type'          => 'text',
							'default'       => esc_html__( 'Your account has been deleted!', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_delete_account_email',
							],
						],
						[
							'id'            => $prefix . 'delete_account_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera-core' ),
							'description'   => '',
							'type'          => 'editor',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
We are sorry to see you go! If you change your mind feel free to register on our website again anytime.'
								)
							),
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_delete_account_email',
							],
						],

						/***** LISTING PROMOTED */
						[
							'id'            => $prefix . 'header_listing_promoted',
							'label'         => '',
							'description'   => esc_html__( 'Available tags that can be used in the mail content: ', 'cariera-core' ) . '<strong>{user_name}, {user_mail}, {listing_name}, {listing_url}</strong>',
							'type'          => 'title',
							'title'         => esc_html__( 'Listing Promotion', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'listing_promoted_email',
							'label'         => esc_html__( 'Listing Promotion', 'cariera-core' ),
							'description'   => esc_html__( 'Enable/Disable email notifications to notify the author when their listing get\'s promoted.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'listing_promoted_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera-core' ),
							'description'   => '',
							'type'          => 'text',
							'default'       => esc_html__( 'Listing Promoted Successfully!', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_listing_promoted_email',
							],
						],
						[
							'id'            => $prefix . 'listing_promoted_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera-core' ),
							'description'   => '',
							'type'          => 'editor',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
Your listing <strong>{listing_name}</strong> has been promoted successfully.<br>'
								)
							),
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_listing_promoted_email',
							],
						],

						/***** LISTING PROMOTION EXPIRED */
						[
							'id'            => $prefix . 'header_promotion_expired_email',
							'label'         => '',
							'description'   => esc_html__( 'Available tags that can be used in the mail content: ', 'cariera-core' ) . '<strong>{user_name}, {user_mail}, {listing_name}, {listing_url}</strong>',
							'type'          => 'title',
							'title'         => esc_html__( 'Listing Promotion Expired', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'promotion_expired_email',
							'label'         => esc_html__( 'Promotion Expired', 'cariera-core' ),
							'description'   => esc_html__( 'Enable/Disable email notifications to notify the author when their listing get\'s promoted.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'promotion_expired_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera-core' ),
							'description'   => '',
							'type'          => 'text',
							'default'       => esc_html__( 'Promotion has Expired!', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_promotion_expired_email',
							],
						],
						[
							'id'            => $prefix . 'promotion_expired_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera-core' ),
							'description'   => '',
							'type'          => 'editor',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
Your promotion for <strong>{listing_name}</strong> has expired.<br>'
								)
							),
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_promotion_expired_email',
							],
						],

						/***** PRIVATE MESSAGES */
						[
							'id'            => $prefix . 'private_messages_email_header',
							'label'         => '',
							'description'   => esc_html__( 'User will receive an email notification when receiving a new message. Available tags are: ', 'cariera-core' ) . '<strong>{user_name}, {user_mail}, {first_name}, {last_name}, {sender_name}, {sender_mail}</strong>',
							'type'          => 'title',
							'title'         => esc_html__( 'Private Messages', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [],
						],
						[
							'id'            => $prefix . 'private_messages_email_notification',
							'label'         => esc_html__( 'New Message Notification', 'cariera-core' ),
							'description'   => esc_html__( 'Enable/Disable email notifications when user receives a new message.', 'cariera-core' ),
							'type'          => 'switch',
							'default'       => 1,
							'class_wrapper' => '',
							'attributes'    => [
								'class' => 'toggle-settings-field',
							],
						],
						[
							'id'            => $prefix . 'private_messages_email_subject',
							'label'         => esc_html__( 'Email Subject', 'cariera-core' ),
							'type'          => 'text',
							'default'       => esc_html__( 'You have a new message!', 'cariera-core' ),
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages_email_notification',
							],
						],
						[
							'id'            => $prefix . 'private_messages_email_content',
							'label'         => esc_html__( 'Email Content', 'cariera-core' ),
							'type'          => 'editor',
							'default'       => trim(
								preg_replace(
									'/\t+/',
									'',
									'Hi {user_name},<br>
You have a new message from {sender_name}.'
								)
							),
							'class_wrapper' => '',
							'attributes'    => [
								'data-toggleon' => 'cariera_private_messages_email_notification',
							],
						],

					],
				],

			] // END.
		);
	}

	/**
	 * Register plugin settings with WordPress's Settings API.
	 *
	 * @since   1.4.8
	 * @version 1.8.5
	 */
	public function register_settings() {
		if ( ! cariera_activation_status() ) {
			return;
		}

		$this->init_settings();

		foreach ( $this->settings as $section ) {
			foreach ( $section[1] as $option ) {
				if ( isset( $option['default'] ) ) {
					add_option( $option['id'], $option['default'] );
				}
				register_setting( $this->settings_group, $option['id'] );
			}
		}
	}

	/**
	 * Load settings page content
	 *
	 * @since   1.4.8
	 * @version 2.0.0
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

		wp_enqueue_script( 'cariera-core-settings' );
		wp_enqueue_style( 'cariera-core-settings' );

		// WP Editor assets.
		wp_enqueue_editor();
		wp_enqueue_media();

		$this->init_settings();
		?>

		<!-- Build Settings Page -->
		<div class="wrap cariera-settings-wrap">
			<h2><?php echo esc_html( $this->settings_title ); ?></h2>

			<form id="cariera-settings-form" class="cariera-options" method="post" data-settings="<?php echo esc_attr( $this->settings_group ); ?>">
				<?php settings_fields( $this->settings_group ); ?>

				<div class="options-nav">
					<h2 class="nav-tab-wrapper">
						<div class="logo">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/gnodesign-logo.svg' ); ?>" class="gnodesign-logo" alt="<?php esc_attr_e( 'Gnodesign logo', 'cariera-core' ); ?>" />
						</div>
						<?php
						foreach ( $this->settings as $key => $section ) {
							echo '<a href="#settings-' . esc_attr( sanitize_title( $key ) ) . '" class="nav-tab ' . esc_attr( sanitize_title( $key ) ) . '-tab">';

							echo esc_html( $section[0] );

							if ( ! empty( $section['version'] ) ) {
								echo ' <span class="version">' . esc_html( $section['version'] ) . '</span>';
							}

							echo '</a>';
						}
						?>
					</h2>
				</div>

				<div class="cariera-settings">
					<div class="loader"><span></span></div>
					<div class="settings-header">
						<h4 class="headline">
							<?php echo esc_html( $this->settings_name ); ?>
							<span class="version"><?php echo esc_html__( 'Version', 'cariera-core' ) . ' ' . esc_html( $this->version ); ?></span>
							
							<?php if ( $this->update ) { ?>
								<button class="update-check" title="<?php esc_html_e( 'Check for updates', 'cariera-core' ); ?>"></button>
							<?php } ?>
						</h4>
						<p class="submit">
							<button type="button" class="cariera-btn save-settings"><?php esc_html_e( 'Save Changes', 'cariera-core' ); ?></button>
						</p>
					</div>

					<div class="settings-wrapper">
						<?php
						foreach ( $this->settings as $key => $section ) {
							echo '<div id="settings-' . esc_attr( sanitize_title( $key ) ) . '" class="settings_panel" style="display:none;"></div>';
						}
						?>

						<p class="submit main-submit">
							<button type="button" class="cariera-btn save-settings"><?php esc_html_e( 'Save Changes', 'cariera-core' ); ?></button>
						</p>
					</div>
				</div>

			</form>
		</div>
		
		<?php get_template_part( 'templates/backend/admin/ajax-response' ); ?>
		<?php get_template_part( 'templates/backend/admin/support-link' ); ?>

		<script type="text/javascript"></script>
		<?php
	}

	/**
	 * Save settings via AJAX
	 *
	 * @since   1.8.4
	 * @version 2.0.0
	 */
	public function save_settings() {
		// Verify the nonce for security.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), '_cariera_core_admin_nonce' ) ) {
			wp_send_json_error(
				[
					'message'       => esc_html__( 'Your session has expired. Please refresh the page and try again.', 'cariera-core' ),
					'nonce_expired' => true,
				],
				403
			);
			wp_die();
		}

		// Check if the user has permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized access.', 'cariera-core' ) ], 403 );
			wp_die();
		}

		// Filter POST data to only include valid options.
		$options = array_diff_key(
			wp_unslash( $_POST ),
			[
				'action'         => '',
				'nonce'          => '',
				'settings_group' => '',
			]
		);

		// Pre-calculate content fields.
		$content_fields = array_filter(
			array_keys( $options ),
			fn( $k ) => strpos( $k, '_content' ) !== false || strpos( $k, '_editor' ) !== false
		);

		foreach ( $options as $key => $value ) {
			$sanitized_key = sanitize_text_field( $key );

			// Skip if key is empty after sanitization.
			if ( empty( $sanitized_key ) ) {
				continue;
			}

			// Get existing value first to avoid unnecessary updates.
			$existing = get_option( $sanitized_key );

			// Determine sanitization based on pre-calculated fields.
			if ( in_array( $key, $content_fields, true ) ) {
				$sanitized_value = wp_kses_post( $value );
			} else {
				$sanitized_value = is_array( $value )
					? array_map( 'sanitize_text_field', $value )
					: sanitize_text_field( $value );
			}

			// Only update if value changed.
			if ( $existing !== $sanitized_value ) {
				update_option( $sanitized_key, $sanitized_value );
			}
		}

		wp_send_json_success( [ 'message' => esc_html__( 'Settings saved successfully.', 'cariera-core' ) ] );
	}

	/**
	 * Loading setting sections via AJAX.
	 *
	 * @since   1.8.4
	 * @version 1.9.1
	 */
	public function load_settings_section() {
		$settings_group = isset( $_POST['settings_group'] ) ? sanitize_text_field( wp_unslash( $_POST['settings_group'] ) ) : '';

		if ( $settings_group !== $this->settings_group ) {
			return;
		}

		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_admin_nonce', 'nonce' );

		// Get the requested section key from the AJAX request.
		$section_key = isset( $_POST['section'] ) ? sanitize_text_field( wp_unslash( $_POST['section'] ) ) : '';

		// Output the HTML for the requested section.
		if ( ! empty( $section_key ) && isset( $this->settings[ $section_key ][1] ) ) {
			$options    = $this->settings[ $section_key ][1];
			$option_ids = wp_list_pluck( $options, 'id' );

			// Get all options in one query.
			$all_options     = wp_load_alloptions();
			$section_options = array_intersect_key( $all_options, array_flip( $option_ids ) );

			echo '<table class="form-table settings parent-settings">';
			foreach ( $options as $option ) {
				$value = array_key_exists( $option['id'], $section_options ) ? maybe_unserialize( $section_options[ $option['id'] ] ) : get_option( $option['id'] );
				$this->output_field( $option, $value );
			}
			echo '</table>';
		}

		wp_die();
	}

	/**
	 * Checkbox input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.7
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $ignored_placeholder
	 */
	protected function input_checkbox( $option, $attributes, $value, $ignored_placeholder ) {
		if ( ! isset( $option['hidden_value'] ) ) {
			$option['hidden_value'] = '0';
		}
		?>
		<label>
		<input type="hidden" name="<?php echo esc_attr( $option['id'] ); ?>" value="<?php echo esc_attr( $option['hidden_value'] ); ?>" />
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			type="checkbox"
			value="1"
			<?php
			echo implode( ' ', $attributes ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			checked( '1', $value );
			?>
		/> <?php echo wp_kses_post( $option['cb_label'] ); ?></label>
		<?php
		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Checkbox input switch.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $ignored_placeholder
	 */
	protected function input_switch( $option, $attributes, $value, $ignored_placeholder ) {
		?>
		<div class="switch-container">
			<label class="switch">
				<input type="hidden" name="<?php echo esc_attr( $option['id'] ); ?>" value="0" />
				<input 
					id="setting-<?php echo esc_attr( $option['id'] ); ?>" 
					name="<?php echo esc_attr( $option['id'] ); ?>" 
					type="checkbox" 
					value="1" 
					<?php
					echo implode( ' ', $attributes ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					checked( '1', $value );
					?>
				/>
				<span class="switch-btn"><span data-on="<?php esc_html_e( 'on', 'cariera-core' ); ?>" data-off="<?php esc_html_e( 'off', 'cariera-core' ); ?>"></span></span>
			</label>
			<?php
			if ( ! empty( $option['description'] ) ) {
				echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Checkbox input multi switch.
	 *
	 * @since   1.8.9
	 * @version 1.8.9
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $values
	 * @param string $ignored_placeholder
	 */
	protected function input_multi_switch( $option, $attributes, $values, $ignored_placeholder ) {
		if ( empty( $option['options'] ) || ! is_array( $option['options'] ) ) {
			return;
		}
		?>
		<div class="multi-switch-container">
			<?php foreach ( $option['options'] as $key => $label ) { ?>
				<div class="switch-container">
					<label class="switch">
						<input type="hidden" name="<?php echo esc_attr( $option['id'] . '[' . $key . ']' ); ?>" value="0" />
						<input 
							id="setting-<?php echo esc_attr( $option['id'] . '-' . $key ); ?>" 
							name="<?php echo esc_attr( $option['id'] . '[' . $key . ']' ); ?>" 
							type="checkbox" 
							value="1"
							<?php
							echo implode( ' ', $attributes ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							checked( isset( $values[ $key ] ) ? $values[ $key ] : '0', '1' );
							?>
						/>
						<span class="switch-btn">
							<span data-on="<?php esc_html_e( 'on', 'cariera-core' ); ?>" data-off="<?php esc_html_e( 'off', 'cariera-core' ); ?>"></span>
						</span>
					</label>
					<label for="setting-<?php echo esc_attr( $option['id'] . '-' . $key ); ?>" class="switch-label">
						<?php echo esc_html( $label ); ?>
					</label>
				</div>
			<?php } ?>
		</div>

		<?php if ( ! empty( $option['description'] ) ) { ?>
			<p class="description"><?php echo wp_kses_post( $option['description'] ); ?></p>
			<?php
		}
	}

	/**
	 * Text area input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $placeholder
	 */
	protected function input_textarea( $option, $attributes, $value, $placeholder ) {
		?>
		<textarea
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			class="large-text"
			cols="50"
			rows="3"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			<?php
			echo implode( ' ', $attributes ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		>
			<?php echo esc_textarea( $value ); ?>
		</textarea>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Select input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $ignored_placeholder
	 */
	protected function input_select( $option, $attributes, $value, $ignored_placeholder ) {
		?>
		<select
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			autocomplete="off"
			<?php
			echo implode( ' ', $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		>
		<?php
		foreach ( $option['options'] as $key => $name ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $name ) . '</option>';
		}
		?>
		</select>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Multiple Choice field output (radio or checkbox).
	 *
	 * @since   1.7.7
	 * @version 1.7.7
	 *
	 * @param array  $option Option data.
	 * @param mixed  $value  Current value.
	 * @param string $type   'radio' or 'checkbox'.
	 */
	protected function multiple_choice_output( $option, $value, $type ) {
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo esc_html( $option['label'] ); ?></span>
			</legend>
			<?php
			if ( ! empty( $option['desc'] ) ) {
				echo ' <p class="description">' . wp_kses_post( $option['desc'] ) . '</p>';
			}
			foreach ( $option['options'] as $key => $name ) {
				$input_name = esc_attr( 'checkbox' === $type ? $option['id'] . '[fields][' . $key . ']' : $option['id'] );
				$input_type = esc_attr( $type );
				$is_checked = 'checkbox' === $type
					? checked( isset( $value['fields'] ) && is_array( $value['fields'] ) && in_array( $key, $value['fields'], true ), true, 0 )
					: checked( $value, $key, false );
				$label      = esc_html( $name );

				echo "<label><input name='{$input_name}' type='{$input_type}' value='{$key}' {$is_checked} />{$label}</label><br>"; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( ! empty( $option['description'] ) ) {
				echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
			}
			?>
		</fieldset>
		<?php
	}

	/**
	 * Radio input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.7
	 *
	 * @param array  $option
	 * @param array  $ignored_attributes
	 * @param mixed  $value
	 * @param string $ignored_placeholder
	 */
	protected function input_radio( $option, $ignored_attributes, $value, $ignored_placeholder ) {
		$this->multiple_choice_output( $option, $value, 'radio' );
	}

	/**
	 * Multiple Checkbox input field.
	 *
	 * @since   1.7.7
	 * @version 1.7.7
	 *
	 * @param array  $option
	 * @param array  $ignored_attributes
	 * @param mixed  $value
	 * @param string $ignored_placeholder
	 */
	protected function input_multi_checkbox( $option, $ignored_attributes, $value, $ignored_placeholder ) {
		$this->multiple_choice_output( $option, $value, 'checkbox' );
	}

	/**
	 * Editor input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $ignored_placeholder
	 */
	protected function input_editor( $option, $attributes, $value, $ignored_placeholder ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="editor-wrapper"' . implode( ' ', $attributes ) . '>';
		wp_editor(
			$value,
			$option['id'],
			[
				'textarea_name' => $option['id'],
				'editor_height' => 200,
			]
		);
		echo '</div>';
	}

	/**
	 * Editor input field.
	 *
	 * @since   1.4.8
	 * @version 2.0.0
	 *
	 * @param array $option
	 */
	protected function input_title( $option ) {
		?>
		<div class="settings-title <?php echo esc_attr( $option['id'] ); ?>">
			<h3 class="title">
				<?php echo esc_html( $option['title'] ); ?>
				<?php if ( ! empty( $option['version'] ) ) { ?>
					<small class="version">
						<?php echo esc_html( $option['version'] ); ?>
					</small>
				<?php } ?>
			</h3>
			<span class="description">
				<?php echo wp_kses_post( $option['description'] ); ?>
			</span>
		</div>
		<?php
	}

	/**
	 * Editor input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param string $placeholder
	 */
	protected function input_button( $option, $attributes, $placeholder ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<a href="#" class="cariera-btn" <?php echo implode( ' ', $attributes ) . ' '; ?>><?php echo esc_html( $option['title'] ); ?></a>
		<?php
		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
		?>
		<p class="message"></p>
		<?php
	}

	/**
	 * Page input field.
	 *
	 * @since   1.4.8
	 * @version 1.9.0
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $ignored_placeholder
	 */
	protected function input_page( $option, $attributes, $value, $ignored_placeholder ) {
		$args = [
			'name'             => $option['id'],
			'id'               => 'setting-' . $option['id'],
			'sort_column'      => 'menu_order',
			'sort_order'       => 'ASC',
			'show_option_none' => esc_html__( '--no page--', 'cariera-core' ),
			'echo'             => false,
			'selected'         => absint( $value ),
		];

		// Generate the dropdown HTML.
		$dropdown = wp_dropdown_pages( $args );

		// Insert the attributes into the <select> tag.
		$attributes_str = implode( ' ', $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$dropdown       = str_replace(
			'<select ',
			'<select ' . $attributes_str . ' data-placeholder="' . esc_attr__( 'Select a page&hellip;', 'cariera-core' ) . '" ',
			$dropdown
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe output.
		echo $dropdown;

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Hidden input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $ignored_placeholder
	 */
	protected function input_hidden( $option, $attributes, $value, $ignored_placeholder ) {
		$human_value = $value;
		if ( $option['human_value'] ) {
			$human_value = $option['human_value'];
		}
		?>
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			type="hidden"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php
			echo implode( ' ', $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		/><strong><?php echo esc_html( $human_value ); ?></strong>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Password input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $placeholder
	 */
	protected function input_password( $option, $attributes, $value, $placeholder ) {
		?>
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			class="regular-text"
			type="password"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php
			echo implode( ' ', $attributes ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		/>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Number input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $placeholder
	 */
	protected function input_number( $option, $attributes, $value, $placeholder ) {
		echo isset( $option['before'] ) ? wp_kses_post( $option['before'] ) : '';
		?>
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			class="small-text"
			type="number"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php
			echo implode( ' ', $attributes ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		/>
		<?php
		echo isset( $option['after'] ) ? wp_kses_post( $option['after'] ) : '';
		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Text input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $placeholder
	 */
	protected function input_text( $option, $attributes, $value, $placeholder ) {
		?>
		<input
			id="setting-<?php echo esc_attr( $option['id'] ); ?>"
			class="regular-text"
			type="text"
			name="<?php echo esc_attr( $option['id'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			<?php
			echo implode( ' ', $attributes ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		/>
		<?php

		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * Outputs the field row.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array $option
	 * @param mixed $value
	 */
	protected function output_field( $option, $value ) {
		$placeholder    = ! empty( $option['placeholder'] ) ? 'placeholder="' . esc_attr( $option['placeholder'] ) . '"' : '';
		$class          = ! empty( $option['class_wrapper'] ) ? $option['class_wrapper'] : '';
		$option['type'] = ! empty( $option['type'] ) ? $option['type'] : 'text';
		$attributes     = [];
		if ( ! empty( $option['attributes'] ) && is_array( $option['attributes'] ) ) {
			foreach ( $option['attributes'] as $attribute_name => $attribute_value ) {
				$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		echo '<tr valign="top" class="' . esc_attr( $class ) . '">';

		if ( ! empty( $option['label'] ) ) {
			echo '<th scope="row"><label for="setting-' . esc_attr( $option['id'] ) . '">' . esc_html( $option['label'] ) . '</a></th><td>';
		} else {
			echo '<td colspan="2">';
		}

		$method_name = 'input_' . $option['type'];
		if ( method_exists( $this, $method_name ) ) {
			$this->$method_name( $option, $attributes, $value, $placeholder );
		} else {
			/**
			 * Allows for custom fields in admin setting panes.
			 *
			 * @since 1.4.8
			 *
			 * @param string $option     Field name.
			 * @param array  $attributes Array of attributes.
			 * @param mixed  $value      Field value.
			 * @param string $placeholder      Placeholder text.
			 */
			do_action( 'cariera_admin_field_' . $option['type'], $option, $attributes, $value, $placeholder );
		}
		echo '</td></tr>';
	}

	/**
	 * Multiple settings stored in one setting array that are shown when the `enable` setting is checked.
	 *
	 * @since   1.4.8
	 * @version 1.7.7
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $values
	 * @param string $placeholder
	 */
	protected function input_multi_enable_expand( $option, $attributes, $values, $placeholder ) {
		if ( empty( $values ) ) {
			$values = [];
		}

		echo '<div class="setting-enable-expand">';
		$enable_option               = $option['enable_field'];
		$enable_option['id']         = $option['id'] . '[' . $enable_option['id'] . ']';
		$enable_option['type']       = 'checkbox';
		$enable_option['attributes'] = [ 'class="sub-settings-expander"' ];

		if ( isset( $enable_option['force_value'] ) && is_bool( $enable_option['force_value'] ) ) {
			if ( true === $enable_option['force_value'] ) {
				$values[ $option['enable_field']['name'] ] = '1';
			} else {
				$values[ $option['enable_field']['name'] ] = '0';
			}

			$enable_option['hidden_value'] = $values[ $option['enable_field']['name'] ];
			$enable_option['attributes'][] = 'disabled="disabled"';
		}

		$value = $values[ $option['enable_field']['name'] ] ?? '';

		$this->input_checkbox( $enable_option, $enable_option['attributes'], $value, null );

		echo '<div class="sub-settings-expandable">';
		$this->input_multi( $option, $attributes, $values, $placeholder );
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Multiple settings stored in one setting array.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $ignored_attributes
	 * @param mixed  $values
	 * @param string $ignored_placeholder
	 */
	protected function input_multi( $option, $ignored_attributes, $values, $ignored_placeholder ) {
		echo '<table class="form-table settings child-settings">';
		foreach ( $option['settings'] as $sub_option ) {
			$value            = isset( $values[ $sub_option['id'] ] ) ? $values[ $sub_option['id'] ] : $sub_option['default'];
			$sub_option['id'] = $option['id'] . '[' . $sub_option['id'] . ']';
			$this->output_field( $sub_option, $value );
		}
		echo '</table>';
	}

	/**
	 * Proxy for text input field.
	 *
	 * @since   1.4.8
	 * @version 1.7.3
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $placeholder
	 */
	protected function input_input( $option, $attributes, $value, $placeholder ) {
		$this->input_text( $option, $attributes, $value, $placeholder );
	}

	/**
	 * AJAX input field.
	 *
	 * @since   1.8.4
	 * @version 1.8.4
	 *
	 * @param array  $option
	 * @param array  $attributes
	 * @param mixed  $value
	 * @param string $placeholder
	 */
	protected function input_ajax( $option, $attributes, $value, $placeholder ) {
		?>
		<div class="ajax-wrapper">
			<?php if ( $option['input_field'] ) { ?>
				<input
					id="setting-<?php echo esc_attr( $option['id'] ); ?>"
					class="regular-text"
					type="text"
					name="<?php echo esc_attr( $option['id'] ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					data-field="<?php echo esc_attr( $option['ajax_action'] ); ?>"
					<?php
					echo implode( ' ', $attributes ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				/>
			<?php } ?>
	
			<button class="cariera-btn" data-action="<?php echo esc_attr( $option['ajax_action'] ); ?>"><?php echo esc_html( $option['btn_label'] ); ?></button>
		</div>
		
		<?php
		if ( ! empty( $option['description'] ) ) {
			echo ' <p class="description">' . wp_kses_post( $option['description'] ) . '</p>';
		}
	}

	/**
	 * A list of all the available currencies.
	 *
	 * @since 1.8.9
	 */
	protected function currencies() {
		$currency = [
			'none' => esc_html__( 'Disable Currency Symbol', 'cariera-core' ),
			'USD'  => esc_html__( 'US Dollars', 'cariera-core' ),
			'AED'  => esc_html__( 'United Arab Emirates Dirham', 'cariera-core' ),
			'ARS'  => esc_html__( 'Argentine Peso', 'cariera-core' ),
			'AUD'  => esc_html__( 'Australian Dollars', 'cariera-core' ),
			'BAM'  => esc_html__( 'Bosnian Mark', 'cariera-core' ),
			'BDT'  => esc_html__( 'Bangladeshi Taka', 'cariera-core' ),
			'BHD'  => esc_html__( 'Bahraini Dinar', 'cariera-core' ),
			'BRL'  => esc_html__( 'Brazilian Real', 'cariera-core' ),
			'BGN'  => esc_html__( 'Bulgarian Lev', 'cariera-core' ),
			'CAD'  => esc_html__( 'Canadian Dollars', 'cariera-core' ),
			'CLP'  => esc_html__( 'Chilean Peso', 'cariera-core' ),
			'CNY'  => esc_html__( 'Chinese Yuan', 'cariera-core' ),
			'COP'  => esc_html__( 'Colombian Peso', 'cariera-core' ),
			'CZK'  => esc_html__( 'Czech Koruna', 'cariera-core' ),
			'DKK'  => esc_html__( 'Danish Krone', 'cariera-core' ),
			'DOP'  => esc_html__( 'Dominican Peso', 'cariera-core' ),
			'EUR'  => esc_html__( 'Euros', 'cariera-core' ),
			'HKD'  => esc_html__( 'Hong Kong Dollar', 'cariera-core' ),
			'HRK'  => esc_html__( 'Croatia kuna', 'cariera-core' ),
			'HUF'  => esc_html__( 'Hungarian Forint', 'cariera-core' ),
			'ISK'  => esc_html__( 'Icelandic krona', 'cariera-core' ),
			'IDR'  => esc_html__( 'Indonesia Rupiah', 'cariera-core' ),
			'INR'  => esc_html__( 'Indian Rupee', 'cariera-core' ),
			'NPR'  => esc_html__( 'Nepali Rupee', 'cariera-core' ),
			'ILS'  => esc_html__( 'Israeli Shekel', 'cariera-core' ),
			'JPY'  => esc_html__( 'Japanese Yen', 'cariera-core' ),
			'KIP'  => esc_html__( 'Lao Kip', 'cariera-core' ),
			'KRW'  => esc_html__( 'South Korean Won', 'cariera-core' ),
			'LKR'  => esc_html__( 'Sri Lankan Rupee', 'cariera-core' ),
			'MYR'  => esc_html__( 'Malaysian Ringgits', 'cariera-core' ),
			'MXN'  => esc_html__( 'Mexican Peso', 'cariera-core' ),
			'NGN'  => esc_html__( 'Nigerian Naira', 'cariera-core' ),
			'NOK'  => esc_html__( 'Norwegian Krone', 'cariera-core' ),
			'NZD'  => esc_html__( 'New Zealand Dollar', 'cariera-core' ),
			'PYG'  => esc_html__( 'Paraguayan Guaraní', 'cariera-core' ),
			'PHP'  => esc_html__( 'Philippine Pesos', 'cariera-core' ),
			'PLN'  => esc_html__( 'Polish Zloty', 'cariera-core' ),
			'GBP'  => esc_html__( 'Pounds Sterling', 'cariera-core' ),
			'RON'  => esc_html__( 'Romanian Leu', 'cariera-core' ),
			'RUB'  => esc_html__( 'Russian Ruble', 'cariera-core' ),
			'SGD'  => esc_html__( 'Singapore Dollar', 'cariera-core' ),
			'ZAR'  => esc_html__( 'South African rand', 'cariera-core' ),
			'SEK'  => esc_html__( 'Swedish Krona', 'cariera-core' ),
			'CHF'  => esc_html__( 'Swiss Franc', 'cariera-core' ),
			'TWD'  => esc_html__( 'Taiwan New Dollars', 'cariera-core' ),
			'THB'  => esc_html__( 'Thai Baht', 'cariera-core' ),
			'TRY'  => esc_html__( 'Turkish Lira', 'cariera-core' ),
			'UAH'  => esc_html__( 'Ukrainian Hryvnia', 'cariera-core' ),
			'VND'  => esc_html__( 'Vietnamese Dong', 'cariera-core' ),
			'EGP'  => esc_html__( 'Egyptian Pound', 'cariera-core' ),
			'ZMK'  => esc_html__( 'Zambian Kwacha', 'cariera-core' ),
		];

		return apply_filters( 'cariera_core_currency', $currency );
	}
}
