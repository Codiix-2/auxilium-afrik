<?php

namespace Cariera\Onboarding;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/onboarding/cariera-license-manager.php';

class License {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	public $plugin_file = __FILE__;

	/**
	 * Response object after license activation.
	 *
	 * @var object
	 */
	public $response_obj;

	/**
	 * License message.
	 *
	 * @var string
	 */
	public $license_message;

	/**
	 * Flag to show a message.
	 *
	 * @var bool
	 */
	public $show_message = false;

	/**
	 * Theme slug.
	 *
	 * @var string
	 */
	public $slug = 'cariera';

	/**
	 * Option name for theme's license activation status.
	 *
	 * @var string
	 */
	private $license_activated_option = 'cariera_license_activated';

	/**
	 * Option name for license key.
	 *
	 * @var string
	 */
	private $license_key_name = 'Cariera_lic_Key';

	/**
	 * Option name for core bundle URL.
	 *
	 * @var string
	 */
	private $core_bundle_url_option = 'cariera_core_bundle_url';

	/**
	 * Core bundle URL.
	 *
	 * @var string
	 */
	private $core_bundle_url;

	/**
	 * Constructor initializes the license handling process.
	 */
	public function __construct() {
		// Retrieve the license key and email from options.
		$license_key   = $this->get_license_option( $this->license_key_name );
		$license_email = get_option( 'Cariera_lic_email', '' );

		// Set up cleanup action on deletion.
		\Cariera_License_Manager::add_on_delete_callback( [ $this, 'cleanup_license_data' ] );

		// Handle license activation or deactivation based on retrieved key and email.
		$this->license_handling( $license_key, $license_email );
		$this->deactivate_core_plugin();

		// Register actions for checking license status and getting bundled plugins URL.
		add_action( 'cariera_check_license_activated', [ $this, 'check_license_activated' ] );
		add_action( 'wp_ajax_get_bundled_plugins_url', [ $this, 'get_bundled_plugins_url' ] );
	}

	/**
	 * Handles license activation and deactivation.
	 *
	 * @since   1.8.3
	 * @version 1.9.3
	 *
	 * @param string $license_key   License key.
	 * @param string $license_email License email.
	 */
	private function license_handling( $license_key, $license_email ) {
		// Get the path to the stylesheet directory.
		$template_dir = get_stylesheet_directory();

		// Check if the license is active by validating the key and email.
		$is_license_active = $this->is_license_active( $license_key, $license_email, $template_dir . '/style.css' );

		// Define the hooks to be added based on the license status.
		$active_hooks = [
			'cariera_onboarding_license'               => 'activated', // Hook to handle onboarding when license is active.
			'admin_post_Cariera_el_deactivate_license' => 'action_deactivate_license', // Hook for deactivating the license.
			'cariera_onboarding_license_sidebar'       => 'sidebar_support_expired', // Hook for showing support expired in the sidebar.
			'admin_notices'                            => 'support_expired_notice', // Hook for displaying an admin notice if support expired.
		];

		$inactive_hooks = [
			'cariera_onboarding_license'             => 'license_form', // Hook to display the license form when not active.
			'admin_post_Cariera_el_activate_license' => 'action_activate_license', // Hook for activating the license.
			'admin_notices'                          => 'license_activation_notice', // Hook for showing an activation notice.
		];

		if ( $is_license_active ) {
			// If the license is active, set the core bundle URL from the response object.
			$this->core_bundle_url = $this->response_obj->plugins_url;

			// Update or add options related to license activation and core bundle URL.
			$this->update_options(
				[
					$this->license_activated_option => true,
					$this->core_bundle_url_option   => $this->encrypt_data( $this->core_bundle_url ),
				]
			);

			// Register hooks related to an active license.
			$this->add_license_hooks( $active_hooks );
		} else {
			// If the license is not active, clear the options related to license activation and core bundle URL.
			$this->update_options(
				[
					$this->license_activated_option => '',
					$this->core_bundle_url_option   => '',
				]
			);

			// Set flag to show a message if the license key and message are provided.
			if ( ! empty( $license_key ) && ! empty( $this->license_message ) ) {
				$this->show_message = true;
			}

			// Register hooks related to an inactive license.
			$this->add_license_hooks( $inactive_hooks );
		}
	}

