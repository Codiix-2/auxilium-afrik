<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Templates {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'archive_template', [ $this, 'archive_template' ] );
		add_filter( 'taxonomy_template', [ $this, 'taxonomy_template' ] );
		add_filter( 'single_template', [ $this, 'single_template' ], 10, 3 );

		// Split view loading for resume post type.
		add_filter( 'cariera_before_split_view_template_loading', [ $this, 'split_view_loading' ], 10, 3 );

		// Resume single page content templates.
		add_action( 'single_resume_content', [ $this,'candidate_portfolio' ], 15 );
		add_action( 'single_resume_content', [ $this, 'single_resume_print' ], 61 );

		// Resume single page sidebar templates.
		add_action( 'cariera_single_resume_sidebar', [ $this,'single_resume_sidebar_overview' ], 10 );
		add_action( 'cariera_single_resume_sidebar', [ $this, 'single_resume_sidebar_map' ], 20 );
		add_action( 'cariera_single_resume_sidebar', [ $this, 'single_featured_resumes' ], 21 );

		// Resume single page templates end.
		add_action( 'cariera_single_resume_after', [ $this, 'single_resume_related_resumes' ], 20 );

		// Single Resume Page V2-3.
		add_action( 'single_resume_content', [ $this, 'single_resume_v2_overview' ], 11 );
		add_action( 'single_resume_content', [ $this, 'single_resume_v2_map' ], 41 );

		// Listing Submission flow backwards compatibility.
		add_action( 'cariera_resume_submission_steps', [ $this, 'listing_submission_flow' ] );
	}

	/**
	 * Filter Archive Template
	 *
	 * @since 0.9.5
	 *
	 * @param mixed $archive_template
	 */
	public function archive_template( $archive_template ) {
		if ( is_post_type_archive( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME ) ) {
			$archive_template = CARIERA_ADDONS_PATH . '/templates/resumes/archive-resume.php';
		}

		return $archive_template;
	}

	/**
	 * Filter Taxonomy Template
	 *
	 * @since 0.9.5
	 *
	 * @param mixed $tax_template
	 */
	public function taxonomy_template( $tax_template ) {
		if ( is_tax( \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY ) ) {
			$tax_template = CARIERA_ADDONS_PATH . '/templates/resumes/taxonomy-category.php';
		}

		return $tax_template;
	}

	/**
	 * Filter on Single Template
	 *
	 * @since 0.9.5
	 *
	 * @param mixed  $template
	 * @param string $type
	 * @param mixed  $templates
	 */
	public function single_template( $template, $type, $templates ) {
		global $post;

		if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
			return $template;
		}

		$single_template = CARIERA_ADDONS_PATH . '/templates/resumes/single-resume.php';

		if ( ! file_exists( $single_template ) ) {
			return $template;
		}

		return $single_template;
	}

	/**
	 * Split view loading for resume post type.
	 *
	 * @since 0.9.5
	 *
	 * @param string   $output
	 * @param \WP_Post $listing
	 * @param string   $post_type
	 */
	public function split_view_loading( $output, $listing, $post_type ) {
		if ( ! $listing instanceof \WP_Post || \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post_type ) {
			return $output;
		}

		// Load restricted template content.
		ob_start();

		$template = CARIERA_ADDONS_PATH . 'templates/resumes/content-single-resume.php';

		if ( file_exists( $template ) ) {
			include $template;
		}

		return ob_get_clean();
	}

	/**
	 * Adding Candidate Portfolio to Single Resume Page
	 *
	 * @since 0.9.9
	 */
	public function candidate_portfolio() {
		if ( ! get_option( 'cariera_resume_manager_enable_portfolio' ) ) {
			return;
		}

		get_job_manager_template_part( 'resumes/single-resume/candidate', 'portfolio', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Adding Print button to Single Resume
	 *
	 * @since 0.9.9
	 */
	public function single_resume_print() {
		get_job_manager_template_part( 'resumes/single-resume/single', 'resume-print', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Adding Resume Overview to the sidebar
	 *
	 * @since 0.9.9
	 */
	public function single_resume_sidebar_overview() {
		get_job_manager_template_part( 'resumes/single-resume/single', 'resume-overview', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Adding Map to the resume sidebar
	 *
	 * @since   0.9.9
	 * @version 1.0.3
	 */
	public function single_resume_sidebar_map() {
		$map_provider = get_option( 'cariera_map_provider' );

		if ( 'none' === $map_provider ) {
			return;
		}

		get_job_manager_template_part( 'resumes/single-resume/listing', 'map', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Featured resumes in single resume page
	 *
	 * @since 0.9.9
	 */
	public function single_featured_resumes() {
		get_job_manager_template_part( 'resumes/single-resume/featured', 'listings', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Adding Related Resumes to single resume page
	 *
	 * @since 0.9.9
	 */
	public function single_resume_related_resumes() {
		if ( ! get_option( 'cariera_resume_manager_related_resumes' ) ) {
			return;
		}

		get_job_manager_template_part( 'resumes/single-resume/related', 'resumes', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Adding Resume overview to the single page
	 *
	 * @since 0.9.9
	 */
	public function single_resume_v2_overview() {
		$layout = cariera_single_resume_layout();

		if ( 'v1' === $layout ) {
			return;
		}

		echo '<div id="candidate-overview" class="candidate-overview">';
		get_job_manager_template_part( 'resumes/single-resume/single', 'resume-overview', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
		echo '</div>';
	}

	/**
	 * Adding Resume overview to the single page
	 *
	 * @since   0.9.9
	 * @version 1.0.3
	 */
	public function single_resume_v2_map() {
		global $post;

		$map_provider = get_option( 'cariera_map_provider' );
		$resume_map   = cariera_get_option( 'cariera_resume_map' );
		$lng          = $post->geolocation_long;
		$lat          = $post->geolocation_lat;
		$layout       = cariera_single_resume_layout();

		if ( 'none' === $map_provider || ! $resume_map || empty( $lng ) || empty( $lat ) || 'v1' === $layout ) {
			return;
		}

		echo '<div id="candidate-map" class="candidate-map">';
		echo '<h2 class="content-title">' . esc_html__( 'Candidate Location', 'cariera-addons' ) . '</h2>';
		get_job_manager_template_part( 'resumes/single-resume/listing', 'map', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
		echo '</div>';
	}

	/**
	 * Resume Submission Flow backwards compatibility support.
	 *
	 * @since 1.0.1
	 */
	public function listing_submission_flow() {
		get_job_manager_template_part( 'resumes/listing-submission', 'flow', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}
}
