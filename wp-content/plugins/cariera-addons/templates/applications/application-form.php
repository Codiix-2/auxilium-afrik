<?php
/**
 * Application form shown on job listing page.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/applications/application-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.3
 * @version     0.9.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$captcha_version = ( class_exists( 'WP_Job_Manager\WP_Job_Manager_Recaptcha' ) && get_option( 'job_application_enable_recaptcha_application_submission' ) )
	? WP_Job_Manager\WP_Job_Manager_Recaptcha::instance()->get_recaptcha_version()
	: null;

?>
<form class="job-manager-application-form job-manager-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( get_permalink() ); ?>">
	<?php do_action( 'job_application_form_fields_start' ); ?>

	<?php foreach ( $application_fields as $key => $field ) : ?>
		<?php if ( 'output-content' === $field['type'] ) : ?>
			<div class="form-content">
				<h3><?php echo esc_html( wp_unslash( $field['label'] ) ); ?></h3>
				<?php
				if ( ! empty( $field['description'] ) ) {
					echo wpautop( wp_kses_post( $field['description'] ) );
				}
				?>
			</div>
		<?php else : ?>
			<fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . apply_filters( 'submit_job_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'cariera-addons' ) . '</small>', $field ); ?></label>
				<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
					<?php $class->get_field_template( $key, $field ); ?>
				</div>
			</fieldset>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php do_action( 'job_application_form_fields_end' ); ?>

	<p>
		<input type="submit" class="button cariera_addons_send_application_button" value="<?php esc_attr_e( 'Send application', 'cariera-addons' ); ?>" />
		<input type="hidden" name="cariera_addons_send_application" value="1" />
		<input type="hidden" name="job_id" value="<?php echo absint( $post->ID ); ?>" />
		<input type="hidden" name="form_id" value="<?php echo absint( $form_id ); ?>" />
	</p>
</form>
