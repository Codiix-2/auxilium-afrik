<?php
/**
 * Custom: Company Listing - Grid Version 2
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-templates/content-company_grid2.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.5
 * @version     1.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$company_id    = get_the_ID();
$company_class = 'company-grid single_company_2';
$logo          = get_the_company_logo();
$featured      = absint( get_post_meta( $company_id, '_featured', true ) ) === 1 ? 'featured' : '';
$logo_img      = ! empty( $logo ) ? $logo : apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
?>

<li <?php cariera_company_class( esc_attr( $company_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( $company_id ); ?>" data-featured="<?php echo esc_attr( $featured ); ?>">
	<div class="company-content-wrapper">
		<a href="<?php cariera_the_company_permalink(); ?>">

			<div class="company-logo-wrapper">          
				<div class="company-logo">
					<?php cariera_the_company_logo(); ?>
				</div>
			</div>

			<!-- Company Details -->
			<div class="company-details">
				<div class="company-title">
					<h2 class="title">
						<?php the_title(); ?>
						<?php do_action( 'cariera_company_title_after' ); ?>
					</h2>
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
</li>
