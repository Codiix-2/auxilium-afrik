<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cariera_License_Manager' ) ) {
	class Cariera_License_Manager {

		/**
		 * License key for encryption/decryption
		 *
		 * @var string
		 */
		public $key = '0A6137FBBB602DCF';

		/**
		 * Product ID
		 *
		 * @var string
		 */
		private $product_id = '1';

		/**
		 * Product base slug
		 *
		 * @var string
		 */
		private $product_base = 'cariera';

		/**
		 * License server URL
		 *
		 * @var string
		 */
		private $server_host = 'https://api.gnodesign.com/wp-json/licensor/';

		/**
		 * Whether to check for updates
		 *
		 * @var bool
		 */
		private $enable_updates = false;

		/**
		 * Plugin/theme file path (matching original property name)
		 *
		 * @var string
		 */
		private $file_path;

		/**
		 * Legacy plugin file property (exact original name)
		 *
		 * @var string
		 */
		private $plugin_file;

		/**
		 * Theme directory name
		 *
		 * @var string
		 */
		private $theme_dir_name = '';

		/**
		 * Singleton instance (matching original property name)
		 *
		 * @var self|null
		 */
		private static $instance = null;

		/**
		 * Legacy singleton instance (exact original name)
		 *
		 * @var self|null
		 */
		private static $selfobj = null;

		/**
		 * Current version
		 *
		 * @var string
		 */
		private $version = '';

		/**
		 * Whether this is a theme (false for plugin)
		 *
		 * @var bool
		 */
		private $is_theme = true;

		/**
		 * Email address for license
		 *
		 * @var string
		 */
		private $email_address = '';

		/**
		 * Callbacks to execute on license deletion
		 *
		 * @var array
		 */
		private static $on_delete_callbacks = [];

		/**
		 * Cache expiration time in seconds
		 *
		 * @var int
		 */
		private $cache_expiration = DAY_IN_SECONDS;

		/**
		 * Constructor
		 *
		 * @param string $plugin_base_file Plugin/theme file path.
		 */
		public function __construct( $plugin_base_file = '' ) {
			$this->initialize_paths( $plugin_base_file );
			$this->setup_version();
			$this->setup_hooks();
		}

		/**
		 * Initialize file paths and determine if theme or plugin
		 *
		 * @since 1.9.5
		 *
		 * @param string $plugin_base_file Plugin/theme file path.
		 */
		private function initialize_paths( $plugin_base_file ) {
			$dir = empty( $plugin_base_file )
				? str_replace( '\\', '/', __DIR__ )
				: str_replace( '\\', '/', dirname( $plugin_base_file ) );

			if ( false !== strpos( $dir, 'wp-content/themes' ) ) {
				$this->is_theme       = true;
				$this->theme_dir_name = $this->get_theme_directory_name();
			}

			$this->file_path   = $plugin_base_file;
			$this->plugin_file = $plugin_base_file; // Legacy property.

			if ( empty( $this->file_path ) && $this->is_theme ) {
				$this->file_path   = $this->get_theme_style_path();
				$this->plugin_file = $this->file_path; // Keep legacy in sync.
			}

			// Additional legacy compatibility - ensure plugin_file is set if file_path is.
			if ( empty( $this->plugin_file ) && ! empty( $this->file_path ) ) {
				$this->plugin_file = $this->file_path;
			}
		}

		/**
		 * Setup version information
		 *
		 * @since 1.9.5
		 */
		private function setup_version() {
			if ( $this->is_theme ) {
				$theme_data = wp_get_theme( $this->theme_dir_name );
				$version    = $theme_data->get( 'Version' );
				if ( ! empty( $version ) ) {
					$this->version = $version;
				}
			}

			if ( empty( $this->version ) ) {
				$this->version = $this->get_current_version();
			}
		}

		/**
		 * Setup WordPress hooks
		 *
		 * @since 1.9.5
		 */
		private function setup_hooks() {
			if ( ! $this->enable_updates ) {
				return;
			}

			add_action( 'admin_post_cariera_fupc', [ $this, 'force_update_check' ] );
			add_action( 'init', [ $this, 'handle_server_requests' ] );
			add_action( 'upgrader_process_complete', [ $this, 'clear_update_cache' ], 10, 2 );

			if ( $this->is_theme ) {
				$this->setup_theme_hooks();
			} else {
				$this->setup_plugin_hooks();
			}
		}

		/**
		 * Setup theme-specific hooks
		 *
		 * @since 1.9.5
		 */
		private function setup_theme_hooks() {
			add_filter( 'pre_set_site_transient_update_themes', [ $this, 'check_for_updates' ] );
			add_filter( 'themes_api', [ $this, 'theme_update_info' ], 10, 3 );
			add_action( 'admin_menu', [ $this, 'add_theme_update_page' ], 999 );
		}

		/**
		 * Setup plugin-specific hooks
		 *
		 * @since 1.9.5
		 */
		private function setup_plugin_hooks() {
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_for_updates' ] );
			add_filter( 'plugins_api', [ $this, 'plugin_update_info' ], 10, 3 );
			add_filter( 'plugin_row_meta', [ $this, 'add_plugin_row_meta' ], 10, 2 );
			add_action( 'in_plugin_update_message-' . plugin_basename( $this->file_path ), [ $this, 'show_update_message' ], 20, 2 );
		}

		/**
		 * Add theme update check page
		 *
		 * @since 1.9.5
		 */
		public function add_theme_update_page() {
			add_theme_page(
				esc_html__( 'Update Check', 'cariera' ),
				esc_html__( 'Update Check', 'cariera' ),
				'edit_theme_options',
				'update_check',
				[ $this, 'theme_update_page' ]
			);
		}

		/**
		 * Theme update page callback
		 *
		 * @since 1.9.5
		 */
		public function theme_update_page() {
			$this->clear_update_cache();
			$url = admin_url( 'themes.php' );
			echo wp_kses_post( '<h1>' . __( 'Update Checking..', 'cariera' ) . '</h1>' );
			call_user_func( 'printf', '%s', "<script>location.href = '" . $url . "'</script>" );
		}

		/**
		 * Add plugin row meta links
		 *
		 * @since 1.9.5
		 *
		 * @param array  $links       Plugin row meta links.
		 * @param string $plugin_file Plugin file.
		 */
		public function add_plugin_row_meta( $links, $plugin_file ) {
			if ( plugin_basename( $this->file_path ) === $plugin_file ) {
				$links[] = " <a class='edit coption' href='" . esc_url( admin_url( 'admin-post.php' ) . '?action=cariera_fupc' ) . "'>Update Check</a>";
			}
			return $links;
		}

		/**
		 * Force update check
		 *
		 * @since 1.9.5
		 */
		public function force_update_check() {
			$this->clear_update_cache();
			$redirect_url = $this->is_theme ? admin_url( 'themes.php' ) : admin_url( 'plugins.php' );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		/**
		 * Handle server requests for license management
		 *
		 * @since 1.9.5
		 */
		public function handle_server_requests() {
			$handler = hash( 'crc32b', $this->product_id . $this->key . $this->get_domain() ) . '_handle';

			// phpcs:ignore
			if ( ! isset( $_GET['action'] ) || $_GET['action'] !== $handler ) {
				return;
			}

			$this->process_server_request();
			exit;
		}

		/**
		 * Process server request
		 *
		 * @since 1.9.5
		 */
		private function process_server_request() {
			$type = isset( $_GET['type'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['type'] ) ) ) : ''; // phpcs:ignore

			if ( ! in_array( $type, [ 'rl', 'rc', 'dl' ], true ) ) {
				return;
			}

			// Always delete transient at the start.
			$this->delete_license_data();

			$response          = new stdClass();
			$response->product = $this->product_id;
			$response->status  = false;

			switch ( $type ) {
				case 'rl':
					// Remove license (clean update info and old WP response).
					$this->clear_update_cache();
					$this->remove_stored_license();
					$response->status = true;
					break;

				case 'rc':
					// Remove license (delete license option).
					delete_option( $this->get_license_option_key() );
					$response->status = true;
					break;

				case 'dl':
					// Delete plugin or theme.
					$this->remove_stored_license();
					require_once ABSPATH . 'wp-admin/includes/file.php';

					if ( $this->is_theme ) {
						$theme_slug = $this->theme_dir_name;
						if ( $theme_slug && wp_get_theme( $theme_slug )->exists() ) {
							$delete_result = delete_theme( $theme_slug );
						} else {
							$delete_result = new WP_Error( 'theme_not_found', 'Theme does not exist or slug is empty.' );
						}
					} else {
						$plugin_basename = plugin_basename( $this->file_path );
						if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_basename ) ) {
							deactivate_plugins( [ $plugin_basename ] );
							$delete_result = delete_plugins( [ $plugin_basename ] );
						} else {
							$delete_result = new WP_Error( 'plugin_not_found', 'Plugin file does not exist.' );
						}
					}

					if ( ! is_wp_error( $delete_result ) ) {
						$response->status = true;
					}
					break;
			}

			call_user_func( 'printf', '%s', $this->encrypt_object( $response ) );
		}

		/**
		 * Add callback for license deletion
		 *
		 * @since 1.9.5
		 *
		 * @param callable $callback Callback function.
		 */
		public static function add_on_delete_callback( $callback ) {
			if ( is_callable( $callback ) ) {
				self::$on_delete_callbacks[] = $callback;
			}
		}

		/**
		 * Get current version
		 *
		 * @since 1.9.5
		 */
		private function get_current_version() {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Use plugin_file for compatibility with original.
			$data = get_plugin_data( $this->plugin_file );
			return isset( $data['Version'] ) ? $data['Version'] : '1.0.0';
		}

		/**
		 * Clear update cache
		 *
		 * @since 1.9.5
		 *
		 * @param WP_Upgrader $upgrader_object Upgrader object.
		 * @param array       $options         Upgrade options.
		 */
		public function clear_update_cache( $upgrader_object = null, $options = [] ) {
			update_option( '_site_transient_update_plugins', '' );
			update_option( '_site_transient_update_themes', '' );
			set_site_transient( 'update_themes', null );
			delete_transient( $this->product_base . '_up' );
		}

		/**
		 * Show update message
		 *
		 * @since 1.9.5
		 *
		 * @param array $plugin_data Plugin data.
		 * @param array $response    Update response.
		 */
		public function show_update_message( $plugin_data, $response ) {
			if ( is_array( $plugin_data ) ) {
				$plugin_data = (object) $plugin_data;
			}

			if ( isset( $plugin_data->package ) && empty( $plugin_data->package ) ) {
				if ( empty( $plugin_data->update_denied_type ) ) {
					print "<br/><span style='display: block; border-top: 1px solid #ccc;padding-top: 5px; margin-top: 10px;'>Please <strong>active product</strong> or  <strong>renew support period</strong> to get latest version</span>";
				} elseif ( 'L' === $plugin_data->update_denied_type ) {
					print "<br/><span style='display: block; border-top: 1px solid #ccc;padding-top: 5px; margin-top: 10px;'>Please <strong>active product</strong> to get latest version</span>";
				} elseif ( 'S' === $plugin_data->update_denied_type ) {
					print "<br/><span style='display: block; border-top: 1px solid #ccc;padding-top: 5px; margin-top: 10px;'>Please <strong>renew support period</strong> to get latest version</span>";
				}
			}
		}

		/**
		 * Get update information
		 *
		 * @since 1.9.5
		 */
		private function get_update_information() {
			if ( function_exists( 'wp_remote_get' ) ) {
				$response  = get_transient( $this->product_base . '_up' );
				$old_found = false;
				if ( ! empty( $response['data'] ) ) {
					$response = unserialize( $this->decrypt( $response['data'] ) );
					if ( is_array( $response ) ) {
						$old_found = true;
					}
				}

				if ( ! $old_found ) {
					$license_info = self::get_registration_info();
					$url          = $this->server_host . 'product/update/' . $this->product_id;
					if ( ! empty( $license_info->license_key ) ) {
						$url .= '/' . $license_info->license_key . '/' . $this->version;
					}
					$args     = [
						'sslverify'   => true,
						'timeout'     => 120,
						'redirection' => 5,
						'cookies'     => [],
					];
					$response = wp_remote_get( $url, $args );
					if ( is_wp_error( $response ) ) {
						$args['sslverify'] = false;
						$response          = wp_remote_get( $url, $args );
					}
				}

				if ( ! is_wp_error( $response ) ) {
					$body          = $response['body'];
					$response_json = @json_decode( $body );
					if ( ! $old_found ) {
						set_transient( $this->product_base . '_up', [ 'data' => $this->encrypt( serialize( [ 'body' => $body ] ) ) ], DAY_IN_SECONDS );
					}

					if ( ! ( is_object( $response_json ) && isset( $response_json->status ) ) ) {
						$body          = $this->decrypt( $body, $this->key );
						$response_json = json_decode( $body );
					}

					if ( is_object( $response_json ) && ! empty( $response_json->status ) && ! empty( $response_json->data->new_version ) ) {
						$response_json->data->slug = plugin_basename( $this->file_path );

						$response_json->data->new_version        = ! empty( $response_json->data->new_version ) ? $response_json->data->new_version : '';
						$response_json->data->url                = ! empty( $response_json->data->url ) ? $response_json->data->url : '';
						$response_json->data->package            = ! empty( $response_json->data->download_link ) ? $response_json->data->download_link : '';
						$response_json->data->update_denied_type = ! empty( $response_json->data->update_denied_type ) ? $response_json->data->update_denied_type : '';

						$response_json->data->sections    = (array) $response_json->data->sections;
						$response_json->data->plugin      = plugin_basename( $this->file_path );
						$response_json->data->icons       = (array) $response_json->data->icons;
						$response_json->data->banners     = (array) $response_json->data->banners;
						$response_json->data->banners_rtl = (array) $response_json->data->banners_rtl;
						unset( $response_json->data->is_stopped_update );

						return $response_json->data;
					}
				}
			}

			return null;
		}

		/**
		 * Get theme directory name
		 *
		 * @since 1.9.5
		 */
		private function get_theme_directory_name() {
			$wp_theme_dir = str_replace( '\\', '/', WP_CONTENT_DIR ) . '/themes/';
			$current_dir  = str_replace( '\\', '/', __DIR__ );
			$theme_name   = str_replace( $wp_theme_dir, '', $current_dir );

			$pos = strpos( $theme_name, '/' );
			if ( false !== $pos ) {
				$theme_name = substr( $theme_name, 0, $pos );
			}

			return $theme_name;
		}

		/**
		 * Get theme style.css path
		 *
		 * @since 1.9.5
		 */
		private function get_theme_style_path() {
			$wp_theme_dir   = str_replace( '\\', '/', WP_CONTENT_DIR ) . '/themes/';
			$theme_name     = $this->get_theme_directory_name();
			$style_css_path = $wp_theme_dir . $theme_name . '/style.css';

			return file_exists( $style_css_path ) ? $style_css_path : get_stylesheet_directory() . '/style.css';
		}

		/**
		 * Check for updates
		 *
		 * @since 1.9.5
		 *
		 * @param object $transient Update transient.
		 */
		public function check_for_updates( $transient ) {
			if ( empty( $transient ) ) {
				$transient           = new stdClass();
				$transient->response = [];
			}
			$response = $this->get_update_information();
			if ( ! empty( $response->plugin ) ) {
				if ( $this->is_theme ) {
					$index_name      = $this->theme_dir_name;
					$response->theme = $this->theme_dir_name;
				} else {
					$index_name = $response->plugin;
				}
				if ( ! empty( $response ) && version_compare( $this->version, $response->new_version, '<' ) ) {
					unset( $response->download_link );
					unset( $response->is_stopped_update );
					if ( $this->is_theme ) {
						$transient->response[ $index_name ] = (array) $response;
					} else {
						$transient->response[ $index_name ] = (object) $response;
					}
				} elseif ( isset( $transient->response[ $index_name ] ) ) {
					unset( $transient->response[ $index_name ] );
				}
			}

			return $transient;
		}

		/**
		 * Theme update info
		 *
		 * @since 1.9.5
		 *
		 * @param mixed  $result Result.
		 * @param string $action Action.
		 * @param object $args   Arguments.
		 */
		public function theme_update_info( $result, $action, $args ) {
			return $this->get_update_info( $result, $action, $args );
		}

		/**
		 * Plugin update info
		 *
		 * @since 1.9.5
		 *
		 * @param mixed  $result Result.
		 * @param string $action Action.
		 * @param object $args   Arguments.
		 */
		public function plugin_update_info( $result, $action, $args ) {
			return $this->get_update_info( $result, $action, $args );
		}

		/**
		 * Get update info
		 *
		 * @since 1.9.5
		 *
		 * @param mixed  $result Result.
		 * @param string $action Action.
		 * @param object $args   Arguments.
		 */
		private function get_update_info( $result, $action, $args ) {
			if ( empty( $args->slug ) ) {
				return $result;
			}
			if ( $this->is_theme ) {
				if ( ! empty( $args->slug ) && $this->product_base === $args->slug ) {
					$response = $this->get_update_information();
					if ( ! empty( $response ) ) {
						return $response;
					}
				}
			} elseif ( ! empty( $args->slug ) && plugin_basename( $this->file_path ) === $args->slug ) {
				$response = $this->get_update_information();
				if ( ! empty( $response ) ) {
					return $response;
				}
			}

			return $result;
		}

		/**
		 * Get singleton instance (matching original method exactly)
		 *
		 * @since 1.9.5
		 *
		 * @param string $plugin_base_file Plugin/theme file path.
		 */
		public static function get_instance( $plugin_base_file = null ) {
			if ( null === self::$instance ) {
				if ( ! empty( $plugin_base_file ) ) {
					self::$instance = new self( $plugin_base_file );
					// Also set the legacy property for backward compatibility.
					self::$selfobj = self::$instance;
				}
			}

			return self::$instance;
		}

		/**
		 * Legacy method for backward compatibility (exact original signature)
		 *
		 * @since 1.9.5
		 * @deprecated 1.9.5 Use get_instance() instead.
		 *
		 * @param string $plugin_base_file Plugin/theme file path.
		 */
		public static function &getInstance( $plugin_base_file = null ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
			return self::get_instance( $plugin_base_file );
		}

		/**
		 * Get renew link
		 *
		 * @since 1.9.5
		 *
		 * @param object $response_obj License response object.
		 * @param string $type         Link type ('s' for support, 'l' for license).
		 */
		public static function get_renew_link( $response_obj, $type = 's' ) {
			if ( empty( $response_obj->renew_link ) ) {
				return '';
			}
			$is_show_button = false;
			if ( 's' === $type ) {
				$support_str = strtolower( trim( $response_obj->support_end ) );
				if ( 'no support' === $support_str ) {
					$is_show_button = true;
				} elseif ( ! in_array( $support_str, [ 'unlimited' ], true ) ) {
					if ( strtotime( '+30 days', strtotime( $response_obj->support_end ) ) < time() ) {
						$is_show_button = true;
					}
				}
				if ( $is_show_button ) {
					return $response_obj->renew_link . ( strpos( $response_obj->renew_link, '?' ) === false ? '?type=s&lic=' . rawurlencode( $response_obj->license_key ) : '&type=s&lic=' . rawurlencode( $response_obj->license_key ) );
				}
				return '';
			} else {
				$is_show_button = false;
				$expire_str     = strtolower( trim( $response_obj->expire_date ) );
				if ( ! in_array( $expire_str, [ 'unlimited', 'no expiry' ], true ) ) {
					if ( strtotime( '+30 days', strtotime( $response_obj->expire_date ) ) < time() ) {
						$is_show_button = true;
					}
				}
				if ( $is_show_button ) {
					return $response_obj->renew_link . ( strpos( $response_obj->renew_link, '?' ) === false ? '?type=l&lic=' . rawurlencode( $response_obj->license_key ) : '&type=l&lic=' . rawurlencode( $response_obj->license_key ) );
				}
				return '';
			}
		}

		/**
		 * Encrypt text
		 *
		 * @since 1.9.5
		 *
		 * @param string $plain_text Text to encrypt.
		 * @param string $password   Encryption password.
		 */
		private function encrypt( $plain_text, $password = '' ) {
			if ( empty( $password ) ) {
				$password = $this->key;
			}
			$plain_text = wp_rand( 10, 99 ) . $plain_text . wp_rand( 10, 99 );
			$method     = 'aes-256-cbc';
			$key        = substr( hash( 'sha256', $password, true ), 0, 32 );
			$iv         = substr( strtoupper( md5( $password ) ), 0, 16 );
			return $this->base64_encode_custom( openssl_encrypt( $plain_text, $method, $key, OPENSSL_RAW_DATA, $iv ) );
		}

		/**
		 * Decrypt text
		 *
		 * @since 1.9.5
		 *
		 * @param string $encrypted Encrypted text.
		 * @param string $password  Decryption password.
		 */
		private function decrypt( $encrypted, $password = '' ) {
			if ( empty( $password ) ) {
				$password = $this->key;
			}
			$method    = 'aes-256-cbc';
			$key       = substr( hash( 'sha256', $password, true ), 0, 32 );
			$iv        = substr( strtoupper( md5( $password ) ), 0, 16 );
			$plaintext = openssl_decrypt( $this->base64_decode_custom( $encrypted ), $method, $key, OPENSSL_RAW_DATA, $iv );
			return substr( $plaintext, 2, -2 );
		}

		/**
		 * Custom base64 decode (obfuscated method)
		 *
		 * @since 1.9.5
		 *
		 * @param string $encrypted Encrypted string.
		 */
		private function base64_decode_custom( $encrypted ) {
			$b64 = preg_replace( '#[^a-z0-9\_]#i', '', 'ba*s-e#6-4#_d$e!c#o!d#e' );
			return $b64( $encrypted );
		}

		/**
		 * Custom base64 encode (obfuscated method)
		 *
		 * @since 1.9.5
		 *
		 * @param string $str String to encode.
		 */
		private function base64_encode_custom( $str ) {
			$b64 = preg_replace( '#[^a-z0-9\_]#i', '', 'ba*s-e#6-4#_e$n!c#o!d#e' );
			return $b64( $str );
		}

		/**
		 * Encrypt object
		 *
		 * @since 1.9.5
		 *
		 * @param object $obj Object to encrypt.
		 */
		private function encrypt_object( $obj ) {
			$text = serialize( $obj );
			return $this->encrypt( $text );
		}

		/**
		 * Decrypt object
		 *
		 * @since 1.9.5
		 *
		 * @param string $ciphertext Encrypted object.
		 */
		private function decrypt_object( $ciphertext ) {
			$text = $this->decrypt( $ciphertext );
			return unserialize( $text );
		}

		/**
		 * Get domain
		 *
		 * @since 1.9.5
		 */
		private function get_domain() {
			return self::get_raw_domain();
		}

		/**
		 * Get raw domain
		 *
		 * @since 1.9.5
		 */
		private static function get_raw_domain() {
			if ( function_exists( 'site_url' ) ) {
				return site_url();
			}
			if ( defined( 'WPINC' ) && function_exists( 'home_url' ) ) {
				return esc_url( home_url() );
			}
		}

		/**
		 * Get raw WordPress URL
		 *
		 * @since 1.9.5
		 */
		private static function get_raw_wp_url() {
			$domain = self::get_raw_domain();
			return preg_replace( '(^https?://)', '', $domain );
		}

		/**
		 * Get license key parameter
		 *
		 * @since 1.9.5
		 *
		 * @param string $key License key.
		 */
		public static function get_license_key_param( $key ) {
			$raw_url = self::get_raw_wp_url();
			return $key . '_s' . hash( 'crc32b', $raw_url );
		}

		/**
		 * Set email address
		 *
		 * @since 1.9.5
		 *
		 * @param string $email_address Email address.
		 */
		public function set_email_address( $email_address ) {
			$this->email_address = $email_address;
		}

		/**
		 * Get email address
		 *
		 * @since 1.9.5
		 */
		private function get_email() {
			return $this->email_address;
		}

		/**
		 * Process server response
		 *
		 * @since 1.9.5
		 *
		 * @param string $response Server response.
		 */
		private function process_response( $response ) {
			$default_response         = new stdClass();
			$default_response->status = false;
			$default_response->data   = null;

			if ( empty( $response ) ) {
				$default_response->msg = 'Unknown response';
				return $default_response;
			}

			// Attempt decryption if a key is set.
			$original_response = $response;
			if ( ! empty( $this->key ) ) {
				$response = $this->decrypt( $response );
			}

			// Decode the JSON response.
			$decoded_response = json_decode( $response );

			// Return the decoded response if it's a valid object.
			if ( is_object( $decoded_response ) ) {
				return $decoded_response;
			}

			// Fallback error handling.
			$default_response->msg = 'Response Error: Please contact your hosting provider to disable any firewalls, increase the HTTP request timeout, and if the issue persists, please open a support ticket.';

			// Attempt to decode the original response if available.
			$backup_decoded = json_decode( $original_response );
			if ( is_object( $backup_decoded ) && ! empty( $backup_decoded->msg ) ) {
				$default_response->msg = $backup_decoded->msg;
			}

			return $default_response;
		}

		/**
		 * Send HTTP request to license server
		 *
		 * @since 1.9.5
		 *
		 * @param string $relative_url Relative URL.
		 * @param mixed  $data         Request data.
		 * @param string $error        Error message (by reference).
		 */
		private function send_request( $relative_url, $data, &$error = '' ) {
			$response                   = new stdClass();
			$response->status           = false;
			$response->msg              = 'Empty Response';
			$response->is_request_error = false;

			// Prepare data and encrypt if key is available.
			$final_data = wp_json_encode( $data );
			if ( ! empty( $this->key ) ) {
				$final_data = $this->encrypt( $final_data );
			}

			// Construct the full URL.
			$url = rtrim( $this->server_host, '/' ) . '/' . ltrim( $relative_url, '/' );

			// Check if wp_remote_post exists before making the request.
			if ( ! function_exists( 'wp_remote_post' ) ) {
				$response->msg              = 'No valid request method works for license checking. wp_remote_post function not available.';
				$response->is_request_error = true;
				return $response;
			}

			// Define request parameters.
			$rq_params = [
				'method'      => 'POST',
				'sslverify'   => true,
				'timeout'     => 120,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => [],
				'body'        => $final_data,
				'cookies'     => [],
			];

			// Send initial request.
			$server_response = wp_remote_post( $url, $rq_params );

			// Retry without SSL verification if an error occurred.
			if ( is_wp_error( $server_response ) ) {
				$rq_params['sslverify'] = false;
				$server_response        = wp_remote_post( $url, $rq_params );

				// Handle error after retry.
				if ( is_wp_error( $server_response ) ) {
					$response->msg              = $server_response->get_error_message();
					$response->is_request_error = true;
					return $response;
				}
			}

			// Retrieve response code and body for validation.
			$response_code = (int) wp_remote_retrieve_response_code( $server_response );
			$response_body = wp_remote_retrieve_body( $server_response );

			// Process successful response.
			if ( ! empty( $response_body ) && 200 === $response_code && 'GET404' !== $response_body ) {
				return $this->process_response( $response_body );
			}

			// Handle different error scenarios.
			switch ( $response_code ) {
				case 0:
					$response->msg = 'Could not connect to the license server. Please check your internet connection, firewall, or DNS settings.';
					break;
				case 403:
					$response->msg = 'Access denied (403). Your server may be blocking outgoing requests or your license is invalid.';
					break;
				case 404:
					$response->msg = 'License server endpoint not found (404). Please make sure your plugin/theme is updated to the latest version.';
					break;
				case 500:
				case 502:
				case 503:
				case 504:
					$response->msg = 'The license server is temporarily unavailable (HTTP ' . $response_code . '). Please try again later.';
					break;
				default:
					if ( empty( $response_body ) ) {
						$response->msg = 'The license server returned an empty response. This could be caused by security plugins, firewalls, or server misconfiguration.';
					} else {
						$response->msg = sprintf(
							'Unexpected response from license server (HTTP %d): %s',
							$response_code,
							substr( wp_strip_all_tags( $response_body ), 0, 200 ) // short preview of body.
						);
					}
					break;
			}

			$response->is_request_error = true;
			return $response;
		}

		/**
		 * Get request parameters
		 *
		 * @since 1.9.5
		 *
		 * @param string $purchase_key License key.
		 * @param string $app_version  App version.
		 * @param string $admin_email  Admin email.
		 */
		private function get_request_params( $purchase_key, $app_version, $admin_email = '' ) {
			$req               = new stdClass();
			$req->license_key  = $purchase_key;
			$req->email        = ! empty( $admin_email ) ? $admin_email : $this->get_email();
			$req->domain       = $this->get_domain();
			$req->app_version  = $app_version;
			$req->product_id   = $this->product_id;
			$req->product_base = $this->product_base;

			return $req;
		}

		/**
		 * Get license option key
		 *
		 * @since 1.9.5
		 */
		private function get_license_option_key() {
			// Use plugin_file for compatibility with original.
			return hash( 'crc32b', $this->get_domain() . $this->plugin_file . $this->product_id . $this->product_base . $this->key . 'LIC' );
		}

		/**
		 * Save license response
		 *
		 * @since 1.9.5
		 *
		 * @param object $response License response.
		 */
		private function save_license_response( $response ) {
			$key  = $this->get_license_option_key();
			$data = $this->encrypt( serialize( $response ), $this->get_domain() );
			if ( ! update_option( $key, $data ) ) {
				add_option( $key, $data );
			}
		}

		/**
		 * Get stored license
		 *
		 * @since 1.9.5
		 */
		private function get_stored_license() {
			$key      = $this->get_license_option_key();
			$response = get_option( $key, null );
			if ( empty( $response ) ) {
				return null;
			}

			return unserialize( $this->decrypt( $response, $this->get_domain() ) );
		}

		/**
		 * Remove stored license
		 *
		 * @since 1.9.5
		 */
		private function remove_stored_license() {
			$key        = $this->get_license_option_key();
			$is_deleted = delete_option( $key );
			foreach ( self::$on_delete_callbacks as $func ) {
				if ( is_callable( $func ) ) {
					call_user_func( $func );
				}
			}

			return $is_deleted;
		}

		/**
		 * Remove license key
		 *
		 * @since 1.9.5
		 *
		 * @param string $plugin_base_file Plugin base file.
		 * @param string $message         Error message (by reference).
		 * @param string $license_key     License key.
		 * @param string $license_email   License email.
		 */
		public static function remove_license_key( $plugin_base_file, &$message = '', $license_key = '', $license_email = '' ) {
			$obj = self::get_instance( $plugin_base_file );
			$obj->clear_update_cache();
			return $obj->remove_license( $message, $license_key, $license_email );
		}

		/**
		 * Check license
		 *
		 * @since 1.9.5
		 *
		 * @param string $purchase_key     Purchase key.
		 * @param string $email           Email address.
		 * @param string $error           Error message (by reference).
		 * @param object $response_obj    Response object (by reference).
		 * @param string $plugin_base_file Plugin base file.
		 */
		public static function check_license( $purchase_key, $email, &$error = '', &$response_obj = null, $plugin_base_file = '' ) {
			$instance = self::get_instance( $plugin_base_file );
			$instance->set_email_address( $email );
			return $instance->validate_license( $purchase_key, $error, $response_obj );
		}

		/**
		 * Remove license
		 *
		 * @since 1.9.5
		 *
		 * @param string $message       Error message (by reference).
		 * @param string $license_key   License key.
		 * @param string $license_email License email.
		 */
		private function remove_license( &$message = '', $license_key = '', $license_email = '' ) {
			$old_respons = $this->get_stored_license();

			// First try with explicit license data if available.
			if ( ! empty( $license_key ) && ! empty( $license_email ) ) {
				$param    = $this->get_request_params( $license_key, $this->version, $license_email );
				$response = $this->send_request( 'product/deactive/' . $this->product_id, $param, $message );

				if ( empty( $response->code ) ) {
					if ( ! empty( $response->status ) && ( 'success' === $response->status || 1 === $response->status ) ) {
						$message = $response->msg;
						$this->remove_stored_license();
						return true;
					} else {
						$message = ! empty( $response->msg ) ? $response->msg : 'Deactivation failed on server';
						return false;
					}
				} else {
					$message = $response->message;
					return false;
				}
			}

			// Fallback to original method with stored response.
			if ( ! empty( $old_respons->is_valid ) ) {
				if ( ! empty( $old_respons->license_key ) ) {
					$param    = $this->get_request_params( $old_respons->license_key, $this->version );
					$response = $this->send_request( 'product/deactive/' . $this->product_id, $param, $message );

					if ( empty( $response->code ) ) {
						if ( ! empty( $response->status ) ) {
							$message = $response->msg;
							$this->remove_stored_license();
							return true;
						} else {
							$message = $response->msg;
						}
					} else {
						$message = $response->message;
					}
				}
			} else {
				$this->remove_stored_license();
				return true;
			}
			return false;
		}

		/**
		 * Get registration information (exact original method)
		 *
		 * @since 1.9.5
		 */
		public static function get_registration_info() {
			// Check both new and legacy singleton properties for compatibility.
			if ( ! empty( self::$instance ) ) {
				return self::$instance->get_stored_license();
			}
			if ( ! empty( self::$selfobj ) ) {
				return self::$selfobj->get_stored_license();
			}
			return null;
		}

		/**
		 * Validate license
		 *
		 * @since 1.9.5
		 *
		 * @param string $purchase_key Purchase key.
		 * @param string $error        Error message (by reference).
		 * @param object $response_obj Response object (by reference).
		 */
		private function validate_license( $purchase_key, &$error = '', &$response_obj = null ) {
			if ( empty( $purchase_key ) ) {
				$this->remove_stored_license();
				$error = '';
				return false;
			}
			$old_respons = $this->get_stored_license();
			$is_force    = false;
			if ( ! empty( $old_respons ) ) {
				if ( ! empty( $old_respons->expire_date ) && strtolower( $old_respons->expire_date ) !== 'no expiry' && strtotime( $old_respons->expire_date ) < time() ) {
					$is_force = true;
				}
				if ( ! $is_force && ! empty( $old_respons->is_valid ) && $old_respons->next_request > time() && ( ! empty( $old_respons->license_key ) && $purchase_key === $old_respons->license_key ) ) {
					$response_obj = clone $old_respons;
					unset( $response_obj->next_request );

					return true;
				}
			}

			$param    = $this->get_request_params( $purchase_key, $this->version );
			$response = $this->send_request( 'product/active/' . $this->product_id, $param, $error );
			if ( empty( $response->is_request_error ) ) {
				if ( empty( $response->code ) ) {
					if ( ! empty( $response->status ) ) {
						if ( ! empty( $response->data ) ) {
							$serial_obj = $this->decrypt( $response->data, $param->domain );

							$license_obj = unserialize( $serial_obj );
							if ( $license_obj->is_valid ) {
								$response_obj           = new stdClass();
								$response_obj->is_valid = $license_obj->is_valid;
								if ( $license_obj->request_duration > 0 ) {
									$response_obj->next_request = strtotime( "+ {$license_obj->request_duration} hour" );
								} else {
									$response_obj->next_request = time();
								}
								$response_obj->expire_date        = $license_obj->expire_date;
								$response_obj->support_end        = $license_obj->support_end;
								$response_obj->license_title      = $license_obj->license_title;
								$response_obj->license_key        = $purchase_key;
								$response_obj->msg                = $response->msg;
								$response_obj->renew_link         = ! empty( $license_obj->renew_link ) ? $license_obj->renew_link : '';
								$response_obj->expire_renew_link  = self::get_renew_link( $response_obj, 'l' );
								$response_obj->support_renew_link = self::get_renew_link( $response_obj, 's' );

								// Custom: Saving custom response.
								$response_obj->plugins_url    = $license_obj->plugins_url;
								$response_obj->latest_version = $license_obj->latest_version;

								$this->save_license_response( $response_obj );
								unset( $response_obj->next_request );
								delete_transient( $this->product_base . '_up' );
								return true;
							} elseif ( $this->check_fallback_license( $old_respons, $response_obj, $response ) ) {
								return true;
							} else {
								$this->remove_stored_license();
								$error = ! empty( $response->msg ) ? $response->msg : '';
							}
						} else {
							$error = 'Invalid data';
						}
					} else {
						$error = $response->msg;
					}
				} else {
					$error = $response->message;
				}
			} elseif ( $this->check_fallback_license( $old_respons, $response_obj, $response ) ) {
				return true;
			} else {
				$this->remove_stored_license();
				$error = ! empty( $response->msg ) ? $response->msg : '';
			}
			return $this->check_fallback_license( $old_respons, $response_obj );
		}

		/**
		 * Check fallback license
		 *
		 * @since 1.9.5
		 *
		 * @param object $old_respons Old license response.
		 * @param object $response_obj Response object (by reference).
		 * @param object $response     Current response.
		 */
		private function check_fallback_license( &$old_respons, &$response_obj, $response = null ) {
			if ( ! empty( $old_respons ) && ( empty( $old_respons->tried ) || $old_respons->tried <= 2 ) ) {
				$old_respons->next_request = strtotime( '+ 1 hour' );
				$old_respons->tried        = empty( $old_respons->tried ) ? 1 : ( $old_respons->tried + 1 );
				$response_obj              = clone $old_respons;
				unset( $response_obj->next_request );
				if ( isset( $response_obj->tried ) ) {
					unset( $response_obj->tried );
				}
				$this->save_license_response( $old_respons );
				return true;
			}
			return false;
		}

		/**
		 * Check plugins URL (Custom method)
		 *
		 * @since 1.9.5
		 *
		 * @param string $purchase_key     Purchase key.
		 * @param string $email           Email address.
		 * @param string $error           Error message (by reference).
		 * @param object $response_obj    Response object (by reference).
		 * @param string $plugin_base_file Plugin base file.
		 */
		public static function check_plugins_url( $purchase_key, $email, &$error = '', &$response_obj = null, $plugin_base_file = '' ) {
			$obj = self::get_instance( $plugin_base_file );
			$obj->set_email_address( $email );
			return $obj->get_plugins_url( $purchase_key, $error, $response_obj );
		}

		/**
		 * Get plugins URL
		 *
		 * @since 1.9.5
		 *
		 * @param string $purchase_key Purchase key.
		 * @param string $error        Error message (by reference).
		 * @param object $response_obj Response object (by reference).
		 */
		private function get_plugins_url( $purchase_key, &$error = '', &$response_obj = null ) {
			if ( empty( $purchase_key ) ) {
				$this->remove_stored_license();
				$error = '';
				return false;
			}

			$param    = $this->get_request_params( $purchase_key, $this->version );
			$response = $this->send_request( 'product/active/' . $this->product_id, $param, $error );

			if ( ! empty( $response->is_request_error ) || ! empty( $response->code ) || empty( $response->status ) ) {
				return false;
			}

			if ( ! empty( $response->data ) ) {
				$serial_obj = $this->decrypt( $response->data, $param->domain );

				$license_obj = unserialize( $serial_obj );
				if ( $license_obj->is_valid ) {
					$response_obj = new stdClass();

					// Custom: Saving custom response.
					$response_obj->plugins_url    = $license_obj->plugins_url;
					$response_obj->latest_version = $license_obj->latest_version;

					$this->save_license_response( $response_obj->plugins_url );
					delete_transient( $this->product_base . '_up' );

					return $response_obj->plugins_url;
				}
			}

			return $this->check_fallback_license( $old_respons, $response_obj );
		}

		/**
		 * Delete Cariera license data (Custom method)
		 *
		 * @since 1.9.5
		 */
		private function delete_license_data() {
			$license_key   = get_option( 'Cariera_lic_Key' );
			$license_email = get_option( 'Cariera_lic_email', '' );
			$transient_key = 'cariera_license_status_' . md5( $license_key . $license_email );

			// Delete the transient.
			delete_transient( $transient_key );

			// Delete the options.
			delete_option( 'Cariera_lic_Key' );
			delete_option( 'Cariera_lic_email' );
			delete_option( 'cariera_license_activated' );
		}

		/**
		 * Legacy: Check WP plugin (exact signature match)
		 *
		 * @param string $purchase_key
		 * @param string $email
		 * @param string $error
		 * @param array  $response_obj
		 * @param string $plugin_base_file
		 *
		 * @deprecated 1.9.5 Use check_license() instead.
		 */
		public static function check_wp_plugin( $purchase_key, $email, &$error = '', &$response_obj = null, $plugin_base_file = '' ) {
			$obj = self::get_instance( $plugin_base_file );
			$obj->set_email_address( $email );
			return $obj->_check_wp_plugin( $purchase_key, $error, $response_obj );
		}

		/**
		 * Legacy: Internal check wp plugin method (exact match to original)
		 *
		 * @since 1.9.5
		 *
		 * @param string $purchase_key Purchase key.
		 * @param string $error        Error message (by reference).
		 * @param object $response_obj Response object (by reference).
		 */
		final function _check_wp_plugin( $purchase_key, &$error = '', &$response_obj = null ) {
			return $this->validate_license( $purchase_key, $error, $response_obj );
		}

		/**
		 * Legacy: Remove wp plugin license (exact signature match)
		 *
		 * @since 1.9.5
		 *
		 * @param string $message       Error message (by reference).
		 * @param string $license_key   License key.
		 * @param string $license_email License email.
		 */
		final function _remove_wp_plugin_license( &$message = '', $license_key = '', $license_email = '' ) {
			return $this->remove_license( $message, $license_key, $license_email );
		}

		/**
		 * Legacy: Check plugins url (exact signature match)
		 *
		 * @since 1.9.5
		 *
		 * @param string $purchase_key Purchase key.
		 * @param string $error        Error message (by reference).
		 * @param object $response_obj Response object (by reference).
		 */
		private function _check_plugins_url( $purchase_key, &$error = '', &$response_obj = null ) {
			return $this->get_plugins_url( $purchase_key, $error, $response_obj );
		}

		/**
		 * Legacy: Get register info
		 *
		 * @since 1.9.5
		 * @deprecated 1.9.5 Use get_registration_info() instead.
		 */
		public static function get_register_info() {
			return self::get_registration_info();
		}

		/**
		 * Legacy: Add on delete callback
		 *
		 * @since 1.9.5
		 *
		 * @param callable $func Callback function.
		 */
		static function add_on_delete( $func ) {
			self::add_on_delete_callback( $func );
		}
	}
}
