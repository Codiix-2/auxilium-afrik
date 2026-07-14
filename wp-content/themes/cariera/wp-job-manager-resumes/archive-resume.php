<?php
/**
 * Custom: Archive Template - Resume
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/archive-resume.php.
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

$count = wp_count_posts( 'resume' )->publish;
?>

<section class="page-header">
	<h1 class="title">
		<?php
		// translators: %s is the count of listings.
		echo apply_filters( 'cariera_resumes_archive_title', wp_kses_post( sprintf( _n( 'We found %s Resume in our database', 'We found %s Resumes in our database', $count, 'cariera' ), '<span class="listing-count">' . $count . '</span>' ) ) );
		?>
	</h1>
</section>

<?php
if ( cariera_get_option( 'cariera_resume_search_map' ) ) {
	echo do_shortcode( '[cariera-map type="resume" class="resume_page"]' );
}
?>

<main class="cariera-section-padding">
	<div class="container">
		<div class="col-md-12">
			<?php echo do_shortcode( '[resumes]' ); ?>
		</div>
	</div>
</main>
