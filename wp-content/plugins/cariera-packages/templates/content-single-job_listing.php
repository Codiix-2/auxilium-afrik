<?php
/**
 * Single job listing template
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/content-single-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.0
 * @version     0.9.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

do_action( 'cariera_packages_template_content_single_job_listing_before' );
?>

<div class="single_job_listing cariera-section-padding" itemscope itemtype="http://schema.org/JobPosting">
	<div class="container">
		<meta itemprop="title" content="<?php echo esc_attr( $post->post_title ); ?>" />
		<div class="cariera-packages-single-job-require-package">
			<?php do_action( 'cariera_packages_single_job_listing' ); ?>
		</div>
	</div>
</div>

<?php do_action( 'cariera_packages_template_content_single_job_listing_after' ); ?>
