<?php
/**
 * Template to show when submitting a resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/resume-submit.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$captcha_version = ( class_exists( 'WP_Job_Manager\WP_Job_Manager_Recaptcha' ) && get_option( 'resume_manager_enable_recaptcha_resume_submission' ) ) ? WP_Job_Manager\WP_Job_Manager_Recaptcha::instance()->get_recaptcha_version() : null;

wp_enqueue_style( 'cariera-wpjm-submissions' );
wp_enqueue_script( 'cariera-addons-resume-submission' );

// Enqueue the location autocomplete script if enabled.
if ( class_exists( '\Cariera_Core\Core\Assets' ) ) {
	Cariera_Core\Core\Assets::enqueue_location_autocomplete();
}

if ( cariera_addons_user_can_submit_resumes() ) { ?>
	<form action="<?php echo esc_url( $action ); ?>" method="post" id="submit-resume-form" class="job-manager-form" enctype="multipart/form-data" <?php echo ( 'v3' === $captcha_version ) ? "onsubmit='return jm_job_submit_click(event)'" : ''; ?>>
		<?php do_action( 'submit_resume_form_start' ); ?>

		<?php if ( apply_filters( 'submit_resume_form_show_signin', true ) ) { ?>
			<?php get_job_manager_template( 'resumes/account-signin.php', [ 'class' => $class ], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' ); ?>
		<?php } ?>

		<?php if ( resume_manager_user_can_post_resume() ) { ?>
			<!-- Resume Fields -->
			<?php do_action( 'submit_resume_form_resume_fields_start' ); ?>

			<?php foreach ( $resume_fields as $key => $field ) { ?>
				<fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . apply_filters( 'submit_resume_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'cariera-addons' ) . '</small>', $field ); ?></label>
					<div class="field">
						<?php $class->get_field_template( $key, $field ); ?>
					</div>
				</fieldset>
			<?php } ?>

			<?php do_action( 'submit_resume_form_resume_fields_end' ); ?>

			<div class="cariera-listing-submission">
				<div class="submission-progress"></div>
				<input type="hidden" name="resume_manager_form" value="<?php echo esc_attr( $form ); ?>" />
				<input type="hidden" name="resume_id" value="<?php echo esc_attr( $resume_id ); ?>" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
				<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
				<?php get_job_manager_template_part( 'resumes/listing-submission', 'flow', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' ); ?>
				<input type="submit" name="submit_resume" class="button" value="<?php echo esc_attr( $submit_button_text ); ?>" />
			</div>
		<?php } else { ?>
			<?php do_action( 'submit_resume_form_disabled' ); ?>
		<?php } ?>

		<?php do_action( 'submit_resume_form_end' ); ?>
	</form>
<?php } else { ?>
	<?php get_job_manager_template_part( 'resumes/access-denied', 'submit-resume', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' ); ?>
<?php } ?>
