<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get post terms from the given taxonomy.
 *
 * @param int    $post_id
 * @param string $taxonomy
 *
 * @since 1.7.0
 */
function get_terms( $post_id, $taxonomy = 'category' ) {
	$raw_terms = (array) wp_get_post_terms( $post_id, $taxonomy );

	$terms = [];
	if ( ! empty( $raw_terms['errors'] ) ) {
		return $terms;
	}

	foreach ( $raw_terms as $raw_term ) {
		$terms[] = [
			'name' => $raw_term->name,
			'link' => get_term_link( $raw_term ),
		];
	}

	return $terms;
}

/**
 * Searches for term parents' IDs of hierarchical taxonomies, including current term.
 * This function is similar to the WordPress function get_category_parents() but handles any type of taxonomy.
 *
 * @since  1.2.7
 *
 * @param int    $term_id
 * @param string $taxonomy
 */
function get_term_parents( $term_id = '', $taxonomy = 'category' ) {
	// Set up some default arrays.
	$list = [];

	// If no term ID or taxonomy is given, return an empty array.
	if ( empty( $term_id ) || empty( $taxonomy ) ) {
		return $list;
	}

	do {
		$list[] = $term_id;

		// Get next parent term.
		$term    = get_term( $term_id, $taxonomy );
		$term_id = $term->parent;
	} while ( $term_id );

	// Reverse the array to put them in the proper order for the trail.
	$list = array_reverse( $list );
	array_pop( $list );

	return $list;
}

/**
 * Gets parent posts' IDs of any post type, include current post
 *
 * @since  1.2.7
 *
 * @param int $post_id
 */
function get_post_parents( $post_id = '' ) {
	// Set up some default array.
	$list = [];

	// If no post ID is given, return an empty array.
	if ( empty( $post_id ) ) {
		return $list;
	}

	do {
		$list[] = $post_id;

		// Get next parent post.
		$post    = get_post( $post_id );
		$post_id = $post->post_parent;
	} while ( $post_id );

	// Reverse the array to put them in the proper order for the trail.
	$list = array_reverse( $list );
	array_pop( $list );

	return $list;
}

/**
 * Get page by title
 *
 * @param string $title
 * @param string $post_type
 *
 * @since   1.6.6
 */
function get_page_by_title( $title, $post_type = 'page' ) {
	$posts = get_posts(
		[
			'post_type'              => $post_type,
			'title'                  => $title,
			'post_status'            => 'all',
			'numberposts'            => 1,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => 'post_date ID',
			'order'                  => 'ASC',
		]
	);

	if ( ! empty( $posts ) ) {
		$page = $posts[0];
	} else {
		$page = null;
	}

	return $page;
}

/**
 * Fetching the pages titles.
 *
 * @since 1.3.3
 *
 * TODO: Check for fixes and improvements.
 */
