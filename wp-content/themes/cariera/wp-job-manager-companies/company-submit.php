<?php
/**
 * Custom: Company - Company Submit
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-submit.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.4
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$submission_limit = get_option( 'cariera_company_submission_limit' );
$company_count    = cariera_count_user_companies();
$captcha_version  = ( class_exists( 'WP_Job_Manager\WP_Job_Manager_Recaptcha' ) && get_option( 'cariera_enable_recaptcha_company_submission' ) ) ? WP_Job_Manager\WP_Job_Manager_Recaptcha::instance()->get_recaptcha_version() : null;

wp_enqueue_style( 'cariera-wpjm-submissions' );
wp_enqueue_script( 'cariera-company-manager-submission' );

// Enqueue the location autocomplete script if enabled.
if ( class_exists( '\Cariera_Core\Core\Assets' ) ) {
	Cariera_Core\Core\Assets::enqueue_location_autocomplete();
}

if ( cariera_user_can_submit_companies() ) { ?>
	<form action="<?php echo esc_url( $action ); ?>" method="post" id="submit-company-form" class="job-manager-form" enctype="multipart/form-data" <?php if ( 'v3' === $captcha_version ) echo "onsubmit='return jm_job_submit_click(event)'" ?>>
		<?php
		do_action( 'submit_company_form_start' );

		if ( apply_filters( 'submit_company_form_show_signin', true ) ) {
			get_job_manager_template( 'account-signin.php', [ 'class' => $class ], 'wp-job-manager-companies' );
		}

		if ( cariera_user_can_post_company() ) {
			// Company Fields.
			get_job_manager_template(
				'company-submit-fields.php',
				[
					'class'          => $class,
					'form'           => $form,
					'company_id'     => $company_id,
					'job_id'         => $job_id,
					'action'         => $action,
					'company_fields' => $company_fields,
					'step'           => $step,
				],
				'wp-job-manager-companies'
			);
			?>

			<?php do_action( 'submit_company_form_company_fields_end' ); ?>

			<div class="cariera-listing-submission">
				<div class="submission-progress"></div>
				<input type="hidden" name="company_manager_form" value="<?php echo esc_attr( $form ); ?>" />
				<input type="hidden" name="company_id" value="<?php echo esc_attr( $company_id ); ?>" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
				<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
				<?php do_action( 'cariera_company_submission_steps' ); ?>
				<input type="submit" name="submit_company" class="button" value="<?php echo esc_attr( $submit_button_text ); ?>" />
			</div>

			<?php
		} else {
			do_action( 'submit_company_form_disabled' );
		}

		do_action( 'submit_company_form_end' );
		?>
	</form>
<?php } else { ?>
	<?php get_job_manager_template_part( 'access-denied', 'submit-company', 'wp-job-manager-companies' ); ?>
<?php } ?>
