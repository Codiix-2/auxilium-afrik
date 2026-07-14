<?php
/**
 * WooCommerce Cariera Package: Job Listing Post Type Options
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/admin/packages/post-types/job-listing.php.
 *
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.8
 * @version     0.9.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $post_id ) ) {
	$post_id = get_the_ID(); // phpcs:ignore
}

// Get saved values.
$listing_limit      = get_post_meta( $post_id, '_job_listing_limit', true );
$listing_duration   = get_post_meta( $post_id, '_job_listing_duration', true );
$listing_featured   = get_post_meta( $post_id, '_job_listing_featured', true );
$promotion_duration = get_post_meta( $post_id, '_job_promotion_duration', true );
$view_limit         = get_post_meta( $post_id, '_view_job_limit', true );
?>

<!-- Job Submission Package Options -->
<div id="job_submission_package" class="cariera-packages-options">
	<?php
	woocommerce_wp_text_input(
		[
			'id'                => '_job_listing_limit',
			'label'             => esc_html__( 'Job Listing Limit', 'cariera-packages' ),
			'description'       => esc_html__( 'Enter the number of job listings included in this package. Leave empty to allow unlimited listings.', 'cariera-packages' ),
			'value'             => $listing_limit ? $listing_limit : '',
			'placeholder'       => esc_html__( 'Unlimited', 'cariera-packages' ),
			'type'              => 'number',
			'desc_tip'          => true,
			'custom_attributes' => [
				'min'  => '',
				'step' => '1',
			],
		]
	);
	woocommerce_wp_text_input(
		[
			'id'                => '_job_listing_duration',
			'label'             => esc_html__( 'Job Listing Duration', 'cariera-packages' ),
			'description'       => esc_html__( 'Specify how many days each job listing will remain active before expiring.', 'cariera-packages' ),
			'value'             => $listing_duration ? $listing_duration : '',
			'placeholder'       => get_option( 'job_manager_submission_duration' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => [
				'min'  => '',
				'step' => '1',
			],
		]
	);
	woocommerce_wp_checkbox(
		[
			'id'          => '_job_listing_featured',
			'label'       => esc_html__( 'Feature Listings?', 'cariera-packages' ),
			'description' => esc_html__( 'Automatically mark all submitted listings with this package as featured.', 'cariera-packages' ),
			'value'       => $listing_featured,
		]
	);
	?>
</div>

<!-- Job Promotional Package Options -->
<div id="job_promotional_package" class="cariera-packages-options">
	<?php
	woocommerce_wp_text_input(
		[
			'id'                => '_job_promotion_duration',
			'label'             => esc_html__( 'Promotion duration', 'cariera-packages' ),
			'description'       => esc_html__( 'The number of days that the listing will be featured.', 'cariera-packages' ),
			'value'             => $promotion_duration ? $promotion_duration : '',
			'placeholder'       => '',
			'type'              => 'number',
			'desc_tip'          => true,
			'custom_attributes' => [
				'min'  => '',
				'step' => '1',
			],
		]
	);
	?>
</div>

<!-- Job View Package Options -->
<div id="job_view_package" class="cariera-packages-options">
	<?php
	woocommerce_wp_text_input(
		[
			'id'                => '_view_job_limit',
			'label'             => esc_html__( 'View job limit', 'cariera-packages' ),
			'description'       => esc_html__( 'Number of listings a user can view with this package. Leave blank for unlimited.', 'cariera-packages' ),
			'value'             => esc_html( $view_limit ? $view_limit : '' ),
			'placeholder'       => esc_html__( 'Unlimited', 'cariera-packages' ),
			'type'              => 'number',
			'desc_tip'          => true,
			'custom_attributes' => [
				'min'  => '',
				'step' => '1',
			],
		]
	);
	?>
</div>
