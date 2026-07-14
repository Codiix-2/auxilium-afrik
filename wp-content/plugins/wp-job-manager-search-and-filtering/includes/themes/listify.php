<?php
namespace WPJMSF\Themes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Listify
 *
 * @package WPJMSF\Themes
 */
class Listify {

	/**
	 * Listify constructor.
	 */
	public function __construct() {
	}
}
//namespace {
//
//	if ( ! function_exists( 'listify_partial_search_filters_home' ) ) {
//
//		/**
//		 * Search filters for the homepage (redirects).
//		 *
//		 * @return string.
//		 * @since 1.9.0
//		 *
//		 */
//		function listify_partial_search_filters_home() {
//
//			ob_start();
//
//			if ( listify_has_integration( 'facetwp' ) ) {
//				locate_template( array( 'job-filters-home-facetwp.php' ), true, false );
//			} else {
//				locate_template( array( 'job-filters-home.php' ), true, false );
//			}
//
//			return ob_get_clean();
//		}
//	}
//}