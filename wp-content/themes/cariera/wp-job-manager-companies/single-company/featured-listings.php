<?php
/**
 * Custom: Featured Companies in single company page
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/featured-listings.php.
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

if ( ! get_option( 'cariera_single_company_featured_companies' ) ) {
	return;
}

global $company_preview;

if ( $company_preview ) {
	return;
}

wp_enqueue_style( 'cariera-company-listings' );

$listing_args = [
	'post_type'      => 'company',
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

$featured_companies = new WP_Query( apply_filters( 'cariera_single_company_featured_listings_args', $listing_args ) );

if ( ! $featured_companies->have_posts() ) {
	return;
}
?>

<div class="single-listing-featured">
	<h2 class="content-title"><?php esc_html_e( 'Featured Companies', 'cariera' ); ?></h2>

	<div class="featured-listings">
		<!-- Start of Slider -->
		<ul class="company_listings featured-companies-slider" data-autoplay="1" data-autoplay-speed="2500">                    
			<?php
			while ( $featured_companies->have_posts() ) :
				$featured_companies->the_post();
				get_job_manager_template_part( 'company-templates/content', 'company_grid3', 'wp-job-manager-companies' );
			endwhile;
			?>
		</ul>
	</div>
</div>

<?php
wp_reset_postdata();
