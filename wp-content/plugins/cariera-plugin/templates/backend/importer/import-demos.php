<?php
/**
 * Onboarding Importer: Import Demos
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/import-demos.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$demos       = \Cariera_Core\Importer\Importer::get_import_demos();
$demos_count = count( $demos );
?>

<div class="theme-import-demos">
	<div class="import-header">
		<h3><?php esc_html_e( 'Select a demo to import', 'cariera-core' ); ?></h3>
	</div>

	<div class="import-body">
		<?php
		/**
		 * Hook: cariera_import_demos_before_content
		 */
		do_action( 'cariera_import_demos_before_content' );
		?>

		<p class="cariera-error-text"></p>

		<?php if ( ! empty( $demos ) ) { ?>
			<div class="demo-list">
				<?php
				foreach ( $demos as $demo_slug => $demo ) {
					$imported = get_option( $theme_slug . '_' . $demo_slug . '_imported', false );
					if ( isset( $demo['name'], $demo['preview_image_url'] ) ) {
						?>
						<div class="<?php echo esc_attr( 'import-demo import-demo-' . $demo_slug ); ?>">
							<div class="demo-container">
								<div class="preview-img">
									<img src="<?php echo esc_attr( $demo['preview_image_url'] ); ?>" alt="<?php echo esc_attr( $demo['name'] ); ?>" lazy="loading" />
								</div>

								<div class="footer">
									<h4 class="demo-name">
										<?php echo esc_html( $demo['name'] ); ?>
										<?php if ( $imported ) { ?>
											<small><?php esc_html_e( '(has been imported before)', 'cariera-core' ); ?></small>
										<?php } ?>
									</h4>

									<div class="actions">
										<a href="#" class="button button-primary cariera-import-demo-btn" data-demo-slug="<?php echo esc_attr( $demo_slug ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'fetch_demo_steps' ) ); ?>">
											<?php esc_html_e( 'Import', 'cariera-core' ); ?>
										</a>
										
										<?php if ( $imported ) { ?>
											<a href="#" class="button button-primary cariera-uninstall-demo-btn" data-demo-slug="<?php echo esc_attr( $demo_slug ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'fetch_uninstall_confirmation' ) ); ?>">
												<?php esc_html_e( 'Uninstall', 'cariera-core' ); ?>
											</a>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>
		<?php } ?>

		<?php
		/**
		 * Hook: cariera_import_demos_after_content
		 */
		do_action( 'cariera_import_demos_after_content' );
		?>
	</div>

	<div id="cariera-import-demo-popup" class="cariera-popup mfp-hide"></div>
</div>
