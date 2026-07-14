<?php
/**
 * Elementor Element: Job Category Slider
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing/job-category-slider.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$classes = [ 'listing-term-slider' ];

// Add custom class if provided.
if ( ! empty( $settings['custom_class'] ) ) {
	$classes[] = $settings['custom_class'];
}
?>

<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-arrows="<?php echo esc_attr( $settings['arrows'] ? 1 : 0 ); ?>" data-autoplay="<?php echo esc_attr( $settings['autoplay'] ? 1 : 0 ); ?>" data-columns="<?php echo esc_attr( $settings['columns'] ); ?>">
	<?php
	foreach ( $categories as $term ) {
		$img_icon  = get_term_meta( $term->term_id, 'cariera_image_icon', true );
		$font_icon = get_term_meta( $term->term_id, 'cariera_font_icon', true );
		?>

		<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="item">
			<div class="term-item">
				<?php if ( 'show' === $settings['icon'] && ( ! empty( $img_icon ) || ! empty( $font_icon ) ) ) { ?>
					<span class="term-icon">
						<?php if ( ! empty( $img_icon ) ) { ?>
							<img src="<?php echo esc_attr( $img_icon ); ?>" class="category-icon" alt="<?php esc_attr_e( 'Image icon', 'cariera-core' ); ?>" />
						<?php } ?>

						<?php if ( ! empty( $font_icon ) ) { ?>
							<i class="<?php echo esc_attr( $font_icon ); ?>"></i>
						<?php } ?>
					</span>
				<?php } ?>

				<span class="term-title"><?php echo esc_html( $term->name ); ?></span>
			</div>
		</a>
		<?php
	}
	?>
</div>
