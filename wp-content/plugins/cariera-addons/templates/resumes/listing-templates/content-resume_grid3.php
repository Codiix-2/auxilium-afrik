<?php
/**
 * Resume Listing - Grid Version 3
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/listing-templates/content-resume_grid3.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$resume_class = 'resume-grid single_resume_3';
$category     = get_the_resume_category();
$featured     = absint( get_post_meta( get_the_ID(), '_featured', true ) ) === 1 ? 'featured' : '';
$logo         = get_the_candidate_photo();

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
} ?>

<li <?php cariera_resume_class( esc_attr( $resume_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>" data-featured="<?php echo esc_attr( $featured ); ?>">
	<a href="<?php the_resume_permalink(); ?>">
		<div class="resume-info-wrapper">
			<div class="candidate-photo">
				<?php cariera_the_candidate_photo(); ?>
			</div>

			<!-- Resume Details -->
			<div class="resume-details">
				<h2 class="candidate-title">
					<?php the_title(); ?>
					<?php do_action( 'cariera_resume_title_after' ); ?>
				</h2>

				<ul>
					<li class="location">
						<i class="las la-map-marker"></i>
						<?php
						if ( get_the_candidate_location() ) {
							the_candidate_location( false );
						} else {
							esc_html_e( 'No location', 'cariera-addons' );
						}
						?>
					</li>

					<?php if ( get_post_meta( $post->ID, '_salary_min', true ) ) { ?>
						<li class="salary"><i class="las la-money-bill"></i><?php cariera_job_salary(); ?></li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<!-- Resume Extras -->
		<div class="resume-extras">
			<div class="resume-title-icon"></div>
			<span class="professional-title">
				<?php
				if ( get_the_candidate_title() ) {
					the_candidate_title();
				} else {
					esc_html_e( 'No occupation', 'cariera-addons' );
				}
				?>
			</span>
		</div>
	</a>
</li>
