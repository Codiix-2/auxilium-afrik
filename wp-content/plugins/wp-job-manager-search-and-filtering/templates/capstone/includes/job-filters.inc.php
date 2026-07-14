<?php do_action('capstone_jobs_filters_start'); ?>
<?php 

  // Helper Variable(s)
  $jobs_filters_order = get_theme_mod( 'capstone_jobs_filters_order', array( 'job_types', 'job_tags', 'job_alert' ) );
  $jobs_filters_breakpoint = get_theme_mod( 'capstone_jobs_filters_breakpoint', 3 );

  // Check if filter is active-passive
  $is_job_types_passive = array_search('job_types', $jobs_filters_order) >= $jobs_filters_breakpoint ? 'true' : '';
  $is_job_tags_passive = array_search('job_tags', $jobs_filters_order) >= $jobs_filters_breakpoint ? 'true' : '';
  $is_job_salary_range_passive = array_search('job_salary_range', $jobs_filters_order) >= $jobs_filters_breakpoint ? 'true' : '';

?>
<div id="jobs-filters-module" class="job_filters_dummy" data-filters-breakpoint="<?php echo esc_attr($jobs_filters_breakpoint); ?>">

  <?php if ( taxonomy_exists( 'job_listing_type' ) && !is_tax( 'job_listing_type' ) ) : ?>
    <div class="job_filter jobs_types_filter"  data-is-passive="<?php echo esc_attr($is_job_types_passive); ?>">
      <h4 class="title"><?php esc_html_e('Job Type', 'capstone', 'wp-job-manager-search-and-filtering'); ?></h4>
      <ul class="job_types">
        <?php foreach ( get_job_listing_types() as $type ) : ?>
          <li><label for="job_type_<?php echo esc_attr($type->slug); ?>_dummy" class="<?php echo sanitize_title( $type->name ); ?>"><input type="checkbox" name="filter_job_type[]" value="<?php echo esc_attr($type->slug); ?>" checked="checked" id="job_type_<?php echo esc_attr($type->slug); ?>_dummy" /><span></span> <?php echo esc_html($type->name); ?></label></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ( taxonomy_exists( 'job_listing_tag' ) && !is_tax( 'job_listing_tag' ) ) : ?>
    <div class="job_filter job_tags_filter" data-is-passive="<?php echo esc_attr($is_job_tags_passive); ?>">
      <h4 class="title"><?php echo get_theme_mod('capstone_jobs_tags_title', esc_html__('Perks &amp; Privilges', 'capstone', 'wp-job-manager-search-and-filtering')); ?></h4>
      <div class="job_tags">
        <?php if ( shortcode_exists( 'job_tag_cloud' ) ) { ?> 
				  <?php echo do_shortcode('[job_tag_cloud]'); ?>
			  <?php } ?>
      </div>
    </div>
  <?php endif; ?>

  <?php get_template_part( 'includes/filters-toggle.inc' ); ?>

</div>
<?php do_action('capstone_jobs_filters_end'); ?>