<?php
/**
 * Search filters for the archive.
 *
 * @see https://github.com/Automattic/WP-Job-Manager/blob/master/templates/job-filters.php
 *
 * @since 1.8.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */
wp_enqueue_script( 'wp-job-manager-ajax-filters' );

// Must remain for Listify enqueues, etc
do_action( 'job_manager_job_filters_before', $atts );
do_action( 'job_manager_sf_job_filters_before', $atts );
?>

<form class="job_filters wpjmsf_filters">
	<?php do_action('search_and_filtering_filters_listify_top', $atts ); ?>

	<div class="archive-job_listing-filter-title">
		<?php do_action( 'search_and_filtering_filters_listify_results', $atts ); ?>
	</div>
	<?php do_action( 'search_and_filtering_filters_listify_bottom', $atts ); ?>
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>
