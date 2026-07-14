<?php
/**
 * Custom: Featured Job Listings in single job listing page
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/featured-listings.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.6
 * @version     1.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! get_option( 'cariera_job_manager_featured_jobs' ) ) {
	return;
}

global $job_preview;

if ( $job_preview ) {
	return;
}

wp_enqueue_style( 'cariera-job-listings' );

$listing_args = [
	'post_type'      => 'job_listing',
	'post_status'    => 'publish',
	'posts_per_page' => '5',
	'orderby'        => 'date',
	'order'          => 'DESC',
	'post__not_in'   => [ $post->ID ],
	'tax_query'      => [], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	'meta_query'     => [
		[
			'key'     => '_featured',
			'value'   => '1',
			'compare' => '=',
		],
	],
];

if ( 1 === absint( get_option( 'job_manager_hide_filled_positions' ) ) ) {
	$listing_args['meta_query'][] = [
		'key'     => '_filled',
		'value'   => '1',
		'compare' => '!=',
	];
}

$featured_jobs = new WP_Query( apply_filters( 'cariera_single_job_featured_listings_args', $listing_args ) );

if ( ! $featured_jobs->have_posts() ) {
	return;
}
?>

<div class="single-listing-featured">
	<h2 class="content-title"><?php esc_html_e( 'Featured Job Listings', 'cariera' ); ?></h2>

	<div class="featured-listings">
		<!-- Start of Slider -->
		<ul class="job_listings featured-jobs-slider" data-autoplay="1" data-autoplay-speed="2500">                    
			<?php
			while ( $featured_jobs->have_posts() ) :
				$featured_jobs->the_post();
				get_job_manager_template_part( 'job-templates/content', 'job_listing_grid4' );
			endwhile;
			?>
		</ul>
	</div>
</div>

<?php
wp_reset_postdata();
