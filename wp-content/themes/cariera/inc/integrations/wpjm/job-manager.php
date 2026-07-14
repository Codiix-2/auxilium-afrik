<?php

namespace Cariera\Integrations\WPJM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_Manager {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		require get_template_directory() . '/inc/wp-job-manager/functions.php';

		// Prevent from enqueueing wpjm frontend.css.
		add_filter( 'job_manager_enqueue_frontend_style', '__return_false', 30 );
		add_filter( 'job_manager_addon_upsell_applications', '__return_false' );
		add_filter( 'job_manager_addon_upsell_resumes', '__return_false' );

		// Remove Job Search Button.
		add_filter( 'job_manager_job_filters_show_submit_button', '__return_false' );

		// Schema.
		add_filter( 'wpjm_get_job_listing_structured_data', [ $this, 'job_schema_salary_range' ], 10, 2 );

		// General.
		add_action( 'init', [ $this, 'wpjm_remove_actions' ] );
		add_action( 'single_job_listing_start', [ $this, 'job_content_start' ] );
		add_action( 'wp_head', [ $this, 'job_og_image' ] );
		add_action( 'cariera_job_listing_title_after', [ $this, 'job_listing_status_title' ] );

		// Single Job Page V1.
		add_action( 'single_job_listing_start', [ $this, 'single_job_application_msg' ], 20 );
		add_action( 'single_job_listing_end', [ $this, 'single_job_share' ] );
		add_action( 'single_job_listing_end', [ $this, 'single_job_print' ], 12 );
		add_action( 'cariera_single_job_listing_after', [ $this, 'single_job_related_jobs' ], 20 );
		add_action( 'cariera_single_job_listing_after', [ $this, 'edit_single_job_listing' ], 21 );
		add_action( 'cariera_single_job_listing_sidebar', [ $this, 'single_job_sidebar_overview' ], 10 );
		add_action( 'cariera_single_job_listing_sidebar', [ $this, 'single_job_sidebar_map' ], 20 );
		add_action( 'cariera_single_job_listing_sidebar', [ $this, 'single_featured_jobs' ], 21 );
		add_action( 'cariera_single_job_listing_sidebar', [ $this, 'single_job_sidebar' ], 30 );
		add_action( 'single_job_listing_meta_end', [ $this, 'single_job_application' ], 999 );

		// Single Job Page V2-3.
		add_action( 'single_job_listing_end', [ $this,'single_job_v2_overview' ], 7 );
		add_action( 'single_job_listing_end', [ $this,'single_job_v2_map' ], 8 );
		add_action( 'cariera_job_listing_actions', [ $this, 'single_job_v2_application' ], 10 );
		add_action( 'cariera_job_listing_actions', [ $this, 'single_job_v2_expire' ], 11 );

		// Submission.
		add_action( 'submit_job_step_choose_package_submit_text', [ $this, 'wcpl_package_submit_text' ] );
		add_action( 'cariera_job_submission_steps', [ $this, 'job_submission_flow' ] );
		add_action( 'submit_job_form_job_fields_start', [ $this, 'submit_job_fields_start' ] );
		add_action( 'submit_job_form_job_fields_end', [ $this, 'submit_job_fields_end' ] );
		add_action( 'submit_job_form_company_fields_start', [ $this, 'submit_company_fields_start' ] );
		add_action( 'submit_company_form_company_fields_start', [ $this, 'submit_company_fields_start' ] );
		add_action( 'submit_job_form_company_fields_end', [ $this, 'submit_company_fields_end' ], 20 );
		add_action( 'submit_company_form_company_fields_end', [ $this, 'submit_company_fields_end' ] );
		add_filter( 'submit_job_form_submit_button_text', [ $this, 'submit_job_form_button_text' ] );

