<?php
/**
 * Message to display when a resume has been submitted.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/resume-submitted.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-resumes
 * @category    Template
 * @version     1.18.0
 *
 * @var int     $job_id When initiating resume submission, this is the job that the user intends to apply for.
 * @var WP_Post $resume Resume post object that was just submitted.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-listing-submitted' );
?>

<div class="listing-submitted">
	<h3 class="title"><?php esc_html_e( 'Successfully Submitted!', 'cariera' ); ?></h3>

	<?php
	switch ( $resume->post_status ) :
		case 'publish':
			if ( resume_manager_user_can_view_resume( $resume->ID ) ) {
				echo '<p class="resume-submitted">';
				echo wp_kses_post(
					sprintf(
						// translators: Placeholder is URL to view the resume.
						esc_html__( 'Your resume has been submitted successfully. To view your resume <a href="%s">click here</a>.', 'cariera' ),
						esc_url( get_permalink( $resume->ID ) )
					)
				);
				echo '</p>';
			} else {
				echo '<p class="resume-submitted">';
				echo esc_html( esc_html__( 'Your resume has been submitted successfully.', 'cariera' ) );
				echo '</p>';
			}
			break;
		case 'pending':
			echo '<p class="resume-submitted">';
			echo esc_html( esc_html__( 'Your resume has been submitted successfully and is pending approval.', 'cariera' ) );
			if (
				$job_id
				&& 'publish' === get_post_status( $job_id )
				&& 'job_listing' === get_post_type( $job_id )
			) {
				$job_title     = wpjm_get_the_job_title( $job_id );
				$job_permalink = get_the_job_permalink( $job_id );
				echo wp_kses_post(
					sprintf(
						// translators: %1$s is the url to the job listing; %2$s is the title of the job listing.
						__( ' You will be able to apply for <a href="%1$s">%2$s</a> once your resume has been approved.', 'cariera' ),
						$job_permalink,
						$job_title
					)
				);
			}
			echo '</p>';
			break;
		default:
			$hook_friendly_post_status = str_replace( '-', '_', sanitize_title( $resume->post_status ) );
			do_action( 'resume_manager_resume_submitted_content_' . $hook_friendly_post_status, $resume );
			break;
	endswitch;
	?>
</div>
