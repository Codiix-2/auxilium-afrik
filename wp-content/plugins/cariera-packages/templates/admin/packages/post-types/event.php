<?php
/**
 * WooCommerce Cariera Package: Event Post Type Options
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/admin/packages/post-types/event.php.
 *
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.8
 * @version     0.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $post_id ) ) {
	$post_id = get_the_ID(); // phpcs:ignore
}

// Get saved values.
$promotion_duration = get_post_meta( $post_id, '_event_promotion_duration', true );
$view_limit         = get_post_meta( $post_id, '_view_event_limit', true );
?>

<div id="event_promotional_package" class="cariera-packages-options">
	<?php
	woocommerce_wp_text_input(
		[
			'id'                => '_event_promotion_duration',
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

<div id="event_view_package" class="cariera-packages-options">
	<?php
	woocommerce_wp_text_input(
		[
			'id'                => '_view_event_limit',
			'label'             => esc_html__( 'View event limit', 'cariera-packages' ),
			'description'       => esc_html__( 'Number of listings a user can view with this package. Leave blank for unlimited.', 'cariera-packages' ),
			'value'             => esc_html( $view_limit ? $view_limit : '' ),
			'placeholder'       => esc_html__( 'Unlimited', 'cariera-packages' ),
			'type'              => 'number',
			'desc_tip'          => true,
			'custom_attributes' => [ 'step' => '1' ],
		]
	);
	?>
</div>
