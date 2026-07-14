<?php
/**
 * Custom: Company Listing - List Version 2
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-templates/content-company_list2.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.5
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$company_id    = get_the_ID();
$company_class = 'company-list single_company_2';
$logo          = get_the_company_logo();
$featured      = absint( get_post_meta( $company_id, '_featured', true ) ) === 1 ? 'featured' : '';
$logo_img      = ! empty( $logo ) ? $logo : apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
?>

<li <?php cariera_company_class( esc_attr( $company_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( $company_id ); ?>" data-featured="<?php echo esc_attr( $featured ); ?>">
	<div class="company-content-wrapper">

		<!-- Company Content Body -->
		<div class="company-content-body">
			<div class="company-logo">
				<?php cariera_the_company_logo(); ?>
			</div>

			<!-- Company Info -->
			<div class="company-info">
				<div class="company-title">
					<a href="<?php cariera_the_company_permalink(); ?>">
						<h2 class="title">	
							<?php the_title(); ?>
							<?php do_action( 'cariera_company_title_after' ); ?>
						</h2>
					</a>
				</div>
			</div>
		</div>

		<!-- Company Content Footer -->
		<div class="company-content-footer">
			<div class="company-details">
				<?php
				// Action to add support for WPJM Field Editor.
				ob_start();
					do_action( 'cariera_company_listing_meta_start' );
					$company_listing_meta_start = ob_get_contents();
				ob_end_clean();

				if ( ! empty( $company_listing_meta_start ) ) {
					do_action( 'cariera_company_listing_meta_start' );
				}
				?>

				<div class="location">
					<h3 class="title"><?php esc_html_e( 'Location', 'cariera' ); ?></h3>
					<span>
						<?php
						if ( cariera_get_the_company_location() ) {
							echo cariera_get_the_company_location( false );
						} else {
							esc_html_e( 'No location', 'cariera' );
						}
						?>
					</span>
				</div>

				<div class="published">
					<h3 class="title"><?php esc_html_e( 'Published', 'cariera' ); ?></h3>
					<span>
						<?php $display_date = sprintf( esc_html__( 'Posted %s ago', 'cariera' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
						<time datetime="<?php echo esc_attr( get_post_time( 'Y-m-d' ) ); ?>"><?php echo wp_kses_post( $display_date ); ?></time>
					</span>
				</div>

				<?php
				// Action to add support for WPJM Field Editor.
				ob_start();
					do_action( 'cariera_company_listing_meta_end' );
					$company_listing_meta_end = ob_get_contents();
				ob_end_clean();

				if ( ! empty( $company_listing_meta_end ) ) {
					do_action( 'cariera_company_listing_meta_end' );
				}
				?>

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
		</div>
	</div>
</li>
