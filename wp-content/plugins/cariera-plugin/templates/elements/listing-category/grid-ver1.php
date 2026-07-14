<?php
/**
 * Elementor Element: Listing Category Grid Version 1
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing-category/grid-ver1.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="listing-categories <?php echo esc_attr( $settings['listing'] ); ?>-categories grid-layout1">
	<?php
	foreach ( $chunk as $term ) {
		$bg_img    = get_term_meta( $term->term_id, 'cariera_background_image', true );
		$img_icon  = get_term_meta( $term->term_id, 'cariera_image_icon', true );
		$font_icon = get_term_meta( $term->term_id, 'cariera_font_icon', true );
		?>

		<div class="listing-category">
			<?php
			// Background Options.
			if ( 'show' === $settings['background'] && ! empty( $bg_img ) ) {
				?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="listing-category-bg" style="background-image: url(<?php echo esc_attr( $bg_img ); ?>)">
			<?php } else { ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
			<?php } ?>

				<?php
				// Category Icon.
				if ( 'show' === $settings['icon'] ) {
					if ( ! empty( $img_icon ) ) {
						?>
						<img src="<?php echo esc_attr( $img_icon ); ?>" class="category-icon" alt="<?php esc_attr_e( 'Image icon', 'cariera-core' ); ?>" />
					<?php } elseif ( ! empty( $font_icon ) ) { ?>
						<i class="<?php echo esc_attr( $font_icon ); ?>"></i>
						<?php
					}
				}
				?>
				<h4 class="title"><?php echo esc_html( $term->name ); ?></h4>

				<?php if ( 'job_listing' === $settings['listing'] && 'show' === $settings['job_counter'] ) { ?>
					<span class="positions"><?php echo wp_kses_post( sprintf( __( '%s open positions', 'cariera-core' ), $term->count ) ); ?></span>
				<?php } ?>
				
				<?php if ( 'resume' === $settings['listing'] && 'show' === $settings['resume_counter'] ) { ?>
					<span class="positions"><?php echo wp_kses_post( sprintf( __( '(%s Resumes)', 'cariera-core' ), $term->count ) ); ?></span>
				<?php } ?>

				<?php if ( 'company' === $settings['listing'] && 'show' === $settings['company_counter'] ) { ?>
					<span class="positions"><?php echo wp_kses_post( sprintf( __( '(%s Companies)', 'cariera-core' ), $term->count ) ); ?></span>
				<?php } ?>
			</a>
		</div>
	<?php } ?>
</div>
