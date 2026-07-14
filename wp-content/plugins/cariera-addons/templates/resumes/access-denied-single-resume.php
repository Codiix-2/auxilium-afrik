<?php
/**
 * Message to display when access is denied to a single resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/access-denied-single-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$singular_label = cariera_addons_resume_cpt_singular_label();
?>

<main class="cariera-section-padding">
	<div class="container">
		<?php if ( 'expired' === $post->post_status ) { ?>
			<div class="job-manager-info"><?php esc_html_e( 'This listing has expired', 'cariera-addons' ); ?></div>
		<?php } else { ?>
			<p class="job-manager-message error">
				<?php
				printf(
					/* translators: %s: singular label (e.g. Resume) */
					esc_html__( 'Sorry, you do not have permission to view this %s.', 'cariera-addons' ),
					esc_html( $singular_label )
				);
				?>
			</p>
		<?php } ?>
	</div>
</main>
