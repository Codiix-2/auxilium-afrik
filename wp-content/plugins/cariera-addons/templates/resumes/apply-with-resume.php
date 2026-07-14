<?php
/**
 * Apply with Resume content that displays on single job listings.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/apply-with-resume.php.
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

global $post;

if ( ! get_option( 'resume_manager_force_application' ) ) {
	echo '<hr/>';
}

$singular_label = cariera_addons_resume_cpt_singular_label();

if ( is_user_logged_in() && count( $resumes ) ) : ?>
	<form class="apply_with_resume" method="post">
		<p>
			<?php
			printf(
				/* translators: %s: singular resume label */
				esc_html__( 'Apply with your online %s by adding a short message.', 'cariera-addons' ),
				esc_html( $singular_label )
			);
			?>
		</p>
		<p>
			<label for="resume_id">
				<?php
				printf(
					/* translators: %s: singular resume label */
					esc_html__( 'Online %s:', 'cariera-addons' ),
					esc_html( $singular_label )
				);
				?>
			</label>
			<select name="resume_id" id="resume_id" class="cariera-select2" required>
				<?php
				foreach ( $resumes as $resume ) {
					echo '<option value="' . esc_attr( absint( $resume->ID ) ) . '">' . esc_html( $resume->post_title ) . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label><?php esc_html_e( 'Message', 'cariera-addons' ); ?>:</label>
			<textarea name="application_message" cols="20" rows="4" required>
				<?php
				if ( isset( $_POST['application_message'] ) ) {
					echo esc_textarea( wp_unslash( $_POST['application_message'] ) );
				} else {
					echo esc_html_x( 'To whom it may concern,', 'default cover letter', 'cariera-addons' ) . "\n\n";

					$company_name = get_the_company_name( $post );
					$default_body = sprintf(
						/* translators: 1: Job title, 2: Company name */
						esc_html__( 'I am very interested in the %1$s position at %2$s. I believe my skills and work experience make me an ideal candidate for this role. I look forward to speaking with you soon about this position.', 'cariera-addons' ),
						esc_html( $post->post_title ),
						esc_html( $company_name )
					);

					echo $default_body . "\n\n";
					echo esc_html_x( 'Thank you for your consideration.', 'default cover letter', 'cariera-addons' );
				}
				?>
			</textarea>
		</p>
		<p>
			<input type="submit" class="btn btn-main btn-effect" name="cariera_addons_resumes_apply_with_resume" value="<?php esc_attr_e( 'Send Application', 'cariera-addons' ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( absint( $post->ID ) ); ?>" />
		</p>
	</form>
<?php else : ?>
	<form class="apply_with_resume" method="post" action="<?php echo esc_url( get_permalink( get_option( 'resume_manager_submit_resume_form_page_id' ) ) ); ?>">
		<p>
			<?php
			printf(
				/* translators: %s: singular resume label */
				esc_html__( 'You can apply to this job and others using your online %1$s. Click the link below to submit your online %2$s and email your application to this employer.', 'cariera-addons' ),
				esc_html( $singular_label ),
				esc_html( $singular_label )
			);
			?>
		</p>

		<p>
			<input type="submit" class="btn btn-main btn-effect" name="cariera_addons_resumes_apply_with_resume_create" value="<?php echo esc_attr( sprintf( __( 'Submit %s &amp; Apply', 'cariera-addons' ), $singular_label ) ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( absint( $post->ID ) ); ?>" />
		</p>
	</form>
<?php endif; ?>
