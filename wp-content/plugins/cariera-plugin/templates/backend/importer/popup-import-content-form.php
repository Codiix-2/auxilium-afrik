<?php
/**
 * Onboarding Importer Popup: Import content view
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/popup-demo-steps-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="import-content-wrapper">
	<h4 class="popup-title"><?php esc_html_e( 'Importing Demo Data', 'cariera-core' ); ?></h4>
	<p class="cariera-error-text"></p>

	<div class="import-progress-wrapper">
		<div class="import-progress-bar">
			<div class="import-progress-fill"></div>
		</div>
		<p class="import-progress-text"><span>0%</span><?php esc_html_e( 'Imported!', 'cariera-core' ); ?></p>
	</div>

	<?php if ( isset( $import_content_steps ) && ! empty( $import_content_steps ) ) { ?>
		<ul class="import-content-list">
			<?php
			$i             = 0;
			$content_steps = '';
			foreach ( $import_content_steps as $key => $text ) {
				$content_steps .= $key . ',';
				?>
				<li id="<?php echo esc_attr( $key ); ?>" class="import-content-item loading" data-action="<?php echo esc_attr( "import_{$key}" ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( "import_{$key}" ) ); ?>">
					<div class="loader"><span class="circle"></span></div>
					<div class="radio-option">
						<span class="checkmark">
							<div class="checkmark_stem"></div>
							<div class="checkmark_kick"></div>
						</span>
					</div>
					<span class="import-content-text"><?php echo esc_html( $text ); ?></span>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
	
	<div class="popup-footer">
		<?php if ( isset( $import_content_steps ) && ! empty( $import_content_steps ) ) { ?>
			<input type="hidden" name="import_content_steps" id="import_content_steps" value="<?php echo esc_attr( $content_steps ); ?>">
		<?php } ?>
		<input type="hidden" name="demo_slug" id="demo_slug" value="<?php echo esc_attr( $demo_slug ); ?>">
		
		<div class="buttons">
			<span class="popup-note"><?php esc_html_e( 'Please do not close this window until the process is completed', 'cariera-core' ); ?></span>
			<a href="#" class="close-button"><?php esc_html_e( 'Cancel', 'cariera-core' ); ?></a>
		</div>
	</div>
</form>
