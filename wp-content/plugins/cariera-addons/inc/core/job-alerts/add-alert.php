<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Add_Alert {

	use \Cariera_Addons\Src\Traits\Singleton;

	const MODAL_ID = 'jm_add_alert_modal';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add the alert link to job search.
		add_filter( 'job_manager_job_filters_showing_jobs_links', [ $this, 'alert_link' ], 10, 2 );

		// Add the alert modal to job search.
		add_action( 'job_manager_job_filters_after', [ $this, 'alert_modal' ] );
		add_action( 'cariera_wpjm_sidebar_job_filters_end', [ $this, 'alert_modal' ] );

		// Single listing alert link.
		add_action( 'single_job_listing_end', [ $this, 'single_alert_link' ] );
	}

	/**
	 * Add the alert link to job search.
	 *
	 * @since   0.9.2
	 * @version 1.0.6
	 *
	 * @param array $links Existing links.
	 * @param array $args Search terms.
	 */
	public function alert_link( $links, $args ) {
		$alert_tags    = [];
		$alert_regions = '';

		if ( Settings::get_alerts_page() ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Frontend search form.
			if ( isset( $_POST['form_data'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Frontend search form processing. Sanitized on next line.
				parse_str( $_POST['form_data'], $params );
				$alert_regions = isset( $params['search_region'] ) ? absint( $params['search_region'] ) : '';

				if ( isset( $params['job_tag'] ) ) {
					$tags = array_filter( $params['job_tag'] );
					foreach ( $tags as $tag ) {
						$tag          = get_term_by( 'name', $tag, \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG );
						$alert_tags[] = $tag->term_id;
					}
				}
			}

			$all_job_types  = get_job_listing_types( 'ids' );
			$job_type_array = [];
			if ( count( (array) $args['filter_job_types'] ) !== count( $all_job_types ) ) {
				foreach ( (array) $args['filter_job_types'] as $job_type ) {
					$job_type_term    = get_term_by( 'slug', $job_type, 'job_listing_type' );
					$job_type_array[] = $job_type_term->term_id;
				}
			}

			$alert_data = [
				'alert_job_type' => $job_type_array,
				'alert_location' => rawurlencode( $args['search_location'] ),
				'alert_cats'     => $args['search_categories'],
				'alert_tags'     => $alert_tags,
				'alert_keyword'  => rawurlencode( $args['search_keywords'] ),
				'alert_regions'  => $alert_regions,
			];

			// Remove only truly empty values (null, empty string, empty array).
			$alert_data = array_filter(
				$alert_data,
				function ( $value ) {
					if ( is_array( $value ) ) {
						return ! empty( $value );
					}

					return ( $value !== null && $value !== '' );
				}
			);

			$links['alert'] = [
				'name'    => esc_html__( 'Add alert', 'cariera-addons' ),
				'onclick' => 'cariera_addons_job_alerts.open_add_alert_modal(' . self::MODAL_ID . ', this, event);',
				'url'     => add_query_arg(
					array_merge(
						$alert_data,
						[
							'action'     => 'add_alert',
							'alert_name' => Shortcodes::generate_alert_name( $alert_data ),
						]
					),
					Shortcodes::get_page_url(),
				),
			];
		}

		return $links;
	}

	/**
	 * Show an add alert modal dialog on the job search page.
	 *
	 * @since   0.9.2
	 * @version 1.1.0
	 *
	 * @param array $atts Modal attributes.
	 */
	public function alert_modal( $atts = [] ) {
		wp_enqueue_script( 'cariera-addons-job-alerts' );

		if ( Settings::is_account_required() && ! is_user_logged_in() ) {
			$modal_content = $this->logged_out_message();
		} else {
			$template = \Cariera_Addons\Helpers::get_template(
				'alerts/add-alert-modal.php',
				[
					'page'        => Shortcodes::get_page_url(),
					'alert_email' => Shortcodes::get_user_email(),
				]
			);

			$modal_content = \WP_Job_Manager\UI\Notice::render(
				[
					'title' => esc_html__( 'Add Alert', 'cariera-addons' ),
					'html'  => $template,
				]
			);
		}

		$modal = new \WP_Job_Manager\UI\Modal_Dialog(
			[
				'id'    => self::MODAL_ID,
				'style' => 'width: 500px;',
			]
		);

		echo $modal->render( $modal_content ); // phpcs:ignore
	}

	/**
	 * Single listing alert link
	 *
	 * @since   0.9.2
	 * @version 0.9.11
	 */
	public function single_alert_link() {
		global $post, $job_preview;

		if ( ! empty( $job_preview ) ) {
			return;
		}

		if ( is_user_logged_in() && Settings::get_alerts_page() ) {
			$job_types = wpjm_get_the_job_types( $post );
			$args      = [
				'action'         => 'add_alert',
				'alert_name'     => rawurlencode( $post->post_title ),
				'alert_job_type' => wp_list_pluck( $job_types, 'slug' ),
				'alert_location' => rawurlencode( wp_strip_all_tags( get_the_job_location( $post ) ) ),
				'alert_cats'     => taxonomy_exists( 'job_listing_category' ) ? wp_get_post_terms( $post->ID, 'job_listing_category', [ 'fields' => 'ids' ] ) : '',
				'alert_keyword'  => rawurlencode( $post->post_title ),
				'alert_regions'  => taxonomy_exists( 'job_listing_region' ) ? current( wp_get_post_terms( $post->ID, 'job_listing_region', [ 'fields' => 'ids' ] ) ) : '',
			];

			/**
			 * Filter the link arguments for creating an alert based on a single listing.
			 */
			$args = apply_filters( 'job_manager_alerts_single_listing_link', $args );
			$link = add_query_arg( $args, Shortcodes::get_page_url() );
			echo '<p class="job-manager-single-alert-link"><a href="' . esc_url( $link ) . '">' . esc_html__( 'Alert me to jobs like this', 'cariera-addons' ) . '</a></p>';
		}
	}

	/**
	 * Render a logged out notice.
	 *
	 * @since   0.9.2
	 * @version 0.9.4
	 */
	private function logged_out_message(): string {
		$template = \Cariera_Addons\Helpers::get_template( 'alerts/add-alert-modal-login.php', [] );

		/**
		 * Filter the logged out notice in the 'Add Alert' modal.
		 */
		return apply_filters( 'cariera_addons_alerts_modal_logged_out_message', $template );
	}
}
