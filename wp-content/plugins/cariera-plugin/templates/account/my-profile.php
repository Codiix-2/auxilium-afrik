<?php
/**
 * Cariera My Profile template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/my-profile.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'cariera_my_account_start' );

$current_user = wp_get_current_user();
$user_id      = get_current_user_id();
$user_img     = get_avatar( get_the_author_meta( 'ID', $user_id ), 120 );
$user_role    = $current_user->roles[0];
?>

<div class="row">
	<!-- Start of Edit My Profile -->
	<div class="col-lg-7 col-md-12">
		<div class="dashboard-card-box">
			<h2 class="title"><?php esc_html_e( 'Profile Details', 'cariera-core' ); ?></h2>

			<div class="dashboard-card-box-inner">
				<form name="change_details_form" class="change-details-form" method="post" >
					<div class="my-profile">
						<div class="user-avatar-upload">
							<?php
							$avatar_id   = absint( $current_user->cariera_avatar_id ?? 0 );
							$avatar_url  = $avatar_id ? wp_get_attachment_url( $avatar_id ) : '';
							$avatar_path = $avatar_id ? get_attached_file( $avatar_id ) : '';
							$avatar_size = ( $avatar_path && file_exists( $avatar_path ) ) ? filesize( $avatar_path ) : 0;
							$avatar_name = $avatar_id ? get_the_title( $avatar_id ) : __( 'Your Avatar', 'cariera-core' );

							// Prepare data attributes.
							$data_attrs = '';
							if ( ! empty( $avatar_url ) ) {
								$data_attrs = sprintf(
									'data-photo="%s" data-name="%s" data-size="%d" data-id="%d"',
									esc_url( $avatar_url ),
									esc_attr( $avatar_name ),
									absint( $avatar_size ),
									absint( $avatar_id )
								);
							}
							?>

							<div class="cariera-avatar-uploader" <?php echo $data_attrs; ?>></div>
							<input type="hidden" name="cariera_avatar_id" id="avatar-uploader-id" value="<?php echo absint( $avatar_id ); ?>" />

							<div class="user-avatar-description">
								<p><?php echo wp_kses_post( apply_filters( 'cariera_my_account_avatar_description', __( 'Update your photo manually. If the photo is not set, the default Gravatar will be the same as your login email account. Please make sure that your uploaded image is a square size image.', 'cariera-core' ) ) ); ?></p>
							</div>
						</div>

						<?php
						if ( get_option( 'cariera_account_role_change' ) ) {
							if ( in_array( $user_role, [ 'employer', 'candidate' ], true ) ) {
								?>
								<div class="form-group">
									<!-- User Roles Wrapper -->
									<div class="user-roles-wrapper">
										<?php if ( class_exists( 'WP_Resume_Manager' ) ) { ?>
											<div class="user-role candidate-role">
												<input type="radio" name="cariera_user_role" id="candidate-input" value="candidate" class="user-role-radio" <?php checked( 'candidate', $user_role ); ?>>
												<label for="candidate-input">
													<i class="las la-user-tie"></i>
													<div>
														<span><?php esc_html_e( 'Registered as a', 'cariera-core' ); ?></span>
														<h6><?php esc_html_e( 'Candidate', 'cariera-core' ); ?></h6>
													</div>
												</label>
											</div>
										<?php } ?>

										<div class="user-role employer-role">
											<input type="radio" name="cariera_user_role" id="employer-input" value="employer" class="user-role-radio" <?php checked( 'employer', $user_role ); ?>>
											<label for="employer-input">
												<i class="las la-briefcase"></i>
												<div>
													<span><?php esc_html_e( 'Registered as an', 'cariera-core' ); ?></span>
													<h6><?php esc_html_e( 'Employer', 'cariera-core' ); ?></h6>
												</div>
											</label>
										</div>
									</div>
								</div>
								<?php
							}
						}
						?>

						<div class="form-fields">
							<fieldset>
								<label for="first-name"><?php esc_html_e( 'First Name', 'cariera-core' ); ?></label>
								<input name="first-name" type="text" id="first-name" value="<?php echo esc_attr( $current_user->user_firstname ); ?>" autocomplete="given-name" />
							</fieldset>
							<fieldset>
								<label for="last-name"><?php esc_html_e( 'Last Name', 'cariera-core' ); ?></label>
								<input name="last-name" type="text" id="last-name" value="<?php echo esc_attr( $current_user->user_lastname ); ?>" />
							</fieldset>

							<fieldset>
								<label for="user_email"><?php esc_html_e( 'E-mail', 'cariera-core' ); ?></label>
								<input name="user_email" type="text" id="user_email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
							</fieldset>
							<fieldset>
								<label for="phone"><?php esc_html_e( 'Phone Number', 'cariera-core' ); ?></label>
								<input name="phone" type="text" id="phone" value="<?php the_author_meta( 'phone', $current_user->ID ); ?>" autocomplete="tel" />
							</fieldset>
						</div>

						<button type="submit" class="btn btn-main btn-effect"><?php esc_html_e( 'Save Changes', 'cariera-core' ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- End of Edit My Profile -->

	<!-- 2nd Column -->
	<div class="col-lg-5 col-md-12">
		<!-- Start of Change Password Form -->
		<div class="dashboard-card-box">
			<h2 class="title"><?php esc_html_e( 'Change Password', 'cariera-core' ); ?></h2>

			<div class="dashboard-card-box-inner">
				<form name="change_password_form" class="change-pass-form" method="post">
					<div class="form-group password-handling">
						<label for="current_password"><?php esc_html_e( 'Current Password', 'cariera-core' ); ?></label>
						<input id="current_password" name="current_password" type="password">
						<i class="lar la-eye"></i>
					</div>

					<div class="form-group password-handling">
						<label for="new_password"><?php esc_html_e( 'New Password', 'cariera-core' ); ?></label>
						<input id="new_password" name="new_password" type="password">
						<i class="lar la-eye"></i>
					</div>

					<div class="form-group password-handling">
						<label for="confirm_password"><?php esc_html_e( 'Confirm New Password', 'cariera-core' ); ?></label>
						<input id="confirm_password" name="confirm_password" type="password">
						<i class="lar la-eye"></i>
					</div>

					<div class="form-group">
						<button type="submit" class="btn btn-main btn-effect" /><?php esc_html_e( 'Change Password', 'cariera-core' ); ?></button>
					</div>
				</form>
			</div>
		</div>
		<!-- End of Change Password Form -->

		<?php if ( ! current_user_can( 'manage_options' ) && apply_filters( 'cariera_delete_account', '__return_true' ) ) { ?>
			<!-- Start of Delete Account -->
			<div class="dashboard-card-box delete-account">
				<h4 class="title"><?php esc_html_e( 'Delete Account', 'cariera-core' ); ?></h4>

				<div class="dashboard-card-box-inner">
					<form name="delete-account" class="delete-account-form" method="post">
						<p><?php esc_html_e( 'Before you delete your account, remember that all of your data will also be deleted. This action can not be undone!', 'cariera-core' ); ?></p>

						<div class="form-group password-handling">
							<label for="current_pass"><?php esc_html_e( 'Current Password', 'cariera-core' ); ?></label>
							<input name="current_pass" id="current_pass" type="password">
							<i class="lar la-eye"></i>
						</div>

						<div class="form-group">
							<?php wp_nonce_field( 'cariera_delete_account', 'nonce' ); ?>
							<button type="submit" class="btn btn-main btn-effect" /><?php esc_html_e( 'Delete Account', 'cariera-core' ); ?></button>
						</div>
					</form>
				</div>
			</div>
			<!-- End of Delete Account -->
		<?php } ?>

	</div>
</div>
