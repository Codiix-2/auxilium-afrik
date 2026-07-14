<?php
/**
 * Theme Demo actions: Doc, support, purchase
 *
 * This template can be overridden by copying it to cariera-child/templates/demo/theme-demo.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url      = apply_filters( 'cariera_envato_purchase_url', 'https://1.envato.market/cariera' );
$price    = apply_filters( 'cariera_envato_purchase_price', '79$' );
$products = \Cariera\compatible_addons();
?>

<div class="cariera-demo-actions">
	<ul class="demo-actions">
		<li>
			<a href="https://docs.cariera.cc/" target="_blank">
				<i class="las la-book-open"></i>
				<span class="demo-tooltip"><?php echo esc_html( 'Documentation' ); ?><span></span></span>
			</a>
		</li>
		<li>
			<a href="https://support.gnodesign.com/" target="_blank">
				<i class="las la-headset"></i>
				<span class="demo-tooltip"><?php echo esc_html( 'Support' ); ?><span></span></span>
			</a>
		</li>
		<li>
			<a href="<?php echo esc_attr( $url ); ?>" target="_blank">
				<i class="las la-shopping-cart"></i>
				<span class="demo-tooltip"><?php echo esc_html( 'Purchase Cariera: ' . $price ); ?><span></span></span>
			</a>
		</li>
		<li>
			<a href="#" class="compatible-plugins">
				<i class="las la-rocket"></i>
				<span class="demo-tooltip"><?php echo esc_html( 'Compatible Addons' ); ?><span></span></span>
			</a>
		</li>
		<li>
			<a href="https://1.envato.market/cariera-flutter" target="_blank">
				<i class="las la-mobile"></i>
				<span class="demo-tooltip"><?php echo esc_html( 'Mobile Flutter App' ); ?><span></span></span>
			</a>
		</li>
	</ul>

	<div class="compatible-addons">
		<h5 class="title"><?php esc_html_e( 'Compatible Third Party Plugins', 'cariera' ); ?></h5>
	
		<?php foreach ( $products as $product ) { ?>
			<div class="product-item">
				<a href="<?php echo esc_url( $product['link'] ); ?>" target="_blank">
					<div class="theme-img">
						<img src="<?php echo esc_url( $product['bg-img'] ); ?>" alt="<?php echo esc_attr( $product['title'] ); ?>" lazy="loading">
					</div>
					<div class="title"><?php echo esc_html( $product['title'] ); ?></div>
				</a>
			</div>
		<?php } ?>
	</div>
</div>
