<?php

namespace Cariera_Core\Extensions\Envato;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Envato {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Cariera's ID on ThemeForest
	 *
	 * @var integer
	 */
	private $theme_id = 20167356;

	/**
	 * Plugin ID for third-party plugins
	 *
	 * @var int
	 */
	private $plugin_id;

	/**
	 * Theme's purchase code
	 *
	 * @var string
	 */
	private $purchase_code;

	/**
	 * API Base URL for Envato.
	 */
	private const ENVATO_API_URL = 'https://api.envato.com/v3/market/buyer/';

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Check Envato connection.
		add_action( 'wp_ajax_cariera_envato_api_connection', [ $this, 'envato_api_connection' ] );

		// Check for theme updates.
		add_action( 'wp_ajax_cariera_check_theme_update', [ $this, 'check_for_update' ] );

		// Check for theme update.
		add_action( 'cariera_check_theme_update_cron', [ $this, 'cron_check_for_update' ] );

		// Install theme update.
		add_action( 'wp_ajax_cariera_install_theme_update', [ $this, 'install_theme_update' ] );

		// Inject theme updates into the response array.
		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'check_for_theme_update' ], 1, 99999 );
		add_filter( 'pre_set_transient_update_themes', [ $this, 'check_for_theme_update' ], 1, 99999 );
	}

	/**
	 * Handles Envato API connection and theme update check.
	 *
	 * @since 1.8.4
	 */
	public function envato_api_connection() {
		$this->validate_and_process_envato_request();

		wp_send_json_success( esc_html__( 'You have a successfull connection to the Envato API.', 'cariera-core' ) );
	}

	/**
	 * Check for theme updates.
	 *
	 * @since 1.8.4
	 */
	public function check_for_update() {
		$this->validate_and_process_envato_request();

		$purchase_data = $this->get_purchase_data( get_option( 'cariera_envato_api_token' ) );
		$this->compare_versions( $purchase_data['item']['wordpress_theme_metadata']['version'], $purchase_data );
	}

	/**
	 * Validate request and ensure Envato token and purchase code exist.
	 *
	 * @since 1.8.4
	 */
	private function validate_and_process_envato_request() {
		check_ajax_referer( '_cariera_core_admin_nonce', 'nonce' );

		$this->purchase_code = get_option( 'Cariera_lic_Key' );
		$envato_token        = isset( $_POST['field'] ) ? sanitize_text_field( wp_unslash( $_POST['field'] ) ) : get_option( 'cariera_envato_api_token' );

		if ( empty( $this->purchase_code ) ) {
			wp_send_json_error( esc_html__( 'Purchase Code is missing!', 'cariera-core' ) );
		}

		if ( empty( $envato_token ) ) {
			wp_send_json_error( esc_html__( 'Envato API Token is missing!', 'cariera-core' ) );
		}

		$purchase_data = $this->get_purchase_data( $envato_token );
		$this->validate_purchase_data( $purchase_data );
	}

	/**
	 * Check for theme update via the transient
	 *
	 * @since 1.8.4
	 *
	 * @param [type] $transient
	 */
	public function check_for_theme_update( $transient ) {
		// Check if the transient has the 'checked' property.
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Get the current theme's data.
		$theme_slug = 'cariera';
		$theme      = wp_get_theme( $theme_slug );

		// Check if your theme is installed and the current version is less than the new version.
		if ( isset( $transient->response[ $theme_slug ] ) ) {
			$theme_response  = $transient->response[ $theme_slug ];
			$new_version     = $theme_response->new_version ?? '';
			$current_version = $theme->get( 'Version' );

			if ( ! empty( $new_version ) && version_compare( $current_version, $new_version, '<' ) ) {
				$package = $this->download();

				// Check if the package is a WP_Error instance.
				if ( is_wp_error( $package ) ) {
					$package = '';
				}

				\Cariera\write_log( 'This is the package: ' . $package );

				// Update the transient with the new version info.
				$transient->response[ $theme_slug ] = [
					'theme'       => $theme_slug,
					'new_version' => $new_version,
					'url'         => esc_url( $theme_response->url ?? '' ),
					'package'     => $package,
				];
			}
		}

		return $transient;
	}

	/**
	 * Set theme update transient
	 *
	 * @since 1.8.4
	 *
	 * @param [type] $new_version
	 * @param [type] $theme_url
	 */
	private function set_theme_update_transient( $new_version, $theme_url ) {
		$update_data = $this->prepare_update_response( $new_version, $theme_url );
		$transient   = get_site_transient( 'update_themes' );

		if ( false === $transient ) {
			\Cariera\write_log( 'Failed to set theme update transient.' );
		}

		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		if ( empty( $transient->response ) ) {
			$transient->response = [];
		}

		$transient->response['cariera'] = (object) $update_data;

		// Set the updated transient with a longer expiration time (1 hour).
		set_site_transient( 'update_themes', $transient, HOUR_IN_SECONDS );
	}

	/**
	 * Retrieve purchase data from the Envato API via purchase code.
	 *
	 * @since   1.8.4
	 * @version 1.8.8
	 *
	 * @param string $envato_token Envato API token.
	 * @return array $purchase_data API response data.
	 */
	private function get_purchase_data( $envato_token ) {
		$api_url  = self::ENVATO_API_URL . 'purchase?code=' . $this->purchase_code;
		$response = wp_remote_get(
			$api_url,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $envato_token,
				],
			]
		);

		// Handle any errors in the API request.
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( esc_html__( 'Error connecting to Envato API.', 'cariera-core' ) );
		}

		$purchase_data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $purchase_data['error'] ) ) {
			wp_send_json_error( $purchase_data['error'] );
		}

		if ( ! isset( $purchase_data['item'] ) ) {
			wp_send_json_error( esc_html__( 'No purchase data returned from Envato API.', 'cariera-core' ) );
		}

		return $purchase_data;
	}

	/**
	 * Retrieve purchase data from the Envato API via token.
	 *
	 * @since   1.8.4
	 * @version 1.8.8
	 *
	 * @param string $envato_token  Envato API token.
	 * @param string $item_id       Plugin ID from envato.
	 * @return array $purchase_data API response data.
	 */
	protected function get_purchase_data_by_token( $envato_token, $item_id ) {
		if ( empty( $envato_token ) ) {
			wp_send_json_error( esc_html__( 'API token is missing.', 'cariera-core' ) );
		}

		$api_url = self::ENVATO_API_URL . 'list-purchases';

		// Perform the API request.
		$response = wp_remote_get(
			$api_url,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $envato_token,
				],
			]
		);

		// Handle any errors in the API request.
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( esc_html__( 'Error connecting to Envato API.', 'cariera-core' ) );
		}

		// Decode the API response.
		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );

		// Handle API-specific errors.
		if ( isset( $response_data['error'] ) ) {
			wp_send_json_error( $response_data['error'] );
		}

		// Find the purchase data for the given item ID.
		foreach ( $response_data['results'] as $purchase ) {
			if ( isset( $purchase['item']['id'] ) && $purchase['item']['id'] == $item_id ) {
				return $purchase['item'];
			}
		}

		// If no matching purchase was found, return an error.
		wp_send_json_error( esc_html__( 'No purchases found for the provided item ID.', 'cariera-core' ) );
	}

	/**
	 * Validate the purchase data.
	 *
	 * @since 1.8.4
	 *
	 * @param array $purchase_data The data returned by the API.
	 */
	private function validate_purchase_data( $purchase_data ) {
		if ( ! isset( $purchase_data['item']['id'] ) ) {
			wp_send_json_error( esc_html__( 'No theme ID found in the purchase data.', 'cariera-core' ) );
		}

		if ( $purchase_data['item']['id'] !== $this->theme_id ) {
			wp_send_json_error( esc_html__( 'Theme ID mismatch. The purchase code does not correspond to this theme.', 'cariera-core' ) );
		}
	}

	/**
	 * Compare the current and new theme versions.
	 *
	 * @since   1.8.4
	 * @version 2.0.0
	 *
	 * @param string $new_version The new version of the theme.
	 * @param array  $purchase_data
	 */
	private function compare_versions( $new_version, $purchase_data ) {
		$theme_data      = wp_get_theme();
		$current_version = is_child_theme() ? $theme_data->parent()->get( 'Version' ) : $theme_data->get( 'Version' );

		// Perform version comparison once and store result.
		$version_comparison = version_compare( $new_version, $current_version );

		switch ( $version_comparison ) {
			case 1:
				// New version available, set transient.
				$this->set_theme_update_transient( $new_version, $purchase_data['item']['url'] );
				$message = sprintf(
					__( 'A theme update is available: upgrade from <strong>%1$s</strong> to <strong>%2$s</strong> now!<br> View the full <a href="%3$s" target="_blank">changelog</a>.', 'cariera-core' ),
					esc_html( $current_version ),
					esc_html( $new_version ),
					esc_url( 'https://1.envato.market/cariera-changelog' )
				);
				wp_send_json_success(
					[
						'message' => wp_kses_post( $message ),
						'button'  => '<a href="#" class="install-update">' . esc_html__( 'Install Update', 'cariera-core' ) . '</a><span class="updating">' . esc_html__( 'Updating...', 'cariera-core' ) . '</span>',
					]
				);
				break;

			case -1:
				$message = sprintf(
					__( 'Your theme version <strong>(%1$s)</strong> is newer than the available update version <strong>(%2$s)</strong>.', 'cariera-core' ),
					esc_html( $current_version ),
					esc_html( $new_version )
				);
				wp_send_json_success( [ 'message' => wp_kses_post( $message ) ] );
				break;

			default:
				$message = sprintf(
					__( 'Your theme version <strong>(%1$s)</strong> is up to date.', 'cariera-core' ),
					esc_html( $current_version )
				);
				wp_send_json_success( [ 'message' => wp_kses_post( $message ) ] );
				break;
		}

		// If theme is using the latest version.
		wp_send_json_success( esc_html__( 'Your theme is up to date!', 'cariera-core' ) );
	}

	/**
	 * Prepare the response for theme updates.
	 *
	 * @since 1.8.4
	 *
	 * @param string $new_version
	 * @param string $theme_url
	 */
	private function prepare_update_response( string $new_version, string $theme_url ): array {
		return [
			'theme'       => 'cariera',
			'new_version' => $new_version,
			'url'         => esc_url( $theme_url ),
			'package'     => $this->download(),
		];
	}

	/**
	 * Download the latest update.
	 *
	 * @since 1.8.4
	 */
	private function download() {
		$api = 'https://api.envato.com/v3/market/buyer/download';
		$url = $api . '?item_id=' . $this->theme_id . '&shorten_url=true';

		// Retrieve the Envato token from options (or wherever it is stored).
		$envato_token = get_option( 'cariera_envato_api_token' );

		// Ensure the token is available.
		if ( empty( $envato_token ) ) {
			return new \WP_Error( 'no_token', __( 'Envato API token is missing.', 'cariera-core' ) );
		}

		// Prepare the request arguments.
		$args = [
			'headers' => [
				'Authorization' => 'Bearer ' . $envato_token,
			],
		];

		$response = wp_remote_get( $url, $args );

		// Handle errors during the request.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Parse the response body.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Handle API errors or invalid responses.
		if ( empty( $data ) || isset( $data['error'] ) ) {
			\Cariera\write_log( 'Error fetching download URL from Envato API: ' . $body );
			return new \WP_Error( 'api_error', __( 'Error fetching download URL from Envato API.', 'cariera-core' ) );
		}

		// Check if the response contains the theme or plugin URL.
		if ( ! empty( $data['wordpress_theme'] ) ) {
			return esc_url( $data['wordpress_theme'] );
		}

		return new \WP_Error( 'no_download_url', __( 'No valid download URL found in the API response.', 'cariera-core' ) );
	}

	/**
	 * Cron check for theme updates.
	 *
	 * @since 1.8.4
	 */
	public function cron_check_for_update() {
		// Validate API connection.
		$this->purchase_code = get_option( 'Cariera_lic_Key' );
		$envato_token        = get_option( 'cariera_envato_api_token' );

		if ( empty( $this->purchase_code ) || empty( $envato_token ) ) {
			return;
		}

		// Perform the update check.
		$purchase_data = $this->get_purchase_data( $envato_token );
		$this->compare_versions( $purchase_data['item']['wordpress_theme_metadata']['version'], $purchase_data );
	}

	/**
	 * Install theme update ajax
	 *
	 * @since 1.8.4
	 */
	public function install_theme_update() {
		$this->validate_and_process_envato_request();

		// Get the latest purchase data.
		$purchase_data = $this->get_purchase_data( get_option( 'cariera_envato_api_token' ) );

		// Check if we got valid purchase data.
		if ( is_wp_error( $purchase_data ) ) {
			wp_send_json_error( __( 'Error fetching purchase data from Envato API. TESTOOO', 'cariera-core' ) );
		}

		// Check if the new version is available.
		$new_version     = $purchase_data['item']['wordpress_theme_metadata']['version'];
		$current_version = is_child_theme() ? wp_get_theme()->parent()->get( 'Version' ) : wp_get_theme()->get( 'Version' );

		if ( version_compare( $current_version, $new_version, '>=' ) ) {
			wp_send_json_error( __( 'You are already using the latest version.', 'cariera-core' ) );
		}

		// Get the package URL.
		$package_url = $this->download();

		// Check if the download() function returned a WP_Error.
		if ( is_wp_error( $package_url ) ) {
			wp_send_json_error( $package_url->get_error_message() );
		}

		// Use WP_Filesystem to handle the download and installation.
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Set up the WordPress filesystem.
		WP_Filesystem();

		// Create an instance of the theme upgrader.
		$upgrader = new \Theme_Upgrader();

		// Perform the update.
		$result = $upgrader->upgrade( 'cariera', $package_url );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		\Cariera\write_log( 'Theme has been successfully updated!' );

		wp_send_json_success(
			[
				'message' => esc_html__( 'Cariera has been successfully updated!', 'cariera-core' ),
			]
		);
	}
}
