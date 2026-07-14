<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// JM Ajax endpoints.
		add_action( 'job_manager_ajax_get_companies', [ $this, 'get_ajax_companies' ] );

		// AJAX Actions.
		add_action( 'wp_ajax_nopriv_cariera_get_companies', [ $this, 'get_ajax_companies' ] );
		add_action( 'wp_ajax_cariera_get_companies', [ $this, 'get_ajax_companies' ] );
	}

	/**
	 * Returns Company Listings for Ajax endpoint.
	 *
	 * @since   1.3.0
	 * @version 1.9.3
	 */
	public function get_ajax_companies() {
		global $wpdb, $cariera_distances;

		$search_keywords   = isset( $_REQUEST['search_keywords'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search_keywords'] ) ) : ''; // phpcs:ignore
		$search_location   = isset( $_REQUEST['search_location'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search_location'] ) ) : ''; // phpcs:ignore
		$search_categories = isset( $_REQUEST['search_categories'] ) ? wp_unslash( $_REQUEST['search_categories'] ) : ''; // phpcs:ignore
		$order             = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC'; // phpcs:ignore
		$orderby           = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'featured'; // phpcs:ignore
		$page              = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1; // phpcs:ignore
		$per_page          = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : absint( get_option( 'cariera_companies_per_page' ) ); // phpcs:ignore
		$featured          = isset( $_REQUEST['featured'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['featured'] ) ) : null; // phpcs:ignore
		$active_jobs       = isset( $_REQUEST['active_jobs'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['active_jobs'] ) ) : null; // phpcs:ignore
		$show_pagination   = isset( $_REQUEST['show_pagination'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['show_pagination'] ) ) : null; // phpcs:ignore
		$companies_layout  = isset( $_REQUEST['company_layout'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['company_layout'] ) ) : ''; // phpcs:ignore
		$companies_version = isset( $_REQUEST['company_version'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['company_version'] ) ) : ''; // phpcs:ignore

		if ( is_array( $search_categories ) ) {
			$search_categories = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $search_categories ) ) );
		} else {
			$search_categories = array_filter( [ sanitize_text_field( wp_unslash( $search_categories ) ), 0 ] );
		}

		$args = [
			'search_keywords'   => $search_keywords,
			'search_location'   => $search_location,
			'search_categories' => $search_categories,
			'orderby'           => $orderby,
			'order'             => $order,
			'offset'            => ( $page - 1 ) * $per_page,
			'posts_per_page'    => max( 1, $per_page ), // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Known slow query.
		];

		if ( 'true' === $featured || 'false' === $featured ) {
			$args['featured'] = 'true' === $featured;
		}

		if ( 'true' === $active_jobs || 'false' === $active_jobs ) {
			$args['active_jobs'] = 'true' === $active_jobs;
		}

		// Get the arguments to use when building the Companies WP Query.
		$companies = cariera_get_companies( apply_filters( 'cariera_get_companies_args', $args ) );

		$result = [
			'found_companies' => $companies->have_posts(),
			'showing'         => '',
			'max_num_pages'   => $companies->max_num_pages,
		];

		if ( ( $search_location || $search_keywords || $search_categories ) ) {
			// translators: Placeholder %d is the number of found search results.
			$message               = sprintf( _n( 'Search completed. Found %d matching record.', 'Search completed. Found %d matching records.', $companies->found_posts, 'cariera-core' ), $companies->found_posts );
			$result['showing_all'] = true;
		} else {
			$message = '';
		}

		$search_values = [
			'location'   => $search_location,
			'keywords'   => $search_keywords,
			'categories' => $search_categories,
		];

		/**
		 * Filter the message that describes the results of the search query.
		 *
		 * @since 1.7.0
		 *
		 * @param string $message Default message that is generated when posts are found.
		 * @param array $search_values {
		 *  Helpful values often used in the generation of this message.
		 *
		 *  @type string $location   Query used to filter by company listing location.
		 *  @type string $keywords   Query used to filter by general keywords.
		 *  @type array  $categories List of the categories to filter by.
		 * }
		 */
		$result['showing'] = apply_filters( 'cariera_get_companies_custom_filter_text', $message, $search_values );

		// Generate RSS link.
		$result['showing_links'] = cariera_get_companies_filtered_links(
			[
				'search_location'   => $search_location,
				'search_categories' => $search_categories,
				'search_keywords'   => $search_keywords,
			]
		);

		/**
		 * Send back a response to the AJAX request without creating HTML.
		 */
		if ( true !== apply_filters( 'cariera_ajax_get_companies_html_results', true, $result, $companies ) ) {
			// Filters the results of the company ajax query to be sent back to the client.
			wp_send_json( apply_filters( 'cariera_get_companies_result', $result, $companies ) );

			return;
		}

		ob_start();

		if ( $result['found_companies'] ) {
			while ( $companies->have_posts() ) {
				$companies->the_post();
				get_job_manager_template_part( 'company-templates/content', 'company' . $companies_layout . $companies_version, 'wp-job-manager-companies' );
			}
		} else {
			get_job_manager_template_part( 'content', 'no-companies-found', 'wp-job-manager-companies' );
		}

		$result['html'] = ob_get_clean();

		// Generate pagination.
		if ( 'true' === $show_pagination ) {
			$result['pagination'] = get_job_listing_pagination( $companies->max_num_pages, $page );
		}

		wp_send_json( apply_filters( 'cariera_get_companies_result', $result, $companies ) );
	}
}
