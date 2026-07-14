<?php
/**
 * Header Extra: Cart template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/extra/cart.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! cariera_get_option( 'header_cart' ) ) {
	return;
}

if ( ! \Cariera\wc_is_activated() ) {
	return;
}

$cart_count = WC()->cart->get_cart_contents_count();
$cart_class = $cart_count < 1 ? 'counter-hidden' : '';
?>

<div class="extra-menu-item extra-shop mini-cart woocommerce">
	<a href="#shopping-cart-modal" class="cart-contents popup-with-zoom-anim" aria-label="<?php esc_attr_e( 'Shopping cart modal trigger', 'cariera' ); ?>">
		<i class="las la-shopping-bag"></i>
		<span class="notification-count cart-count <?php echo esc_html( $cart_class ); ?>"><?php echo esc_html( number_format_i18n( $cart_count ) ); ?></span>
	</a>
</div>
