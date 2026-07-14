<?php
/**
 * Custom: Archive Template - Company
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/archive-company.php.
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

$count = wp_count_posts( 'company' )->publish;
?>

<section class="page-header">
	<h1 class="title">
		<?php
		// translators: %s is the count of listings.
		echo apply_filters( 'cariera_company_archive_title', wp_kses_post( sprintf( _n( 'We found %s Company in our database', 'We found %s Companies in our database', $count, 'cariera' ), '<span class="listing-count">' . $count . '</span>' ) ) );
		?>
	</h1>
</section>

<?php
if ( cariera_get_option( 'cariera_company_search_map' ) ) {
	echo do_shortcode( '[cariera-map type="company" class="companies_page"]' );
}
?>

<main class="cariera-section-padding">
	<div class="container">
		<div class="col-md-12">
			<?php
			do_action( 'cariera_before_company_loop' );

			echo do_shortcode( '[companies]' );

			do_action( 'cariera_after_company_loop' );
			?>
		</div>
	</div>
</main>
