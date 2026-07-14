<?php
/**
 * Custom: Job Listing - Quickview
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-template/listing-quickview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.7
 * @version     1.7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div id="job-id-<?php echo esc_attr( get_the_ID() ); ?>" class="<?php echo esc_attr( $classes ); ?>">
	<div class="row">
		<!-- Job summary -->
		<div class="col-md-7 col-sm-12 col-xs-12 job-summary">
			<div class="single-job-listing cariera-scroll">
				<?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) { ?>
					<div class="job-manager-info"><?php esc_html_e( 'This listing has expired.', 'cariera' ); ?></div>
				<?php } else { ?>
					<?php get_job_manager_template_part( 'content-single-job_listing-company' ); ?>

					<div class="job-description">
						<?php wpjm_the_job_description(); ?>
					</div>

					<?php
					if ( candidates_can_apply() ) {
						get_job_manager_template( 'job-application.php' );
					}
				}
				?>
			</div>
		</div>

		<!-- Job map -->
		<div class="col-md-5 col-sm-12 col-xs-12 job-map-wrapper">
			<div id="job-map" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>"></div>
		</div>
	</div>
</div>