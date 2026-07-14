<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * List of script handles to defer.
	 *
	 * @var array
	 */
	protected $deferred_scripts = [
		'cariera-core-messages',
		'google-platform-js',
		'recaptcha',
		'google-maps',
	];

	/**
	 * Defer non-critical CSS.
	 *
	 * @var array
	 */
	protected $deferred_styles = [
		'wp-block-library',
		'wc-block-style',
	];

	/**
	 * List of script handles to be completely deregistered/dequeued.
	 *
	 * @var string[]
	 */
	protected array $scripts_to_remove = [
		// 'job-regions',
	];

	/**
	 * List of style handles to be completely deregistered/dequeued.
	 *
	 * @var string[]
	 */
	protected array $styles_to_remove = [
		'wc-block-style',
		'wp-job-manager-job-listings',
		'wp-job-manager-resume-frontend',
		'job-alerts-frontend',
		'jm-application-deadline',
		'wp-job-manager-applications-frontend',
		'wc-paid-listings-packages',
		'wp-job-manager-tags-frontend',
		'wpjml-job-application',
		'resume-alerts-frontend',
	];

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Register Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );

		// Enqueue Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 15 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ], 15 );

		// Dequeue unnecessary assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_unneeded_assets' ], 30 );

		// Defer scripts.
		add_filter( 'script_loader_tag', [ $this, 'defer_scripts' ], 10, 2 );
		add_filter( 'style_loader_tag', [ $this, 'defer_styles' ], 10, 4 );

		// Remove jQuery Migrate.
		add_action( 'wp_default_scripts', [ $this, 'remove_jquery_migrate' ] );
	}

	/**
	 * Register theme assets.
	 *
	 * @since   1.6.3
	 * @version 1.9.9
	 */
	public function register_assets() {
		$version = \Cariera\get_assets_version();
		$suffix  = is_rtl() ? '.rtl' : '';
		$vuejs   = \Cariera\is_dev_mode() ? 'vue.global.js' : 'vue.global.prod.js';

		// Admin.
		wp_register_style( 'cariera-admin', get_template_directory_uri() . '/assets/dist/css/admin.css', [], $version );
		wp_register_script( 'cariera-admin', get_template_directory_uri() . '/assets/dist/js/admin.js', [ 'jquery' ], $version, true );
		wp_localize_script(
			'cariera-admin',
			'cariera_admin',
			[
				'ajax_url' => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
			]
		);

		// Frontend.
		wp_register_style( 'cariera-style', get_template_directory_uri() . '/style.css', [], $version );
		wp_register_style( 'cariera-frontend', get_template_directory_uri() . '/assets/dist/css/frontend' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-main', get_template_directory_uri() . '/assets/dist/js/frontend.js', [ 'jquery' ], $version, true );

		$args = [
			'ajax_url'              => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
			'nonce'                 => wp_create_nonce( '_cariera_nonce' ),
			'theme_url'             => get_template_directory_uri(),
			'cookie_notice'         => cariera_get_option( 'cariera_cookie_notice' ),
			'views_statistics'      => cariera_get_option( 'cariera_dashboard_views_statistics' ),
			'statistics_border'     => cariera_get_option( 'cariera_dashboard_statistics_border' ),
			'statistics_background' => cariera_get_option( 'cariera_dashboard_statistics_background' ),
			'map_provider'          => get_option( 'cariera_map_provider' ),
			'gmap_api_key'          => get_option( 'cariera_gmap_api_key' ),
			'strings'               => [
				'mmenu_text'        => esc_html__( 'Main Menu', 'cariera' ),
				'views_chart_label' => esc_html__( 'Views', 'cariera' ),
			],
		];

		wp_localize_script( 'cariera-main', 'cariera_settings', $args );

		// Blog.
		wp_register_style( 'cariera-blog-feed', get_template_directory_uri() . '/assets/dist/css/blog-feed' . $suffix . '.css', [], $version );
		wp_register_style( 'cariera-single-blog', get_template_directory_uri() . '/assets/dist/css/blog-single' . $suffix . '.css', [], $version );

		// Dashboard.
		wp_register_style( 'cariera-dashboard', get_template_directory_uri() . '/assets/dist/css/dashboard' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-dashboard', get_template_directory_uri() . '/assets/dist/js/dashboard.js', [ 'jquery' ], $version, true );

		// Dashboard Charts.
		wp_register_script( 'cariera-dashboard-charts', get_template_directory_uri() . '/assets/dist/js/dashboard-charts.js', [ 'jquery' ], $version, true );

		// WooCommerce General Styles.
		wp_register_style( 'cariera-wc-general-styles', get_template_directory_uri() . '/assets/dist/css/woocommerce-general' . $suffix . '.css', [], $version );

		// WooCommerce Product Page.
		wp_register_style( 'cariera-wc-product-page', get_template_directory_uri() . '/assets/dist/css/woocommerce-product' . $suffix . '.css', [], $version );

		// WooCommerce Cart Page.
		wp_register_style( 'cariera-wc-cart-page', get_template_directory_uri() . '/assets/dist/css/woocommerce-cart' . $suffix . '.css', [], $version );

		// WooCommerce Checkout Page.
		wp_register_style( 'cariera-wc-checkout-page', get_template_directory_uri() . '/assets/dist/css/woocommerce-checkout' . $suffix . '.css', [], $version );

		// Vuejs.
		wp_register_script( 'vue', get_template_directory_uri() . '/assets/vendors/vuejs/' . $vuejs, [], '3.5.9', true );

		// Select2.
		if ( ! wp_script_is( 'select2', 'registered' ) && \Cariera\wp_job_manager_is_activated() ) {
			\WP_Job_Manager::register_select2_assets();
		} elseif ( ! \Cariera\wp_job_manager_is_activated() ) {
			wp_register_style( 'select2', get_template_directory_uri() . '/assets/vendors/select2/select2.min.css', [], '4.0.13' );
			wp_register_script( 'select2', get_template_directory_uri() . '/assets/vendors/select2/select2.min.js', [ 'jquery' ], '4.0.13', true );
		}

		// Cariera Listings Search Forms.
		wp_register_style( 'cariera-wpjm-search-forms', get_template_directory_uri() . '/assets/dist/css/wpjm-search-forms' . $suffix . '.css', [], $version );

		// Cariera Listing Dashboards.
		wp_register_style( 'cariera-wpjm-dashboards', get_template_directory_uri() . '/assets/dist/css/wpjm-dashboards' . $suffix . '.css', [], $version );

		// Cariera Listing Submissions.
		wp_register_style( 'cariera-wpjm-submissions', get_template_directory_uri() . '/assets/dist/css/wpjm-submissions' . $suffix . '.css', [], $version );

		// WPJM Job Listings.
		wp_register_style( 'cariera-job-listings', get_template_directory_uri() . '/assets/dist/css/job-listings' . $suffix . '.css', [], $version );
		wp_register_style( 'cariera-single-job-listing', get_template_directory_uri() . '/assets/dist/css/single-job' . $suffix . '.css', [], $version );

		// WPJM Resumes.
		wp_register_style( 'cariera-resume-listings', get_template_directory_uri() . '/assets/dist/css/resume-listings' . $suffix . '.css', [], $version );
		wp_register_style( 'cariera-single-resume', get_template_directory_uri() . '/assets/dist/css/single-resume' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-single-resume', get_template_directory_uri() . '/assets/dist/js/single-resume.js', [ 'jquery' ], $version, true );

		// Cariera Company Manager.
		wp_register_style( 'cariera-company-listings', get_template_directory_uri() . '/assets/dist/css/company-listings' . $suffix . '.css', [], $version );
		wp_register_style( 'cariera-single-company', get_template_directory_uri() . '/assets/dist/css/single-company' . $suffix . '.css', [], $version );

		// WPJM Alerts.
		wp_register_style( 'cariera-wpjm-alerts', get_template_directory_uri() . '/assets/dist/css/wpjm-alerts' . $suffix . '.css', [], $version );

		// WPJM Resume Alerts.
		wp_register_style( 'cariera-wpjm-resume-alerts', get_template_directory_uri() . '/assets/dist/css/wpjm-resume-alerts' . $suffix . '.css', [], $version );

		// WPJM Applications.
		wp_register_style( 'cariera-wpjm-applications-dashboard', get_template_directory_uri() . '/assets/dist/css/job-applications-dashboard' . $suffix . '.css', [], $version );

		// WPJM Bookmarks.
		wp_register_style( 'cariera-wpjm-bookmarks', get_template_directory_uri() . '/assets/dist/css/wpjm-bookmarks' . $suffix . '.css', [], $version );

		// WPJM Listing Submitted.
		wp_register_style( 'cariera-wpjm-listing-submitted', get_template_directory_uri() . '/assets/dist/css/wpjm-listing-submitted' . $suffix . '.css', [], $version );

		// WPJM WC Paid Listings.
		wp_register_style( 'cariera-wpjm-wcpl', get_template_directory_uri() . '/assets/dist/css/wpjm-wcpl' . $suffix . '.css', [], $version );

		// Icons.
		wp_register_style( 'line-awesome', get_template_directory_uri() . '/assets/vendors/font-icons/line-awesome.min.css', [], '1.3.0' );
		wp_register_style( 'font-awesome-5', get_template_directory_uri() . '/assets/vendors/font-icons/all.min.css', [], '5.15.3' );
		wp_register_style( 'simple-line-icons', get_template_directory_uri() . '/assets/vendors/font-icons/simple-line-icons.min.css', [], '2.4.0' );
		wp_register_style( 'iconsmind', get_template_directory_uri() . '/assets/vendors/font-icons/iconsmind.min.css', [], $version );

		// Font Icon Picker.
		wp_register_style( 'font-icon-picker', get_template_directory_uri() . '/assets/vendors/fonticon-picker/fonticonpicker.css', [], '3.1.1' );
		wp_register_script( 'font-icon-picker', get_template_directory_uri() . '/assets/vendors/fonticon-picker/jquery.fonticonpicker.js', [ 'jquery' ], '3.1.1', true );

		// Countdown Elementor Element.
		wp_register_script( 'cariera-countdown', get_template_directory_uri() . '/assets/dist/js/elements/countdown.js', [], $version, true );

		// CountUp Elementor Element.
		wp_register_script( 'cariera-countup', get_template_directory_uri() . '/assets/dist/js/elements/countup.js', [], $version, true );
	}

	/**
	 * Enqueue theme assets.
	 *
	 * @since   1.6.3
	 * @version 2.0.0
	 */
	public function enqueue_assets() {
		// Select2.
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'select2' );

		// Lineawesome Icons.
		wp_enqueue_style( 'line-awesome' );

		// Fontawesome icons.
		if ( get_option( 'cariera_fonticon_fontawesome' ) ) {
			wp_enqueue_style( 'font-awesome-5' );
		}

		// Simple Line Icons.
		if ( get_option( 'cariera_fonticon_simplelineicons' ) ) {
			wp_enqueue_style( 'simple-line-icons' );
		}

		// Iconsmind.
		if ( get_option( 'cariera_fonticon_iconsmind' ) ) {
			wp_enqueue_style( 'iconsmind' );
		}

		// Frontend Styles.
		wp_enqueue_style( 'cariera-style' );
		wp_enqueue_style( 'cariera-frontend' );
		wp_add_inline_style( 'cariera-frontend', $this->dynamic_styles() );

		// Main Script.
		wp_enqueue_script( 'cariera-main' );

		// Comment Reply Script.
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// WooCommerce Styles.
		if ( \Cariera\wc_is_activated() ) {
			wp_enqueue_style( 'cariera-wc-general-styles' );
			if ( is_product() ) {
				wp_enqueue_style( 'cariera-wc-product-page' );
			}
			if ( is_cart() ) {
				wp_enqueue_style( 'cariera-wc-cart-page' );
			}
			if ( is_checkout() ) {
				wp_enqueue_style( 'cariera-wc-checkout-page' );
			}
		}
	}

	/**
	 * Admin enqueue assets
	 *
	 * @since   1.5.2
	 * @version 1.8.4
	 *
	 * @param string $hook
	 */
	public function enqueue_admin_assets( $hook ) {
		$target_pages = [
			'edit-tags.php',
			'term.php',
			'post.php',
			'nav-menus.php',
		];

		if ( in_array( $hook, $target_pages, true ) ) {
			wp_enqueue_style( 'font-icon-picker' );
			wp_enqueue_script( 'font-icon-picker' );

			wp_enqueue_style( 'line-awesome' );

			if ( get_option( 'cariera_fonticon_fontawesome' ) ) {
				wp_enqueue_style( 'font-awesome-5' );
			}

			if ( get_option( 'cariera_fonticon_simplelineicons' ) ) {
				wp_enqueue_style( 'simple-line-icons' );
			}

			if ( get_option( 'cariera_fonticon_iconsmind' ) ) {
				wp_enqueue_style( 'iconsmind' );
			}
		}

		wp_enqueue_style( 'cariera-admin' );
		wp_enqueue_script( 'cariera-admin' );
	}

	/**
	 * Defer some of the theme scripts.
	 *
	 * @since 1.6.3
	 *
	 * @param [type] $tag
	 * @param [type] $handle
	 */
	public function defer_scripts( $tag, $handle ) {
		if ( in_array( $handle, $this->deferred_scripts, true ) ) {
			return str_replace( '<script ', '<script async defer ', $tag );
		}

		return $tag;
	}

	/**
	 * Defer non-critical CSS.
	 *
	 * @see     https://web.dev/defer-non-critical-css/
	 * @since   1.6.3
	 *
	 * @param string $tag
	 * @param string $handle
	 * @param string $href
	 * @param string $media
	 */
	public function defer_styles( $tag, $handle, $href, $media ) {
		if ( in_array( $handle, $this->deferred_styles, true ) ) {
			return str_replace( "rel='stylesheet'", "rel='preload stylesheet' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $tag );
		}

		return $tag;
	}

	/**
	 * Deregister/remove unneeded scripts & styles
	 *
	 * @since   1.3.0
	 * @version 1.9.7
	 */
	public function remove_unneeded_assets() {
		// Remove scripts.
		foreach ( $this->scripts_to_remove as $script ) {
			if ( wp_script_is( $script, 'enqueued' ) ) {
				wp_dequeue_script( $script );
			} elseif ( wp_script_is( $script, 'registered' ) ) {
				wp_deregister_script( $script );
			}
		}

		// Remove styles.
		foreach ( $this->styles_to_remove as $style ) {
			if ( wp_style_is( $style, 'enqueued' ) ) {
				wp_dequeue_style( $style );
			} elseif ( wp_style_is( $style, 'registered' ) ) {
				wp_deregister_style( $style );
			}
		}

		// Specifically target wp-job-manager-job-dashboard after dashboard output.
		add_action(
			'job_manager_job_dashboard_after',
			function () {
				wp_dequeue_style( 'wp-job-manager-job-dashboard' );
			},
			10
		);
	}

	/**
	 * Dynamic CSS styles
	 *
	 * @since   1.5.2
	 * @version 1.9.7
	 */
	public function dynamic_styles() {
		// Retrieve options.
		$options = [
			'colors'    => [
				'main'         => cariera_get_option( 'cariera_main_color' ),
				'secondary'    => cariera_get_option( 'cariera_secondary_color' ),
				'wrapper'      => cariera_get_option( 'cariera_wrapper_color' ),
				'navbar'       => cariera_get_option( 'cariera_navbar_bg' ),
				'menu_hover'   => cariera_get_option( 'cariera_menu_hover_color' ),
				'footer'       => cariera_get_option( 'cariera_footer_bg' ),
				'footer_title' => cariera_get_option( 'cariera_footer_title_color' ),
				'footer_text'  => cariera_get_option( 'cariera_footer_text_color' ),
			],
			'logo'      => [
				'width'  => absint( cariera_get_option( 'logo_width' ) ),
				'height' => absint( cariera_get_option( 'logo_height' ) ),
				'margin' => cariera_get_option( 'logo_margins' ),
			],
			'radius'    => [
				'scale' => get_option( 'cariera_search_radius' ),
				'unit'  => get_option( 'cariera_search_radius_unit' ),
			],
			'body_typo' => cariera_get_option( 'cariera_body_typo' ),
		];

		$body_color = $options['body_typo']['color'] ?? '#948a99';

		// Start output buffering.
		ob_start();
		?>
		:root {
			--cariera-primary: <?php echo esc_attr( $options['colors']['main'] ); ?>;
			--cariera-secondary: <?php echo esc_attr( $options['colors']['secondary'] ); ?>;
			--cariera-body-wrapper-bg: <?php echo esc_attr( $options['colors']['wrapper'] ); ?>;
			--cariera-body-text: <?php echo esc_attr( $body_color ); ?>;
			--cariera-header-bg: <?php echo esc_attr( $options['colors']['navbar'] ); ?>;
			--cariera-menu-hover: <?php echo esc_attr( $options['colors']['menu_hover'] ); ?>;
			--cariera-footer-bg: <?php echo esc_attr( $options['colors']['footer'] ); ?>;
			--cariera-footer-title: <?php echo esc_attr( $options['colors']['footer_title'] ); ?>;
			--cariera-footer-color: <?php echo esc_attr( $options['colors']['footer_text'] ); ?>;
		}

		<?php
		// Logo CSS.
		$logo_css = [];
		if ( $options['logo']['width'] ) {
			$logo_css[] = 'width: ' . esc_attr( $options['logo']['width'] ) . 'px';
		}
		if ( $options['logo']['height'] ) {
			$logo_css[] = 'height: ' . esc_attr( $options['logo']['height'] ) . 'px';
		}
		if ( ! empty( $options['logo']['margin'] ) ) {
			foreach ( $options['logo']['margin'] as $side => $value ) {
				if ( $value ) {
					$logo_css[] = "margin-{$side}: " . esc_attr( $value ) . ' !important';
				}
			}
		}
		if ( $logo_css ) {
			echo 'header .navbar-brand img {' . implode( '; ', $logo_css ) . ';}';
		}

		// Radius scale.
		if ( $options['radius']['scale'] ) {
			echo ".range-output:after {
				content: '" . esc_attr( $options['radius']['unit'] ) . "';
			}";
		}

		// Minify the CSS output.
		$css = ob_get_clean();
		$css = preg_replace( '/\s+/', ' ', $css ); // Remove whitespace.
		$css = str_replace( [ '; ', ': ', ' {', '{ ', ' }', '} ', ' ,' ], [ ';', ':', '{', '{', '}', '}', ',' ], $css );

		return $css;
	}

	/**
	 * Remove jQuery Migrate from the frontend.
	 *
	 * @since 2.0.0
	 *
	 * @param object $scripts
	 */
	public function remove_jquery_migrate( $scripts ) {
		if ( is_admin() ) {
			return;
		}

		if ( isset( $scripts->registered['jquery'] ) ) {
			$scripts->registered['jquery']->deps = array_diff(
				$scripts->registered['jquery']->deps,
				[ 'jquery-migrate' ]
			);
		}
	}
}
