<?php
/**
 * WC Shopping Cart Popup
 *
 * This template can be overridden by copying it to cariera-child/templates/popups/shopping-cart.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.9.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="shopping-cart-modal" class="small-dialog zoom-anim-dialog mfp-hide">
	<div class="small-dialog-headline">
		<h3 class="title"><?php esc_html_e( 'Cart', 'cariera' ); ?></h3>
	</div>

	<div class="small-dialog-content">
		<?php if ( WC()->cart->is_empty() ) { ?>
			<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'Your cart is currently empty.', 'cariera' ); ?></p>
		<?php } else { ?>
			<?php the_widget( 'WC_Widget_Cart' ); ?>
		<?php } ?>
	</div>
</div>
