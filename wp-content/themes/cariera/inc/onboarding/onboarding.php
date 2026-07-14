<?php

namespace Cariera\Onboarding;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Onboarding {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * TGMPA plugin loader.
	 *
	 * @var $tgmpa
	 */
	protected $tgmpa;

	/**
	 * Theme name.
	 *
	 * @var $theme
	 */
	protected $theme;

	/**
	 * Gnodesign website url.
	 *
	 * @var $gnodesign_url
	 */
	protected $gnodesign_url;

	/**
	 * User capability.
	 *
	 * @var $capability
	 */
	protected $capability;

	/**
	 * Theme License
	 *
	 * @var $license Cariera\Onboarding\License()
	 */
	protected $license;

	/**
	 * License status.
	 *
	 * @var $status
	 */
	protected $status;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Helpers.
		$this->theme         = 'Cariera';
		$this->gnodesign_url = 'cariera_theme';
		$this->capability    = 'edit_theme_options';
		$this->license       = \Cariera\Onboarding\License::instance();
		$this->status        = $this->activation_status();

		// Plugins if active.
		if ( $this->status ) {
			require get_template_directory() . '/inc/onboarding/plugins/activate-plugins.php';

			if ( class_exists( 'TGM_Plugin_Activation' ) ) {
				$this->tgmpa = isset( $GLOBALS['tgmpa'] ) ? $GLOBALS['tgmpa'] : TGM_Plugin_Activation::get_instance();
			}
		}

		// Actions.
		add_action( 'admin_menu', [ $this, 'add_menu_item' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'onboarding_assets' ] );
		add_action( 'after_switch_theme', [ $this, 'redirect' ], 30 );

		// Plugins.
		add_filter( 'tgmpa_load', [ $this, 'load_tgmpa' ], 10, 1 );
		add_action( 'wp_ajax_cariera_plugins', [ $this, 'ajax_plugins' ], 10, 0 );

