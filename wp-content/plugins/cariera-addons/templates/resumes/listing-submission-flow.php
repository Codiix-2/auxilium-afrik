<?php
/**
 * Listing submission flow
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/listing-submission-flow.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     Cariera Addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Setup.
$is_packages_enabled = false;
$current_page_id     = get_queried_object_id();
$submit_page_id      = get_option( 'resume_manager_submit_resume_form_page_id', false );
$action              = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore
$is_edit             = ( 'edit' === $action );
$package_flow        = get_option( 'resume_manager_paid_listings_flow' ); // 'before' or 'after'
$singular_label      = cariera_addons_resume_cpt_singular_label();
$plural_label        = cariera_addons_resume_cpt_plural_label();

// Exit if not on the submission page.
if ( absint( $submit_page_id ) !== $current_page_id ) {
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
		<?php
		if ( ! $is_edit && 'before' === $package_flow && $is_packages_enabled ) {
			echo '<li class="choose-package">' . esc_html__( 'Choose Package', 'cariera-addons' ) . '</li>';
		}

		/* translators: %s: Singular label of the listing. */
		echo '<li class="listing-details">' . sprintf( esc_html__( '%s Details', 'cariera-addons' ), esc_html( $singular_label ) ) . '</li>';

		if ( ! $is_edit ) {
			/* translators: %s: Singular label of the listing. */
			echo '<li class="preview-listing">' . sprintf( esc_html__( 'Preview %s', 'cariera-addons' ), esc_html( $singular_label ) ) . '</li>';

			if ( 'before' !== $package_flow && $is_packages_enabled ) {
				echo '<li class="choose-package">' . esc_html__( 'Choose Package', 'cariera-addons' ) . '</li>';
			}

			if ( $is_packages_enabled ) {
				echo '<li class="checkout">' . esc_html__( 'Checkout', 'cariera-addons' ) . '</li>';
			}
		}
		?>
	</ul>
</div>
