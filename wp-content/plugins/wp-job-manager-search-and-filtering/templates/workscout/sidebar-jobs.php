<?php
/**
 * WorkScout Jobs Sidebar Template
 * @version 1.0.0
 */
?>
<div class="five columns sidebar" role="complementary">
	<form class="job_filters in_sidebar">
		<div class="job_filters_links"></div>
		<?php if ( get_query_var( 'company' ) ): ?>
			<input type="hidden" name="company_field" value="<?php echo urldecode( get_query_var( 'company' ) ) ?>">
		<?php endif; ?>
		<?php do_action( 'search_and_filtering_filters_workscout_job_sidebar' ); ?>
	</form>
	<?php dynamic_sidebar( 'sidebar-jobs' ); ?>
</div><!-- #secondary -->
