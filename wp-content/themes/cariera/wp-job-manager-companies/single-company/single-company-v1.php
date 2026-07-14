<?php
/**
 * Custom: Single Company - Layout Version 1
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/single-company-v1.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$company_classes = [ 'single-company-page', 'single-company-v1' ];
$featured        = get_post_meta( $post->ID, '_featured', true );
$image           = get_post_meta( $post->ID, '_company_header_image', true );

if ( 1 === absint( $featured ) ) {
	$company_classes[] = 'featured-listing';
}
?>

<main id="post-<?php the_ID(); ?>" class="<?php echo esc_attr( join( ' ', $company_classes ) ); ?>">
	<?php do_action( 'cariera_single_company_before' ); ?>
	<section class="page-header company-header" <?php echo ! empty( $image ) ? 'style="background: url(' . esc_attr( $image ) . ')"' : ''; ?>></section>

	<section class="single-company-content">
		<div class="container">
			<?php get_job_manager_template_part( 'single-company/header', 'info', 'wp-job-manager-companies' ); ?>

			<div class="company-main row">
				<div class="col-md-8 col-xs-12">
					<div class="company-content-wrapper">
						<?php do_action( 'cariera_single_company_listing_start' ); ?>
						<?php do_action( 'cariera_single_company_listing' ); ?>
						<?php do_action( 'cariera_single_company_listing_end' ); ?>
					</div>
				</div>

				<div class="col-md-4 col-xs-12 company-sidebar">
					<div class="sidebar-info">
						<?php do_action( 'cariera_single_company_sidebar' ); ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php do_action( 'cariera_single_company_after' ); ?>
</main>
