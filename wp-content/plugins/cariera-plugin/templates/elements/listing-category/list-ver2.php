<?php
/**
 * Elementor Element: Listing Category List Version 2
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing-category/list-ver2.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<ul class="listing-categories <?php echo esc_attr( $settings['listing'] ); ?>-categories list-layout2">
	<?php foreach ( $chunk as $term ) { ?>
		<?php $bg_img = get_term_meta( $term->term_id, 'cariera_background_image', true ); ?>
		
		<li>
			<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">

				<?php if ( ! empty( $bg_img ) ) { ?>
					<div class="category-img" style="background-image: url(<?php echo esc_attr( $bg_img ); ?>);"></div>
				<?php } else { ?>
					<div class="category-img"></div>
				<?php } ?>

				<div class="category-info">
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

					<span class="cat-description"><?php echo esc_html( $term->description ); ?></span>
				</div>
			</a>
		</li>
	<?php } ?>
</ul>
