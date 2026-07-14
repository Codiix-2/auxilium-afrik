<?php
/**
 * Elementor Element: Counter
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/counter.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="counter-container">
	<div class="counter">
		<?php if ( 'enable' === $settings['enable_icon'] ) { ?>
			<div class="counter-icon">
				<?php
				if ( 'icon' === $settings['icon_type'] ) {
					\Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
				} else {
					echo '<img src="' . esc_url( $settings['image']['url'] ) . '" alt="' . esc_attr__( 'Image icon', 'cariera-core' ) . '">';
				}
				?>
			</div>
		<?php } ?>

		<div class="counter-details">
			<div class="counter-number-wrapper">
				<span class="counter-number" data-from="0" data-to="<?php echo esc_attr( $number ); ?>">0</span>

				<?php if ( 'custom' === $settings['value'] && ! empty( $settings['suffix'] ) ) { ?>
					<span class="counter-suffix"><?php echo esc_html( $settings['suffix'] ); ?></span>
				<?php } ?>
			</div>

			<h3 class="title"><?php echo esc_html( $settings['title'] ); ?></h3>                
		</div>
	</div>
</div>
