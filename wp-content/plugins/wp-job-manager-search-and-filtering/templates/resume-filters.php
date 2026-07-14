<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//wp_enqueue_script( 'wp-resume-manager-ajax-filters' );
do_action( 'resume_manager_sf_resume_filters_before', $atts );
?>

<form class="resume_filters wpjmsf_filters">
	<?php do_action( 'search_and_filtering_filters_start_resume', $atts ); ?>

	<div class="search_resumes">
		<?php do_action( 'search_and_filtering_filters_search_resume', $atts ); ?>
	</div>

	<?php do_action( 'search_and_filtering_filters_end_resume', $atts ); ?>
	<div class="showing_resumes"></div>
	<div id="search-filtering-resume-not-init-error" style="text-align: center; display: none;">
		<h4><?php _e( 'Listings were not initialized due to no sections configured to output on this page.', 'wp-job-manager-search-and-filtering' ); ?></h4>
		<p><?php _e( 'Use one of the built in methods to output a section on this page, or if you want to disable Search and Filtering, set the shortcode like this:', 'wp-job-manager-search-and-filtering' ); ?>
			<br/><code>&#x5B;resumes show_filters="false" show_sf_filters="false"&#x5D;</code></p>
	</div>
</form>

<?php do_action( 'job_manager_sf_resume_filters_after', $atts ); ?>

<noscript><?php esc_html_e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-job-manager', 'wp-job-manager-search-and-filtering' ); ?></noscript>