function get_the_title() {

	// Blog Page.
	if ( is_home() ) {
		$blog_title = cariera_get_option( 'cariera_blog_title' );

		return $blog_title;
	}

	// WooCommerce Page.
	if ( \Cariera\wc_is_activated() && is_woocommerce() ) {
		if ( is_single() && ! is_attachment() ) {
			echo \get_the_title();
		} elseif ( ! is_single() ) {
			woocommerce_page_title();
		}

		return;
	}

	// 404 Page.
	if ( is_404() ) {
		return esc_html__( 'Error 404', 'cariera' );
	}

	// Homepage and Single Page.
	if ( is_home() || is_single() || is_404() ) {
		return \get_the_title();
	}

	// Search Page.
	if ( is_search() ) {
		// translators: %s is the search query.
		return sprintf( esc_html__( 'Search Results for: %s', 'cariera' ), '<span>' . get_search_query() . '</span>' );
	}

	// Archive Pages.
	if ( is_archive() ) {
		if ( is_author() ) {
			// translators: %s is the author.
			return sprintf( esc_html__( 'All posts by %s', 'cariera' ), get_the_author() );
		} elseif ( is_day() ) {
			// translators: %s is the date.
			return sprintf( esc_html__( 'Day: %s', 'cariera' ), get_the_date() );
		} elseif ( is_month() ) {
			// translators: %s is the date.
			return sprintf( esc_html__( 'Month: %s', 'cariera' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'cariera' ) ) );
		} elseif ( is_year() ) {
			// translators: %s is the date.
			return sprintf( esc_html__( 'Year: %s', 'cariera' ), get_the_date( _x( 'Y', 'yearly archives date format', 'cariera' ) ) );
		} elseif ( is_tag() ) {
			// translators: %s is the tag title.
			return sprintf( esc_html__( 'Tag: %s', 'cariera' ), single_tag_title( '', false ) );
		} elseif ( is_category() ) {
			// translators: %s is the category title.
			return sprintf( esc_html__( 'Category: %s', 'cariera' ), single_cat_title( '', false ) );
		} elseif ( is_tax( 'post_format', 'post-format-aside' ) ) {
			return esc_html__( 'Asides', 'cariera' );
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			return esc_html__( 'Videos', 'cariera' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			return esc_html__( 'Audio', 'cariera' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			return esc_html__( 'Quotes', 'cariera' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			return esc_html__( 'Galleries', 'cariera' );
		} else {
			return esc_html__( 'Archives', 'cariera' );
		}
	}

	return \get_the_title();
}

/**
 * Returns an array with all the compatible addons
 *
 * @since 1.8.7
 */
function compatible_addons() {
	$products = [
		'cariera-events'        => [
			'title'  => esc_html( 'Cariera Events' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/cariera-events.jpg',
			'link'   => 'https://1.envato.market/cariera-events',
		],
		'wpjm-search-filtering' => [
			'title'  => esc_html( 'S&F for WPJM' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-sf.jpg',
			'link'   => 'https://plugins.smyl.es/wp-job-manager-search-and-filtering',
		],
		'wpjm-field-editor'     => [
			'title'  => esc_html( 'WPJM Field Editor' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-field-editor.jpg',
			'link'   => 'https://plugins.smyl.es/wp-job-manager-field-editor/',
		],
		'wpjm-packages'         => [
			'title'  => esc_html( 'WPJM Packages' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-packages.jpg',
			'link'   => 'https://plugins.smyl.es/wp-job-manager-packages/',
		],
		'wpjm-resume-alerts'    => [
			'title'  => esc_html( 'WPJM Resumes Alerts' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-resume-alerts.jpg',
			'link'   => 'https://plugins.smyl.es/wp-job-manager-resume-alerts/',
		],
		'wpjm-visibility'       => [
			'title'  => esc_html( 'WPJM Visibility' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-visibility.jpg',
			'link'   => 'https://plugins.smyl.es/wp-job-manager-visibility/',
		],
		'wpjm-emails'           => [
			'title'  => esc_html( 'WPJM Emails' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-emails.jpg',
			'link'   => 'https://plugins.smyl.es/wp-job-manager-emails/',
		],

		'wpjm-linkedin'         => [
			'title'  => esc_html( 'Linkedin for WPJM' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-linkedin.jpg',
			'link'   => 'https://1.envato.market/Linkedin-wpjm',
		],
		'wpjm-essentials'       => [
			'title'  => esc_html( 'Essentials for WPJM' ),
			'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-essentials.jpg',
			'link'   => 'https://1.envato.market/wpjm-essentials',
		],
	];

	return $products;
}

/**
 * Footer social media choices (used in Customizer).
 *
 * @since 1.9.8
 */
function footer_social_media() {
	$choices = [
		''            => '-',
		'facebook'    => esc_html__( 'Facebook', 'cariera' ),
		'twitter'     => esc_html__( 'Twitter', 'cariera' ),
		'twitter-x'   => esc_html__( 'X (Twitter)', 'cariera' ),
		'google-plus' => esc_html__( 'Google Plus', 'cariera' ),
		'instagram'   => esc_html__( 'Instagram', 'cariera' ),
		'linkedin'    => esc_html__( 'LinkedIn', 'cariera' ),
		'pinterest'   => esc_html__( 'Pinterest', 'cariera' ),
		'tumblr'      => esc_html__( 'Tumblr', 'cariera' ),
		'github'      => esc_html__( 'GitHub', 'cariera' ),
		'dribbble'    => esc_html__( 'Dribbble', 'cariera' ),
		'wordpress'   => esc_html__( 'WordPress', 'cariera' ),
		'amazon'      => esc_html__( 'Amazon', 'cariera' ),
		'dropbox'     => esc_html__( 'Dropbox', 'cariera' ),
		'paypal'      => esc_html__( 'PayPal', 'cariera' ),
		'yahoo'       => esc_html__( 'Yahoo', 'cariera' ),
		'flickr'      => esc_html__( 'Flickr', 'cariera' ),
		'reddit'      => esc_html__( 'Reddit', 'cariera' ),
		'vimeo'       => esc_html__( 'Vimeo', 'cariera' ),
		'spotify'     => esc_html__( 'Spotify', 'cariera' ),
		'youtube'     => esc_html__( 'YouTube', 'cariera' ),
		'whatsapp'    => esc_html__( 'WhatsApp', 'cariera' ),
		'telegram'    => esc_html__( 'Telegram', 'cariera' ),
		'vk'          => esc_html__( 'Vkontakte', 'cariera' ),
		'tiktok'      => esc_html__( 'TikTok', 'cariera' ),
		'threads'     => esc_html__( 'Threads', 'cariera' ),
		'discord'     => esc_html__( 'Discord', 'cariera' ),
		'bluesky'     => esc_html__( 'Bluesky', 'cariera' ),
		'xing'        => esc_html__( 'Xing', 'cariera' ),
	];

	return apply_filters( 'cariera_footer_social_media', $choices );
}
