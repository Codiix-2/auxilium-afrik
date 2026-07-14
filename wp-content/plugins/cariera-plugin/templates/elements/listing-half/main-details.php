<?php
/**
 * Elementor Element: Main Listing Half Details
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing-half/main-details.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.8.3
 * @version     1.9.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$job_msg      = sprintf( _n( 'We found %s Job Listing for you!', 'We found %s Job Listings for you!', $count_jobs->publish, 'cariera-core' ), '<span class="listing-count">' . $count_jobs->publish . '</span>' );
$resume_msg   = sprintf( _n( 'We found %s Resume for you!', 'We found %s Resumes for you!', $count_resumes->publish, 'cariera-core' ), '<span class="listing-count">' . $count_resumes->publish . '</span>' );
$company_msg  = sprintf( _n( 'We found %s Company for you!', 'We found %s Companies for you!', $count_companies->publish, 'cariera-core' ), '<span class="listing-count">' . $count_companies->publish . '</span>' );
$job_attr     = [ $per_page, $orderby, $order, $featured ];
$resume_attr  = [ $per_page, $orderby, $order, $featured ];
$company_attr = [ $per_page, $orderby, $order, $featured, $active_jobs ];
?>

<div class="listing-split-view">
	<div class="listing-search hidden">
		<div class="close-search"><i class="las la-times"></i></div>
		<?php do_action( 'cariera_listing_split_view_search', $settings['listing_type'] ); ?>
	</div>

	<div class="listings-container">
		<div class="info">
			<h2 class="title">
				<?php
				switch ( $settings['listing_type'] ) {
					case 'job_listing':
						echo wp_kses_post( $job_msg );
						break;

					case 'resume':
						echo wp_kses_post( $resume_msg );
						break;

					case 'company':
						echo wp_kses_post( $company_msg );
						break;
				}
				?>
			</h2>

			<a href="#" class="filters-btn"><i class="las la-sliders-h"></i><i class="las la-times"></i></a>
		</div>

		<div class="listings">
			<?php
			switch ( $settings['listing_type'] ) {
				case 'job_listing':
					echo do_shortcode( '[jobs show_filters="false" jobs_layout="grid" jobs_grid_version="4" ' . join( ' ', $job_attr ) . ']' );
					break;

				case 'resume':
					echo do_shortcode( '[resumes show_filters="false" resumes_layout="grid" resumes_grid_version="3" ' . join( ' ', $resume_attr ) . ']' );
					break;

				case 'company':
					echo do_shortcode( '[companies show_filters="false" companies_layout="grid" companies_grid_version="3" ' . join( ' ', $company_attr ) . ']' );
					break;
			}
			?>
		</div>
	</div>

	<div class="listing-details-container">
		<div class="loader"><div></div></div>
		<div class="listing"></div>
	</div>
</div>
