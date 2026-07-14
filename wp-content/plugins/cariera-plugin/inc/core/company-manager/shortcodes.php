<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'submit_company', [ $this, 'submit_company' ] );
		add_shortcode( 'companies', [ $this, 'output_companies' ] );
		add_shortcode( 'cariera_companies_list', [ $this, 'output_companies_list' ] );
	}

	/**
	 * Show the company submission form
	 *
	 * @since   1.4.4
	 * @version 2.0.0
	 *
	 * @param array $atts
	 */
	public function submit_company( $atts = [] ) {
		global $cariera_company_manager;

		return $cariera_company_manager->forms->get_form( 'submit-company', $atts );
	}

	/**
	 * Companies shortcode
	 *
	 * @since   1.3.0
	 * @version 1.9.3
	 *
	 * @param array $atts
	 */
	public function output_companies( $atts ) {
		global $cariera_company_manager;

		wp_enqueue_style( 'cariera-company-listings' );

		ob_start();

		if ( ! cariera_user_can_browse_companies() ) {
			get_job_manager_template_part( 'access-denied', 'browse-companies', 'wp-job-manager-companies' );
			return ob_get_clean();
		}

		$atts = shortcode_atts(
			apply_filters(
				'cariera_output_companies_defaults',
				[
					'companies_layout'          => 'list',
					'companies_list_version'    => '1',
					'companies_grid_version'    => '1',

					'per_page'                  => get_option( 'cariera_companies_per_page' ),
					'orderby'                   => 'featured',
					'order'                     => 'DESC',

					// Filters.
					'show_filters'              => true,
					'show_categories'           => true,
					'show_category_multiselect' => get_option( 'cariera_company_category_multiselect', false ),
					'show_pagination'           => false,
					'show_more'                 => true,

					// Limit what companies are shown based on category, post status, and type.
					'categories'                => '',
					'post_status'               => '',
					'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.
					'active_jobs'               => null, // True to show only companies with jobs, false to hide companies with jobs, leave null to show all.

					// Default values for filters.
					'location'                  => '',
					'keywords'                  => '',
					'selected_category'         => '',
				]
			),
			$atts
		);

		// Companies Layout.
		$companies_layout         = ( 'list' === $atts['companies_layout'] ) ? '_list' : '_' . $atts['companies_layout'];
		$companies_layout_wrapper = ( 'list' === $atts['companies_layout'] ) ? 'company_list' : 'company_grid';
		$companies_version        = ( 'list' === $atts['companies_layout'] ) ? $atts['companies_list_version'] : $atts['companies_grid_version'];

		// String and bool handling.
		$atts['show_filters']              = $this->string_to_bool( $atts['show_filters'] );
		$atts['show_categories']           = $this->string_to_bool( $atts['show_categories'] );
		$atts['show_category_multiselect'] = $this->string_to_bool( $atts['show_category_multiselect'] );
		$atts['show_more']                 = $this->string_to_bool( $atts['show_more'] );
		$atts['show_pagination']           = $this->string_to_bool( $atts['show_pagination'] );

		if ( ! is_null( $atts['featured'] ) ) {
			$atts['featured'] = ( is_bool( $atts['featured'] ) && $atts['featured'] ) || in_array( $atts['featured'], [ '1', 'true', 'yes' ], true ) ? true : false;
		}

		if ( ! is_null( $atts['active_jobs'] ) ) {
			$atts['active_jobs'] = ( is_bool( $atts['active_jobs'] ) && $atts['active_jobs'] ) || in_array( $atts['active_jobs'], [ '1', 'true', 'yes' ], true ) ? true : false;
		}

		// By default, use client-side state to populate form fields.
		$disable_client_state = false;

		// Get keywords and location from querystring if set.
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

		// Array handling.
		$atts['categories']        = is_array( $atts['categories'] ) ? $atts['categories'] : array_filter( array_map( 'trim', explode( ',', $atts['categories'] ) ) );
		$atts['selected_category'] = is_array( $atts['selected_category'] ) ? $atts['selected_category'] : array_filter( array_map( 'trim', explode( ',', $atts['selected_category'] ) ) );
		$atts['post_status']       = is_array( $atts['post_status'] ) ? $atts['post_status'] : array_filter( array_map( 'trim', explode( ',', $atts['post_status'] ) ) );

		// Normalize field for categories.
		if ( ! empty( $atts['selected_category'] ) ) {
			foreach ( $atts['selected_category'] as $cat_index => $category ) {
				if ( ! is_numeric( $category ) ) {
					$term = get_term_by( 'slug', $category, 'company_category' );

					if ( $term ) {
						$atts['selected_category'][ $cat_index ] = $term->term_id;
					}
				}
			}
		}

		$data_attributes = [
			'company_layout'             => $companies_layout,
			'company_version'            => $companies_version,
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
			get_company_template(
				'company-filters.php',
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
				]
			);

			get_job_manager_template( 'companies-start.php', [ 'companies_layout_wrapper' => $companies_layout_wrapper ], 'wp-job-manager-companies' );
			get_job_manager_template( 'companies-end.php', [], 'wp-job-manager-companies' );

			if ( ! $atts['show_pagination'] && $atts['show_more'] ) {
				echo '<a class="load_more_companies" href="#" style="display:none;">' . esc_html__( 'Load more companies', 'cariera-core' ) . '</a>';
			}
		} else {
			$companies = cariera_get_companies(
				apply_filters(
					'cariera_output_companies_args',
					[
						'search_location'   => $atts['location'],
						'search_keywords'   => $atts['keywords'],
						'post_status'       => $atts['post_status'],
						'search_categories' => $atts['categories'],
						'orderby'           => $atts['orderby'],
						'order'             => $atts['order'],
						'posts_per_page'    => $atts['per_page'],
						'featured'          => $atts['featured'],
						'active_jobs'       => $atts['active_jobs'],
					]
				)
			);

			if ( $companies->have_posts() ) {
				get_job_manager_template( 'companies-start.php', [ 'companies_layout_wrapper' => $companies_layout_wrapper ], 'wp-job-manager-companies' );

				while ( $companies->have_posts() ) {
					$companies->the_post();
					get_job_manager_template_part( 'company-templates/content', 'company' . $companies_layout . $companies_version, 'wp-job-manager-companies' );
				}

				get_job_manager_template( 'companies-end.php', [], 'wp-job-manager-companies' );

				if ( $companies->found_posts > $atts['per_page'] && $atts['show_more'] ) {
					wp_enqueue_script( 'cariera-company-ajax-filters' );

					if ( $atts['show_pagination'] ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template output.
						echo get_job_listing_pagination( $companies->max_num_pages );
					} else { ?>
						<a class="load_more_companies" href="#"><?php esc_html_e( 'Load more companies', 'cariera-core' ); ?></a>
						<?php
					}
				}
			} else {
				do_action( 'cariera_company_no_results' );
			}

			wp_reset_postdata();
		}

		$data_attributes_string = '';
		if ( ! is_null( $atts['featured'] ) ) {
			$data_attributes['featured'] = $atts['featured'] ? 'true' : 'false';
		}
		if ( ! is_null( $atts['active_jobs'] ) ) {
			$data_attributes['active_jobs'] = $atts['active_jobs'] ? 'true' : 'false';
		}
		if ( ! empty( $atts['post_status'] ) ) {
			$data_attributes['post_status'] = implode( ',', $atts['post_status'] );
		}

		$data_attributes['post_id'] = isset( $GLOBALS['post'] ) ? $GLOBALS['post']->ID : 0;

		/**
		 * Pass additional data to the company listings <div> wrapper.
		 */
		$data_attributes = apply_filters( 'cariera_companies_shortcode_data_attributes', $data_attributes, $atts );

		foreach ( $data_attributes as $key => $value ) {
			$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}

		$companies_output = apply_filters( 'cariera_companies_output', ob_get_clean() );

		return '<div class="company_listings" ' . $data_attributes_string . '>' . $companies_output . '</div>';
	}

	/**
	 * Output of the company list shortcode
	 *
	 * @since   1.3.0
	 * @version 1.9.7
	 *
	 * @param array $atts
	 */
	public function output_companies_list( $atts ) {
		wp_enqueue_style( 'cariera-companies-list' );
		wp_enqueue_script( 'cariera-companies-list' );

		$atts = shortcode_atts(
			[
				'show_letters' => true,
			],
			$atts
		);

		$companies = get_posts(
			[
				'numberposts' => -1,
				'post_type'   => \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY,
				'post_status' => 'publish',
				'orderby'     => 'title',
				'order'       => 'ASC',
			]
		);

		if ( empty( $companies ) ) {
			return '';
		}

		$_companies = [];
		foreach ( $companies as $company ) {
			// Use multibyte safe functions.
			$first_char = mb_strtoupper( mb_substr( $company->post_title, 0, 1 ) );

			if ( is_numeric( $first_char ) ) {
				$_companies['numeric'][] = $company;
			} elseif ( preg_match( '/[A-Z]/u', $first_char ) ) {
				$_companies[ $first_char ][] = $company;
			} else {
				$_companies['other'][] = $company;
			}
		}

		// Sort groups alphabetically.
		foreach ( $_companies as $key => &$group ) {
			usort(
				$group,
				function ( $a, $b ) {
					return strcasecmp( $a->post_title, $b->post_title );
				}
			);
		}
		unset( $group );

		// Allow devs to modify grouped companies.
		$_companies = apply_filters( 'cariera_companies_list_grouped', $_companies, $companies );

		ob_start();

		get_company_template(
			'company-list.php',
			[
				'companies'    => $_companies,
				'show_letters' => $this->string_to_bool( $atts['show_letters'] ),
			]
		);

		return ob_get_clean();
	}

	/**
	 * Gets string as a bool.
	 *
	 * @since   1.3.0
	 * @version 1.5.1
	 *
	 * @param mixed $value
	 */
	public function string_to_bool( $value ) {
		return ( is_bool( $value ) && $value ) || in_array( $value, [ '1', 'true', 'yes' ], true ) ? true : false;
	}
}
