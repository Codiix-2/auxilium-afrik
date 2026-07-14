<?php
/**
 * Custom: Single Company - Related Companies
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/related-companies.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.7
 * @version     1.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-company-listings' );

global $post, $company_preview;

if ( $company_preview ) {
	return;
}

$category = get_the_terms( $post->ID, 'company_category' );

if ( ! $category || is_wp_error( $category ) || ! is_array( $category ) ) {
	return;
}

$category = wp_list_pluck( $category, 'term_id' );

$related_args = [
	'post_type'      => 'company',
	'orderby'        => 'rand',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
	'post__not_in'   => [ $post->ID ],
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	'tax_query'      => [
		[
			'taxonomy' => 'company_category',
			'field'    => 'id',
			'terms'    => $category,
		],
	],
];

$related_companies = new WP_Query( apply_filters( 'cariera_related_company_args', $related_args ) );

if ( ! $related_companies->have_posts() ) {
	return;
}
?>

<section class="related-companies">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2 class="title"><?php esc_html_e( 'Related Companies', 'cariera' ); ?></h2>

				<!-- Start of Slider -->
				<ul class="company_listings related-companies-slider" data-autoplay="0" data-autoplay-speed="2500">                    
					<?php
					while ( $related_companies->have_posts() ) :
						$related_companies->the_post();
						get_job_manager_template_part( 'company-templates/content', 'company_grid3', 'wp-job-manager-companies' );
					endwhile;
					?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php
wp_reset_postdata();
