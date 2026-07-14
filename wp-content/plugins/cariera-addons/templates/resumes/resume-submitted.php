<?php
/**
 * Message to display when a resume has been submitted.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/resume-submitted.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.5
 *
 * @var int     $job_id When initiating resume submission, this is the job that the user intends to apply for.
 * @var WP_Post $resume Resume post object that was just submitted.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-listing-submitted' );

$singular_label = cariera_addons_resume_cpt_singular_label();
?>

<div class="listing-submitted">
	<h3 class="title"><?php esc_html_e( 'Successfully Submitted!', 'cariera-addons' ); ?></h3>

	<?php
	/**
	 * Triggers before the resume-submitted template is displayed.
	 */
	do_action( 'cariera_addons_resume_submitted_content_before', $resume );

	switch ( $resume->post_status ) :
		case 'publish':
			if ( resume_manager_user_can_view_resume( $resume->ID ) ) {
				echo '<p class="resume-submitted">';
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s: singular resume label, %2$s: resume URL */
						__( 'Your %1$s has been submitted successfully. To view your %1$s <a href="%2$s">click here</a>.', 'cariera-addons' ),
						esc_html( $singular_label ),
						esc_url( get_permalink( $resume->ID ) )
					)
				);
				echo '</p>';
			} else {
				echo '<p class="resume-submitted">';
				echo esc_html(
					sprintf(
						/* translators: %s: singular resume label */
						__( 'Your %s has been submitted successfully.', 'cariera-addons' ),
						esc_html( $singular_label )
					)
				);
				echo '</p>';
			}
			break;
		case 'pending':
			echo '<p class="resume-submitted">';
			echo esc_html(
				sprintf(
					/* translators: %s: singular resume label */
					__( 'Your %s has been submitted successfully and is pending approval.', 'cariera-addons' ),
					esc_html( $singular_label )
				)
			);

			if (
				$job_id
				&& 'publish' === get_post_status( $job_id )
				&& \WP_Job_Manager_Post_Types::PT_LISTING === get_post_type( $job_id )
			) {
				$job_title     = wpjm_get_the_job_title( $job_id );
				$job_permalink = get_the_job_permalink( $job_id );
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s: job URL, %2$s: job title, %3$s: singular resume label */
						__( ' You will be able to apply for <a href="%1$s">%2$s</a> once your %3$s has been approved.', 'cariera-addons' ),
						$job_permalink,
						$job_title,
						esc_html( $singular_label )
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

	/**
	 * Triggers after the resume-submitted template is displayed.
	 */
	do_action( 'cariera_addons_resume_submitted_content_after', $resume );
	?>
</div>
