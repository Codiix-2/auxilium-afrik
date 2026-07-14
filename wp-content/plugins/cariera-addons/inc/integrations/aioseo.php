<?php
namespace Cariera_Addons\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIOSEO {

	/**
	 * Initialize AIOSEO integration.
	 *
	 * @since 0.9.5
	 */
	public static function init() {
		if ( ! function_exists( 'resume_manager_discourage_resume_search_indexing' ) || ! resume_manager_discourage_resume_search_indexing() ) {
			return;
		}

		add_action( 'aiosp_sitemap_post_filter', [ __CLASS__, 'exclude_resumes_from_sitemap' ], 10, 3 );
	}

	/**
	 * Exclude resume posts from the sitemap.
	 *
	 * @since 0.9.5
	 *
	 * @param WP_Post[] $posts
	 */
	public static function exclude_resumes_from_sitemap( $posts ) {
		foreach ( $posts as $index => $post ) {
			if ( $post instanceof \WP_Post && \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME === $post->post_type ) {
				unset( $posts[ $index ] );
			}
		}
		return $posts;
	}
}
