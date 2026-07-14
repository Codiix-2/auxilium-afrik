<?php
/**
 * Email content when notifying employer of a new application with a resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/emails/plain/apply-with-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.1
 *
 * @var WP_Job_Manager_Email $email          Email object for the notification.
 * @var bool                 $sent_to_admin  True if this is being sent to an administrator.
 * @var bool                 $plain_text     True if the email is being sent as plain text.
 * @var array                $args           Arguments used to generate the email notification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$resume         = $args['resume'];
$job            = $args['job'];
$singular_label = cariera_addons_resume_cpt_singular_label();

echo esc_html(
	sprintf(
		// translators:  %1$s is the name of the site, %2$s is the URL for the site, %3$s is the job listing title, %4$s is the job listing permalink.
		__( 'A candidate has applied on the site %1$s (%2$s) for the position %3$s (%4$s).', 'cariera-addons' ),
		get_bloginfo( 'name' ),
		esc_url( home_url() ),
		get_the_title( $job ),
		esc_url( get_the_job_permalink( $job ) )
	)
);
echo PHP_EOL . PHP_EOL;

if ( ! empty( $args['resume_link'] ) ) {
	// translators: %1$s is the resume link; %2$s is the singular resume label.
	echo esc_html(
		sprintf(
			__( 'You can view their online %2$s by visiting %1$s.', 'cariera-addons' ),
			$args['resume_link'],
			esc_html( $singular_label )
		)
	);
}

$candidate_email = get_post_meta( $args['resume']->ID, '_candidate_email', true );
if ( ! empty( $candidate_email ) ) {
	// translators: Placeholder is the candidate email address.
	echo esc_html( sprintf( __( ' Additionally, you can contact them directly at %s.', 'cariera-addons' ), $candidate_email ) );
}

echo PHP_EOL . PHP_EOL;

// translators: %s is the singular resume label.
echo esc_html( sprintf( __( 'They included the following message along with their %s:', 'cariera-addons' ), esc_html( $singular_label ) ) ) . PHP_EOL;
echo esc_html( $args['message'] );

echo PHP_EOL . PHP_EOL;

if ( $email->show_resume_details() ) {
	/**
	 * Show details about the resume.
	 *
	 * @param WP_Post              $resume         The resume to show details for.
	 * @param WP_Job_Manager_Email $email          Email object for the notification.
	 * @param bool                 $sent_to_admin  True if this is being sent to an administrator.
	 * @param bool                 $plain_text     True if the email is being sent as plain text.
	 */
	do_action( 'resume_manager_email_resume_details', $resume, $email, false, $plain_text );
}
