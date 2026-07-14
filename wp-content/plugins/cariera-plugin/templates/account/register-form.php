<?php
/**
 * Cariera Register Form template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/register-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$candidate_role    = get_option( 'cariera_user_role_candidate' );
$employer_role     = get_option( 'cariera_user_role_employer' );
$default_user_role = get_option( 'cariera_default_registration_user_role' );

wp_enqueue_script( 'cariera-user-ajax' );

do_action( 'cariera_register_form_before' ); ?>

<form id="cariera_registration" method="POST">
	<p class="status"></p>

	<div class="form-group">
		<!-- User Roles Wrapper -->
		<div class="user-roles-wrapper">
			<?php
			if ( class_exists( 'WP_Resume_Manager' ) ) {
				if ( $candidate_role ) {
					?>
					<div class="user-role candidate-role">
						<input type="radio" name="cariera_user_role" id="candidate-input" value="candidate" class="user-role-radio" <?php echo 'candidate' === $default_user_role ? 'checked' : ''; ?>>
						<label for="candidate-input">
							<i class="las la-user-tie"></i>
							<div>
								<h6><?php esc_html_e( 'Candidate', 'cariera-core' ); ?></h6>
								<span><?php esc_html_e( 'Register as a Candidate', 'cariera-core' ); ?></span>
							</div>
						</label>
					</div>
					<?php
				}
			}
			?>

			<?php if ( $employer_role ) { ?>
				<div class="user-role employer-role">
					<input type="radio" name="cariera_user_role" id="employer-input" value="employer" class="user-role-radio" <?php echo 'employer' === $default_user_role ? 'checked' : ''; ?>>
					<label for="employer-input">
						<i class="las la-briefcase"></i>
						<div>
							<h6><?php esc_html_e( 'Employer', 'cariera-core' ); ?></h6>
							<span><?php esc_html_e( 'Register as an Employer', 'cariera-core' ); ?></span>
						</div>
					</label>
				</div>
			<?php } ?>
		</div>
	</div>

	<?php if ( ! get_option( 'cariera_register_hide_username' ) ) { ?>
		<div class="form-group">
			<label for="register_username"><?php esc_html_e( 'Username', 'cariera-core' ); ?></label>
			<input name="register_username" id="register_username" class="form-control" type="text" placeholder="<?php esc_html_e( 'Your Username', 'cariera-core' ); ?>" />
		</div>
	<?php } ?>

	<div class="form-group">
		<label for="register_email"><?php esc_html_e( 'Email', 'cariera-core' ); ?></label>
		<input name="register_email" id="register_email" class="form-control" type="email" placeholder="<?php esc_html_e( 'Your Email', 'cariera-core' ); ?>" />
	</div>

	<div class="form-group">
		<label for="register_password"><?php esc_html_e( 'Password', 'cariera-core' ); ?></label>
		<div class="cariera-password">
			<input name="register_password" id="register_password" class="form-control" type="password" placeholder="<?php esc_html_e( 'Your Password', 'cariera-core' ); ?>" />
			<i class="lar la-eye"></i>
		</div>        
	</div>

	<?php
	$registration_captcha = get_option( 'cariera_captcha_register' );
	if ( $registration_captcha ) {
		\Cariera_Core\Extensions\Captcha\Captcha::render_field( 'register-form' );
	}

	if ( get_option( 'cariera_register_privacy_policy' ) ) {
		$gdpr_link = apply_filters( 'cariera_register_privacy_policy_page', get_option( 'cariera_register_privacy_policy_page' ) );
		$gdpr_page = '<a href="' . esc_url( get_permalink( $gdpr_link ) ) . '">' . get_the_title( $gdpr_link ) . '</a>';
		$gdpr_text = str_replace( '{gdpr_link}', $gdpr_page, get_option( 'cariera_register_privacy_policy_text' ) );
		?>

		<div class="form-group gdpr-wrapper">
			<div class="checkbox">
				<input id="check-privacy" type="checkbox" name="privacy_policy">
				<label for="check-privacy"><?php echo wp_kses_post( $gdpr_text ); ?></label>
			</div>
		</div>
	<?php } ?>

	<div class="form-group">
		<input type="submit" class="btn btn-main btn-effect" id="cariera-user-register" value="<?php esc_attr_e( 'Register', 'cariera-core' ); ?>"/>
	</div>

	<?php wp_nonce_field( 'cariera-ajax-register-nonce', 'register_security' ); ?>
</form>

<?php do_action( 'cariera_register_form_after' ); ?>
