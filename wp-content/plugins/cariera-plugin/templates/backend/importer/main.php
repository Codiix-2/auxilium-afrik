<?php
/**
 * Onboarding Importer: Main
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/main.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="demo-importer-container">
	<?php
	/**
	 * Action: cariera_page_import_before_content
	 */
	do_action( 'cariera_page_import_before_content' );

	if ( ! empty( $import_issues ) && ! $ignore_import_issues ) {
		cariera_get_template(
			'backend/importer/import-issues.php',
			[
				'import_issues' => $import_issues,
			]
		);
	} else {
		cariera_get_template(
			'backend/importer/import-demos.php',
			[
				'theme_slug' => $theme_slug,
			]
		);
	}

	/**
	 * Action: cariera_page_import_after_content
	 */
	do_action( 'cariera_page_import_after_content' );
	?>
</div>
