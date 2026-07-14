<?php
/**
 * Displays contact details when viewing a single resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/contact-details.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     0.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $resume_preview;

if ( $resume_preview ) {
	return;
}

if ( resume_manager_user_can_view_contact_details( $post->ID ) ) {
	?>

	<div class="resume_contact">
		<a href="#contact-candidate" class="btn btn-secondary btn-effect popup-with-zoom-anim"><?php esc_html_e( 'Contact', 'cariera-addons' ); ?></a>

		<!-- Start Contact Candidate Popup -->
		<div id="contact-candidate" class="small-dialog zoom-anim-dialog mfp-hide">
			<div class="contact-candidate-popup">
				<div class="small-dialog-headline">
					<h3 class="title"><?php esc_html_e( 'Contact Candidate', 'cariera-addons' ); ?></h3>
				</div>

				<div class="small-dialog-content text-center">
					<?php do_action( 'resume_manager_contact_details' ); ?>
				</div>                    
			</div>
		</div>        
	</div>
<?php } else { ?>
	<?php get_job_manager_template_part( 'resumes/access-denied', 'contact-details', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' ); ?>
<?php } ?>
