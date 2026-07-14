<?php
/**
 * Onboarding: Installation video
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/installation-video.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.7
 * @version     1.8.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="installation-video">
	<h3><?php esc_html_e( 'Installation Video:', 'cariera' ); ?></h3>
	<?php echo wp_oembed_get( 'https://youtu.be/oUCh3IQVeCY?si=VT3TOEsnIWs1aqfs' ); ?>
	<p><?php esc_html_e( 'You can view the complete list of Cariera related tutorial videos by clicking the button below.', 'cariera' ); ?></p>
	<a href="https://youtube.com/playlist?list=PLqi8ZuoqRtfNdHgkJqFOEd71gJ7k0ZyDx&si=dyy-IkKPJpCKL9_G" class="button button-primary" target="_blank"><?php esc_html_e( 'Cariera Playlist', 'cariera' ); ?></a>
</div>
