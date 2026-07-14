<?php
/**
 * Onboarding: Compatible Plugins
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/addons.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.8.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$products = \Cariera\compatible_addons();
?>

<h2 class="title"><?php esc_html_e( 'Fully Compatible Third Party Plugins', 'cariera' ); ?></h2>
<div class="onboarding-notice success">
	<p><?php esc_html_e( 'Please contact the author of the plugins regarding any presale or support related questions.', 'cariera' ); ?></p>
	<p><strong><?php echo esc_html( '-5% Coupon for all sMyles products & licenses:' ); ?></strong><span class="coupon-code"><?php echo esc_html( '4carieratheme' ); ?></span></p>
</div>

<div class="onboarding-products">
	<?php foreach ( $products as $product ) { ?>
		<div class="product-item">
			<a href="<?php echo esc_url( $product['link'] ); ?>" target="_blank">
				<div class="theme-img">
					<img src="<?php echo esc_url( $product['bg-img'] ); ?>" lazy="loading">
				</div>
				<div class="title"><?php echo esc_html( $product['title'] ); ?></div>
			</a>
		</div>
	<?php } ?>
</div>
