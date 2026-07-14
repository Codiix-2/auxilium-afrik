<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// JM Ajax endpoints.
		add_action( 'job_manager_ajax_get_resumes', [ $this, 'get_resumes' ] );

		// BW compatible handlers.
		add_action( 'wp_ajax_nopriv_resume_manager_get_resumes', [ $this, 'get_resumes' ] );
		add_action( 'wp_ajax_resume_manager_get_resumes', [ $this, 'get_resumes' ] );
	}

	/**
	 * Get resumes via ajax
	 *
	 * @since   0.9.5
	 * @version 0.9.11
	 */
	public function get_resumes() {
		global $wpdb;

		$search_location   = isset( $_REQUEST['search_location'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search_location'] ) ) : ''; // phpcs:ignore
		$search_keywords   = isset( $_REQUEST['search_keywords'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search_keywords'] ) ) : ''; // phpcs:ignore
		$search_categories = isset( $_REQUEST['search_categories'] ) ? $_REQUEST['search_categories'] : ''; // phpcs:ignore
		$search_skills     = isset( $_REQUEST['search_skills'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search_skills'] ) ) : ''; // phpcs:ignore
		$order             = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC'; // phpcs:ignore
		$orderby           = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'featured'; // phpcs:ignore
		$page              = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1; // phpcs:ignore
		$per_page          = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : absint( get_option( 'resume_manager_per_page' ) ); // phpcs:ignore
		$show_pagination   = isset( $_REQUEST['show_pagination'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['show_pagination'] ) ) : null; // phpcs:ignore
		$featured          = isset( $_REQUEST['featured'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['featured'] ) ) : null; // phpcs:ignore

		// Pull Resume attr via ajax file.
		$resumes_layout  = isset( $_REQUEST['resume_layout'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['resume_layout'] ) ) : ''; // phpcs:ignore
		$resumes_version = isset( $_REQUEST['resume_version'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['resume_version'] ) ) : ''; // phpcs:ignore

		// In case S&F for WPJM is activated.
		$resumes_layout  = isset( $_REQUEST['data_params'], $_REQUEST['data_params']['resume_layout'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['data_params']['resume_layout'] ) ) : $resumes_layout; // phpcs:ignore
		$resumes_version = isset( $_REQUEST['data_params'], $_REQUEST['data_params']['resume_version'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['data_params']['resume_version'] ) ) : $resumes_version; // phpcs:ignore

		if ( is_array( $search_categories ) ) {
			$search_categories = array_filter( array_map( 'sanitize_text_field', array_map( 'wp_unslash', $search_categories ) ) );
		} else {
			$search_categories = array_filter( [ sanitize_text_field( stripslashes( $search_categories ) ), 0 ] );
		}

		$args = [
			'search_location'   => $search_location,
			'search_keywords'   => $search_keywords,
			'search_categories' => $search_categories,
			'search_skills'     => $search_skills,
			'orderby'           => $orderby,
			'order'             => $order,
			'offset'            => ( $page - 1 ) * $per_page,
			'posts_per_page'    => max( 1, $per_page ), // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Known slow query.
		];

		// phpcs:ignore
		if ( ! empty( $_POST['exclude_ids'] ) ) {
			$args['post__not_in'] = array_map( 'absint', $_POST['exclude_ids'] ); // phpcs:ignore
		}

		if ( 'true' === $featured || 'false' === $featured ) {
			$args['featured'] = 'true' === $featured;
			$args['orderby']  = 'featured' === $orderby ? 'date' : $orderby;
		}

		/**
		 * Get the arguments to use when building the Resume WP Query.
		 *
		 * @param array $args Arguments used for generating Resume query (see `get_resumes()`).
		 */
		$resumes = get_resumes( apply_filters( 'resume_manager_get_resumes_args', $args ) );

		$result = [
			'found_resumes' => $resumes->have_posts(),
			'showing'       => '',
			'max_num_pages' => $resumes->max_num_pages,
		];

		if ( ( $search_location || $search_keywords || $search_categories ) ) {
			// translators: Placeholder %d is the number of found search results.
			$message               = sprintf( _n( 'Search completed. Found %d matching record.', 'Search completed. Found %d matching records.', $resumes->found_posts, 'cariera-addons' ), $resumes->found_posts );
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
		 */
		$result['showing'] = apply_filters( 'resume_manager_get_resumes_custom_filter_text', $message, $search_values );

		// Generate RSS link.
		$result['showing_links'] = resume_manager_get_filtered_links(
			[
				'search_location'   => $search_location,
				'search_categories' => $search_categories,
				'search_keywords'   => $search_keywords,
			]
		);

		/**
		 * Send back a response to the AJAX request without creating HTML.
		 */
		if ( true !== apply_filters( 'cariera_addons_ajax_get_resumes_html_results', true, $result, $resumes ) ) {
			// Filters the results of the resume Ajax query to be sent back to the client.
			wp_send_json( apply_filters( 'resume_manager_get_listings_result', $result, $resumes ) );

			return;
		}

		ob_start();

		if ( $result['found_resumes'] ) {
			while ( $resumes->have_posts() ) {
				$resumes->the_post();
				get_job_manager_template_part( 'resumes/listing-templates/content', 'resume' . $resumes_layout . $resumes_version, 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
			}
		} else {
			get_job_manager_template_part( 'resumes/content', 'no-resumes-found', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
		}

		$result['html'] = ob_get_clean();

		// Generate pagination.
		if ( 'true' === $show_pagination ) {
			$result['pagination'] = get_job_listing_pagination( $resumes->max_num_pages, $page );
		}

		wp_send_json( apply_filters( 'resume_manager_get_listings_result', $result, $resumes ) );
	}
}
