<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		// Init Classes.
		new \Cariera_Core\Core\Job_Manager\Jobs_Extender();
		new \Cariera_Core\Core\Job_Manager\Fields();
		new \Cariera_Core\Core\Job_Manager\Search();
		new \Cariera_Core\Core\Job_Manager\Geocode();
		new \Cariera_Core\Core\Job_Manager\Settings();
		new \Cariera_Core\Core\Job_Manager\Taxonomy();
		\Cariera_Core\Core\Job_Manager\Type_Colors::instance();
		\Cariera_Core\Core\Job_Manager\Maps::instance();
		\Cariera_Core\Core\Job_Manager\Writepanels::instance();

		// Cariera Company Manager.
		$GLOBALS['cariera_company_manager'] = new \Cariera_Core\Core\Company_Manager\Company_Manager();

		// Listing half details AJAX.
		add_action( 'wp_ajax_cariera_listing_half_loading', [ $this, 'loading_listing_details' ] );
		add_action( 'wp_ajax_nopriv_cariera_listing_half_loading', [ $this, 'loading_listing_details' ] );

		// Listing Split View Search.
		add_action( 'cariera_listing_split_view_search', [ $this, 'split_view_search' ] );
	}

	/**
	 * Load listing ajax details
	 *
	 * @since   1.8.3
	 * @version 2.0.0
	 */
	public function loading_listing_details() {
		global $listing_half_detail;

		// phpcs:ignore
		if ( ! isset( $_POST['listing_id'] ) ) {
			return;
		}

		$listing_half_detail = true;
		$listing_id          = str_replace( 'listing-id-', '', sanitize_text_field( wp_unslash( $_POST['listing_id'] ) ) ); // phpcs:ignore
		$listing             = get_post( $listing_id );
		$post_type           = $listing->post_type;

		ob_start();

		setup_postdata( $GLOBALS['post'] =& $listing ); // phpcs:ignore

		$content = apply_filters( 'cariera_before_split_view_template_loading', '', $listing, $post_type );

		if ( ! empty( $content ) ) {
			wp_send_json_success(
				[
					'response' => $content,
					'link'     => get_permalink( $listing_id ),
				]
			);
			return;
		}

		switch ( $post_type ) {
			case 'job_listing':
				get_job_manager_template( 'content-single-job_listing.php' );
				break;

			case 'resume':
				get_job_manager_template( 'content-single-resume.php', [], 'wp-job-manager-resumes' );
				break;

			default:
				get_job_manager_template( 'content-single-company.php', [], 'wp-job-manager-companies' );
				break;
		}

		\Cariera_Core\Extensions\Social_Share\Sharer::instance()->sharing_modal( $listing_id );
		wp_reset_postdata();

		$response = ob_get_clean();

		wp_send_json_success(
			[
				'response' => $response,
				'link'     => get_permalink( $listing_id ),
			]
		);
	}

	/**
	 * Add the search form for dynamic listing split-view.
	 *
	 * @since   1.8.3
	 * @version 1.9.4
	 *
	 * @param string $listing_type
	 */
	public function split_view_search( $listing_type ) {
		if ( empty( $listing_type ) ) {
			return;
		}

		wp_enqueue_style( 'cariera-wpjm-search-forms' );

		$listing_type = apply_filters( 'cariera_listing_split_view_search_listing_type', $listing_type );

		switch ( $listing_type ) {
			case 'job_listing':
				do_shortcode( '[cariera_job_sidebar_search]' );
				break;

			case 'resume':
				do_shortcode( '[cariera_resume_sidebar_search]' );
				break;

			case 'company':
				do_shortcode( '[cariera_company_sidebar_search]' );
				break;
		}
	}

	/**
	 * Get dashboard expiring listings.
	 *
	 * @since 2.0.0
	 */
	public static function get_dashboard_expiring_listings() {
		$current_user = wp_get_current_user();
		$post_types   = [ 'job_listing', 'company' ];

		if ( in_array( 'candidate', (array) $current_user->roles, true ) ) {
			$post_types = [ 'resume' ];
		}

		$listings = cariera_check_soon_to_expire_listings( $post_types );

		if ( empty( $listings ) ) {
			return [];
		}

		$expires_meta_map = [
			'resume'      => '_resume_expires',
			'company'     => '_company_expires',
			'job_listing' => '_job_expires',
		];

		$icon_map = [
			'resume'      => 'las la-user-tie',
			'company'     => 'las la-building',
			'job_listing' => 'las la-briefcase',
		];

		$items = [];

		foreach ( $listings as $listing ) {
			$post_type   = $listing->post_type;
			$expire_meta = '_job_expires';

			if ( isset( $expires_meta_map[ $post_type ] ) ) {
				$expire_meta = $expires_meta_map[ $post_type ];
			}

			$icon_class = 'las la-briefcase';

			if ( isset( $icon_map[ $post_type ] ) ) {
				$icon_class = $icon_map[ $post_type ];
			}

			$items[] = [
				'id'         => $listing->ID,
				'title'      => $listing->post_title,
				'url'        => get_permalink( $listing->ID ),
				'icon'       => $icon_class,
				'expiration' => get_post_meta( $listing->ID, $expire_meta, true ),
			];
		}

		return $items;
	}
}
