<?php
/**
 * Theme footer template
 *
 * This template can be overridden by copying it to cariera-child/templates/footer/footer.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.7
 * @version     1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 'hide' === get_post_meta( get_the_ID(), 'cariera_show_footer', 'true' ) ) {
	return;
}

$footer_info      = cariera_get_option( 'cariera_footer_info' );
$footer_sidebar_1 = cariera_get_option( 'cariera_footer_sidebar_1' );
$footer_sidebar_2 = cariera_get_option( 'cariera_footer_sidebar_2' );
$footer_sidebar_3 = cariera_get_option( 'cariera_footer_sidebar_3' );
$footer_sidebar_4 = cariera_get_option( 'cariera_footer_sidebar_4' );
?>

<footer class="main-footer footer-default">
	<?php
	if ( $footer_info ) {
		if ( 'hide' !== get_post_meta( get_the_ID(), 'cariera_show_footer_widgets', 'true' ) ) {
			if ( is_active_sidebar( 'footer-widget-area' ) || is_active_sidebar( 'footer-widget-area-2' ) || is_active_sidebar( 'footer-widget-area-3' ) || is_active_sidebar( 'footer-widget-area-4' ) ) {
				?>

				<div class="footer-widget-area footer-info">
					<div class="container">
						<div class="row">
							<?php if ( 'disabled' !== $footer_sidebar_1 ) { ?>
								<div class="<?php echo esc_attr( $footer_sidebar_1 ); ?>">
									<?php dynamic_sidebar( 'footer-widget-area' ); ?>
								</div>
							<?php } ?>

							<?php if ( 'disabled' !== $footer_sidebar_2 ) { ?>
								<div class="<?php echo esc_attr( $footer_sidebar_2 ); ?>">
									<?php dynamic_sidebar( 'footer-widget-area-2' ); ?>
								</div>
							<?php } ?>

							<?php if ( 'disabled' !== $footer_sidebar_3 ) { ?>
								<div class="<?php echo esc_attr( $footer_sidebar_3 ); ?>">
									<?php dynamic_sidebar( 'footer-widget-area-3' ); ?>
								</div>
							<?php } ?>

							<?php if ( 'disabled' !== $footer_sidebar_4 ) { ?>
								<div class="<?php echo esc_attr( $footer_sidebar_4 ); ?>">
									<?php dynamic_sidebar( 'footer-widget-area-4' ); ?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
			}
		}
	}
	?>

	<?php get_template_part( 'templates/footer/copyright' ); ?>
</footer>
