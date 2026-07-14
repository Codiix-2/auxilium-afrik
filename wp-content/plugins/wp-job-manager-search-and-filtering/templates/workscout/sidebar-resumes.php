<?php
/**
 * WorkScout Resume Sidebar Template
 *
 * @version 1.0.0
 */
?>
<div class="five columns sidebar" role="complementary">
  <form class="resume_filters in_sidebar">
    <div class="job_filters_links"></div>
	  <?php do_action( 'search_and_filtering_filters_workscout_resume_sidebar' ); ?>
  </form>
	<?php dynamic_sidebar( 'sidebar-resumes' ); ?>
</div><!-- #secondary -->
