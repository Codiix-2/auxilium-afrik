<?php
/**
 * Access denied message when attempting to submit a resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/access-denied-submit-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.10
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$singular_label = cariera_addons_resume_cpt_singular_label();
?>

<p class="job-manager-message error">
	<?php
	printf(
		/* translators: %s: singular label (e.g. Resume) */
		esc_html__( 'Sorry, you do not have permission to submit a %s.', 'cariera-addons' ),
		esc_html( $singular_label )
	);
	?>
</p>
