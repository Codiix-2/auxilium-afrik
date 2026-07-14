<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'get_resumes' ) ) {
	/**
	 * Queries job listings with certain criteria and returns them
	 *
	 * @since   0.9.5
	 * @version 1.0.6
	 *
	 * @param array $args
	 */
	function get_resumes( $args = [] ) {
		global $resume_manager_keyword;

		$args = wp_parse_args(
			$args,
			[
				'search_location'   => '',
				'search_keywords'   => '',
				'search_categories' => [],
				'search_skills'     => '',
				'offset'            => 0,
				'posts_per_page'    => 20,
				'orderby'           => 'date',
				'order'             => 'DESC',
				'featured'          => null,
				'fields'            => 'all',
			]
		);

		// Query args.
		$query_args = [
			'post_type'              => \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME,
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => 1,
			'offset'                 => absint( $args['offset'] ),
			'posts_per_page'         => intval( $args['posts_per_page'] ),
			'orderby'                => $args['orderby'],
			'order'                  => $args['order'],
			'tax_query'              => [], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Empty.
			'meta_query'             => [], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Empty.
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false,
			'fields'                 => $args['fields'],
		];

		if ( $args['posts_per_page'] < 0 ) {
			$query_args['no_found_rows'] = true;
		}

		if ( ! empty( $args['search_location'] ) ) {
			$location_meta_keys = [ 'geolocation_formatted_address', '_candidate_location', 'geolocation_state_long' ];
			$location_search    = [ 'relation' => 'OR' ];
			foreach ( $location_meta_keys as $meta_key ) {
				$location_search[] = [
					'key'     => $meta_key,
					'value'   => $args['search_location'],
					'compare' => 'like',
				];
			}
			$query_args['meta_query'][] = $location_search;
		}

		if ( ! empty( $args['search_skills'] ) ) {
			$skills_search = [
				'key'     => '_resume_skills',
				'value'   => $args['search_skills'],
				'compare' => 'like',
			];

			$query_args['meta_query'][] = $skills_search;
		}

		if ( ! is_null( $args['featured'] ) ) {
			$query_args['meta_query'][] = [
				'key'     => '_featured',
				'value'   => '1',
				'compare' => $args['featured'] ? '=' : '!=',
			];
		}

		if ( ! empty( $args['search_categories'] ) ) {
			$field                     = is_numeric( $args['search_categories'][0] ) ? 'term_id' : 'slug';
			$operator                  = 'all' === get_option( 'resume_manager_category_filter_type', 'all' ) && count( $args['search_categories'] ) > 1 ? 'AND' : 'IN';
			$query_args['tax_query'][] = [
				'taxonomy'         => \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY,
				'field'            => $field,
				'terms'            => array_values( $args['search_categories'] ),
				'include_children' => 'AND' !== $operator,
				'operator'         => $operator,
			];
		}

		if ( 'featured' === $args['orderby'] ) {
			$query_args['orderby'] = [
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			];
		}

		if ( 'rand_featured' === $args['orderby'] ) {
			$query_args['orderby'] = [
				'menu_order' => 'ASC',
				'rand'       => 'ASC',
			];
		}

		if ( ! empty( $args['post__not_in'] ) ) {
			$query_args['post__not_in'] = $args['post__not_in'];
		}

		$resume_manager_keyword = sanitize_text_field( $args['search_keywords'] );

		if ( $resume_manager_keyword ) {
			$query_args['s'] = $resume_manager_keyword;
			add_filter( 'posts_search', 'get_resumes_keyword_search' );
		}

		$query_args = apply_filters( 'resume_manager_get_resumes', $query_args, $args );

		if ( empty( $query_args['meta_query'] ) ) {
			unset( $query_args['meta_query'] );
		}

		if ( empty( $query_args['tax_query'] ) ) {
			unset( $query_args['tax_query'] );
		}

		// Filter args.
		$query_args = apply_filters( 'get_resumes_query_args', $query_args, $args );

		do_action( 'before_get_resumes', $query_args, $args );

		$should_cache = 'rand_featured' !== $args['orderby'] && 'rand' !== $args['orderby'];

		// Cache results.
		if ( apply_filters( 'cariera_addons_get_resumes_cache_results', $should_cache ) ) {
			$to_hash            = defined( 'ICL_LANGUAGE_CODE' ) ? wp_json_encode( $query_args ) . ICL_LANGUAGE_CODE : wp_json_encode( $query_args );
			$query_args_hash    = 'jm_' . md5( $to_hash . CARIERA_ADDONS_VERSION ) . WP_Job_Manager_Cache_Helper::get_transient_version( 'get_resume_listings' );
			$result             = false;
			$cached_query_posts = get_transient( $query_args_hash );

			if ( is_string( $cached_query_posts ) ) {
				$cached_query_posts = json_decode( $cached_query_posts, false );
				if (
					$cached_query_posts
					&& is_object( $cached_query_posts )
					&& isset( $cached_query_posts->max_num_pages )
					&& isset( $cached_query_posts->found_posts )
					&& isset( $cached_query_posts->posts )
					&& is_array( $cached_query_posts->posts )
				) {
					if ( in_array( $query_args['fields'], [ 'ids', 'id=>parent' ], true ) ) {
						// For these special requests, just return the array of results as set.
						$posts = $cached_query_posts->posts;
					} else {
						$posts = array_map( 'get_post', $cached_query_posts->posts );
					}

					$result = new WP_Query();
					$result->parse_query( $query_args );
					$result->posts         = $posts;
					$result->found_posts   = intval( $cached_query_posts->found_posts );
					$result->max_num_pages = intval( $cached_query_posts->max_num_pages );
					$result->post_count    = count( $posts );
				}
			}

			if ( false === $result ) {
				$result = new WP_Query( $query_args );

				$cacheable_result                  = [];
				$cacheable_result['posts']         = array_values( $result->posts );
				$cacheable_result['found_posts']   = $result->found_posts;
				$cacheable_result['max_num_pages'] = $result->max_num_pages;
				set_transient( $query_args_hash, wp_json_encode( $cacheable_result ), DAY_IN_SECONDS );
			}
		} else {
			$result = new WP_Query( $query_args );
		}

		do_action( 'after_get_resumes', $query_args, $args );

		remove_filter( 'posts_search', 'get_resumes_keyword_search' );

		return $result;
	}
}

