<?php
/**
 * Onboarding Importer Popup: Copy images form
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/popup-copy-images-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form action="#" method="POST" id="copy-images-form">
	<h4 class="popup-title"><?php esc_html_e( 'Copy images', 'cariera-core' ); ?></h4>
	<p class="cariera-error-text"></p>

	<div class="popup-footer">
		<?php if ( isset( $media_package_local ) ) { ?>
			<input type="hidden" name="media_package_local" value="<?php echo esc_attr( $media_package_local ); ?>">
		<?php } ?>
		<?php if ( isset( $selected_steps_str ) && ! empty( $selected_steps_str ) ) { ?>
			<input type="hidden" name="selected_steps" value="<?php echo esc_attr( $selected_steps_str ); ?>">
		<?php } ?>
		<input type="hidden" name="demo_slug" id="demo_slug" value="<?php echo esc_attr( $demo_slug ); ?>">
		<input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'copy_images' ) ); ?>">
		
		<div class="buttons">
			<span class="popup-note"><?php esc_html_e( 'Please do not close this window until the process is completed', 'cariera-core' ); ?></span>
			<a href="#" class="close-button"><?php esc_html_e( 'Close', 'cariera-core' ); ?></a>
		</div>
	</div>
</form>
