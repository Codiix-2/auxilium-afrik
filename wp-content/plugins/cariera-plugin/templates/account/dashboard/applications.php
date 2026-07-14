<?php
/**
 * Cariera Dashboard - Applications template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard/applications.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.8.0
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager' ) || ! class_exists( 'WP_Job_Manager_Applications' ) ) {
	return;
}

$user               = wp_get_current_user();
$dashboard_title    = esc_html__( 'New Applications', 'cariera-core' );
$list_class         = 'new-applications';
$applications       = cariera_check_user_new_applications();
$applications_count = cariera_check_user_new_applications_count();

if ( ! in_array( 'administrator', (array) $user->roles, true ) && ! in_array( 'employer', (array) $user->roles, true ) ) {
	return;
}
?>

<div class="dashboard-card-box dashboard-content-packages">
	<div class="dashboard-card-title">
		<h3 class="title"><?php echo esc_html( $dashboard_title ); ?></h3>
		<span class="item-count"><?php echo esc_html( $applications_count ); ?></span>
	</div>

	<div class="dashboard-card-box-inner">
		<ul class="cariera-dashboard-list <?php echo esc_attr( $list_class ); ?>">
			<?php
			if ( $applications ) {
				foreach ( $applications as $application ) {
					$job_dashboard     = get_option( 'job_manager_job_dashboard_page_id' );
					$job_id            = wp_get_post_parent_id( $application );
					$job               = get_post( $job_id );
					$application       = get_post( $application );
					$job_name          = $application->_job_applied_for;
					$application_title = ! empty( $application->post_title ) ? $application->post_title : esc_html__( 'Untitled Application', 'cariera-core' );
					$application_url   = $job_dashboard ? htmlspecialchars_decode(
						add_query_arg(
							[
								'action' => 'show_applications',
								'job_id' => $job_id,
							],
							get_permalink( $job_dashboard )
						)
					) : '';
					?>

					<li>
						<div class="content">
							<a href="<?php echo esc_url( $application_url ); ?>"><h6 class="listing-title"><?php echo esc_html( $application_title ); ?></h6></a>
							<div class="job-applied">
								<small>
									<?php
									echo wp_kses_post(
										sprintf(
											/* translators: %s: job title */
											__( 'Applied for: <span>%s</span>', 'cariera-core' ),
											$job_name
										)
									);
									?>
								</small>
							</div>
						</div>
					</li>
					<?php
				}
			} else {
				?>
				<li class="no-applications"><?php esc_html_e( 'There are no new applications.', 'cariera-core' ); ?></li>
			<?php } ?>
		</ul>
	</div>
</div>
