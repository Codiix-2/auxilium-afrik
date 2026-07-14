<?php
/**
 * Custom: Single Job Page - Layout Version 3
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/single-job-listing-v3.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.8.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$job_classes = [ 'single-job-listing-page', 'single-job-v3' ];
$image       = get_post_meta( $post->ID, '_job_cover_image', true );
$job_types   = wpjm_get_the_job_types();
$featured    = get_post_meta( $post->ID, '_featured', true );

if ( 1 === absint( $featured ) ) {
	$job_classes[] = 'featured-listing';
}
?>

<main id="post-<?php the_ID(); ?>" class="<?php echo esc_attr( join( ' ', $job_classes ) ); ?>">
	<?php do_action( 'cariera_single_job_listing_before' ); ?>

	<section class="page-header job-header" <?php echo ! empty( $image ) ? 'style="background-image: url(' . esc_attr( $image ) . ');"' : ''; ?>>
		<?php
		get_job_manager_template( 'content-single-job_listing-company.php', [] );

		if ( get_option( 'cariera_private_messages' ) && get_option( 'cariera_private_messages_job_listings' ) ) {
			get_job_manager_template_part( 'single-job/private', 'message' );
		}
		?>
	</section>

	<section class="single-job-content">
		<div class="container">
			<div class="row justify-content-center">
				<?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) { ?>
					<div class="col-md-12">
						<div class="job-manager-message error"><?php esc_html_e( 'This listing has expired.', 'cariera' ); ?></div>
					</div>
				<?php } else { ?>
					<div class="col-md-9 col-sm-12">
						<div class="single-job-listing">

							<div class="job-info">
								<div class="title">
									<?php
									if ( ! empty( $job_types ) ) {
										foreach ( $job_types as $job_type ) {
											?>
											<span class="job-type term-<?php echo esc_attr( $job_type->term_id ); ?> <?php echo esc_attr( sanitize_title( $job_type->slug ) ); ?>"><?php echo esc_html( $job_type->name ); ?></span>
											<?php
										}
									}
									?>
									<h1 class="job-title"><?php wpjm_the_job_title(); ?></h1>
								</div>

								<div class="listing-actions">
									<?php do_action( 'cariera_bookmark_hook' ); ?>
									<?php do_action( 'cariera_job_listing_actions' ); ?>
								</div>
							</div>

							<?php
								/**
								 * Single_job_listing_start hook
								 *
								 * @hooked job_listing_meta_display - 20
								 * @hooked job_listing_company_display - 30
								 */
								do_action( 'single_job_listing_start' );
							?>

							<div class="job-description">
								<?php wpjm_the_job_description(); ?>
							</div>

							<?php
								/**
								 * Single_job_listing_end hook
								 *
								 * @hooked cariera_single_job_v2_overview - 7
								 * @hooked cariera_single_job_v2_map - 8
								 */
								do_action( 'single_job_listing_end' );
							?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</section>

	<?php do_action( 'cariera_single_job_listing_after' ); ?>
</main>
