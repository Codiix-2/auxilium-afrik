<?php
/**
 * Resume invite candidate template
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/resume-invite-candidate.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.9.2
 * @version     1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $resume_preview;

if ( $resume_preview ) {
	return;
}

do_action( 'cariera_invite_candidate_start' ); ?>

<div class="resume-invite-candidate">
	<a href="#invite-candidate" class="btn btn-main btn-effect popup-with-zoom-anim"><?php esc_html_e( 'Invite Candidate', 'cariera' ); ?></a>

	<!-- Start Invite Candidate Popup -->
	<div id="invite-candidate" class="small-dialog zoom-anim-dialog mfp-hide">
		<div class="contact-candidate-popup">
			<div class="small-dialog-headline">
				<h3 class="title"><?php esc_html_e( 'Invite Candidate', 'cariera' ); ?></h3>
			</div>

			<div class="small-dialog-content">
				<form method="post" action="post" id="invite-candidate-form-<?php echo esc_attr( get_the_ID() ); ?>" class="cariera-invite-candidate-form">
					<?php do_action( 'cariera_invite_candidate_before' ); ?>

					<?php wp_nonce_field( 'cariera_invite_candidate_action', 'cariera_invite_candidate_nonce' ); ?>
					
					<div class="candidate-invite-details">
						<div class="status"></div>

						<?php if ( ! empty( $jobs ) ) { ?>
							<p><?php esc_html_e( 'Invite candidate to apply to one of your jobs.', 'cariera' ); ?></p>

							<div class="form-group">
								<select name="job_id" class="cariera-select2">
									<option value="" selected="selected"><?php esc_html_e( 'Select Job', 'cariera' ); ?></option>

									<?php foreach ( $jobs as $job ) { ?>
										<option value="<?php echo esc_attr( $job->ID ); ?>"><?php echo wpjm_the_job_title( $job ); ?></option>
									<?php } ?>
								</select>
							</div>

							<input type="hidden" name="candidate_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
							<button class="invite-candidate-button button" name="invite-candidate"><?php esc_html_e( 'Invite Candidate', 'cariera' ); ?></button>
							<span class="spinner" style="background-image: url(<?php echo esc_url( includes_url( 'images/spinner.gif' ) ); ?>);"></span>
						<?php } else { ?>
							<p><?php esc_html_e( 'You don\'t have any published job listings on your account!', 'cariera' ); ?></p>
						<?php } ?>
					</div>

					<?php do_action( 'cariera_invite_candidate_after' ); ?>
				</form>
			</div>                    
		</div>
	</div>        
</div>

<?php
do_action( 'cariera_invite_candidate_end' );
