<?php
/**
 * Single Resume Layout Version 3
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/single-resume/single-resume-v3.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     0.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$resume_classes = [ 'single-resume-page', 'single-resume-v3' ];
$featured       = get_post_meta( $post->ID, '_featured', true );
$image          = get_post_meta( $post->ID, '_featured_image', true );

if ( 1 === absint( $featured ) ) {
	$resume_classes[] = 'featured-listing';
}
?>

<main id="post-<?php the_ID(); ?>" class="<?php echo esc_attr( join( ' ', $resume_classes ) ); ?>">
	<?php do_action( 'cariera_single_resume_before' ); ?>

	<section class="page-header resume-header" <?php echo ! empty( $image ) ? 'style="background-image: url(' . esc_attr( $image ) . ');"' : ''; ?>>
		<?php get_job_manager_template_part( 'resumes/single-resume/single', 'candidate-details', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' ); ?>
	</section>

	<section class="single-resume-content">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-9 col-sm-12">
					<div class="single-resume">

						<div class="resume-info">
							<div class="title">
								<?php
								if ( get_option( 'resume_manager_enable_categories' ) ) {
									$categories = wp_get_object_terms( $post->ID, \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY );

									if ( is_wp_error( $categories ) ) {
										return '';
									}

									echo '<ul class="candidate-categories">';
									foreach ( $categories as $category ) {
										echo '<li><a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
									}
									echo '</ul>';
								}
								?>
								<h2 class="resume-title"><?php the_candidate_title(); ?></h2>
							</div>

							<div class="listing-actions">
								<?php do_action( 'cariera_bookmark_hook' ); ?>
								<?php do_action( 'cariera_resume_actions' ); ?>
							</div>
						</div>

						<?php
						do_action( 'single_resume_start' );
						do_action( 'single_resume_content' );
						do_action( 'single_resume_end' );
						?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php do_action( 'cariera_single_resume_after' ); ?>
</main>
