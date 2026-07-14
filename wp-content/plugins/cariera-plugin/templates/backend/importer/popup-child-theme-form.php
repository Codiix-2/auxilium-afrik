<?php
/**
 * Onboarding Importer Popup: Child Theme Generator Form
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/popup-child-theme-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.9.8
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$child_theme_gen = \Cariera_Core\Importer\Child_Theme_Generator::instance();
$child_exists    = $child_theme_gen->child_theme_exists();
$message         = $child_exists ? __( 'Child theme already exists but is not currently active. Activating it is recommended for safe theme customizations.', 'cariera-core' ) : __( 'Child theme is recommended! It allows you to update the parent theme without losing any customizations.', 'cariera-core' );
?>

<form action="#" method="POST" id="child-theme-generator-form">
	<h4 class="popup-title"><?php esc_html_e( 'Child Theme Setup', 'cariera-core' ); ?></h4>
	<p class="popup-description"><?php echo esc_html( $message ); ?></p>
	<p class="cariera-error-text"></p>

	<div class="notice-info">
		<?php if ( ! $child_exists ) { ?>
			<h5 class="headline"><?php esc_html_e( 'What will be created:', 'cariera-core' ); ?></h5>
			<ul>
				<li><?php esc_html_e( 'Child theme folder: /wp-content/themes/cariera-child/', 'cariera-core' ); ?></li>
				<li><?php esc_html_e( 'style.css with proper theme headers', 'cariera-core' ); ?></li>
				<li><?php esc_html_e( 'functions.php with style enqueue', 'cariera-core' ); ?></li>
				<li><?php esc_html_e( 'screenshot.png (copied from parent)', 'cariera-core' ); ?></li>
			</ul>
		<?php } else { ?>
			<p><?php esc_html_e( 'The child theme will be activated to ensure your customizations are preserved during theme updates.', 'cariera-core' ); ?></p>
		<?php } ?>
	</div>

	<div class="popup-footer">
		<?php if ( isset( $import_data_url ) && ! empty( $import_data_url ) ) { ?>
			<input type="hidden" name="import_data_url" value="<?php echo esc_attr( $import_data_url ); ?>">
		<?php } ?>
		<input type="hidden" name="demo_slug" id="demo_slug" value="<?php echo esc_attr( $demo_slug ); ?>">
		<input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'generate_child_theme' ) ); ?>">
		
		<div class="buttons">
			<a href="#" class="skip-button"><?php esc_html_e( 'Skip', 'cariera-core' ); ?></a>
			<button type="submit" class="next-button">
				<?php echo $child_exists ? esc_html__( 'Activate Child Theme', 'cariera-core' ) : esc_html__( 'Generate & Activate', 'cariera-core' ); ?>
			</button>
		</div>
	</div>
</form>
