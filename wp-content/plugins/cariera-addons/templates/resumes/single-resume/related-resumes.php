<?php
/**
 * Single Resume - Related Resumes
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/single-resume/related-resumes.php.
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

wp_enqueue_style( 'cariera-resume-listings' );

global $post, $resume_preview;

if ( $resume_preview ) {
	return;
}

$plural_label = cariera_addons_resume_cpt_plural_label();
$category     = get_the_terms( $post->ID, \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY );

if ( ! $category || is_wp_error( $category ) || ! is_array( $category ) ) {
	return;
}

$category = wp_list_pluck( $category, 'term_id' );

$related_args = [
	'post_type'      => \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME,
	'orderby'        => 'rand',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
	'post__not_in'   => [ $post->ID ],
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	'tax_query'      => [
		[
			'taxonomy' => \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY,
			'field'    => 'id',
			'terms'    => $category,
		],
	],
];

$related_resumes = new WP_Query( apply_filters( 'cariera_related_resume_args', $related_args ) );

if ( ! $related_resumes->have_posts() ) {
	return;
}
?>

<section class="related-resumes">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2 class="title"><?php printf( esc_html__( 'Related %s', 'cariera-addons' ), $plural_label ); ?></h2>

				<!-- Start of Slider -->
				<ul class="resumes related-resumes-slider" data-autoplay="0" data-autoplay-speed="2500">                    
					<?php
					while ( $related_resumes->have_posts() ) :
						$related_resumes->the_post();
						get_job_manager_template_part( 'resumes/listing-templates/content', 'resume_grid3', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
					endwhile;
					?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php
wp_reset_postdata();
