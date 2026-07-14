<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Past_Applications {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'past_applications', [ $this, 'past_applications' ] );
	}

	/**
	 * Past Applications
	 *
	 * @since 0.9.3
	 *
	 * @param array $atts
	 */
	public function past_applications( $atts ) {
		// If user is not logged in, abort.
		if ( ! is_user_logged_in() ) {
			do_action( 'job_manager_job_applications_past_logged_out' );
			return;
		}

		// Sanitize and parse shortcode attributes.
		$atts = shortcode_atts(
			[
				'posts_per_page' => '25',
			],
			$atts
		);

		$posts_per_page = absint( $atts['posts_per_page'] );

		$args = apply_filters(
			'job_manager_job_applications_past_args',
			[
				'post_type'           => \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION,
				'post_status'         => array_keys( get_job_application_statuses() ),
				'posts_per_page'      => $posts_per_page,
				'offset'              => ( max( 1, get_query_var( 'paged' ) ) - 1 ) * $posts_per_page,
				'ignore_sticky_posts' => 1,
				'meta_key'            => '_candidate_user_id', // phpcs:ignore
				'meta_value'          => get_current_user_id(), // phpcs:ignore
			]
		);

		$applications = new \WP_Query( $args );

		ob_start();

		if ( $applications->have_posts() ) {
			get_job_manager_template(
				'applications/past-applications.php',
				[
					'applications'  => $applications->posts,
					'max_num_pages' => $applications->max_num_pages,
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);
		} else {
			get_job_manager_template( 'applications/past-applications-none.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
		}

		return ob_get_clean();
	}
}