		// AJAX Functions.
		add_action( 'wp_ajax_load_quickjob_content', [ $this, 'load_quickview_content_callback' ] );
		add_action( 'wp_ajax_nopriv_load_quickjob_content', [ $this, 'load_quickview_content_callback' ] );
		add_action( 'wp_ajax_cariera_listing_keyword_search', [ $this, 'search_listings' ] );
		add_action( 'wp_ajax_nopriv_cariera_listing_keyword_search', [ $this, 'search_listings' ] );

		// Other.
		add_filter( 'submit_job_form_wp_editor_args', [ $this, 'customize_editor_toolbar' ] );
		add_filter( 'wpcf7_mail_components', [ $this, 'wpjm_wpcf7_notification_email' ], 10, 3 );
		add_filter( 'job_manager_get_dashboard_date_format', [ $this, 'wp_date_format' ] );

		// Demo.
		add_action( 'cariera_job_manager_single_job_layout', [ $this, 'demo_single_job_layout' ] );

		// Job Statistics.
		add_filter( 'wpjm_get_registered_stats', [ $this, 'job_stats' ] );
		add_filter( 'job_manager_job_stats_summary', [ $this, 'job_stats_summary' ], 10, 2 );
	}

	/**
	 * Add custom schema for salary range
	 *
	 * @since 1.9.0
	 *
	 * @param array    $schema
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	public function job_schema_salary_range( $schema, $post ) {
		if ( ! get_option( 'cariera_enable_filter_salary' ) ) {
			return $schema;
		}

		$salary_min = (float) get_post_meta( $post->ID, '_salary_min', true );
		$salary_max = (float) get_post_meta( $post->ID, '_salary_max', true );
		$currency   = get_option( 'cariera_currency_setting' );

		if ( $salary_min ) {
			$schema['baseSalary'] = [
				'@type'    => 'MonetaryAmount',
				'currency' => $currency,
				'value'    => [
					'@type'    => 'QuantitativeValue',
					'minValue' => $salary_min,
					'unitText' => 'YEAR',
				],
			];
		}

		if ( $salary_max ) {
			$schema['baseSalary']['value']['maxValue'] = $salary_max;
		}

		return $schema;
	}

	/**
	 * Remove WPJM action to handle certain templates
	 *
	 * @since   1.3.0
	 * @version 1.9.8
	 */
	public function wpjm_remove_actions() {
		$layout = cariera_single_job_layout();

		remove_action( 'single_job_listing_start', 'job_listing_meta_display', 20 );

		if ( 'v1' !== $layout ) {
			remove_action( 'single_job_listing_start', 'job_listing_company_display', 30 );
		}

		if ( class_exists( 'WP_Job_Manager_Promoted_Jobs_Admin' ) ) {
			remove_filter( 'manage_edit-job_listing_columns', [ \WP_Job_Manager_Promoted_Jobs_Admin::instance(), 'promoted_jobs_columns' ] );
		}
	}

	/**
	 * Bind default `job_content_start` to the theme
	 *
	 * @since 1.3.8.1
	 */
	public function job_content_start() {
		return do_action( 'job_content_start' );
	}

	/**
	 * Add og:image tag for jobs
	 *
	 * @since 1.4.6
	 */
	public function job_og_image() {
		if ( is_singular( 'job_listing' ) ) {
			echo '<meta property="og:image" content="' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ) . '" />';
		}
	}

	/**
	 * Job Listing status badges
	 *
	 * @since 1.4.8
	 */
	public function job_listing_status_title() {
		global $post;

		if ( is_position_filled() ) {
			echo '<span class="job-listing-status-badge filled">' . esc_html__( 'filled', 'cariera' ) . '</span>';
		}

		if ( 'expired' === $post->post_status ) {
			echo '<span class="job-listing-status-badge expired">' . esc_html__( 'expired', 'cariera' ) . '</span>';
		}
	}

	/**
	 * Modify the registered job statistics.
	 *
	 * @since   1.7.9
	 * @version 1.8.0
	 *
	 * @param array $settings
	 */
	public function job_stats( $settings = [] ) {
		// Fix application stats collection.
		$settings['job_apply_click']['args']['element'] = '.application_button';

		// Contact listing stats.
		$settings['cariera_job_contact_click'] = [
			'type'   => 'domEvent',
			'args'   => [
				'element' => '.contact-listing-btn',
				'event'   => 'click',
			],
			'unique' => true,
			'page'   => 'listing',
		];

		return $settings;
	}

	/**
	 * Add bookmark count to job listing overlay stats section.
	 *
	 * @since 1.8.0
	 * @version 1.8.0
	 *
	 * @param array    $stats
	 * @param \WP_Post $job
	 *
	 * @return array
	 */
	public function job_stats_summary( $stats, $job ) {

		$job_stats = new \WP_Job_Manager\Job_Listing_Stats( $job->ID );

		if ( ! empty( $stats['interest']['stats'] ) && ! empty( $job->ID ) ) {
			$stats['interest']['stats'][] =
			[
				'icon'  => 'cursor',
				'label' => esc_html__( 'Contact Clicks', 'cariera' ),
				'value' => $job_stats->get_event_total( 'cariera_job_contact_click' ),
			];
		}

		return $stats;
	}

	/*
	=====================================================
		SINGLE JOB PAGE
	=====================================================
	*/

	/**
	 * Adding Application message to Single Job Listing
	 *
	 * @since   1.3.0
	 * @version 1.7.7
	 */
	public function single_job_application_msg() {
		if ( ! class_exists( 'WP_Job_Manager_Applications' ) ) {
			return;
		}

		if ( is_position_filled() ) { ?>
			<div class="job-manager-message success position-filled">
				<?php esc_html_e( 'This position has been filled', 'cariera' ); ?>
			</div>
		<?php } ?>
		
		<?php if ( ! candidates_can_apply() ) { ?>
			<div class="job-manager-message error applications-closed">
				<?php esc_html_e( 'Applications have closed', 'cariera' ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Adding Share buttons to Single Job Listing
	 *
	 * @since   1.3.0
	 * @version 1.9.6
	 */
	public function single_job_share() {
		global $job_preview;

		if ( $job_preview || ! cariera_get_option( 'cariera_job_share' ) ) {
			return;
		}

		do_action( 'cariera_social_share' );
	}

	/**
	 * Adding Related Jobs to Single Job Listing
	 *
	 * @since   1.3.0
	 * @version 1.7.7
	 */
	public function single_job_related_jobs() {
		if ( ! get_option( 'cariera_job_manager_related_jobs' ) ) {
			return;
		}

		get_job_manager_template_part( 'single-job/related-jobs' );
	}

	/**
	 * Edit single job listing button
	 *
	 * @since   1.5.5
	 * @version 1.6.2
	 */
	public function edit_single_job_listing() {
		global $post, $job_preview;

		if ( $job_preview ) {
			return;
		}

		if ( ! job_manager_user_can_edit_job( $post->ID ) ) {
			return;
		}

		$dashboard_id = apply_filters( 'cariera_edit_single_job_listing_dashboard_id', get_option( 'job_manager_job_dashboard_page_id' ) );

		$edit_link = add_query_arg(
			[
				'action' => 'edit',
				'job_id' => $post->ID,
			],
			get_permalink( $dashboard_id )
		);
		?>

		<a href="<?php echo esc_url( $edit_link ); ?>" class="edit-listing btn-main"><?php esc_html_e( 'Edit Job', 'cariera' ); ?></a>
		<?php
	}

	/**
	 * Adding Job Overview to the sidebar
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_sidebar_overview() {
		get_job_manager_template_part( 'single-job/single-job-listing-overview' );
	}

	/**
	 * Adding Map to the job sidebar
	 *
	 * @since   1.5.5
	 * @version 1.9.5
	 */
	public function single_job_sidebar_map() {
		$map_provider = get_option( 'cariera_map_provider' );

		if ( 'none' === $map_provider ) {
			return;
		}

		get_job_manager_template_part( 'single-job/listing-map' );
	}

	/**
	 * Featured job listings in single job page
	 *
	 * @since 1.7.6
	 */
	public function single_featured_jobs() {
		get_job_manager_template_part( 'single-job/featured-listings' );
	}

	/**
	 * Adding Sidebar widget area to the job sidebar
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_sidebar() {
		dynamic_sidebar( 'sidebar-single-job' );
	}

	/**
	 * Adding job application to the job-overview
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_application() {
		$layout = cariera_single_job_layout();

		if ( 'v1' !== $layout ) {
			return;
		}

		get_job_manager_template_part( 'single-job/single-job-application' );
	}

	/*
	=====================================================
		SINGLE JOB PAGE V.2-3
	=====================================================
	*/

	/**
	 * Adding Job overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_v2_overview() {
		$layout = cariera_single_job_layout();

		if ( 'v1' === $layout ) {
			return;
		}

		echo '<div class="job-overview">';
		get_job_manager_template_part( 'single-job/single-job-listing-overview' );
		echo '</div>';
	}

	/**
	 * Adding Job overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.9.5
	 */
	public function single_job_v2_map() {
		global $post;

		$map_provider = get_option( 'cariera_map_provider' );
		$job_map      = cariera_get_option( 'cariera_job_map' );
		$lng          = $post->geolocation_long;
		$lat          = $post->geolocation_lat;
		$layout       = cariera_single_job_layout();

		if ( 'none' === $map_provider || ! $job_map || empty( $lng ) || empty( $lat ) || 'v1' === $layout ) {
			return;
		}

		echo '<div class="job-map">';
		echo '<h2 class="content-title">' . esc_html__( 'Job Location', 'cariera' ) . '</h2>';
		get_job_manager_template_part( 'single-job/listing-map' );
		echo '</div>';
	}

	/**
	 * Adding Job overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_v2_application() {
		get_job_manager_template_part( 'single-job/single-job-application' );
	}

	/**
	 * Adding Job expiration date under the listing actions
	 *
	 * @since   1.5.5
	 * @version 1.5.6
	 */
	public function single_job_v2_expire() {
		global $post;

		$expired_date = get_post_meta( $post->ID, '_job_expires', true );

		if ( empty( $expired_date ) ) {
			return;
		}
		?>

		<div class="job-expiration">
			<span><?php esc_html_e( 'Expiration Date:', 'cariera' ); ?></span>
			<span class="expiration-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expired_date ) ) ); ?></span>
		</div>
		<?php
	}

	/*
	=====================================================
		JOB SUBMISSION HTML MARKUP
	=====================================================
	*/

	/**
	 * Submit button text for WC Paid Listings step
	 *
	 * @since   1.5.2
	 * @version 1.5.3
	 */
	public function wcpl_package_submit_text() {
		return esc_html__( 'Select Package', 'cariera' );
	}

	/**
	 * Job Submission Flow
	 *
	 * @since   1.3.2
	 * @version 1.7.8
	 */
	public function job_submission_flow() {
		get_job_manager_template_part( 'listing-submission-flow' );
	}

	/**
	 * Job submission fields start
	 *
	 * @since   1.4.0
	 * @version 1.7.9
	 */
	public function submit_job_fields_start() {
		echo '<div class="submit-listing-box submit-job_job-info">';
		echo '<h2 class="title">' . esc_html__( 'Job Details', 'cariera' ) . '</h2>';
		echo '<div class="form-fields">';
	}

	/**
	 * Job submission fields end
	 *
	 * @since 1.4.0
	 */
	public function submit_job_fields_end() {
		echo '</div></div>';
	}

	/**
	 * Company submission fields start
	 *
	 * @since   1.4.0
	 * @version 1.7.9
	 */
	public function submit_company_fields_start() {
		echo '<div class="submit-listing-box submit-job_company-info">';
		echo '<h2 class="title">' . esc_html__( 'Company Details', 'cariera' ) . '</h2>';
		echo '<div class="form-fields">';
	}

	/**
	 * Company submission fields end
	 *
	 * @since 1.4.0
	 */
	public function submit_company_fields_end() {
		echo '</div></div>';
	}

	/**
	 * Company selection
	 *
	 * @since 1.4.0
	 *
	 * @param string $text
	 */
	public function submit_job_form_button_text( $text ) {
		return esc_html__( 'Preview Listing', 'cariera' );
	}

	/*
	=====================================================
		OTHER FUNCTIONS
	=====================================================
	*/

	/**
	 * Job Quickview AJAX function
	 *
	 * @since   1.3.1
	 * @version 1.8.4
	 */
	public function load_quickview_content_callback() {
		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_nonce', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['id'] ) ) {
			die( '0' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$job_id = absint( (int) ( $_POST['id'] ) );

		global $post;
		$post = get_post( $job_id );

		$classes = 'cariera-quickview-wrapper job-listing single-job-v1';
		ob_start();

		get_job_manager_template(
			'job-templates/listing-quickview.php',
			[
				'job_id'  => $job_id,
				'classes' => $classes,
				'post'    => $post,
			]
		);

		$return = ob_get_clean();
		wp_reset_postdata();

		die( $return );
	}

	/**
	 * AJAX Job Search Suggestions
	 *
	 * @since   1.3.1
	 * @version 1.9.4
	 */
	public function search_listings() {
		check_ajax_referer( '_cariera_nonce', 'nonce' );

		$listing_type = isset( $_REQUEST['listing_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['listing_type'] ) ) : '';
		$search_term  = isset( $_REQUEST['term'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) : '';

		// Limit results for performance.
		$search_query = new \WP_Query(
			[
				's'              => $search_term,
				'post_type'      => $listing_type,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => 10,
			]
		);

		$posts    = $search_query->posts;
		$response = [];

		if ( $posts ) {
			foreach ( $posts as $post_id ) {
				$logo_img = (string) $this->get_logo_image( $listing_type, $post_id );
				$location = $this->get_location( $post_id, $listing_type );

				$response[] = sprintf(
					'<li><a class="search-item" href="%s"><img class="item-thumb" src="%s"><div class="item-details"><span class="title">%s</span><span class="location">%s</span></div></a></li>',
					esc_url( get_permalink( $post_id ) ),
					esc_url( $logo_img ),
					esc_html( get_the_title( $post_id ) ),
					esc_html( $location )
				);
			}
		}

		if ( empty( $response ) ) {
			$response[] = sprintf( '<li>%s</li>', esc_html__( 'Nothing found', 'cariera' ) );
		}

		$output = apply_filters( 'cariera_search_keyword_autocomplete_output', sprintf( '<ul>%s</ul>', implode( ' ', $response ) ) );

		wp_send_json_success( $output );
	}

	/**
	 * Get the logo image based on the listing type.
	 *
	 * @since 1.8.4
	 * @version 1.9.4
	 *
	 * @param string $listing_type
	 * @param int    $post_id
	 */
	private function get_logo_image( $listing_type, $post_id ) {
		$logo_img = '';

		switch ( $listing_type ) {
			case 'job_listing':
				if ( get_option( 'cariera_company_manager_integration', false ) ) {
					$company = cariera_get_the_company( $post_id );
					$logo    = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
				} else {
					$logo = get_the_company_logo( $post_id, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
				}

				$logo_img = ! empty( $logo ) ? $logo : apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
				break;
			case 'resume':
				$logo     = get_the_candidate_photo( $post_id );
				$logo_img = ! empty( $logo ) ? $logo : apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
				break;
			case 'company':
				$logo     = get_the_company_logo( $post_id );
				$logo_img = ! empty( $logo ) ? $logo : apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
				break;
		}

		return apply_filters( 'cariera_listing_keyword_autocomplete_image', $logo_img, $listing_type, $post_id );
	}

	/**
	 * Get the location based on the listing type.
	 *
	 * @since 1.8.4
	 *
	 * @param int    $post_id
	 * @param string $listing_type
	 */
	private function get_location( $post_id, $listing_type ) {
		$location = '';

		switch ( $listing_type ) {
			case 'job_listing':
				$location = get_post_meta( $post_id, '_job_location', true );
				break;
			case 'resume':
				$location = get_post_meta( $post_id, '_candidate_location', true );
				break;
			case 'company':
				$location = get_post_meta( $post_id, '_company_location', true );
				break;
		}

		return apply_filters( 'cariera_listing_keyword_autocomplete_location', $location, $listing_type, $post_id );
	}

	/**
	 * Output the job's min & max salary if there is any
	 *
	 * @since 1.4.1
	 *
	 * @param array $args
	 */
	public function customize_editor_toolbar( $args ) {
		$args['tinymce']['toolbar1'] = 'formatselect,|,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,undo,redo';
		return $args;
	}

	/**
	 * Changing the email for candidates & companies in CF7
	 *
	 * @since 1.3.0
	 *
	 * @param [type] $components
	 * @param [type] $cf7
	 * @param [type] $three
	 */
	public function wpjm_wpcf7_notification_email( $components, $cf7, $three = null ) {
		$forms = apply_filters(
			'cariera_wpjm_wpcf7_notification_email_forms',
			[
				'company' => [
					'contact' => get_option( 'cariera_single_company_contact_form' ),
				],
				'resume'  => [
					'contact' => get_option( 'resume_manager_single_resume_contact_form' ),
				],
			]
		);

		$submission = \WPCF7_Submission::get_instance();
		$unit_tag   = $submission->get_meta( 'unit_tag' );

		if ( ! preg_match( '/^wpcf7-f(\d+)-p(\d+)-o(\d+)$/', $unit_tag, $matches ) ) {
			return $components;
		}

		$post_id = (int) $matches[2];
		$post    = get_post( $post_id );

		// Prevent issues when the form is not submitted via a resume or company page.
		if ( ! isset( $forms[ $post->post_type ] ) ) {
			return $components;
		}

		if ( ! array_search( $cf7->id(), $forms[ $post->post_type ], true ) ) {
			return $components;
		}

		// Bail if this is the second mail.
		if ( isset( $three ) && 'mail_2' == $three->name() ) {
			return $components;
		}

		switch ( $post->post_type ) {
			case 'company':
				$recipient = $post->_company_email ? $post->_company_email : '';
				break;

			case 'resume':
				$recipient = $post->_candidate_email ? $post->_candidate_email : '';
				break;

			default:
				$recipient = '';
				break;
		}

		// If we couldn't find the email by now, get it from the listing owner/author.
		if ( empty( $recipient ) ) {

			// Just get the email of the listing author.
			$owner_id = $post->post_author;

			// Retrieve the owner user data to get the email.
			$owner_info = get_userdata( $owner_id );

			if ( false !== $owner_info ) {
				$recipient = $owner_info->user_email;
			}
		}

		$components['recipient'] = $recipient;

		return $components;
	}

	/**
	 * Single job page layout for demo showcase purposes.
	 *
	 * @since 1.7.0
	 */
	public function demo_single_job_layout() {
		$value = get_option( 'cariera_job_manager_single_job_layout' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended 
		if ( isset( $_GET['job-layout'] ) && ! empty( $_GET['job-layout'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended 
			$value = sanitize_text_field( wp_unslash( $_GET['job-layout'] ) );
		}

		return $value;
	}

	/**
	 * Adding Print button to Single Job Listing
	 *
	 * @since   1.7.1
	 * @version 1.7.1
	 */
	public function single_job_print() {
		get_job_manager_template_part( 'single-job/single-job-listing-print' );
	}

	/**
	 * Get the date format.
	 *
	 * @since 1.9.3
	 *
	 * @param string $format
	 */
	public function wp_date_format( $format ) {
		return get_option( 'date_format' );
	}
}