if ( ! function_exists( '_wpjm_resumes_shuffle_featured_post_results_helper' ) ) {
	/**
	 * Helper function to maintain featured status when shuffling results.
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 *
	 * @param WP_Post $a
	 * @param WP_Post $b
	 */
	function _wpjm_resumes_shuffle_featured_post_results_helper( $a, $b ) {
		if ( -1 === $a->menu_order || -1 === $b->menu_order ) {
			// Left is featured.
			if ( 0 === $b->menu_order ) {
				return -1;
			}
			// Right is featured.
			if ( 0 === $a->menu_order ) {
				return 1;
			}
		}
		return wp_rand( -1, 1 );
	}
}

if ( ! function_exists( 'get_resumes_keyword_search' ) ) {
	/**
	 * Join and where query for keywords
	 *
	 * @since 0.9.5
	 *
	 * @param array $search Search query args.
	 */
	function get_resumes_keyword_search( $search ) {
		global $wpdb, $resume_manager_keyword;

		// Searchable Meta Keys: set to empty to search all meta keys.
		$searchable_meta_keys = [
			'_candidate_name',
			'_candidate_title',
			'_candidate_location',
		];
		/**
		 * Filters meta fields used during search.
		 *
		 * @since 0.9.5
		 *
		 * @param array $meta_keys Meta key used in search.
		 */
		$searchable_meta_keys = apply_filters( 'resume_manager_searchable_meta_keys', $searchable_meta_keys );

		// Set Search DB Conditions.
		$conditions = [];

		/**
		 * Filters whether to search post meta.
		 *
		 * @since 1.18.4
		 *
		 * @param bool $search_meta Switch to search meta or not.
		 */
		if ( apply_filters( 'resume_manager_search_post_meta', true ) ) {

			// Only selected meta keys.
			if ( $searchable_meta_keys ) {
				$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ( '" . implode( "','", array_map( 'esc_sql', $searchable_meta_keys ) ) . "' ) AND meta_value LIKE '%" . esc_sql( $resume_manager_keyword ) . "%' )";
			} else {
				// No meta keys defined, search all post meta value.
				$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%" . esc_sql( $resume_manager_keyword ) . "%' )";
			}
		}

		// Search taxonomy.
		$conditions[] = "{$wpdb->posts}.ID IN ( SELECT object_id FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id WHERE t.name LIKE '%" . esc_sql( $resume_manager_keyword ) . "%' )";

		/**
		 * Filters the conditions to use when querying resume listings. Resulting array is joined with OR statements.
		 *
		 * @since 0.9.5
		 *
		 * @param array  $conditions          Conditions to join by OR when querying resume listings.
		 * @param string $job_manager_keyword Search query.
		 */
		$conditions = apply_filters( 'resume_manager_search_conditions', $conditions, $resume_manager_keyword );
		if ( empty( $conditions ) ) {
			return $search;
		}

		$conditions_str = implode( ' OR ', $conditions );

		if ( ! empty( $search ) ) {
			$search = preg_replace( '/^ AND /', '', $search );
			$search = " AND ( {$search} OR ( {$conditions_str} ) )";
		} else {
			$search = " AND ( {$conditions_str} )";
		}

		return $search;
	}
}

