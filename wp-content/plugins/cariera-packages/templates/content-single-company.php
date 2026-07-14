<?php
/**
 * Single company template
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/content-single-company.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.5
 * @version     0.9.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

do_action( 'cariera_packages_template_content_single_company_before' );
?>

<div class="single_company cariera-section-padding">
	<div class="container">
		<meta itemprop="title" content="<?php echo esc_attr( $post->post_title ); ?>" />
		<div class="cariera-packages-single-company-require-package">
			<?php do_action( 'cariera_packages_single_company' ); ?>
		</div>
	</div>
</div>

<?php do_action( 'cariera_packages_template_content_single_company_after' ); ?>
