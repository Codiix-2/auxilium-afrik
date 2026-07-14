<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$atts = isset( $atts ) ? $atts : array();
global $is_flat;
?>

<?php
do_action( 'job_manager_sf_job_filters_before', $atts );
do_action( "search_and_filtering_jobify_widget_search_hero_form_above_job", $atts );
?>

<form class="job_search_form<?php if ( $is_flat ) : ?> job_search_form--flat<?php endif; ?> wpjmsf_filters"
      action="<?php echo jobify_get_listing_page_permalink() ? jobify_get_listing_page_permalink() : get_post_type_archive_link( 'job_listing' ); ?>" method="GET">
	<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_top_job", $atts ); ?>

	<div class="search_jobs search_listings">
		<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_search_job", $atts ); ?>
	</div>

	<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_bottom_job", $atts ); ?>
	<div class="showing_jobs wpjmsf_showing_listings"></div>
</form>

<?php do_action( "search_and_filtering_jobify_widget_search_hero_form_below_job", $atts ); ?>

<noscript><?php esc_html_e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-job-manager-search-and-filtering' ); ?></noscript>