if ( ! function_exists( 'order_featured_resume' ) ) {
	/**
	 * WP Core doens't let us change the sort direction for invidual orderby params - http://core.trac.wordpress.org/ticket/17065
	 *
	 * @since 0.9.5
	 *
	 * @param array $args
	 */
	function order_featured_resume( $args ) {
		global $wpdb;

		$args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_title ASC";

		return $args;
	}
}

if ( ! function_exists( 'get_resume_share_link' ) ) {
	/**
	 * Generates a sharing link which allows someone to view the resume directly (even if permissions do not usually allow it)
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 *
	 * @param int $resume_id The resume ID.
	 */
	function get_resume_share_link( $resume_id ) {
		$key = get_post_meta( $resume_id, 'share_link_key', true );

		if ( ! $key ) {
			$key = wp_generate_password( 32, false );
			update_post_meta( $resume_id, 'share_link_key', $key );
		}

		return add_query_arg( 'key', $key, get_permalink( $resume_id ) );
	}
}

if ( ! function_exists( 'get_resume_categories' ) ) {
	/**
	 * Outputs a form to submit a new job to the site from the frontend.
	 *
	 * @since   0.9.5
	 * @version 0.9.6
	 */
	function get_resume_categories() {
		if ( ! get_option( 'resume_manager_enable_categories' ) ) {
			return [];
		}

		$terms = get_terms(
			[
				'taxonomy'   => \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			]
		);

		return is_wp_error( $terms ) ? [] : $terms;
	}
}

if ( ! function_exists( 'resume_manager_get_filtered_links' ) ) {
	/**
	 * Shows links after filtering resumes
	 *
	 * @since 0.9.5
	 *
	 * @param array $args
	 */
	function resume_manager_get_filtered_links( $args = [] ) {

		$links = apply_filters(
			'resume_manager_resume_filters_showing_resumes_links',
			[
				'reset' => [
					'name' => esc_html__( 'Reset', 'cariera-addons' ),
					'url'  => '#',
				],
			],
			$args
		);

		$return = '';

		foreach ( $links as $key => $link ) {
			$return .= '<a href="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $key ) . '">' . $link['name'] . '</a>';
		}

		return $return;
	}
}

/**
 * True if an the user can edit a resume.
 *
 * @since 0.9.5
 *
 * @param int $resume_id
 */
