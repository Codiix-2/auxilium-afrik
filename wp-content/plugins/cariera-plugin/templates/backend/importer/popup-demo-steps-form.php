<?php
/**
 * Onboarding Importer Popup: Demo steps form
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/popup-demo-steps-form.php.
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

<form action="#" method="POST" id="demo-steps-form">
	<h4 class="popup-title"><?php esc_html_e( 'Choose what to import', 'cariera-core' ); ?></h4>
	<p class="cariera-error-text"></p>

	<ul class="demo-steps-wrapper">
		<li class="demo-step">
			<input type="checkbox" name="all_demo_steps" id="cariera-all-demo-steps" class="step-checkbox" checked>
			<span class="step-svg">
				<svg width="18px" height="18px" viewBox="0 0 18 18">
					<path d="M1,9 L1,3.5 C1,2 2,1 3.5,1 L14.5,1 C16,1 17,2 17,3.5 L17,14.5 C17,16 16,17 14.5,17 L3.5,17 C2,17 1,16 1,14.5 L1,9 Z"></path>
					<polyline points="1 9 7 14 15 4"></polyline>
				</svg>
			</span>
			<label for="cariera-all-demo-steps" class="step-label"><?php esc_html_e( 'All', 'cariera-core' ); ?></label>
		</li>

		<?php foreach ( $demo_steps as $key => $val ) { ?>
			<li class="demo-step">
				<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" class="step-checkbox" checked>
				<span class="step-svg">
					<svg width="18px" height="18px" viewBox="0 0 18 18">
						<path d="M1,9 L1,3.5 C1,2 2,1 3.5,1 L14.5,1 C16,1 17,2 17,3.5 L17,14.5 C17,16 16,17 14.5,17 L3.5,17 C2,17 1,16 1,14.5 L1,9 Z"></path>
						<polyline points="1 9 7 14 15 4"></polyline>
					</svg>
				</span>
				<label for="<?php echo esc_attr( $key ); ?>" class="step-label"><?php echo esc_html( $val ); ?></label>
			</li>
		<?php } ?>
	</ul>

	<div class="popup-footer">
		<input type="hidden" name="demo_slug" id="demo_slug" value="<?php echo esc_attr( $demo_slug ); ?>">
		<input type="hidden" name="selected_steps" id="selected-steps" value="">
		<input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'import_demo' ) ); ?>">
		
		<div class="buttons">
			<a href="#" class="close-button fetch-demo-close"><?php esc_html_e( 'Cancel', 'cariera-core' ); ?></a>
			<button type="submit" class="next-button"><?php esc_html_e( 'Continue', 'cariera-core' ); ?></button>
		</div>
	</div>
</form>
