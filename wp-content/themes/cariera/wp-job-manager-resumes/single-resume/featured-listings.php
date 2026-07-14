<?php
/**
 * Custom: Featured Resumes in single resume page
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/featured-listings.php.
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

if ( ! get_option( 'cariera_resume_manager_featured_resumes' ) ) {
	return;
}

global $resume_preview;

if ( $resume_preview ) {
	return;
}

wp_enqueue_style( 'cariera-resume-listings' );

$listing_args = [
	'post_type'      => 'resume',
	'post_status'    => 'publish',
	'posts_per_page' => '5',
	'orderby'        => 'date',
	'order'          => 'DESC',
	'post__not_in'   => [ $post->ID ],
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	'tax_query'      => [],
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	'meta_query'     => [
		[
			'key'     => '_featured',
			'value'   => '1',
			'compare' => '=',
		],
	],
];

$featured_resumes = new WP_Query( apply_filters( 'cariera_single_resume_featured_listings_args', $listing_args ) );

if ( ! $featured_resumes->have_posts() ) {
	return;
}
?>

<div class="single-listing-featured">
	<h2 class="content-title"><?php esc_html_e( 'Featured Resumes', 'cariera' ); ?></h2>

	<div class="featured-listings">
		<!-- Start of Slider -->
		<ul class="resumes featured-resumes-slider" data-autoplay="1" data-autoplay-speed="2500">                    
			<?php
			while ( $featured_resumes->have_posts() ) :
				$featured_resumes->the_post();
				get_job_manager_template_part( 'resume-templates/content', 'resume_grid3', 'wp-job-manager-resumes' );
			endwhile;
			?>
		</ul>
	</div>
</div>

<?php
wp_reset_postdata();
