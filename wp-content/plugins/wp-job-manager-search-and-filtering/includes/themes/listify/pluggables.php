<?php
global $wp_version;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This is required for any version of WordPress older than 5.3
 *
 * @see https://core.trac.wordpress.org/ticket/34226
 */
if ( ! function_exists( 'listify_partial_search_filters_home' ) && version_compare( $wp_version, 5.3, '<' )) {

	/**
	 * Search filters for the homepage (redirects).
	 *
	 * @return string.
	 * @since 1.9.0
	 *
	 */
	function listify_partial_search_filters_home() {

		/**
		 * This is required for any version of WordPress older than 5.3
		 * @see https://core.trac.wordpress.org/ticket/34226
		 */
		if ( WPJMSF()->job->output->is_enabled() && WPJMSF()->job->sections->has_enabled_section_by_output( 'listify_widget_search_listings_homepage_hero' ) ) {
			ob_start();
		?>
			<form class="job_search_form" action="<?php echo esc_url( listify_get_listings_page_url() ); ?>" method="GET">
				<?php WPJMSF()->job->output->output_sections_by_location( 'listify_widget_search_listings_homepage_hero' ); ?>
			</form>
		<?php
			return ob_get_clean();
		}

		ob_start();

		if ( listify_has_integration( 'facetwp' ) ) {
			locate_template( array( 'job-filters-home-facetwp.php' ), true, false );
		} else {
			locate_template( array( 'job-filters-home.php' ), true, false );
		}

		return ob_get_clean();
	}
}