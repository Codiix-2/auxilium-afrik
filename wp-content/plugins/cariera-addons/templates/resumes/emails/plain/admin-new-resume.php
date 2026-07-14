<?php
/**
 * Email content when notifying admin of a new resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/emails/plain/admin-new-resume.php.
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
$singular_label = cariera_addons_resume_cpt_singular_label();

// translators: %1$s is the site name; %2$s is the site URL; %3$s is the singular resume label.
echo esc_html(
	sprintf(
		__( 'A new %3$s has been submitted to %1$s (%2$s).', 'cariera-addons' ),
		get_bloginfo( 'name' ),
		home_url(),
		esc_html( $singular_label )
	)
);

switch ( $resume->post_status ) {
	case 'publish':
		echo ' ' . esc_html__( 'It has been published and is now available to the public.', 'cariera-addons' );
		break;
	case 'pending':
		// translators: Placeholder is URL for WP admin.
		echo ' ' . esc_html( sprintf( __( 'It is awaiting approval by an administrator in WordPress admin (%s).', 'cariera-addons' ), esc_url( admin_url( 'edit.php?post_type=resume' ) ) ) );
		break;
}

/**
 * Show details about the resume listing.
 *
 * @param WP_Post              $resume            The resume listing to show details for.
 * @param WP_Job_Manager_Email $email          Email object for the notification.
 * @param bool                 $sent_to_admin  True if this is being sent to an administrator.
 * @param bool                 $plain_text     True if the email is being sent as plain text.
 */
do_action( 'resume_manager_email_resume_details', $resume, $email, true, $plain_text );
