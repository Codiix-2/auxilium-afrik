<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Templates {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'post_class', [ $this, 'company_add_post_class' ], 10, 3 );
		add_action( 'cariera_company_no_results', [ $this, 'output_company_no_results' ] );

		// Single Company Page V1.
		add_action( 'cariera_single_company_listing', [ $this, 'company_description' ], 10 );
		add_action( 'cariera_single_company_listing', [ $this, 'company_video' ], 20 );
		add_action( 'cariera_single_company_listing', [ $this, 'single_company_share' ], 30 );
		add_action( 'cariera_single_company_listing', [ $this, 'company_job_listing' ], 40 );
		add_action( 'cariera_company_submission_steps', [ $this, 'company_submission_flow' ] );
		add_action( 'cariera_single_company_sidebar', [ $this, 'single_company_sidebar_overview' ], 10 );
		add_action( 'cariera_single_company_sidebar', [ $this, 'single_company_sidebar_map' ], 20 );
		add_action( 'cariera_single_company_sidebar', [ $this, 'single_featured_companies' ], 21 );
		add_action( 'cariera_single_company_sidebar', [ $this, 'single_company_sidebar' ], 30 );
		add_action( 'cariera_single_company_after', [ $this, 'single_company_related_companies' ], 20 );
		add_action( 'cariera_single_company_after', [ $this, 'edit_single_company' ], 21 );
		add_action( 'cariera_single_company_listing', [ $this, 'single_company_print' ], 31 );

		// Single Company V2.
		add_action( 'cariera_single_company_listing', [ $this, 'single_company_v2_overview' ], 12 );
		add_action( 'cariera_single_company_listing', [ $this, 'single_company_v2_map' ], 13 );

		// Other.
		add_action( 'cariera_single_company_layout', [ $this,'demo_single_company_layout' ] );
	}

	/**
	 * Add post classes to companies
	 *
	 * @since   1.4.5
	 * @version 1.9.3
	 *
	 * @param array  $classes
	 * @param string $class
	 * @param int    $post_id
	 */
	public function company_add_post_class( $classes, $class, $post_id ) {
		$post = get_post( $post_id );

		if ( empty( $post ) || \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY !== $post->post_type ) {
			return $classes;
		}

		$classes[] = 'company';

		if ( cariera_is_company_featured( $post ) ) {
			$classes[] = 'company_featured';
		}

		return $classes;
	}

	/**
	 * Displays some content when no results are found
	 *
	 * @since   1.3.0
	 * @version 1.7.6
	 */
	public function output_company_no_results() {
		get_company_template( 'content-no-companies-found.php' );
	}

	/*
	==================================================================================
			SINGLE COMPANY PAGE
	==================================================================================
	*/

	/**
	 * Single Company Description
	 *
	 * @since   1.3.0
	 * @version 1.7.9
	 */
	public function company_description() {
		if ( empty( get_the_content() ) ) {
			return;
		}
		?>

		<div id="company-description" class="company-description">
			<h2 class="content-title"><?php esc_html_e( 'About the Company', 'cariera-core' ); ?></h2>
			<?php
			the_content();
			wp_link_pages(
				[
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'cariera-core' ),
					'after'  => '</div>',
				]
			);

			do_action( 'cariera_the_company_description' );
			?>
		</div>
		<?php
	}

	/**
	 * Single Company Video
	 *
	 * @since   1.3.0
	 * @version 1.7.6
	 */
	public function company_video() {
		cariera_the_company_video_output();
	}

	/**
	 * Adding Share buttons to Single Job Listing
	 *
	 * @since   1.4.6
	 * @version 1.9.3
	 */
	public function single_company_share() {
		global $company_preview;

		if ( $company_preview || ! cariera_get_option( 'cariera_company_share' ) ) {
			return;
		}

		do_action( 'cariera_social_share' );
	}

	/**
	 * Single Company Job Listings
	 *
	 * @since   1.3.0
	 * @version 1.7.6
	 */
	public function company_job_listing() {
		if ( ! get_option( 'cariera_single_company_active_jobs' ) ) {
			return;
		}

		get_job_manager_template_part( 'content', 'company-job-listings', 'wp-job-manager-companies' );
	}

	/**
	 * Company Submission Flow
	 *
	 * @since   1.4.4
	 * @version 1.7.8
	 */
	public function company_submission_flow() {
		get_job_manager_template_part( 'listing-submission', 'flow', 'wp-job-manager-companies' );
	}

	/**
	 * Adding Company Overview to the sidebar
	 *
	 * @since   1.5.5
	 * @version 1.7.6
	 */
	public function single_company_sidebar_overview() {
		get_job_manager_template_part( 'single-company/single', 'company-overview', 'wp-job-manager-companies' );
	}

	/**
	 * Adding Map to the company sidebar
	 *
	 * @since   1.5.5
	 * @version 1.9.5
	 */
	public function single_company_sidebar_map() {
		$map_provider = get_option( 'cariera_map_provider' );

		if ( 'none' === $map_provider ) {
			return;
		}

		get_job_manager_template_part( 'single-company/listing', 'map', 'wp-job-manager-companies' );
	}

	/**
	 * Featured companies in single company page
	 *
	 * @since 1.7.6
	 */
	public function single_featured_companies() {
		get_job_manager_template_part( 'single-company/featured', 'listings', 'wp-job-manager-companies' );
	}

	/**
	 * Adding Sidebar widget area to the company sidebar
	 *
	 * @since   1.5.5
	 * @version 1.7.6
	 */
	public function single_company_sidebar() {
		dynamic_sidebar( 'sidebar-single-company' );
	}

	/**
	 * Adding Related Companies to Single Company Listing
	 *
	 * @since   1.7.7
	 * @version 1.7.7
	 */
	public function single_company_related_companies() {
		if ( ! get_option( 'cariera_company_related_companies' ) ) {
			return;
		}

		get_job_manager_template_part( 'single-company/related', 'companies', 'wp-job-manager-companies' );
	}

	/**
	 * Edit single company button
	 *
	 * @since   1.5.5
	 * @version 1.7.6
	 */
	public function edit_single_company() {
		global $post, $company_preview;

		if ( $company_preview ) {
			return;
		}

		if ( ! cariera_user_can_edit_company( $post->ID ) ) {
			return;
		}

		$dashboard_id = apply_filters( 'cariera_edit_single_company_dashboard_id', get_option( 'cariera_company_dashboard_page' ) );

		$edit_link = add_query_arg(
			[
				'action'     => 'edit',
				'company_id' => $post->ID,
			],
			get_permalink( $dashboard_id )
		);
		?>

		<a href="<?php echo esc_url( $edit_link ); ?>" class="edit-listing btn-main"><?php esc_html_e( 'Edit Company', 'cariera-core' ); ?></a>
		<?php
	}

	/**
	 * Adding Print button to Single Resume
	 *
	 * @since   1.7.1
	 * @version 1.7.6
	 */
	public function single_company_print() {
		get_job_manager_template_part( 'single-company/single', 'company-print', 'wp-job-manager-companies' );
	}

	/*
	=====================================================
		SINGLE COMPANY PAGE V.2
	=====================================================
	*/

	/**
	 * Adding Company overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.7.6
	 */
	public function single_company_v2_overview() {
		$layout = cariera_single_company_layout();

		if ( 'v1' === $layout ) {
			return;
		}

		echo '<div id="company-overview" class="company-overview">';
		get_job_manager_template_part( 'single-company/single', 'company-overview', 'wp-job-manager-companies' );
		echo '</div>';
	}

	/**
	 * Adding Job overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.9.5
	 */
	public function single_company_v2_map() {
		global $post;

		$map_provider = get_option( 'cariera_map_provider' );
		$company_map  = cariera_get_option( 'cariera_company_map' );
		$lng          = $post->geolocation_long;
		$lat          = $post->geolocation_lat;
		$layout       = cariera_single_company_layout();

		if ( 'none' === $map_provider || ! $company_map || empty( $lng ) || empty( $lat ) || 'v1' === $layout ) {
			return;
		}

		echo '<div id="company-location" class="company-location">';
		echo '<h2 class="content-title">' . esc_html__( 'Company Location', 'cariera-core' ) . '</h2>';
		get_job_manager_template_part( 'single-company/listing', 'map', 'wp-job-manager-companies' );
		echo '</div>';
	}

	/*
	=====================================================
		OTHER FUNCTIONS
	=====================================================
	*/

	/**
	 * Single company page layout for demo showcase purposes.
	 *
	 * @since   1.7.0
	 * @version 1.7.6
	 */
	public function demo_single_company_layout() {
		$value = get_option( 'cariera_single_company_layout' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['company-layout'] ) && ! empty( $_GET['company-layout'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$value = sanitize_text_field( wp_unslash( $_GET['company-layout'] ) );
		}

		return $value;
	}
}
