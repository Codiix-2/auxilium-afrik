<?php
/**
 * Onboarding Importer: Import Issues
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/import-issues.php.
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

<div class="cariera-import-issues onboarding-notice error">
	<div class="header">
		<h3><?php esc_html_e( 'Issues Detected:', 'cariera-core' ); ?></h3>
	</div>
	<div class="body">
		<?php
		/**
		 * Hook: cariera_import_issues_before_content
		 */
		do_action( 'cariera_import_issues_before_content' );
		?>

		<ol>
			<?php foreach ( $import_issues as $issue ) : ?>
				<li><?php echo wp_kses_post( $issue ); ?></li>
			<?php endforeach; ?>
		</ol>

		<?php
		/**
		 * Hook: cariera_import_issues_after_content
		 */
		do_action( 'cariera_import_issues_after_content' );
		?>
	</div>
	<div class="footer">
		<span><?php esc_html_e( 'Please solve all issues listed above before importing the demo data.', 'cariera-core' ); ?></span>
	</div>
</div>
