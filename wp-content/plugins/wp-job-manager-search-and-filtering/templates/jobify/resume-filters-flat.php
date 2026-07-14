<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$atts = isset( $atts ) ? $atts : array();
global $is_flat;
?>

<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_above_resume", $atts ); ?>

<form class="wpjmsf_filters resume_search_form resume_search_form--flat"
      action="<?php echo resume_manager_get_permalink( 'resumes' ) ? resume_manager_get_permalink( 'resumes' ) : get_post_type_archive_link( 'resume' ); ?>" method="GET">

	<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_top_resume", $atts ); ?>

	<div class="search_resumes">
		<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_search_resume", $atts ); ?>
	</div>

	<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_bottom_resume", $atts ); ?>
	<div class="showing_resumes wpjmsf_showing_listings"></div>
</form>

<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_below_resume", $atts ); ?>

<noscript><?php esc_html_e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-job-manager-search-and-filtering' ); ?></noscript>