		// Onboarding pages.
		add_action( 'cariera_onboarding_plugins', [ $this, 'plugins' ] );
		add_action( 'cariera_onboarding_import', [ $this, 'import' ] );
	}

	/**
	 * Get activation status of the theme
	 *
	 * Keeping this for backwards compatibility for 1.8.3 and before.
	 *
	 * @since   1.7.0
	 * @version 1.8.5
	 */
	public static function activation_status() {
		return ! empty( get_option( 'cariera_license_activated' ) ) && ! empty( get_option( 'Cariera_lic_Key' ) );
	}

	/**
	 * Add admin menu page
	 *
	 * @since 1.5.1
	 */
	public function add_menu_item() {
		// Main Menu Page.
		add_menu_page(
			$this->theme,
			$this->theme,
			$this->capability,
			$this->gnodesign_url,
			[ $this, 'page_template' ],
			get_template_directory_uri() . '/assets/images/admin-icon.png',
			2
		);

		// Welcome Submenu Page.
		add_submenu_page(
			$this->gnodesign_url,
			esc_html__( 'Welcome', 'cariera' ),
			esc_html__( 'Welcome', 'cariera' ),
			$this->capability,
			$this->gnodesign_url,
			[ $this, 'page_template' ]
		);
	}

	/**
	 * Loading scripts and styles for the onboarding
	 *
	 * @since 1.5.1
	 */
	public function onboarding_assets() {
		$this->load_scripts();
		$this->load_styles();
	}

	/**
	 * Onboarding scripts
	 *
	 * @since   1.5.1
	 * @version 1.9.8
	 */
	private function load_scripts() {
		$version = \Cariera\get_assets_version();

		wp_register_script( 'cariera-onboarding', get_template_directory_uri() . '/assets/dist/js/onboarding.js', [ 'vue', 'jquery' ], $version, true );

		$tgma_url = $this->status ? $this->tgmpa->get_tgmpa_url() : '';

		wp_localize_script(
			'cariera-onboarding',
			'cariera_onboarding',
			[
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'tgm_plugin_nonce' => [
					'update'  => wp_create_nonce( 'tgmpa-update' ),
					'install' => wp_create_nonce( 'tgmpa-install' ),
				],
				'tgm_bulk_url'     => $tgma_url,
				'wpnonce'          => wp_create_nonce( 'cariera_onboarding_nonce' ),
				'strings'          => [
					'verify_text'             => esc_html__( '...verifying', 'cariera' ),
					'plugin_error'            => esc_html__( 'Plugin could not be installed! Make sure to refresh plugin URLs before installing/updating the bundled plugins.', 'cariera' ),
					'fetching_demo_data'      => esc_html__( 'Fetching Data', 'cariera' ),
					'fetch_demo_failed'       => esc_html__( 'Importer Failed!', 'cariera' ),
					'fetch_demo_error'        => esc_html__( 'There was an error occurs when applying this patch, please try again.', 'cariera' ),
					'import_demo_error'       => esc_html__( 'There was an error occurs when importing demo data, please try again.', 'cariera' ),
					'import_demo_close_error' => esc_html__( 'The importer is running. Please don\'t navigate away from this page.', 'cariera' ),
					'importer_img_download'   => esc_html__( 'There was an error occurs when downloading the media package, please try again.', 'cariera' ),
					'importer_img_copy'       => esc_html__( 'There was an error occurs when copying the media package, please try again.', 'cariera' ),
					'importer_ajax_error'     => esc_html__( 'There was an error occurs when importing, please try again.', 'cariera' ),
					'starting_uninstall'      => esc_html__( 'Starting uninstallation...', 'cariera' ),
					'child_theme_error'       => esc_html__( 'Could not create child theme. Please try again.', 'cariera' ),
					'child_theme_skipping'    => esc_html__( 'Skipping...', 'cariera' ),
				],
			]
		);
	}

	/**
	 * Onboarding styles
	 *
	 * @since   1.5.1
	 * @version 1.7.3
	 */
	private function load_styles() {
		$version = \Cariera\get_assets_version();
		$suffix  = is_rtl() ? '.rtl' : '';

		wp_register_style( 'cariera-onboarding', get_template_directory_uri() . '/assets/dist/css/onboarding' . $suffix . '.css', [], $version );
	}

	/**
	 * Onboarding main page template
	 *
	 * @since   1.5.1
	 * @version 1.8.3
	 */
	public function page_template() {
		get_template_part( 'templates/backend/onboarding/main', null, [ 'status' => $this->status ] );
	}

	/**
	 * Redirection on activate.
	 *
	 * @since 1.5.1
	 */
	public function redirect() {
		$redirect = admin_url( 'admin.php?page=' . $this->gnodesign_url . '' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( is_admin() && isset( $_GET['activated'] ) ) {
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Conditionally load TGMPA
	 *
	 * @since 1.5.1
	 *
	 * @param bool $status
	 */
	public function load_tgmpa( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Get registered TGMPA plugins
	 *
	 * @since 1.5.1
	 */
	protected function get_tgmpa_plugins() {
		$plugins = [
			'all'      => [], // Meaning: all plugins which still have open actions.
			'install'  => [],
			'update'   => [],
			'activate' => [],
		];

		foreach ( $this->tgmpa->plugins as $slug => $plugin ) {
			if ( $this->tgmpa->is_plugin_active( $slug ) && false === $this->tgmpa->does_plugin_have_update( $slug ) ) {
				continue;
			} else {
				$plugins['all'][ $slug ] = $plugin;

				if ( ! $this->tgmpa->is_plugin_installed( $slug ) ) {
					$plugins['install'][ $slug ] = $plugin;
				} else {
					if ( false !== $this->tgmpa->does_plugin_have_update( $slug ) ) {
						$plugins['update'][ $slug ] = $plugin;
					}
					if ( $this->tgmpa->can_plugin_activate( $slug ) ) {
						$plugins['activate'][ $slug ] = $plugin;
					}
				}
			}
		}

		return $plugins;
	}

	/**
	 * Install plugins AJAX function
	 *
	 * @since   1.5.1
	 * @version 1.8.3
	 */
	public function ajax_plugins() {

		if ( ! check_ajax_referer( 'cariera_onboarding_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) || ! $this->status ) {
			exit( 0 );
		}

		$json      = [];
		$tgmpa_url = $this->tgmpa->get_tgmpa_url();
		$plugins   = $this->get_tgmpa_plugins();

		// Activating plugins.
		foreach ( $plugins['activate'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = [
					'url'           => $tgmpa_url,
					'plugin'        => [ $slug ],
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating', 'cariera' ),
				];
				break;
			}
		}

		// Updating plugins.
		foreach ( $plugins['update'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = [
					'url'           => $tgmpa_url,
					'plugin'        => [ $slug ],
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating', 'cariera' ),
				];
				break;
			}
		}

		// Install plugin.
		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = [
					'url'           => $tgmpa_url,
					'plugin'        => [ $slug ],
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing', 'cariera' ),
				];
				break;
			}
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) ); // Used for checking if duplicates happen, move to next plugin.
			wp_send_json( $json );
		} else {
			wp_send_json(
				[
					'done'    => 1,
					'message' => esc_html__( 'Success', 'cariera' ),
				]
			);
		}

		exit;
	}

	/**
	 * Plugins install page setup
	 *
	 * @since   1.5.1
	 * @version 1.9.7
	 */
	public function plugins() {
		if ( ! $this->status ) {
			echo '<div class="onboarding-notice error">';
			echo '<p>' . esc_html__( 'Activate the theme to be able to install required plugins.', 'cariera' ) . '</p>';
			echo '</div>';

			return;
		}

		// Prepare URL, method, and fields for filesystem credentials.
		$url    = wp_nonce_url( add_query_arg( [ 'plugins' => 'go' ] ), 'cariera' );
		$method = '';
		$fields = array_keys( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$creds  = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields );

		// Load the TGMPA bulk installer.
		tgmpa_load_bulk_installer();

		// If credentials are not available, request them.
		if ( false === $creds ) {
			return true;
		}

		// If filesystem credentials are invalid, request them again.
		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}

		// Retrieve the plugins and separate into required and recommended plugins.
		$plugins             = $this->get_tgmpa_plugins();
		$required_plugins    = [];
		$recommended_plugins = [];
		$total_plugins       = count( $plugins['all'] );

		foreach ( $plugins['all'] as $slug => $plugin ) {
			if ( ! empty( $plugin['required'] ) ) {
				$required_plugins[ $slug ] = $plugin;
			} else {
				$recommended_plugins[ $slug ] = $plugin;
			}
		}

		// Display the installation/activation message if plugins are available.
		if ( $total_plugins > 0 ) {
			?>
			<div class="onboarding-notice">
				<p><?php esc_html_e( 'Your website needs a few essential plugins. The following plugins will be installed and activated.', 'cariera' ); ?></p>
				<div class="important-notice">
					<div class="icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="1.5rem" height="1.5rem" viewBox="0 0 24 24">
							<path fill="#b23b3b" d="M12 4c4.411 0 8 3.589 8 8s-3.589 8-8 8s-8-3.589-8-8s3.589-8 8-8m0-2C6.477 2 2 6.477 2 12s4.477 10 10 10s10-4.477 10-10S17.523 2 12 2m1 13h-2v2h2zm-2-2h2l.5-6h-3z"></path>
						</svg>
					</div>
					<p class="message">
						<?php echo wp_kses_post( __( '<strong>Important Notice:</strong> If the plugins fail to install, click the <strong>"Refresh Plugin URLs"</strong> button above and try again.', 'cariera' ) ); ?>
					</p>
				</div>
			</div>
	
			<form action="" method="post" class="install-plugins">
				<div class="selectors">
					<span class="select-all" @click="select_all"><?php esc_html_e( 'Select all', 'cariera' ); ?></span>
					<span class="deselect-all" @click="deselect_all"><?php esc_html_e( 'Deselect all', 'cariera' ); ?></span>
				</div>
	
				<ul class="onboarding-install-plugins">
					<?php $this->render_plugin_list( $required_plugins, 'Required' ); ?>
					<?php $this->render_plugin_list( $recommended_plugins, '' ); ?>
				</ul>
	
				<a href="#" class="onboarding-btn plugin-installer" data-callback="install_plugins">
					<span class="text"><?php esc_html_e( 'Install & Activate', 'cariera' ); ?></span>
					<span class="btn-loader"></span>
				</a>
			</form>
			<?php
		} else {
			echo '<div class="onboarding-notice success">';
			echo '<p>' . esc_html__( 'All plugins have been installed and are up to date. Feel free to proceed with importing the demo content.', 'cariera' ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Render the list of plugins.
	 *
	 * @since   1.8.5
	 * @version 1.8.8
	 *
	 * @param array  $plugins       Array of plugins to render.
	 * @param string $badge_text    Text to show on the badge (e.g., 'Required').
	 */
	private function render_plugin_list( $plugins, $badge_text ) {
		if ( empty( $plugins ) ) {
			return;
		}

		foreach ( $plugins as $slug => $plugin ) {
			?>
			<li data-slug="<?php echo esc_attr( $slug ); ?>">
				<input type="checkbox" name="default_plugins[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_plugins_<?php echo esc_attr( $slug ); ?>" value="1" checked>
	
				<label for="default_plugins_<?php echo esc_attr( $slug ); ?>">
					<i></i>
					<span><?php echo esc_html( $plugin['name'] ); ?></span>
					<?php if ( ! empty( $badge_text ) ) : ?>
						<span class="badge"><?php echo esc_html( $badge_text ); ?></span>
					<?php endif; ?>

					<?php if ( $this->tgmpa->is_plugin_installed( $slug ) && $this->tgmpa->does_plugin_have_update( $slug ) ) : ?>
						<span class="badge update"><?php esc_html_e( 'Update available', 'cariera' ); ?></span>
					<?php endif; ?>
					<span class="message"></span>
				</label>
	
				<div class="loader"><span class="circle"></span></div>
				<span class="checkmark">
					<div class="checkmark_stem"></div>
					<div class="checkmark_kick"></div>
				</span>
			</li>
			<?php
		}
	}

	/**
	 * Plugins install page setup
	 *
	 * @since   1.5.1
	 * @version 1.8.8
	 */
	public function import() {
		get_template_part( 'templates/backend/onboarding/import-requirements' );

		// If theme is not active.
		if ( ! $this->status ) {
			echo '<div class="onboarding-notice error">';
			echo '<p>' . esc_html__( 'Activate the theme to be able to import the demo data.', 'cariera' ) . '</p>';
			echo '</div>';

			return;
		}

		// If core plugin is not installed and activated.
		if ( ! \Cariera\cariera_core_is_activated() ) {
			echo '<div class="onboarding-notice error">';
			echo '<p>' . esc_html__( 'Install & activate all the required plugins to be able to import the demo data.', 'cariera' ) . '</p>';
			echo '</div>';

			return;
		}
	}
}
