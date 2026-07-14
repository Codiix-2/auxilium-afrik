<?php
/**
 * Custom: Listing submission flow
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/listing-submission-flow.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.8
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Temporary variables.
$is_packages_enabled = false;

// Get page IDs.
$current_page_id        = get_queried_object_id();
$resume_submission_page = apply_filters( 'cariera_dashboard_resume_submit_page', get_option( 'resume_manager_submit_resume_form_page_id', false ) );
$action                 = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore

if ( empty( $resume_submission_page ) && absint( $resume_submission_page ) !== $current_page_id ) {
	return;
}

// Check if resume packages are available.
if ( function_exists( 'wc_get_products' ) ) {
	// WC Paid Listings resume packages.
	$resume_packages = wc_get_products(
		[
			'type'   => 'resume_package',
			'status' => 'publish',
		]
	);

	$resume_subscriptions = wc_get_products(
		[
			'type'   => 'resume_package_subscription',
			'status' => 'publish',
		]
	);

	// Cariera resume packages.
	$cariera_resume_packages = wc_get_products(
		[
			'type'   => 'cariera_package',
			'status' => 'publish',
		]
	);

	$cariera_resume_subscriptions = wc_get_products(
		[
			'type'   => 'cariera_package_subscription',
			'status' => 'publish',
		]
	);

	$is_wcpl_active    = class_exists( 'WC_Paid_Listings' ) && ( ! empty( $resume_packages ) || ! empty( $resume_subscriptions ) );
	$is_cariera_active = class_exists( 'Cariera_Packages' ) && ( ! empty( $cariera_resume_packages ) || ! empty( $cariera_resume_subscriptions ) );

	// Enable packages if either system is active.
	$is_packages_enabled = $is_wcpl_active || $is_cariera_active;
}
?>

<div class="submission-flow resume-submission-flow">
	<ul>
		<?php if ( 'edit' !== $action && get_option( 'resume_manager_paid_listings_flow' ) === 'before' && $is_packages_enabled ) { ?>
			<li class="choose-package"><?php esc_html_e( 'Choose Package', 'cariera' ); ?></li>
		<?php } ?>

		<li class="listing-details"><?php esc_html_e( 'Resume Details', 'cariera' ); ?></li>

		<?php if ( 'edit' !== $action ) { ?>
			<li class="preview-listing"><?php esc_html_e( 'Preview Resume', 'cariera' ); ?></li>
			<?php if ( get_option( 'resume_manager_paid_listings_flow' ) !== 'before' && $is_packages_enabled ) { ?>
				<li class="choose-package"><?php esc_html_e( 'Choose Package', 'cariera' ); ?></li>
			<?php } ?>
			<?php if ( $is_packages_enabled ) { ?>
				<li class="checkout"><?php esc_html_e( 'Checkout', 'cariera' ); ?></li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>
