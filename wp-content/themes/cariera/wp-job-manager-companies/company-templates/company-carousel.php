<?php
/**
 * Company Listing - Carousel Content
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-templates/company-carousel.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.3
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$company_id 	= get_the_ID();
$slider_classes = [ 'single-company-slider-item' ];
$featured    	= get_post_meta( $post->ID, '_featured', true );

// Add featured class.
if ( 1 === absint( $featured ) ) {
	$slider_classes[] = 'featured-listing';
}
?>

<div class="<?php echo esc_attr( join( ' ', $slider_classes ) ); ?>">
	<a href="<?php cariera_the_company_permalink(); ?>" class="company-link" aria-label="<?php the_title(); ?>">
		<!-- Company Logo -->
		<?php if ( '1' === $settings['version'] ) { ?>
			<div class="company-logo-wrapper">
			<?php
		} else {
			$image = get_post_meta( $post->ID, '_company_header_image', true );
			?>
			<div class="company-logo-wrapper" style="background-image: url(<?php echo esc_attr( $image ); ?>);">
		<?php } ?>
			<div class="company-logo">
				<?php cariera_the_company_logo(); ?>
			</div>
		</div>

		<!-- Company Details -->
		<div class="company-details">
			<div class="company-title">
				<h3 class="title"><?php the_title(); ?></h3>
			</div>

			<?php if ( ! empty( cariera_get_the_company_location() ) ) { ?>
				<div class="company-location">
					<span><i class="las la-map-marker"></i><?php echo cariera_get_the_company_location(); ?></span>
				</div>
			<?php } ?>

			<div class="company-jobs">
				<span>
					<?php
					$job_count = cariera_get_the_company_job_listing_active_count( $company_id );
					$job_label = sprintf( _n( '%s Job', '%s Jobs', $job_count, 'cariera' ), $job_count );

					echo apply_filters( 'cariera_company_open_positions_info', esc_html( $job_label ), $company_id, $job_count );
					?>
				</span>
			</div>
		</div>
	</a>
</div>
