<?php
/**
 * Elementor Element: Pricing Tables Version 1
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/pricing-table1.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="pricing-table <?php echo esc_attr( $highlight ); ?>">
	<div class="pricing-header"><h2><?php echo wp_kses_post( $settings['title'] ); ?></h2></div>
	<div class="pricing"><span class="amount"><?php echo wp_kses_post( $settings['price'] ); ?></span></div>

	<div class="pricing-body">
		<ul>
			<?php foreach ( (array) $settings['content'] as $item ) { ?>
				<li><?php echo esc_html( $item['detail'] ); ?></li>
			<?php } ?>
		</ul>
	</div>

	<div class="pricing-footer">
		<a href="<?php echo esc_url( $settings['link']['url'] ); ?>" <?php echo $settings['link']['is_external'] ? 'target="_blank"' : ''; ?> class="btn btn-main btn-effect" <?php echo $settings['link']['nofollow'] ? 'rel="nofollow"' : ''; ?>><?php echo esc_html( $settings['button_text'] ); ?></a>
	</div>
</div>