function resume_manager_user_can_edit_resume( $resume_id ) {
	$can_edit = true;

	if ( ! $resume_id || ! is_user_logged_in() ) {
		$can_edit = false;
		if ( $resume_id
			&& ! resume_manager_user_requires_account()
			&& isset( $_COOKIE[ 'wp-job-manager-submitting-resume-key-' . $resume_id ] )
			&& $_COOKIE[ 'wp-job-manager-submitting-resume-key-' . $resume_id ] === get_post_meta( $resume_id, '_submitting_key', true )
		) {
			$can_edit = true;
		}
	} else {
		$resume = get_post( $resume_id );

		if ( ! $resume || ( absint( $resume->post_author ) !== get_current_user_id() && ! current_user_can( 'edit_post', $resume_id ) ) ) {
			$can_edit = false;
		}
	}

	return apply_filters( 'resume_manager_user_can_edit_resume', $can_edit, $resume_id );
}

/**
 * Checks if users are allowed to edit published resumes.
 *
 * @since 0.9.5
 */
function resume_manager_user_can_edit_published_submissions() {
	$can_edit_published_submissions = in_array( get_option( 'resume_manager_user_edit_published_submissions' ), [ 'yes', 'yes_moderated' ], true );

	/**
	 * Override the setting for allowing a user to edit published resumes.
	 *
	 * @since 0.9.5
	 *
	 * @param bool $can_edit_published_submissions
	 */
	return apply_filters( 'resume_manager_user_can_edit_published_submissions', $can_edit_published_submissions );
}

/**
 * Checks if moderation is required when users edit published resumes.
 *
 * @since 0.9.5
 *
 * @return bool
 */
function resume_manager_published_submission_edits_require_moderation() {
	$require_moderation = 'yes_moderated' === get_option( 'resume_manager_user_edit_published_submissions' );

	/**
	 * Override the setting for user edits to published resumes requiring moderation.
	 *
	 * @since 0.9.5
	 *
	 * @param bool $require_moderation True if moderation is required before making edits public.
	 */
	return apply_filters( 'resume_manager_published_submission_edits_require_moderation', $require_moderation );
}

/**
 * Checks if users are allowed to edit reesumes that are pending approval.
 *
 * @since 0.9.5
 */
function resume_manager_user_can_edit_pending_submissions() {
	return apply_filters( 'resume_manager_user_can_edit_pending_submissions', 1 === intval( get_option( 'resume_manager_user_can_edit_pending_submissions' ) ) );
}

/**
 * True if an the user can view the full resume name.
 *
 * @since   0.9.5
 * @version 0.9.10
 *
 * @param int $resume_id
 */
function resume_manager_user_can_view_resume_name( $resume_id ) {
	$resume = get_post( $resume_id );
	if ( ! $resume ) {
		return false;
	}

	// Allow previews unconditionally.
	if ( 'preview' === $resume->post_status ) {
		return true;
	}

	$caps = get_option( 'cariera_addons_view_resume_name_capability' );

	$can_view = empty( $caps ) ? true : false;

	// Check if current user has any of the required capabilities.
	foreach ( $caps as $cap ) {
		if ( current_user_can( $cap ) ) {
			$can_view = true;
			break;
		}
	}

	// Allow author to view their own resume name.
	if ( 0 < $resume->post_author && get_current_user_id() === absint( $resume->post_author ) ) {
		$can_view = true;
	}

	// Allow access if share key matches.
	$key = get_post_meta( $resume_id, 'share_link_key', true );
	if ( '' !== $key && isset( $_GET['key'] ) && $key === sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) { // phpcs:ignore
		$can_view = true;
	}

	return apply_filters( 'resume_manager_user_can_view_resume_name', $can_view, $resume_id );
}

/**
 * True if an the user can browse resumes.
 *
 * @since   0.9.5
 * @version 0.9.10
 */
function resume_manager_user_can_browse_resumes() {
	$can_browse = true;
	$caps       = get_option( 'cariera_addons_browse_resume_capability' );

	if ( $caps ) {
		$can_browse = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_browse = true;
				break;
			}
		}
	}

	return apply_filters( 'resume_manager_user_can_browse_resumes', $can_browse );
}

/**
 * True if an the user can browse resumes.
 *
 * @since 0.9.10
 */
