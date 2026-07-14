<?php
/**
 * Uninstall Progress Popup
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/popup-uninstall-progress.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.9.8
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="import-content-wrapper" class="uninstall-content-wrapper">
	<h4 class="popup-title"><?php esc_html_e( 'Uninstalling Demo Content', 'cariera-core' ); ?></h4>
	<p class="cariera-error-text"></p>

	<div class="import-progress-wrapper uninstall-progress-wrapper">
		<div class="import-progress-bar uninstall-progress-bar">
			<div class="import-progress-fill uninstall-progress-fill"></div>
		</div>
		<p class="import-progress-text uninstall-progress-text"><span>0%</span><?php esc_html_e( 'Deleted!', 'cariera-core' ); ?></p>
	</div>

	<?php if ( isset( $uninstall_steps ) && ! empty( $uninstall_steps ) ) { ?>
		<ul class="import-content-list uninstall-content-list">
			<?php
			$steps_string = '';
			foreach ( $uninstall_steps as $step_key => $step_label ) {
				$steps_string .= $step_key . ',';
				?>
				<li id="<?php echo esc_attr( $step_key ); ?>" class="import-content-item uninstall-content-item loading" data-action="<?php echo esc_attr( $step_key ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $step_key ) ); ?>">
					<div class="loader"><span class="circle"></span></div>
					<div class="radio-option">
						<span class="checkmark">
							<div class="checkmark_stem"></div>
							<div class="checkmark_kick"></div>
						</span>
					</div>
					<span class="import-content-text"><?php echo esc_html( $step_label ); ?></span></span>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>

	<div class="popup-footer">
		<?php if ( ! empty( $steps_string ) ) { ?>
			<input type="hidden" name="uninstall_steps" id="uninstall_steps" value="<?php echo esc_attr( rtrim( $steps_string, ',' ) ); ?>" />
		<?php } ?>
		<input type="hidden" name="demo_slug" id="demo_slug" value="<?php echo esc_attr( $demo_slug ); ?>" />

		<div class="buttons">
			<span class="popup-note"><?php esc_html_e( 'Please do not close this window until the process is completed', 'cariera-core' ); ?></span>
			<a href="#" class="close-button"><?php esc_html_e( 'Cancel', 'cariera-core' ); ?></a>
		</div>
	</div>
</div>
