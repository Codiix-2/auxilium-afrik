<?php
/**
 * Message to display when forcing a user to apply with a submitted resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/force-apply-with-resume.php.
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

$singular_label = cariera_addons_resume_cpt_singular_label();
?>

<form class="apply_with_resume" method="post" action="<?php echo esc_url( get_permalink( get_option( 'resume_manager_submit_resume_form_page_id' ) ) ); ?>">
	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %s: singular resume label */
				__( 'Before applying for this position you need to submit your <strong>online %s</strong>. Click the button below to continue.', 'cariera-addons' ),
				esc_html( $singular_label )
			)
		);
		?>
	</p>

	<p>
		<input type="submit" name="cariera_addons_resumes_apply_with_resume_create" value="<?php echo esc_attr( sprintf( __( 'Submit %s', 'cariera-addons' ), $singular_label ) ); ?>" />
		<input type="hidden" name="job_id" value="<?php echo esc_attr( absint( $post->ID ) ); ?>" />
	</p>
</form>
