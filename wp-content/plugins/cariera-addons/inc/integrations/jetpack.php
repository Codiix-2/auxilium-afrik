<?php
namespace Cariera_Addons\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Jetpack {

	/**
	 * Initialize integration.
	 *
	 * @since 0.9.5
	 */
	public static function init() {
		// Bail if resume indexing is discouraged.
		if ( function_exists( 'resume_manager_discourage_resume_search_indexing' ) && resume_manager_discourage_resume_search_indexing() ) {
			return;
		}

		add_filter( 'jetpack_sitemap_post_types', [ __CLASS__, 'add_resume_to_sitemap' ] );
	}

	/**
	 * Add the resume post type to Jetpack sitemap.
	 *
	 * @since 0.9.5
	 *
	 * @param array $post_types
	 */
	public static function add_resume_to_sitemap( $post_types ) {
		$post_types[] = \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME;
		return $post_types;
	}
}
