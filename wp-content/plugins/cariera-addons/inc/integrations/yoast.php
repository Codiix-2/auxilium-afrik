<?php
namespace Cariera_Addons\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds additional compatibility with Yoast SEO.
 *
 * @since 0.9.5
 */
class Yoast {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Initialize the integration.
	 *
	 * @since 0.9.5
	 */
	public static function init() {
		if ( ! function_exists( 'resume_manager_discourage_resume_search_indexing' ) || ! resume_manager_discourage_resume_search_indexing() ) {
			return;
		}

		add_filter( 'wpseo_sitemap_entry', [ __CLASS__, 'exclude_resumes_from_sitemap' ], 10, 3 );
		add_filter( 'wpseo_schema_graph_pieces', [ __CLASS__, 'remove_webpage_from_schema' ], 10, 2 );
	}

	/**
	 * Exclude resumes from Yoast sitemap.
	 *
	 * @since   0.9.5
	 * @version 0.9.10
	 *
	 * @param string   $url The URL to be included in the sitemap.
	 * @param string   $type The type of the URL (e.g., 'post', 'page').
	 * @param \WP_Post $post The post object.
	 */
	public static function exclude_resumes_from_sitemap( $url, $type, $post ) {
		if ( $post instanceof \WP_Post && $post->post_type === \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME ) {
			return false;
		}
		return $url;
	}

	/**
	 * Remove WebPage schema object for resumes.
	 *
	 * @since 0.9.5
	 *
	 * @param array  $pieces The schema graph pieces.
	 * @param string $context The context in which the schema is being generated.
	 */
	public static function remove_webpage_from_schema( $pieces, $context ) {
		return array_filter(
			$pieces,
			function ( $piece ) {
				return ! $piece instanceof \Yoast\WP\SEO\Generators\Schema\WebPage;
			}
		);
	}
}
