<?php
/**
 * Cariera Dashboard - Active Packages template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard/active-packages.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager' ) || ! class_exists( 'WooCommerce' ) ) {
	return;
}

$job_packages               = [];
$resume_packages            = [];
$job_visibility_packages    = [];
$resume_visibility_packages = [];
$packages                   = 0;
$displayed_packages         = 0;
$max_packages               = 3;
$user_packages_url          = get_permalink( get_option( 'cariera_user_packages_page' ) );

if ( class_exists( 'WC_Paid_Listings' ) ) {
	$job_packages    = wc_paid_listings_get_user_packages( get_current_user_id(), 'job_listing' );
	$resume_packages = wc_paid_listings_get_user_packages( get_current_user_id(), 'resume' );
	$packages       += count( $job_packages ) + count( $resume_packages );
}

if ( class_exists( 'WP_Job_Manager_Packages' ) ) {
	$job_visibility_packages    = WPJM_Pack_User::get_all( get_current_user_id(), 'job_listing' );
	$resume_visibility_packages = WPJM_Pack_User::get_all( get_current_user_id(), 'resume' );
	$packages                  += count( $job_visibility_packages ) + count( $resume_visibility_packages );
}

// Apply filter to the packages array.
$packages = apply_filters( 'cariera_dashboard_active_packages', $packages );
?>

<div class="dashboard-card-box dashboard-content-packages">
	<div class="dashboard-card-title">
		<h3 class="title"><?php esc_html_e( 'Active Packages', 'cariera-core' ); ?></h3>
		<span class="item-count"><?php echo esc_html( $packages ); ?></span>
	</div>

	<div class="dashboard-card-box-inner">
		<ul class="cariera-dashboard-list listing-packages">
			<?php
			if ( $packages > 0 ) {

				// Showing all the Job Packages.
				foreach ( $job_packages as $job_package ) {
					if ( $displayed_packages >= $max_packages ) {
						break;
					}
					$job_package = wc_paid_listings_get_package( $job_package );
					?>

					<li class="package">
						<i class="las la-rocket"></i>

						<div class="content">
							<h6 class="package-title"><?php echo esc_html( $job_package->get_title() ); ?></h6>
							<p><?php printf( esc_html__( 'You have %s job listings left that you can post.', 'cariera-core' ), $job_package->get_limit() ? absint( $job_package->get_limit() - $job_package->get_count() ) : esc_html__( 'Unlimited', 'cariera-core' ) ); ?></p>
							<p><?php printf( esc_html__( 'Job listing duration: %s', 'cariera-core' ), $job_package->get_duration() ? sprintf( _n( '%d day', '%d days', $job_package->get_duration(), 'cariera-core' ), $job_package->get_duration() ) : '-' ); ?></p>
						</div>
					</li>
					<?php
					++$displayed_packages;
				}

				// Showing all the Resume Packages.
				foreach ( $resume_packages as $resume_package ) {
					if ( $displayed_packages >= $max_packages ) {
						break;
					}
					$resume_package = wc_paid_listings_get_package( $resume_package );
					?>

					<li class="package">
						<i class="las la-rocket"></i>
						<div class="content">
							<h6 class="package-title"><?php echo esc_html( $resume_package->get_title() ); ?></h6>
							<p><?php printf( esc_html__( 'You have %s resumes left that you can post.', 'cariera-core' ), $resume_package->get_limit() ? absint( $resume_package->get_limit() - $resume_package->get_count() ) : esc_html__( 'Unlimited', 'cariera-core' ) ); ?></p>
							<p><?php printf( esc_html__( 'Resume listing duration: %s', 'cariera-core' ), $resume_package->get_duration() ? sprintf( _n( '%d day', '%d days', $resume_package->get_duration(), 'cariera-core' ), $resume_package->get_duration() ) : '-' ); ?></p>
						</div>
					</li>
					<?php
					++$displayed_packages;
				}

				// Showing all the Job Visbility Packages.
				foreach ( $job_visibility_packages as $job_visibility_package ) {
					if ( $displayed_packages >= $max_packages ) {
						break;
					}
					$job_package = job_manager_packages_get_user_package( $job_visibility_package );
					?>

					<li class="package">
						<i class="lar la-eye"></i>

						<div class="content">
							<h6 class="package-title"><?php echo esc_html( $job_package->get_title() ); ?></h6>
							
							<?php if ( $job_package->allow_enabled( 'view' ) ) { ?>
								<p><?php printf( esc_html__( 'You can view %s more job listings.', 'cariera-core' ), $job_package->get_limit( 'view' ) ? absint( $job_package->get_limit( 'view' ) - $job_package->get_used( 'view' ) ) : esc_html__( 'Unlimited', 'cariera-core' ) ); ?></p>
							<?php } ?>

							<?php if ( $job_package->allow_enabled( 'apply' ) ) { ?>
								<p><?php printf( esc_html__( 'You can apply on %s more job listings.', 'cariera-core' ), $job_package->get_limit( 'apply' ) ? absint( $job_package->get_limit( 'apply' ) - $job_package->get_used( 'apply' ) ) : esc_html__( 'Unlimited', 'cariera-core' ) ); ?></p>
							<?php } ?>

							<?php if ( $job_package->allow_enabled( 'contact' ) ) { ?>
								<p><?php printf( esc_html__( 'You can contact %s more job listings.', 'cariera-core' ), $job_package->get_limit( 'contact' ) ? absint( $job_package->get_limit( 'contact' ) - $job_package->get_used( 'contact' ) ) : esc_html__( 'Unlimited', 'cariera-core' ) ); ?></p>
							<?php } ?>
						</div>
					</li>
					<?php
					++$displayed_packages;
				}

				// Showing all the Resume Visbility Packages.
				foreach ( $resume_visibility_packages as $resume_visibility_package ) {
					if ( $displayed_packages >= $max_packages ) {
						break;
					}
					$resume_package = job_manager_packages_get_user_package( $resume_visibility_package );
					?>

					<li class="package">
						<i class="lar la-eye"></i>

						<div class="content">
							<h6 class="package-title"><?php echo esc_html( $resume_package->get_title() ); ?></h6>
							
							<?php if ( $resume_package->allow_enabled( 'view' ) ) { ?>
								<p><?php printf( esc_html__( 'You can view %s more resumes.', 'cariera-core' ), $resume_package->get_limit( 'view' ) ? absint( $resume_package->get_limit( 'view' ) - $resume_package->get_used( 'view' ) ) : esc_html__( 'Unlimited', 'cariera-core' ) ); ?></p>
							<?php } ?>

							<?php if ( $resume_package->allow_enabled( 'contact' ) ) { ?>
								<p><?php printf( esc_html__( 'You can contact %s more resumes.', 'cariera-core' ), $resume_package->get_limit( 'contact' ) ? absint( $resume_package->get_limit( 'contact' ) - $resume_package->get_used( 'contact' ) ) : esc_html__( 'Unlimited', 'cariera-core' ) ); ?></p>
							<?php } ?>
						</div>
					</li>
					<?php
					++$displayed_packages;
				}

				do_action_ref_array( 'cariera_dashboard_active_packages_content', [ &$displayed_packages, $max_packages ] );

				if ( $displayed_packages >= $max_packages ) {
					?>
					<li class="action">
						<a href="<?php echo esc_url( $user_packages_url ); ?>" class="btn btn-main"><?php esc_html_e( 'View all active packages', 'cariera-core' ); ?></a>
					</li>
					<?php
				}
			} else {
				?>
				<li><?php esc_html_e( 'No packages have been bought or all packages have been used.', 'cariera-core' ); ?></li>
			<?php } ?>
		</ul>
	</div>
</div>