function cariera_addons_user_can_submit_resumes() {
	$can_submit = true;
	$caps       = get_option( 'cariera_addons_submit_resume_capability' );

	if ( $caps ) {
		$can_submit = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_submit = true;
				break;
			}
		}
	}

	return apply_filters( 'resume_manager_submit_resume_capability', $can_submit );
}

/**
 * True if an the user can view a resume.
 *
 * @since   0.9.5
 * @version 1.0.2
 *
 * @param int $resume_id The Resume ID.
 */
function resume_manager_user_can_view_resume( $resume_id ) {
	$can_view = true;
	$resume   = get_post( $resume_id );

	// Allow previews.
	if ( 'preview' === $resume->post_status ) {
		return true;
	}

	$caps = get_option( 'cariera_addons_view_resume_capability' );

	if ( $caps ) {
		$can_view = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_view = true;
				break;
			}
		}
	}

	if ( 'expired' === $resume->post_status ) {
		$can_view = false;
	}

	if ( $resume->post_author > 0 && get_current_user_id() === absint( $resume->post_author ) ) {
		$can_view = true;
	}

	$key = get_post_meta( $resume_id, 'share_link_key', true );
	if ( ! empty( $key ) && isset( $_GET['key'] ) && $key === sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) { // phpcs:ignore
		$can_view = true;
	}

	return apply_filters( 'resume_manager_user_can_view_resume', $can_view, $resume_id );
}

/**
 * True if an the user can view a resume.
 *
 * @since   0.9.5
 * @version 1.0.2
 *
 * @param int $resume_id The Resume ID.
 */
function resume_manager_user_can_view_contact_details( $resume_id ) {
	$can_view = true;
	$resume   = get_post( $resume_id );
	$caps     = get_option( 'cariera_addons_contact_resume_capability' );

	if ( $caps ) {
		$can_view = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_view = true;
				break;
			}
		}
	}

	if ( $resume->post_author > 0 && get_current_user_id() === absint( $resume->post_author ) ) {
		$can_view = true;
	}

	$key = get_post_meta( $resume_id, 'share_link_key', true );
	if ( ! empty( $key ) && isset( $_GET['key'] ) && $key === sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) { // phpcs:ignore
		$can_view = true;
	}

	return apply_filters( 'resume_manager_user_can_view_contact_details', $can_view, $resume_id );
}

/**
 * Check if the option to discourage resume search indexing is enabled.
 *
 * @since   0.9.5
 * @version 0.9.6
 */
function resume_manager_discourage_resume_search_indexing() {
	return apply_filters( 'resume_manager_discourage_resume_search_indexing', 1 === absint( get_option( 'resume_manager_discourage_resume_search_indexing' ) ) );
}

/**
 * Checks to see if the standard password setup email should be used.
 *
 * @since 0.9.5
 */
function resume_manager_use_standard_password_setup_email() {
	$use_standard_password_setup_email = true;

	// If username is being automatically generated, force them to send password setup email.
	if ( ! resume_manager_generate_username_from_email() ) {
		$use_standard_password_setup_email = 1 === absint( get_option( 'resume_manager_use_standard_password_setup_email' ) );
	}

	/**
	 * Allows an override of the setting for if a password should be auto-generated for new users.
	 */
	return apply_filters( 'resume_manager_use_standard_password_setup_email', $use_standard_password_setup_email );
}

if ( ! function_exists( 'get_resume_post_statuses' ) ) {
	/**
	 * Get post statuses used for resumes
	 *
	 * @since 0.9.5
	 */
	function get_resume_post_statuses() {
		return apply_filters(
			'resume_post_statuses',
			[
				'draft'           => _x( 'Draft', 'post status', 'cariera-addons' ),
				'expired'         => _x( 'Expired', 'post status', 'cariera-addons' ),
				'hidden'          => _x( 'Hidden', 'post status', 'cariera-addons' ),
				'preview'         => _x( 'Preview', 'post status', 'cariera-addons' ),
				'pending'         => _x( 'Pending approval', 'post status', 'cariera-addons' ),
				'pending_payment' => _x( 'Pending payment', 'post status', 'cariera-addons' ),
				'publish'         => _x( 'Published', 'post status', 'cariera-addons' ),
			]
		);
	}
}

