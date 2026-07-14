<?php
/**
 * Custom: Archive Template - Job Listing
 *
 * This template can be overridden by copying it to yourtheme/job_manager/archive-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.7
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$count = wp_count_posts( 'job_listing' )->publish;
?>

<section class="page-header">
	<h1 class="title">
		<?php
		// translators: %s is the count of listings.
		echo apply_filters( 'cariera_job_listing_archive_title', wp_kses_post( sprintf( _n( 'We found %s Job Listing in our database', 'We found %s Job Listings in our database', $count, 'cariera' ), '<span class="listing-count">' . $count . '</span>' ) ) );
		?>
	</h1>
</section>

<?php
if ( cariera_get_option( 'cariera_job_search_map' ) ) {
	echo do_shortcode( '[cariera-map type="job_listing" class="jobs_page"]' );
}
?>

<main class="cariera-section-padding">
	<div class="container">
		<div class="col-md-12">
			<?php echo do_shortcode( '[jobs]' ); ?>
		</div>
	</div>
</main>
