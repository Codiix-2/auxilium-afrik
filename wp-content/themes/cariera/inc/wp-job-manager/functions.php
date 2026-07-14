<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'cariera_newly_posted' ) ) {
	/**
	 * Newly Job Posted
	 *
	 * @since   1.1.0
	 * @version 1.5.3
	 */
	function cariera_newly_posted() {
		global $post;

		$now       = gmdate( 'U' );
		$published = get_the_time( 'U' );
		$new       = false;

		// set to 48 hours in seconds.
		if ( $now - $published <= 2 * 24 * 60 * 60 ) {
			$new = true;
		}

		return $new;
	}
}

if ( ! function_exists( 'cariera_get_rating_class' ) ) {
	/**
	 * Rating class function
	 *
	 * @since   1.1.0
	 * @version 1.7.5
	 *
	 * @param int $average
	 */
	function cariera_get_rating_class( $average ) {
		if ( ! $average ) {
				$class = 'no-stars';
		} else {
			switch ( $average ) {
				case $average == 1:
					$class = 'one-stars';
					break;
				case $average == 2:
					$class = 'two-stars';
					break;
				case $average == 3:
					$class = 'three-stars';
					break;
				case $average == 4:
					$class = 'four-stars';
					break;
				case $average == 5:
					$class = 'five-stars';
					break;

				default:
					$class = 'no-stars';
					break;
			}
		}
		return $class;
	}
}

if ( ! function_exists( 'cariera_job_applications' ) ) {
	/**
	 * Show Number of Job Applications
	 *
	 * @since   1.2.5
	 * @version 1.7.6
	 *
	 * @param int $post_id
	 */
	function cariera_job_applications( $post_id = '' ) {
		if ( ! class_exists( 'WP_Job_Manager_Applications' ) ) {
			return;
		}

		global $post;

		if ( empty( $post_id ) ) {
			$post_id = $post->ID;
		}

		$count = get_job_application_count( $post_id );

		echo '<span>' . esc_html( $count ) . ' ' . esc_html__( 'Application(s)', 'cariera' ) . '</span>';
	}
}

if ( ! function_exists( 'cariera_the_company_logo' ) ) {
	/**
	 * Company Logos
	 *
	 * @since   1.3.2
	 * @version 1.6.6
	 *
	 * @param array $args
	 */
	function cariera_the_company_logo( $args = [] ) {
		$defaults = apply_filters(
			'cariera_the_company_logo_args',
			[
				'size'    => 'thumbnail',
				'default' => null,
				'post'    => null,
			]
		);

		$args = wp_parse_args( $defaults, $args );
		$size = apply_filters( 'cariera_company_logo_size', $args['size'] );

		the_company_logo( $size, $args['default'], $args['post'] );
	}
}

/**
 * Output the job's min & max rate if there is any
 *
 * @since 1.4.1
 */
function cariera_job_rate() {
	global $post;

	$currency_position = get_option( 'cariera_currency_position', 'before' );
	$rate_min          = get_post_meta( $post->ID, '_rate_min', true );

	if ( $rate_min ) {
		$rate_max = get_post_meta( $post->ID, '_rate_max', true );

		// Currency Symbol Before.
		if ( 'before' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}
		echo esc_html( $rate_min );
		// Currency Symbol After.
		if ( 'after' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}

		// MAX Rate if there is any.
		if ( ! empty( $rate_max ) ) {
			echo esc_html( ' - ' );

			// Currency Symbol Before.
			if ( 'before' === $currency_position ) {
				echo \Cariera\currency_symbol();
			}
			echo esc_html( $rate_max );
			// Currency Symbol After.
			if ( 'after' === $currency_position ) {
				echo \Cariera\currency_symbol();
			}
		}
		esc_html_e( '/hour', 'cariera' );
	}
}

/**
 * Output the job's min & max salary if there is any
 *
 * @since 1.4.1
 */
function cariera_job_salary() {
	global $post;

	$currency_position = get_option( 'cariera_currency_position', 'before' );
	$salary_min        = get_post_meta( $post->ID, '_salary_min', true );

	if ( $salary_min ) {
		$salary_max = get_post_meta( $post->ID, '_salary_max', true );

		// Currency Symbol Before.
		if ( 'before' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}
		echo esc_html( $salary_min );
		// Currency Symbol After.
		if ( 'after' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}

		// MAX Salary if there is any.
		if ( ! empty( $salary_max ) ) {
			echo esc_html( ' - ' );

			// Currency Symbol Before.
			if ( 'before' === $currency_position ) {
				echo \Cariera\currency_symbol();
			}
			echo esc_html( $salary_max );
			// Currency Symbol After.
			if ( 'after' === $currency_position ) {
				echo \Cariera\currency_symbol();
			}
		}
	}
}

/**
 * Returning the single job layout option
 *
 * @since   1.5.5
 * @version 1.5.5
 */
function cariera_single_job_layout() {
	$layout = apply_filters( 'cariera_job_manager_single_job_layout', get_option( 'cariera_job_manager_single_job_layout' ) );

	if ( empty( $layout ) ) {
		$layout = 'v1';
	}

	return $layout;
}