/**
 * Upload dir
 *
 * @since 0.9.5
 *
 * @param  string $dir
 * @param  string $field
 */
function resume_manager_upload_dir( $dir, $field ) {
	if ( 'resume_file' === $field ) {
		$dir = 'resumes/resume_files';
	}
	return $dir;
}
add_filter( 'job_manager_upload_dir', 'resume_manager_upload_dir', 10, 2 );

/**
 * Count user resumes
 *
 * @since   0.9.5
 * @version 1.0.2
 *
 * @param int $user_id
 */
function resume_manager_count_user_resumes( $user_id = 0 ) {
	global $wpdb;

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Use a static variable to cache results per user.
	static $cache = [];

	// Return cached result.
	if ( isset( $cache[ $user_id ] ) ) {
		return $cache[ $user_id ];
	}

	// Get the post type constant value safely before using in the query.
	$post_type = \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME;

	// Prepare and run the query.
	$count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(ID) 
			FROM {$wpdb->posts} 
			WHERE post_author = %d 
			AND post_type = %s 
			AND post_status IN ( 'publish', 'pending', 'expired', 'hidden' );",
			$user_id,
			$post_type
		)
	);

	// Cache the result.
	$cache[ $user_id ] = $count;

	return $count;
}

/**
 * Get the permalink of a page if set
 *
 * @since 0.9.5
 *
 * @param  string $page
 */
function resume_manager_get_permalink( $page ) {
	$page_id = get_option( 'resume_manager_' . $page . '_page_id', false );
	if ( $page_id ) {
		return get_permalink( $page_id );
	} else {
		return false;
	}
}

/**
 * Calculate and return the resume expiry date
 *
 * @since 0.9.5
 *
 * @param  int $resume_id
 *
 * TODO: this has to be reworked like WPJM
 */
