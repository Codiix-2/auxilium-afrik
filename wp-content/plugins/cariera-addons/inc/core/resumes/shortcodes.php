<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'handle_redirects' ] );
		add_shortcode( 'submit_resume_form', [ $this, 'submit_resume_form' ] );
		add_shortcode( 'resumes', [ $this, 'output_resumes' ] );
		add_action( 'resume_manager_output_resumes_no_results', [ $this, 'output_no_results' ] );
	}

	/**
	 * Handle redirects
	 *
	 * @since   0.9.5
	 * @version 1.0.6
	 */
	public function handle_redirects() {
		// Only run on the resume submission page.
		$submit_resume_form_page_id = get_option( 'resume_manager_submit_resume_form_page_id' );
		if ( ! $submit_resume_form_page_id || ! is_page( $submit_resume_form_page_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		if ( ! get_current_user_id() || ( ! empty( $_REQUEST['resume_id'] ) && resume_manager_user_can_edit_resume( intval( $_REQUEST['resume_id'] ) ) ) ) {
			return;
		}

		$submission_limit = get_option( 'resume_manager_submission_limit' );
		$resume_count     = resume_manager_count_user_resumes();

		if ( $submission_limit && $resume_count >= $submission_limit ) {
			$candidate_dashboard_page_id = get_option( 'resume_manager_candidate_dashboard_page_id' );
			if ( $candidate_dashboard_page_id ) {
				$redirect_url = get_permalink( $candidate_dashboard_page_id );
			} else {
				$redirect_url = home_url( '/' );
			}

			/**
			 * Filter on the URL visitors will be redirected upon exceeding submission limit.
			 *
			 * @since 0.9.5
			 *
			 * @param string $redirect_url     URL to redirect when user has exceeded submission limit.
			 * @param int    $submission_limit Maximum number of listings a user can submit.
			 * @param int    $resume_count     Number of resumes the user has submitted.
			 */
			$redirect_url = apply_filters( 'resume_manager_redirect_url_exceeded_listing_limit', $redirect_url, $submission_limit, $resume_count );

			if ( $redirect_url ) {
				wp_safe_redirect( esc_url( $redirect_url ) );
				exit;
			}
		}
	}

	/**
	 * Show the resume submission form
	 *
	 * @since 0.9.5
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public function submit_resume_form( $atts = [] ) {
		return \Cariera_Addons\Core\Resumes\Resumes::instance()->forms->get_form( 'submit-resume', $atts );
	}

	/**
	 * Output resume function.
	 *
	 * @since   0.9.5
	 * @version 0.9.9
	 *
	 * @param mixed $atts
	 */
	public function output_resumes( $atts ) {
		wp_enqueue_style( 'cariera-resume-listings' );

		ob_start();

		if ( ! resume_manager_user_can_browse_resumes() ) {
			get_job_manager_template_part( 'resumes/access-denied', 'browse-resumes', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
			return ob_get_clean();
		}

		$atts = shortcode_atts(
			apply_filters(
				'resume_manager_output_resumes_defaults',
				[
					// Layout.
					'resumes_layout'            => 'list',
					'resumes_list_version'      => '1',
					'resumes_grid_version'      => '1',

					// Sorting.
					'per_page'                  => get_option( 'resume_manager_per_page' ),
					'orderby'                   => 'featured',
					'order'                     => 'DESC',

					// Filters.
					'show_filters'              => true,
					'show_categories'           => get_option( 'resume_manager_enable_categories' ),
					'show_category_multiselect' => get_option( 'resume_manager_enable_default_category_multiselect', false ),
					'show_pagination'           => false,
					'show_more'                 => true,

					// Limit what resumes are shown based on category, post status, and type.
					'location'                  => '',
					'keywords'                  => '',
					'categories'                => '',
					'skills'                    => '',
					'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.
					'selected_category'         => '',
				]
			),
			$atts
		);

		// Resume Layout.
		$resumes_layout         = ( 'list' === $atts['resumes_layout'] ) ? '_list' : '_' . $atts['resumes_layout'];
		$resumes_layout_wrapper = ( 'list' === $atts['resumes_layout'] ) ? 'resume_list' : 'resume_grid';
		$resumes_version        = ( 'list' === $atts['resumes_layout'] ) ? $atts['resumes_list_version'] : $atts['resumes_grid_version'];

		// String and bool handling.
		$atts['show_filters']              = $this->string_to_bool( $atts['show_filters'] );
		$atts['show_categories']           = $this->string_to_bool( $atts['show_categories'] );
		$atts['show_category_multiselect'] = $this->string_to_bool( $atts['show_category_multiselect'] );
		$atts['show_more']                 = $this->string_to_bool( $atts['show_more'] );
		$atts['show_pagination']           = $this->string_to_bool( $atts['show_pagination'] );

		if ( ! is_null( $atts['featured'] ) ) {
			$atts['featured'] = ( is_bool( $atts['featured'] ) && $atts['featured'] ) || in_array( $atts['featured'], [ 1, '1', 'true', 'yes' ], true );
		}

		// By default, use client-side state to populate form fields.
		$disable_client_state = false;

		// Get keywords, location, category and type from querystring if set.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Input is used safely.
		if ( ! empty( $_GET['search_keywords'] ) ) {
			$atts['keywords']     = sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) );
			$disable_client_state = true;
		}

		if ( ! empty( $_GET['search_location'] ) ) {
			$atts['location']     = sanitize_text_field( wp_unslash( $_GET['search_location'] ) );
			$disable_client_state = true;
		}

		if ( ! empty( $_GET['search_category'] ) ) {
			$atts['selected_category'] = sanitize_text_field( wp_unslash( $_GET['search_category'] ) );
			$disable_client_state      = true;
		}

		if ( ! empty( $_GET['search_skills'] ) ) {
			$atts['skills']       = sanitize_text_field( wp_unslash( $_GET['search_skills'] ) );
			$disable_client_state = true;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Array handling.
		$atts['categories'] = is_array( $atts['categories'] ) ? $atts['categories'] : array_filter( array_map( 'trim', explode( ',', $atts['categories'] ) ) );

		$data_attributes = [
			'resume_layout'              => $resumes_layout,
			'resume_version'             => $resumes_version,

			// Default.
			'location'                   => $atts['location'],
			'keywords'                   => $atts['keywords'],
			'show_filters'               => $atts['show_filters'] ? 'true' : 'false',
			'show_pagination'            => $atts['show_pagination'] ? 'true' : 'false',
			'per_page'                   => $atts['per_page'],
			'orderby'                    => $atts['orderby'],
			'order'                      => $atts['order'],
			'categories'                 => implode( ',', $atts['categories'] ),
			'disable-form-state-storage' => $disable_client_state,
		];

		if ( $atts['show_filters'] ) {
			get_job_manager_template(
				'resumes/resume-filters.php',
				[
					'per_page'                  => $atts['per_page'],
					'orderby'                   => $atts['orderby'],
					'order'                     => $atts['order'],
					'show_categories'           => $atts['show_categories'],
					'categories'                => $atts['categories'],
					'selected_category'         => $atts['selected_category'],
					'atts'                      => $atts,
					'location'                  => $atts['location'],
					'keywords'                  => $atts['keywords'],
					'show_category_multiselect' => $atts['show_category_multiselect'],
					'skills'                    => $atts['skills'],
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);

			get_job_manager_template( 'resumes/resumes-start.php', [ 'resumes_layout_wrapper' => $resumes_layout_wrapper ], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
			get_job_manager_template( 'resumes/resumes-end.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );

			if ( ! $atts['show_pagination'] && $atts['show_more'] ) {
				echo '<a class="load_more_resumes" href="#" style="display:none;"><strong>' . esc_html__( 'Load more resumes', 'cariera-addons' ) . '</strong></a>';
			}
		} else {
			$resumes = get_resumes(
				apply_filters(
					'resume_manager_output_resumes_args',
					[
						'search_categories' => $atts['categories'],
						'posts_per_page'    => $atts['per_page'],
						'orderby'           => $atts['orderby'],
						'order'             => $atts['order'],
						'featured'          => $atts['featured'],
					]
				)
			);

			if ( $resumes->have_posts() ) {
				get_job_manager_template( 'resumes/resumes-start.php', [ 'resumes_layout_wrapper' => $resumes_layout_wrapper ], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );

				/**
				 * Show resumes with the right template
				 */
				while ( $resumes->have_posts() ) {
					$resumes->the_post();
					get_job_manager_template_part( 'resumes/listing-templates/content', 'resume' . $resumes_layout . $resumes_version, 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
				}

				get_job_manager_template( 'resumes/resumes-end.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );

				if ( $resumes->found_posts > $atts['per_page'] && $atts['show_more'] ) {
					wp_enqueue_script( 'cariera-addons-resume-ajax-filters' );

					if ( $atts['show_pagination'] ) {
						echo get_job_listing_pagination( $resumes->max_num_pages ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else { ?>
						<a class="load_more_resumes" href="#"><strong><?php esc_html_e( 'Load more resumes', 'cariera-addons' ); ?></strong></a>
						<?php
					}
				}
			} else {
				do_action( 'resume_manager_output_resumes_no_results' );
			}

			wp_reset_postdata();
		}

		$data_attributes_string = '';
		if ( ! is_null( $atts['featured'] ) ) {
			$data_attributes['featured'] = $atts['featured'] ? 'true' : 'false';
		}

		$data_attributes['post_id'] = isset( $GLOBALS['post'] ) ? $GLOBALS['post']->ID : 0;

		/**
		 * Pass additional data to the resume listing <div> wrapper.
		 */
		$data_attributes = apply_filters( 'job_manager_resumes_shortcode_data_attributes', $data_attributes, $atts );

		foreach ( $data_attributes as $key => $value ) {
			$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}

		/**
		 * Get current output buffer contents ( ob_get_clean() )
		 */
		$resume_listings_output = apply_filters( 'job_manager_resume_listings_output', ob_get_clean() );

		return '<div class="resumes" ' . $data_attributes_string . '>' . $resume_listings_output . '</div>';
	}

	/**
	 * Output some content when no results were found
	 *
	 * @since   0.9.5
	 * @version 1.0.7
	 */
	public function output_no_results() {
		get_job_manager_template( 'resumes/content-no-resumes-found.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Get string as a bool
	 *
	 * @since 0.9.5
	 *
	 * @param string $value
	 */
	public function string_to_bool( $value ) {
		return ( is_bool( $value ) && $value ) || in_array( $value, [ '1', 'true', 'yes' ], true ) ? true : false;
	}
}