if ( ! function_exists( 'cariera_get_the_job_listing_category' ) ) {
	/**
	 * Get the category of the job listing
	 *
	 * @since 1.7.5
	 *
	 * @param mixed $post
	 */
	function cariera_get_the_job_listing_category( $post = null ) {
		$post = get_post( $post );
		if ( 'job_listing' !== $post->post_type ) {
			return '';
		}

		if ( ! get_option( 'job_manager_enable_categories' ) ) {
			return '';
		}

		$categories = wp_get_object_terms( $post->ID, 'job_listing_category' );

		if ( is_wp_error( $categories ) ) {
			return '';
		}

		return apply_filters( 'cariera_the_job_listing_category_output', $categories, $post );
	}
}

if ( ! function_exists( 'cariera_the_job_listing_category_output' ) ) {
	/**
	 * Output the category of the job listing.
	 *
	 * @since 1.7.5
	 *
	 * @param mixed $post
	 */
	function cariera_the_job_listing_category_output( $post = null ) {
		$categories = cariera_get_the_job_listing_category( $post );

		if ( ! empty( $categories ) ) {
			echo '<ul class="categories">';
			foreach ( $categories as $category ) {
				echo '<li><a href="' . esc_url( get_term_link( $category ) ) . '" target="_blank">' . esc_html( $category->name ) . '</a></li>';
			}
			echo '</ul>';
		}
	}
}

/**
 * Get meta data from a listing.
 *
 * @since 1.7.7
 *
 * @param string $meta
 * @param string $post_type
 * @param [type] $post
 */
function cariera_wpjm_get_meta( $meta = '', $post_type = 'job_listing', $post = null ) {
	$post       = get_post( $post );
	$post_types = [ 'job_listing', 'resume', 'company' ];

	if ( ! in_array( $post->post_type, $post_types, true ) ) {
		return;
	}

	if ( empty( $meta ) ) {
		return;
	}

	$meta_data = get_post_meta( $post->ID, $meta, true );

	return $meta_data;
}

/**
 * Output a listing's meta data.
 *
 * @since 1.7.7
 *
 * @param string $meta
 * @param string $post_type
 */
function cariera_wpjm_meta_output( $meta = '', $post_type = 'job_listing' ) {
	$meta = cariera_wpjm_get_meta( $meta, $post_type );

	if ( is_array( $meta ) ) {
		foreach ( $meta as $value ) {
			$output_meta[] = $value;
		}

		echo esc_html( join( ', ', $output_meta ) );
	} else {
		echo esc_html( $meta );
	}
}

/**
 * Get terms from a listing.
 *
 * @since 1.7.7
 *
 * @param string $taxonomy
 * @param string $post_type
 * @param [type] $post
 */
function cariera_wpjm_get_terms( $taxonomy = '', $post_type = 'job_listing', $post = null ) {
	$post       = get_post( $post );
	$post_types = [ 'job_listing', 'resume', 'company' ];

	if ( ! in_array( $post->post_type, $post_types, true ) ) {
		return;
	}

	if ( empty( $taxonomy ) ) {
		return;
	}

	$terms = get_the_terms( $post->ID, $taxonomy );

	return $terms;
}

/**
 * Output a listing's terms.
 *
 * @since 1.7.7
 *
 * @param string $taxonomy
 * @param string $post_type
 */
function cariera_wpjm_terms_output( $taxonomy = '', $post_type = 'job_listing' ) {
	$terms = cariera_wpjm_get_terms( $taxonomy, $post_type );

	foreach ( $terms as $term ) {
		$output_term[] = $term->name;
	}

	echo esc_html( join( ', ', $output_term ) );
}

/**
 * Check for listings that are soon to expire
 *
 * @since   1.8.0
 * @version 2.0.0
 *
 * @param array  $post_types
 * @param string $days_notice
 */
function cariera_check_soon_to_expire_listings( $post_types = [ 'job_listing' ], $days_notice = 7 ) {
	$post_types = (array) $post_types;

	$expires_meta_map = [
		'job_listing' => '_job_expires',
		'company'     => '_company_expires',
		'resume'      => '_resume_expires',
	];

	$user_id = get_current_user_id();

	$notice_before_datetime = current_datetime()->add(
		new DateInterval( 'P' . absint( $days_notice ) . 'D' )
	);

	$meta_query = [
		'relation' => 'OR',
	];

	foreach ( $post_types as $post_type ) {
		if ( ! isset( $expires_meta_map[ $post_type ] ) ) {
			continue;
		}

		$expires_meta_key = $expires_meta_map[ $post_type ];

		$meta_query[] = [
			'relation' => 'AND',
			[
				'key'     => $expires_meta_key,
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			],
			[
				'key'     => $expires_meta_key,
				'value'   => $notice_before_datetime->format( 'Y-m-d' ),
				'compare' => '<',
				'type'    => 'DATE',
			],
		];
	}

	$listings = get_posts(
		[
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'author'         => $user_id,
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		]
	);

	return $listings;
}