function calculate_resume_expiry( $resume_id ) {
	// Get duration from the product if set...
	$duration = get_post_meta( $resume_id, '_resume_duration', true );

	// ...otherwise use the global option
	if ( ! $duration ) {
		$duration = absint( get_option( 'resume_manager_submission_duration' ) );
	}

	if ( $duration ) {
		return date( 'Y-m-d', strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
	}

	return '';
}

/**
 * Checks if the visitor is currently on a WP Resume Manager page, resume, or taxonomy.
 *
 * @since 0.9.5
 */
function is_wp_resume_manager() {
	/**
	 * Filter the result of is_wp_resume_manager()
	 *
	 * @since 0.9.5
	 *
	 * @param bool $is_wp_resume_manager
	 */
	return apply_filters( 'is_wp_resume_manager', ( is_wp_resume_manager_page() || has_wp_resume_manager_shortcode() || is_wp_resume_manager_resume() || is_wp_resume_manager_taxonomy() ) );
}

/**
 * Checks if the visitor is currently on a WP Resume Manager page.
 *
 * @since   0.9.5
 * @version 0.9.10
 */
function is_wp_resume_manager_page() {
	$is_wp_resume_manager_page = is_post_type_archive( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME );

	if ( ! $is_wp_resume_manager_page ) {
		$wp_resume_manager_page_ids = array_filter(
			[
				get_option( 'resume_manager_submit_resume_form_page_id', false ),
				get_option( 'resume_manager_candidate_dashboard_page_id', false ),
				get_option( 'resume_manager_resumes_page_id', false ),
			]
		);

		/**
		 * Filters a list of all page IDs related to WP Resume Manager.
		 *
		 * @since 0.9.5
		 *
		 * @param int[] $wp_resume_manager_page_ids
		 */
		$wp_resume_manager_page_ids = array_unique( apply_filters( 'resume_manager_page_ids', $wp_resume_manager_page_ids ) );

		$is_wp_resume_manager_page = is_page( $wp_resume_manager_page_ids );
	}

	/**
	 * Filter the result of is_wp_resume_manager_page()
	 *
	 * @since 0.9.5
	 *
	 * @param bool $is_wp_resume_manager_page
	 */
	return apply_filters( 'is_wp_resume_manager_page', $is_wp_resume_manager_page );
}

/**
 * Checks if the provided content or the current single page or post has a WP Resume Manager shortcode.
 *
 * @since 0.9.5
 *
 * @param string|null       $content   Content to check. If not provided, it uses the current post content.
 * @param string|array|null $tag Check specifically for one or more shortcodes. If not provided, checks for any WP Resume Manager shortcode.
 */
function has_wp_resume_manager_shortcode( $content = null, $tag = null ) {
	global $post;

	$has_wp_resume_manager_shortcode = false;

	if ( null === $content && is_singular() && is_a( $post, 'WP_Post' ) ) {
		$content = $post->post_content;
	}

	if ( ! empty( $content ) ) {
		$wp_resume_manager_shortcodes = [ 'submit_resume_form', 'candidate_dashboard', 'resumes' ];
		/**
		 * Filters a list of all shortcodes associated with WP Resume Manager.
		 *
		 * @since 1.17.1
		 *
		 * @param string[] $wp_resume_manager_shortcodes
		 */
		$wp_resume_manager_shortcodes = array_unique( apply_filters( 'resume_manager_shortcodes', $wp_resume_manager_shortcodes ) );

		if ( null !== $tag ) {
			if ( ! is_array( $tag ) ) {
				$tag = [ $tag ];
			}
			$wp_resume_manager_shortcodes = array_intersect( $wp_resume_manager_shortcodes, $tag );
		}

		foreach ( $wp_resume_manager_shortcodes as $shortcode ) {
			if ( has_shortcode( $content, $shortcode ) ) {
				$has_wp_resume_manager_shortcode = true;
				break;
			}
		}
	}

	/**
	 * Filter the result of has_wp_resume_manager_shortcode()
	 *
	 * @since 0.9.5
	 *
	 * @param bool $has_wp_resume_manager_shortcode
	 */
	return apply_filters( 'has_wp_resume_manager_shortcode', $has_wp_resume_manager_shortcode );
}

/**
 * Checks if the current page is a resume listing.
 *
 * @since 0.9.5
 *
 * @return bool
 */
function is_wp_resume_manager_resume() {
	return is_singular( [ \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME ] );
}

/**
 * Checks if the visitor is on a page for a WP Resume Manager taxonomy.
 *
 * @since 0.9.5
 *
 * @return bool
 */
function is_wp_resume_manager_taxonomy() {
	return is_tax( get_object_taxonomies( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME ) );
}

/**
 * Whether to create attachments for files that are uploaded with a Resume.
 *
 * @since 0.9.5
 *
 * @return bool
 */
function resume_manager_attach_uploaded_files() {
	return apply_filters( 'resume_manager_attach_uploaded_files', false );
}

/**
 * Get Singular CPT Label "Resume"
 *
 * @since 1.0.1
 *
 * @param bool $lowercase
 */
function cariera_addons_resume_cpt_singular_label( $lowercase = false ) {
	$singular = get_option( 'cariera_addons_resume_cpt_singular_label', 'Resume' );

	// In case user saves with empty value.
	if ( empty( $singular ) ) {
		$singular = esc_html__( 'Resume', 'cariera-addons' );
	}

	$singular = esc_html( $singular );

	return apply_filters( 'cariera_addons_resume_get_singular_label', $lowercase ? strtolower( $singular ) : $singular );
}

/**
 * Get Plural CPT Label "Resume"
 *
 * @since 1.0.1
 *
 * @param bool $lowercase
 */
function cariera_addons_resume_cpt_plural_label( $lowercase = false ) {
	$plural = get_option( 'cariera_addons_resume_cpt_plural_label', 'Resumes' );

	// In case user saves with empty value.
	if ( empty( $plural ) ) {
		$plural = esc_html__( 'Resumes', 'cariera-addons' );
	}

	$plural = esc_html( $plural );

	return apply_filters( 'cariera_addons_resume_get_plural_label', $lowercase ? strtolower( $plural ) : $plural );
}
