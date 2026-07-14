<?php
/**
 * Elementor Element: Resume Slider
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/resume-slider.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="resume-carousel resume-carousel-<?php echo esc_attr( $settings['version'] ) . ' ' . esc_attr( $settings['custom_class'] ); ?>" data-columns="<?php echo esc_attr( $settings['columns'] ); ?>" data-autoplay="<?php echo esc_attr( $autoplay ); ?>">
	<?php
	while ( $resumes->have_posts() ) {
		$resumes->the_post();
		$slider_classes = [ 'single-resume-slider-item' ];
		$id 			= get_the_id();
		$featured 		= get_post_meta( $id, '_featured', true );

		// Add featured class.
		if ( 1 === absint( $featured ) ) {
			$slider_classes[] = 'featured-listing';
		}
		?>
		<div class="<?php echo esc_attr( join( ' ', $slider_classes ) ); ?>">
			<a href="<?php the_resume_permalink(); ?>" class="resume-link">

				<!-- Candidate Photo -->
				<div class="candidate-photo-wrapper">
					<div class="candidate-photo">
					<?php cariera_the_candidate_photo(); ?>
					</div>
				</div>

				<?php if ( '1' === $settings['version'] ) { ?>
					<div class="candidate-title">
						<h5 class="title"><?php the_title(); ?></h5>
					</div>

					<div class="candidate-info">
						<span class="occupation">
							<i class="las la-lightbulb"></i>
							<?php
							if ( empty( get_the_candidate_title() ) ) {
								esc_html_e( 'No Occupation', 'cariera' );
							} else {
								the_candidate_title();
							}
							?>
						</span> 

						<span class="location">
							<i class="las la-map-marker"></i>
							<?php
							if ( empty( get_the_candidate_location() ) ) {
								esc_html_e( 'No location', 'cariera' );
							} else {
								the_candidate_location( false );
							}
							?>
						</span>
					</div>
				<?php } ?>
			</a>
		</div>
	<?php } ?>
</div>