	/**
	 * Activates the license.
	 *
	 * @since   1.5.1
	 * @version 1.9.5
	 */
	public function action_activate_license() {
		check_admin_referer( 'el-license' );

		$license_key   = ! empty( $_POST['el_license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['el_license_key'] ) ) : '';
		$license_email = ! empty( $_POST['el_license_email'] ) ? sanitize_email( wp_unslash( $_POST['el_license_email'] ) ) : '';

		// Validate the license key before storing it.
		$this->response_obj    = null;
		$this->license_message = '';
		$is_active             = \Cariera_License_Manager::check_wp_plugin( $license_key, $license_email, $this->license_message, $this->response_obj, get_stylesheet_directory() . '/style.css' );

		if ( $is_active && ! empty( $this->response_obj ) ) {
			$this->update_options(
				[
					$this->license_activated_option => true,
					$this->license_key_name         => $license_key,
					'Cariera_lic_email'             => $license_email,
					'_site_transient_update_themes' => '',
				]
			);
		} else {
			// Store error message in a persistent option.
			update_option( 'cariera_license_error_message', $this->license_message );

			// Clear license-related options to prevent false activation.
			$this->update_options(
				[
					$this->license_activated_option => '',
					$this->license_key_name         => '',
					$this->core_bundle_url_option   => '',
				]
			);
			delete_transient( 'cariera_license_status_' . md5( $license_key . $license_email ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=cariera_theme' ) );
		exit;
	}

	/**
	 * Deactivates the license.
	 *
	 * @since   1.5.1
	 * @version 1.9.5
	 */
	public function action_deactivate_license() {
		check_admin_referer( 'el-license' );

		$message       = '';
		$license_key   = $this->get_license_option( $this->license_key_name );
		$license_email = get_option( 'Cariera_lic_email', '' );
		$transient_key = 'cariera_license_status_' . md5( $license_key . $license_email );

		$license_deactivation = \Cariera_License_Manager::remove_license_key( __FILE__, $message, $license_key, $license_email );

		$this->update_options(
			[
				$this->license_activated_option => '',
				$this->core_bundle_url_option   => '',
				$this->license_key_name         => '',
				'_site_transient_update_themes' => '',
			]
		);

		// Delete transient_key.
		delete_transient( $transient_key );

		// Reset response_obj.
		$this->response_obj = null;

		wp_safe_redirect( admin_url( 'admin.php?page=cariera_theme' ) );
		exit;
	}

	/**
	 * License activation form
	 *
	 * @since   1.5.1
	 * @version 1.9.4
	 */
	public function license_form() {
		$this->license_message = get_option( 'cariera_license_error_message', '' );

		if ( ! empty( $this->license_message ) ) {
			$this->show_message = true;

			// Delete message after use.
			delete_option( 'cariera_license_error_message' );
		}
		?>
		<form method="post" @submit="licenseSubmit" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="Cariera_el_activate_license"/>

			<?php
			if ( ! empty( $this->show_message ) && ! empty( $this->license_message ) ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( $this->license_message ); ?></p>
				</div>
			<?php } ?>

			<div class="el-license-field">
				<label for="el_license_key"><?php esc_html_e( 'License code', 'cariera' ); ?></label>
				<span class="description"><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank"><?php esc_html_e( 'How to get purchase code?', 'cariera' ); ?></a></span>

				<input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required" autocomplete="off">
			</div>

			<div class="el-license-field">
				<label for="el_license_key"><?php esc_html_e( 'Your Email Address', 'cariera' ); ?></label>

				<?php $purchase_email = get_option( 'Cariera_lic_email', get_bloginfo( 'admin_email' ) ); ?>
				<input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo esc_attr( $purchase_email ); ?>" required="required">
			</div>

			<div class="el-license-active-btn">
				<?php wp_nonce_field( 'el-license' ); ?>
				<button class="cariera-btn" :class="submitting ? 'loading' : ''" type="submit"><?php esc_html_e( 'Activate License', 'cariera' ); ?></button>
			</div>
		</form>
		<?php
	}

	/**
	 * License deactivation form
	 *
	 * @since   1.5.1
	 * @version 1.9.7
	 */
	public function activated() {
		?>
		<form method="post" @submit="licenseSubmit" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="Cariera_el_deactivate_license"/>

			<h3 class="el-license-title"><?php esc_html_e( 'License Info:', 'cariera' ); ?><span class="license"><?php echo esc_html( substr( $this->response_obj->license_key, 0, 9 ) . 'XXXXXXXX-XXXXXXXX' . substr( $this->response_obj->license_key, -9 ) ); ?></span></h3>
			<hr>

			<h3><?php esc_html_e( 'Thank you for choosing Cariera!', 'cariera' ); ?></h3>            
			<p><?php esc_html_e( 'You now have full access to utilize the theme on this domain. In the event that you choose to relocate your site to another domain, please ensure to deactivate the license by clicking the button below. Afterwards, you can proceed to activate the license on your new domain.', 'cariera' ); ?></p>

			<div class="el-license-active-btn">
				<?php wp_nonce_field( 'el-license' ); ?>
				<button class="cariera-btn" :class="submitting ? 'loading' : ''" type="submit"><?php esc_html_e( 'Deactivate License', 'cariera' ); ?></button>
			</div>
		</form>
		<?php
	}

	/**
	 * License info
	 *
	 * @since 1.5.1
	 */
	public function sidebar_support_expired() {
		$today = gmdate( 'Y-m-d H:i:s' );

		// Support is still valid.
		if ( $this->response_obj->support_end > $today ) {
			?>
			<div class="license-support valid">
				<h3 class="title"><?php esc_html_e( 'Support is valid', 'cariera' ); ?></h3>
				<p><?php esc_html_e( 'Your support ends on:', 'cariera' ); ?></p>
				<span class="support-date"><?php echo esc_html( $this->response_obj->support_end ); ?></span>
			</div>
			<?php
			// Support has expired.
		} else {
			?>
			<div class="license-support expired">
				<h3 class="title"><?php esc_html_e( 'Support has expired!', 'cariera' ); ?></h3>
				<p><?php esc_html_e( 'Your support expired on:', 'cariera' ); ?></p>
				<span class="support-date"><?php echo esc_html( $this->response_obj->support_end ); ?></span>
				<a target="_blank" class="renew-btn" href="<?php echo esc_url( $this->response_obj->support_renew_link ); ?>"><?php esc_html_e( 'Renew Support', 'cariera' ); ?></a>
			</div>
			<?php
		}
	}

	/**
	 * License activation required admin notice
	 *
	 * @since   1.5.1
	 * @version 1.8.8
	 */
	public function license_activation_notice() {
		// Return if license is active.
		if ( $this->activation_status() ) {
			return;
		}

		\Cariera\Notices::add_notice(
			'cariera_license_activation', // ID.
			esc_html__( 'Cariera has not been activated!', 'cariera' ), // Title.
			esc_html__( 'Make sure to activate your license to be able to use all core functionalities.', 'cariera' ),
			'info', // Type.
			true, // Dismissible.
			esc_url( admin_url( 'admin.php?page=cariera_theme' ) ),
			esc_html__( 'Activate Cariera License', 'cariera' )
		);
	}

	/**
	 * Support has expired admin notice
	 *
	 * @since   1.5.1
	 * @version 1.8.8
	 */
	public function support_expired_notice() {
		$today = gmdate( 'Y-m-d H:i:s' );

		// Return if license is active and support is still valid.
		if ( $this->activation_status() && $this->response_obj->support_end > $today ) {
			return;
		}

		\Cariera\Notices::add_notice(
			'cariera_support_expired', // ID.
			esc_html__( 'Support Expired', 'cariera' ), // Title.
			esc_html__( 'Your support for Cariera has expired.', 'cariera' ),
			'error', // Type.
			true, // Dismissible.
			esc_url( $this->response_obj->support_renew_link ),
			esc_html__( 'Renew Support', 'cariera' )
		);
	}

	/**
	 * Deactivate Core plugin when deactivate theme license
	 *
	 * @since   1.6.3
	 * @version 1.9.4
	 */
	public function deactivate_core_plugin() {
		$plugins_to_deactivate = [
			'cariera-plugin/cariera-core.php',
			'cariera-addons/cariera-addons.php',
		];

		// Ensure the function is available.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $plugins_to_deactivate as $plugin_basename ) {
			if ( $this->activation_status() || ! is_plugin_active( $plugin_basename ) ) {
				continue;
			}

			$result = deactivate_plugins( $plugin_basename, true );

			if ( is_wp_error( $result ) ) {
				\Cariera\write_log( sprintf( 'Failed to deactivate plugin %s: %s', $plugin_basename, $result->get_error_message() ) );
			}

			// Clear any cached data related to the plugin.
			wp_cache_delete( $plugin_basename, 'plugins' );
		}
	}

	/**
	 * Check if license has been activated.
	 *
	 * @since   1.8.3
	 * @version 1.8.7
	 */
	public function check_license_activated() {
		// Retrieve license key and email from options.
		$license_status = get_option( 'cariera_license_activated' );
		$license_key    = get_option( $this->license_key_name );
		$license_email  = get_option( 'Cariera_lic_email' );

		// If the license is not active, update or add the necessary options.
		if ( empty( $license_status ) || empty( $license_key ) ) {
			$this->update_options(
				[
					$this->license_activated_option => '',
					$this->core_bundle_url_option   => '',
				]
			);
		}
	}

	/**
	 * Get bundled plugins URL via AJAX.
	 *
	 * @since   1.8.3
	 * @version 2.0.0
	 */
	public function get_bundled_plugins_url() {
		// Verify nonce for security.
		if ( ! check_ajax_referer( 'cariera_onboarding_nonce', 'wpnonce', false ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid nonce', 'cariera' ) ] );
			return;
		}

		// Retrieve license data.
		$license_activated = get_option( $this->license_activated_option );
		$license_key       = get_option( $this->license_key_name );
		$license_email     = get_option( 'Cariera_lic_email' );
		$encrypted_bundle  = get_option( $this->core_bundle_url_option );

		// Check if license is activated.
		if ( ! $license_activated || ! $license_key ) {
			$this->update_options(
				[
					$this->license_activated_option => '',
					$this->core_bundle_url_option   => '',
					$this->license_key_name         => '',
				]
			);

			delete_transient( 'cariera_license_status_' . md5( $license_key . $license_email ) );

			wp_send_json_error( [ 'message' => esc_html__( 'The theme has not been activated with a valid license code.', 'cariera' ) ] );
			return;
		}

		// Rate-limiting by user.
		$user_identifier = get_current_user_id();
		$rate_transient  = "cariera_get_bundle_plugins_rate_limit_{$user_identifier}";
		$attempts        = get_transient( $rate_transient );

		if ( $attempts && $attempts >= 3 ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You have reached the maximum number of attempts. Please try again in an hour.', 'cariera' ) ] );
			return;
		}

		if ( ! $attempts ) {
			set_transient( $rate_transient, 1, HOUR_IN_SECONDS );
		} else {
			set_transient( $rate_transient, $attempts + 1, HOUR_IN_SECONDS );
		}

		// Decrypt the existing bundle URL.
		$old_bundled_url = '';
		if ( ! empty( $encrypted_bundle ) ) {
			$old_bundled_url = $this->decrypt_data( $encrypted_bundle );
			if ( ! is_string( $old_bundled_url ) ) {
				$old_bundled_url = '';
			}
		}

		// Get the new plugin bundle URL.
		$this->core_bundle_url = \Cariera_License_Manager::check_plugins_url(
			$license_key,
			$license_email,
			$this->license_message,
			$this->response_obj,
			get_stylesheet_directory() . '/style.css'
		);

		if ( empty( $this->core_bundle_url ) || is_wp_error( $this->core_bundle_url ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Failed to retrieve the plugin bundle URL. Your server can not connect to the licensing server.', 'cariera' ) ] );
			return;
		}

		// Version check.
		$current_version = wp_get_theme( get_template() )->get( 'Version' );
		$latest_version  = $this->response_obj->latest_version;

		if ( version_compare( $current_version, $latest_version, '<' ) ) {
			wp_send_json_error(
				[
					'message' => sprintf(
						// translators: 1. Latest version number.
						esc_html__( 'To refresh the plugin URLs, please update to the latest version of the theme: %s.', 'cariera' ),
						esc_html( $latest_version )
					),
				]
			);
			return;
		}

		// Compare old vs new URLs.
		if ( $old_bundled_url === $this->core_bundle_url ) {
			wp_send_json_success(
				[
					'message' => esc_html__( 'No need to proceed further, the URLs are identical.', 'cariera' ),
				]
			);
			return;
		}

		// Save new encrypted bundle URL.
		$encrypted_new_url = $this->encrypt_data( $this->core_bundle_url );

		if ( ! update_option( $this->core_bundle_url_option, $encrypted_new_url ) ) {
			add_option( $this->core_bundle_url_option, $encrypted_new_url );
		}

		// Update cached transient.
		$license_transient_key = 'cariera_license_status_' . md5( $license_key . $license_email );
		$cached_data           = get_transient( $license_transient_key );

		if ( $cached_data && isset( $cached_data['response_obj'] ) ) {
			$cached_data['response_obj']->plugins_url = $this->core_bundle_url;
			set_transient( $license_transient_key, $cached_data, 12 * HOUR_IN_SECONDS );
		}

		// Prepare response data.
		$response = [
			'message' => esc_html__( 'Bundle URL updated successfully.', 'cariera' ),
			'new_url' => esc_url( $this->core_bundle_url ),
		];

		// Send success.
		wp_send_json_success( $response );
	}

	/**
	 * Get activation status of the theme
	 *
	 * @since   1.5.1
	 * @version 1.8.5
	 */
	private function activation_status() {
		return ! empty( get_option( $this->license_activated_option ) ) && ! empty( get_option( $this->license_key_name ) );
	}

	/**
	 * Checks if the license is active.
	 *
	 * @since   1.8.3
	 * @version 1.9.5
	 *
	 * @param string $license_key   The license key to check.
	 * @param string $license_email The email associated with the license.
	 * @param string $template_dir  Path to the template directory.
	 * @return bool True if the license is active, false otherwise.
	 */
	private function is_license_active( $license_key, $license_email, $template_dir ) {
		// No need to continue if the theme is not active.
		if ( ! $this->activation_status() ) {
			$this->response_obj = null;
			return false;
		}

		// Get current domain (normalized).
		$current_domain = $this->get_normalized_domain();

		// Define a unique transient key based on the license key and email.
		$transient_key = 'cariera_license_status_' . md5( $license_key . $license_email );

		// Attempt to retrieve the cached data (activation status + response object).
		$cached_data = get_transient( $transient_key );

		// Domain changed detection.
		if ( false !== $cached_data && isset( $cached_data['domain'] ) ) {
			if ( $cached_data['domain'] !== $current_domain ) {
				delete_transient( $transient_key );
				$cached_data = false;
			}
		}

		if ( false === $cached_data || ! isset( $cached_data['is_active'], $cached_data['response_obj'] ) ) {
			// \Cariera\write_log( 'No valid transient found or transient is corrupted. Clearing and fetching new data.' );

			// Reset response_obj before validation.
			$this->response_obj = null;

			// Delete transient_key.
			delete_transient( $transient_key );

			// If no cached status exists, make the HTTP request to the license server.
			$is_active = \Cariera_License_Manager::check_wp_plugin(
				$license_key,
				$license_email,
				$this->license_message,
				$this->response_obj,
				$template_dir
			);

			// Cache the result (status + response data).
			$cached_data = [
				'is_active'    => $is_active,
				'response_obj' => $this->response_obj, // Cache the full response object.
				'domain'       => $current_domain,
			];

			// Cache the result for a specific time period (e.g., 2 hour).
			set_transient( $transient_key, $cached_data, 2 * HOUR_IN_SECONDS );

			return $is_active;
		}

		// Use cached data to set the response object and return activation status.
		$this->response_obj = $cached_data['response_obj'];

		return $cached_data['is_active'];
	}

	/**
	 * Get normalized domain.
	 *
	 * @since 1.9.0
	 */
	private function get_normalized_domain() {
		$domain = site_url();
		$domain = preg_replace( '#^https?://#', '', $domain );
		$domain = preg_replace( '#^www\.#', '', $domain );
		$domain = trim( $domain, '/' );
		return strtolower( $domain );
	}

	/**
	 * Retrieves the license key option, ensuring it is not an empty string.
	 *
	 * If the license key is empty, attempts to clean up by updating and then clearing the option.
	 *
	 * @since 1.8.3
	 *
	 * @param string $option_name The name of the option to retrieve.
	 * @return string The retrieved license key.
	 */
	private function get_license_option( $option_name ) {
		$license_key = get_option( $option_name, '' );

		// If the license key is empty, attempt to clean up.
		if ( empty( $license_key ) ) {
			// Check again if there is a license key set and update it.
			$license_key = get_option( $option_name, '' );
			if ( ! empty( $license_key ) ) {
				$this->update_option( $option_name, $license_key );
				$this->update_option( $option_name, '' );
			}
		}

		return $license_key;
	}

	/**
	 * Updates multiple options at once.
	 *
	 * @since 1.8.3
	 *
	 * @param array $options Associative array of option names and values.
	 */
	private function update_options( array $options ) {
		foreach ( $options as $option_name => $value ) {
			update_option( $option_name, $value ) || add_option( $option_name, $value );
		}
	}

	/**
	 * Registers multiple action hooks.
	 *
	 * @since 1.8.3
	 *
	 * @param array $hooks Associative array of hooks and their corresponding methods.
	 */
	private function add_license_hooks( array $hooks ) {
		foreach ( $hooks as $hook => $method ) {
			add_action( $hook, [ $this, $method ] );
		}
	}

	/**
	 * Cleans up license-related data on plugin deletion.
	 *
	 * @since 1.8.3
	 */
	private function cleanup_license_data() {
		$this->update_options(
			[
				$this->license_activated_option => '',
				$this->core_bundle_url_option   => '',
				$this->license_key_name         => '',
			]
		);

		delete_option( 'Cariera_lic_email' );
	}

	/**
	 * Encrypt data
	 *
	 * @since 1.8.6
	 *
	 * @param string $data
	 */
	private function encrypt_data( $data ) {
		$license_key = get_option( $this->license_key_name );

		$key = hash( 'sha256', $license_key );
		$iv  = substr( hash( 'sha256', $license_key ), 0, 16 );
		return openssl_encrypt( $data, 'AES-256-CBC', $key, 0, $iv );
	}

	/**
	 * Decrypt data
	 *
	 * @since 1.8.6
	 *
	 * @param string $data
	 */
	private function decrypt_data( $data ) {
		$license_key = get_option( $this->license_key_name );

		$key = hash( 'sha256', $license_key );
		$iv  = substr( hash( 'sha256', $license_key ), 0, 16 );
		return openssl_decrypt( $data, 'AES-256-CBC', $key, 0, $iv );
	}

	/**
	 * Core bundle URL
	 *
	 * @since 1.8.6
	 */
	public function core_bundle_url() {
		return $this->decrypt_data( get_option( 'cariera_core_bundle_url' ) );
	}
}
