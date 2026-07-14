<?php
/**
 * Access denied message when attempting to browse resumes.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/access-denied-browse-resumes.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plural_label = cariera_addons_resume_cpt_plural_label();
?>

<p class="job-manager-message error">
	<?php
	printf(
		// translators: %s: Resume plural label.
		esc_html__( 'Sorry, you do not have permission to browse %s.', 'cariera-addons' ),
		esc_html( $plural_label )
	);
	?>
</p>
