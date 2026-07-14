<?php
/**
 * Profile section of the dashboard menu.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/profile.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user        = wp_get_current_user();
$user_id     = get_current_user_id();
$user_avatar = get_avatar( $user_id, 80 );
$full_name   = trim( esc_html( $user->first_name . ' ' . $user->last_name ) );

// Map user roles to labels.
$role_labels = [
	'administrator' => esc_html__( 'Administrator', 'cariera' ),
	'employer'      => esc_html__( 'Employer', 'cariera' ),
	'candidate'     => esc_html__( 'Candidate', 'cariera' ),
];

// Get primary user role.
$user_role  = $user->roles[0] ?? '';
$role_label = $role_labels[ $user_role ] ?? esc_html( ucfirst( $user_role ) );
?>

<!-- Dashboard collapse button -->
<span class="collaps-dashboard">
	<i class="las la-caret-square-left" aria-hidden="true"></i>
</span>

<!-- User profile box -->
<div class="dashboard-profile-box">
	<span class="avatar-img">
		<div class="login-status" aria-hidden="true"></div>
		<?php echo wp_kses_post( $user_avatar ); ?>
	</span>
	<span class="fullname"><?php echo esc_html( $full_name ); ?></span>
	<span class="user-role"><?php echo esc_html( $role_label ); ?></span>
</div>
