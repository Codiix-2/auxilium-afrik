<?php
/**
 * Featured Resumes in single resume page
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/single-resume/featured-listings.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.1
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
	'post_type'      => \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME,
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

$featured_resumes = new WP_Query( apply_filters( 'cariera_addons_single_resume_featured_listings_args', $listing_args ) );

if ( ! $featured_resumes->have_posts() ) {
	return;
}

$plural_label = cariera_addons_resume_cpt_plural_label();
?>

<div class="single-listing-featured">
	<h2 class="content-title"><?php echo esc_html( sprintf( __( 'Featured %s', 'cariera-addons' ), $plural_label ) ); ?></h2>

	<div class="featured-listings">
		<!-- Start of Slider -->
		<ul class="resumes featured-resumes-slider" data-autoplay="1" data-autoplay-speed="2500">                    
			<?php
			while ( $featured_resumes->have_posts() ) :
				$featured_resumes->the_post();
				get_job_manager_template_part( 'resumes/listing-templates/content', 'resume_grid3', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
			endwhile;
			?>
		</ul>
	</div>
</div>

<?php
wp_reset_postdata();
