<?php
$frontpage = is_front_page();
$action_type = is_front_page() ? 'homepage_job' : 'job';
?>

<?php do_action('capstone_jobs_search_start'); ?>
<form id="jobs-search-module" class="job_search_dummy job-search-fields" method="GET" action="<?php echo esc_url(get_post_type_archive_link('job_listing')); ?>">
	<div class="sortable" style="margin-bottom: 0px;">
		<?php do_action( "search_and_filtering_capstone_form_top_{$action_type}" ); ?>
	</div>

  <!-- Form Caption -->
  <?php if ( get_option('job_manager_jobs_page_id') ) { ?>
    <p class="form-caption caption"><?php echo esc_html__('or go to', 'capstone', 'wp-job-manager-search-and-filtering'); ?> <a href="<?php echo esc_url(get_permalink(get_option('job_manager_jobs_page_id'))); ?>"><?php echo esc_html__('advanced search', 'capstone', 'wp-job-manager-search-and-filtering'); ?></a></p>
  <?php } ?>

	<?php do_action( "search_and_filtering_capstone_form_bottom_{$action_type}" ); ?>

</form>
<?php do_action('capstone_jobs_search_end'); ?>
