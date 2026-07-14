<?php
/**
 * Custom: Listing submission flow
 *
 * This template can be overridden by copying it to yourtheme/job_manager/listing-submission-flow.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.8
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Temporary variables.
$is_packages_enabled = false;

// Get page IDs.
$current_page_id     = get_queried_object_id();
$job_submission_page = apply_filters( 'cariera_dashboard_job_submit_page', get_option( 'job_manager_submit_job_form_page_id', false ) );
$action              = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore

if ( empty( $job_submission_page ) && absint( $job_submission_page ) !== $current_page_id ) {
	return;
}

// Get job packages.
if ( function_exists( 'wc_get_products' ) ) {
	// WC Paid Listings packages.
	$wc_job_packages = wc_get_products(
		[
			'type'   => 'job_package',
			'status' => 'publish',
		]
	);

	$wc_job_subscriptions = wc_get_products(
		[
			'type'   => 'job_package_subscription',
			'status' => 'publish',
		]
	);

	// Cariera Packages.
	$cariera_job_packages = wc_get_products(
		[
			'type'   => 'cariera_package',
			'status' => 'publish',
		]
	);

	$cariera_job_subscriptions = wc_get_products(
		[
			'type'   => 'cariera_package_subscription',
			'status' => 'publish',
		]
	);

	$is_wcpl_active    = class_exists( 'WC_Paid_Listings' ) && ( ! empty( $wc_job_packages ) || ! empty( $wc_job_subscriptions ) );
	$is_cariera_active = class_exists( 'Cariera_Packages' ) && ( ! empty( $cariera_job_packages ) || ! empty( $cariera_job_subscriptions ) );

	// Enable packages if either system is active.
	$is_packages_enabled = $is_wcpl_active || $is_cariera_active;
}
?>

<div class="submission-flow job-submission-flow">
	<ul>
		<?php if ( 'edit' !== $action && get_option( 'job_manager_paid_listings_flow' ) === 'before' && $is_packages_enabled ) { ?>
			<li class="choose-package"><?php esc_html_e( 'Choose Package', 'cariera' ); ?></li>
		<?php } ?>

		<li class="listing-details"><?php esc_html_e( 'Listing Details', 'cariera' ); ?></li>

		<?php if ( 'edit' !== $action ) { ?>
			<li class="preview-listing"><?php esc_html_e( 'Preview Listing', 'cariera' ); ?></li>
			<?php if ( get_option( 'job_manager_paid_listings_flow' ) !== 'before' && $is_packages_enabled ) { ?>
				<li class="choose-package"><?php esc_html_e( 'Choose Package', 'cariera' ); ?></li>
			<?php } ?>
			<?php if ( $is_packages_enabled ) { ?>
				<li class="checkout"><?php esc_html_e( 'Checkout', 'cariera' ); ?></li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>
