<?php
/**
 * Content to show when a candidate has selected email as their preferred contact method.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/contact-details-email.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     0.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php printf( __( 'To contact this candidate email <a class="job_application_email" href="mailto:%1$s%2$s">%1$s</a>', 'cariera-addons' ), esc_attr( $email ), '?subject=' . rawurlencode( $subject ) ); ?></p>

<p>
	<?php esc_html_e( 'Contact using webmail: ', 'cariera-addons' ); ?>
	<a href="<?php echo esc_url('https://mail.google.com/mail/?view=cm&fs=1&to=' . esc_attr( $email ) . '&su=' . urlencode( $subject )) ?>" target="_blank" class="job_application_email">Gmail</a> /
	<a href="<?php echo esc_url('https://mail.aol.com/Mail/ComposeMessage.aspx?to=' . esc_attr( $email ) . '&subject=' . urlencode( $subject )) ?>" target="_blank" class="job_application_email">AOL</a> /
	<a href="<?php echo esc_url('https://compose.mail.yahoo.com/?to=' . esc_attr( $email ) . '&subject=' . urlencode( $subject )) ?>" target="_blank" class="job_application_email">Yahoo</a> /
	<a href="<?php echo esc_url('https://outlook.live.com/mail/0/deeplink/compose?to=' . esc_attr( $email ) . '&subject=' . urlencode( $subject )) ?>" target="_blank" class="job_application_email">Outlook</a> /
</p>
