<?php
/**
 * Cariera User Packages template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/user-packages.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.4
 * @version     1.8.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-dashboards' );

$job_packages               = [];
$resume_packages            = [];
$job_visibility_packages    = [];
$resume_visibility_packages = [];
$packages                   = 0;

if ( class_exists( 'WC_Paid_Listings' ) ) {
	$job_packages    = wc_paid_listings_get_user_packages( get_current_user_id(), 'job_listing', true );
	$resume_packages = wc_paid_listings_get_user_packages( get_current_user_id(), 'resume', true );
	$packages       += count( $job_packages ) + count( $resume_packages );
}

if ( class_exists( 'WP_Job_Manager_Packages' ) ) {
	$job_visibility_packages    = WPJM_Pack_User::get_all( get_current_user_id(), 'job_listing' );
	$resume_visibility_packages = WPJM_Pack_User::get_all( get_current_user_id(), 'resume' );
	$packages                   = $packages + count( $job_visibility_packages ) + count( $resume_visibility_packages );
}

// Apply filter to the packages array.
$packages = apply_filters( 'cariera_user_packages', $packages );

// Package exists.
if ( $packages > 0 ) { ?>
	<div class="table-responsive">
		<table class="cariera-wpjm-dashboard job-manager-job-reports job-manager-user-packages">
			<thead>
				<tr>
					<th class="package-order-id"><?php esc_html_e( 'Order ID', 'cariera-core' ); ?></th>
					<th class="package-title"><?php esc_html_e( 'Package', 'cariera-core' ); ?></th>
					<th class="package-type"><?php esc_html_e( 'Package Type', 'cariera-core' ); ?></th>
					<th class="package-status"><?php esc_html_e( 'Status', 'cariera-core' ); ?></th>
				</tr>
			</thead>
			<tbody>

				<?php
				// Showing all the Job Packages.
				foreach ( $job_packages as $job_package ) {
					$job_package = wc_paid_listings_get_package( $job_package );

					?>

					<tr>
						<td class="package-order-id"><?php echo esc_html( $job_package->get_order_id() ); ?></td>
						<td class="package-title">
							<h6><?php echo esc_html( $job_package->get_title() ); ?></h6>

							<p><?php wp_kses_post( printf( __( 'You have <span>%s</span> job listings left that you can post.', 'cariera-core' ), $job_package->get_limit() ? absint( $job_package->get_limit() - $job_package->get_count() ) : esc_html__( 'Unlimited', 'cariera-core' ) ) ); ?></p>
							<p><?php wp_kses_post( printf( __( 'Job listing duration: <span>%s</span>', 'cariera-core' ), $job_package->get_duration() ? sprintf( _n( '%d day', '%d days', $job_package->get_duration(), 'cariera-core' ), $job_package->get_duration() ) : '-' ) ); ?></p>						
						</td>

						<td class="package-type"><?php esc_html_e( 'Job Submission', 'cariera-core' ); ?></td>

						<td class="package-status">
							<?php
							if ( $job_package->get_count() >= $job_package->get_limit() && $job_package->get_limit() != 0 ) {
								echo '<span class="status used">' . esc_html__( 'Used', 'cariera-core' ) . '</span>';
							} else {
								echo '<span class="status active">' . esc_html__( 'Active', 'cariera-core' ) . '</span>';
							}
							?>
						</td>
					</tr>
					<?php
				}

				// Showing all the Resume Packages.
				foreach ( $resume_packages as $resume_package ) {
					$resume_package = wc_paid_listings_get_package( $resume_package );
					?>
					<tr>
						<td class="package-order-id"><?php echo esc_html( $resume_package->get_order_id() ); ?></td>
						<td class="package-title">
							<h6><?php echo esc_html( $resume_package->get_title() ); ?></h6>

							<p><?php wp_kses_post( printf( __( 'You have <span>%s</span> resumes left that you can post.', 'cariera-core' ), $resume_package->get_limit() ? absint( $resume_package->get_limit() - $resume_package->get_count() ) : esc_html__( 'Unlimited', 'cariera-core' ) ) ); ?></p>
							<p><?php wp_kses_post( printf( __( 'Resume listing duration: <span>%s</span>', 'cariera-core' ), $resume_package->get_duration() ? sprintf( _n( '%d day', '%d days', $resume_package->get_duration(), 'cariera-core' ), $resume_package->get_duration() ) : '-' ) ); ?></p>		
						</td>

						<td class="package-type"><?php esc_html_e( 'Resume Submission', 'cariera-core' ); ?></td>
						<td class="package-status">
							<?php
							$resume_package_used = $resume_package->get_count() >= $resume_package->get_limit() && $resume_package->get_limit() !== 0;

							if ( $resume_package_used ) {
								echo '<span class="status used">' . esc_html__( 'Used', 'cariera-core' ) . '</span>';
							} else {
								echo '<span class="status active">' . esc_html__( 'Active', 'cariera-core' ) . '</span>';
							}
							?>
						</td>
					</tr>
					<?php
				}

				// Showing all the Job Visbility Packages.
				foreach ( $job_visibility_packages as $job_visibility_package ) {
					$job_package = job_manager_packages_get_user_package( $job_visibility_package );
					// Get limits and usage for view, apply, and contact.
					$view_enabled = $job_package->allow_enabled( 'view' );
					$view_limit   = $job_package->get_limit( 'view' );
					$view_used    = $job_package->get_used( 'view' );

					$apply_enabled = $job_package->allow_enabled( 'apply' );
					$apply_limit   = $job_package->get_limit( 'apply' );
					$apply_used    = $job_package->get_used( 'apply' );
					?>

					<tr>
						<td class="package-order-id"><?php echo esc_html( $job_visibility_package->order_id ); ?></td>
						<td class="package-title">
							<h6><?php echo esc_html( $job_package->get_title() ); ?></h6>

							<?php if ( $view_enabled ) { ?>
								<p><?php wp_kses_post( printf( __( 'You can view <span>%s</span> more job listings.', 'cariera-core' ), $view_limit ? absint( $view_limit - $view_used ) : esc_html__( 'Unlimited', 'cariera-core' ) ) ); ?></p>
							<?php } ?>

							<?php if ( $apply_enabled ) { ?>
								<p><?php wp_kses_post( printf( __( 'You can apply on <span>%s</span> more job listings.', 'cariera-core' ), $apply_limit ? absint( $apply_limit - $apply_used ) : esc_html__( 'Unlimited', 'cariera-core' ) ) ); ?></p>
							<?php } ?>
						</td>

						<td class="package-type"><?php esc_html_e( 'Job Package', 'cariera-core' ); ?></td>
						<td class="package-status">
							<?php
							// Determine if all limits have been used up.
							$all_views_used   = $view_enabled && $view_used >= $view_limit;
							$all_applies_used = $apply_enabled && $apply_used >= $apply_limit;

							// Check if all package limits have been fully used.
							if ( $all_views_used && $all_applies_used ) {
								echo '<span class="status used">' . esc_html__( 'Used', 'cariera-core' ) . '</span>';
							} else {
								echo '<span class="status active">' . esc_html__( 'Active', 'cariera-core' ) . '</span>';
							}
							?>
						</td>
					</tr>
					<?php
				}

				// Showing all the Resume Visbility Packages.
				foreach ( $resume_visibility_packages as $resume_visibility_package ) {
					$resume_package = job_manager_packages_get_user_package( $resume_visibility_package );

					// Get limits and usage for view, apply, and contact.
					$view_enabled = $resume_package->allow_enabled( 'view' );
					$view_limit   = $resume_package->get_limit( 'view' );
					$view_used    = $resume_package->get_used( 'view' );

					$view_name_enabled = $resume_package->allow_enabled( 'view_name' );
					$view_name_limit   = $resume_package->get_limit( 'view_name' );
					$view_name_used    = $resume_package->get_used( 'view_name' );

					$contact_enabled = $resume_package->allow_enabled( 'contact' );
					$contact_limit   = $resume_package->get_limit( 'contact' );
					$contact_used    = $resume_package->get_used( 'contact' );
					?>

					<tr>
						<td class="package-order-id"><?php echo esc_html( $resume_visibility_package->order_id ); ?></td>
						<td class="package-title">
							<h6><?php echo esc_html( $resume_package->get_title() ); ?></h6>

							<?php if ( $view_enabled ) { ?>
								<p><?php wp_kses_post( printf( __( 'You can view <span>%s</span> more resumes.', 'cariera-core' ), $view_limit ? absint( $view_limit - $view_used ) : esc_html__( 'Unlimited', 'cariera-core' ) ) ); ?></p>
							<?php } ?>

							<?php if ( $view_name_enabled ) { ?>
								<p><?php wp_kses_post( printf( __( 'You can view <span>%s</span> more resume names.', 'cariera-core' ), $view_name_limit ? absint( $view_name_limit - $view_name_used ) : esc_html__( 'Unlimited', 'cariera-core' ) ) ); ?></p>
							<?php } ?>

							<?php if ( $contact_enabled ) { ?>
								<p><?php wp_kses_post( printf( __( 'You can contact <span>%s</span> more resumes.', 'cariera-core' ), $contact_limit ? absint( $contact_limit - $contact_used ) : esc_html__( 'Unlimited', 'cariera-core' ) ) ); ?></p>
							<?php } ?>
						</td>

						<td class="package-type"><?php esc_html_e( 'Resume Package', 'cariera-core' ); ?></td>
						<td class="package-status">
							<?php
							// Determine if all limits have been used up.
							$all_views_used      = $view_enabled && $view_used >= $view_limit;
							$all_view_names_used = $view_name_enabled && $view_name_used >= $view_name_limit;
							$all_contacts_used   = $contact_enabled && $contact_used >= $contact_limit;

							// Check if all package limits have been fully used.
							if ( $all_views_used && $all_view_names_used && $all_contacts_used ) {
								echo '<span class="status used">' . esc_html__( 'Used', 'cariera-core' ) . '</span>';
							} else {
								echo '<span class="status active">' . esc_html__( 'Active', 'cariera-core' ) . '</span>';
							}
							?>
						</td>
					</tr>
					<?php
				}

				do_action( 'cariera_user_packages_content' );
				?>
			</tbody>
		</table>
	</div>
<?php } else { ?>
	<p class="job-manager-message generic">
		<?php esc_html_e( 'No packages have been bought with this account.', 'cariera-core' ); ?>
	</p>
	<?php
}