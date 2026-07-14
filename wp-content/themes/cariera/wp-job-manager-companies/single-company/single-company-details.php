<?php
/**
 * Custom: Single Company - Company Details
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/single-company-details.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="company-details">
	<div class="company">
		<div class="company-photo">
			<?php cariera_the_company_logo(); ?>
		</div>

		<h1 class="title">
			<?php echo apply_filters( 'cariera_company_name', get_the_title() ); ?>
			<?php do_action( 'cariera_single_company_title' ); ?>
		</h1>
	</div>

	<div class="details">
		<?php if ( ! empty( cariera_get_the_company_location() ) ) { ?>
			<div class="company-detail location">
				<i class="las la-map-marker"></i><?php cariera_the_company_location_output(); ?>
			</div>
		<?php } ?>

		<?php if ( ! empty( cariera_get_the_company_website_link() ) ) { ?>
			<div class="company-detail website">
				<i class="las la-link"></i><a href="<?php echo esc_url( cariera_get_the_company_website_link() ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'The company\'s website', 'cariera' ); ?>"><?php esc_html_e( 'Website', 'cariera' ); ?></a>
			</div>
		<?php } ?>

		<?php if ( ! empty( cariera_get_the_company_phone() ) ) { ?>
			<div class="company-detail phone"><i class="las la-phone"></i><a href="tel:<?php echo esc_attr( cariera_get_the_company_phone() ); ?>"><?php echo cariera_get_the_company_phone(); ?></a>
			</div>
		<?php } ?>

		<?php if ( ! empty( cariera_get_the_company_email() ) ) { ?>
			<div class="company-detail email">
				<i class="las la-envelope"></i><a href="mailto:<?php echo esc_attr( cariera_get_the_company_email() ); ?>"><?php echo cariera_get_the_company_email(); ?></a>
			</div>
		<?php } ?>
	</div>

	<div class="actions">
		<div class="company-social">
			<?php
			cariera_company_fb_output();
			cariera_company_twitter_output();
			cariera_company_linkedin_output();
			cariera_company_instagram_output();

			do_action( 'cariera_company_bookmarks' );
			?>
		</div>

		<?php
		if ( get_option( 'cariera_private_messages' ) && get_option( 'cariera_private_messages_companies' ) ) {
			get_job_manager_template_part( 'single-company/private', 'message', 'wp-job-manager-companies' );
		} else {
			get_job_manager_template_part( 'company', 'contact', 'wp-job-manager-companies' );
		}
		?>
	</div>
</div>
