<?php
/**
 * Template to show when previewing a resume being submitted.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/resume-preview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.1
 *
 * @var \Cariera_Addons\Core\Resumes\Forms\Submit_Resume $form Form object performing the action.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_style( 'cariera-wpjm-submissions' );

$singular_label = cariera_addons_resume_cpt_singular_label();
?>

<form method="post" id="resume_preview" action="<?php echo esc_url( $form->get_action() ); ?>">
	<?php do_action( 'preview_resume_form_start' ); ?>

	<div class="job_listing_preview_title cariera-listing-submission">
		<div class="submission-progress"></div>

		<input type="submit" name="edit_resume" class="button" value="<?php printf( esc_attr__( '&larr; Edit %s', 'cariera-addons' ), esc_attr( $singular_label ) ); ?>" />
		<?php get_job_manager_template_part( 'resumes/listing-submission', 'flow', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' ); ?>
		<input type="submit" name="continue" id="resume_preview_submit_button" class="button job-manager-button-submit-listing" value="<?php echo esc_attr( apply_filters( 'submit_resume_step_preview_submit_text', sprintf( __( 'Submit %s &rarr;', 'cariera-addons' ), esc_attr( $singular_label ) ) ) ); ?>" />
		<input type="hidden" name="resume_id" value="<?php echo esc_attr( $form->get_resume_id() ); ?>" />
		<input type="hidden" name="job_id" value="<?php echo esc_attr( $form->get_job_id() ); ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( $form->get_step() ); ?>" />
		<input type="hidden" name="resume_manager_form" value="<?php echo esc_attr( $form->form_name ); ?>" />
	</div>

	<div class="resume_preview single-resume">
		<?php get_job_manager_template_part( 'resumes/content-single', 'resume', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' ); ?>
	</div>

	<?php do_action( 'preview_resume_form_end' ); ?>
</form>
