<?php
/**
 * Main menu of the dashboard menu.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/main-menu.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.9.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$user = wp_get_current_user();

// Pages for the Dashboard Main Menu.
$dashboard_page      = apply_filters( 'cariera_dashboard_main_dashboard_page', get_option( 'cariera_dashboard_page' ) );
$employer_dashboard  = apply_filters( 'cariera_dashboard_employer_dashboard_page', get_option( 'job_manager_job_dashboard_page_id' ) );
$company_dashboard   = apply_filters( 'cariera_dashboard_company_dashboard_page', get_option( 'cariera_company_dashboard_page' ) );
$candidate_dashboard = apply_filters( 'cariera_dashboard_candidate_dashboard_page', get_option( 'resume_manager_candidate_dashboard_page_id' ) );
$job_alerts          = apply_filters( 'cariera_dashboard_job_alerts_page', get_option( 'job_manager_alerts_page_id' ) );
$resume_alerts       = apply_filters( 'cariera_dashboard_resume_alerts_page', get_option( 'job_manager_resume_alerts_page_id' ) );
$bookmarks           = apply_filters( 'cariera_dashboard_bookmarks_page', get_option( 'cariera_bookmarks_page' ) );
$applied_jobs        = apply_filters( 'cariera_dashboard_past_applications_page', get_option( 'cariera_past_applications_page' ) );
$user_packages       = apply_filters( 'cariera_dashboard_user_packages_page', get_option( 'cariera_user_packages_page' ) );

if ( \Cariera\wc_is_activated() ) {
	$orders = wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) );
}
?>

<ul class="dashboard-nav-main" data-submenu-title="<?php esc_attr_e( 'Main', 'cariera' ); ?>">
	<li class="dashboard-menu-item_dashboard <?php echo absint( $dashboard_page ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
		<a href="<?php echo esc_url( get_permalink( $dashboard_page ) ); ?>">
			<i class="las la-cog"></i><span><?php esc_html_e( 'Dashboard', 'cariera' ); ?></span>
		</a>
	</li>

	<?php
	// Employer Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() ) {
		if ( in_array( 'employer', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_jobs <?php echo absint( $employer_dashboard ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $employer_dashboard ) ); ?>">
					<i class="las la-briefcase"></i><span><?php esc_html_e( 'My Jobs', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	// Company Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() && \Cariera\company_manager_is_activated() ) {
		if ( in_array( 'employer', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_companies <?php echo absint( $company_dashboard ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $company_dashboard ) ); ?>">
					<i class="lar la-building"></i><span><?php esc_html_e( 'My Companies', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	/**
	 * Action to add more listing menu items via third-party plugins
	 *
	 * @param WP_USER $user
	 * @param WP_POST $post
	 */
	do_action( 'cariera_dashboard_listing_menu_item', $user, $post );

	// Candidate Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() && \Cariera\wp_resume_manager_is_activated() ) {
		if ( in_array( 'candidate', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_resumes <?php echo absint( $candidate_dashboard ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $candidate_dashboard ) ); ?>">
					<i class="las la-user-tie"></i><span><?php esc_html_e( 'My Resumes', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	// Job Alerts Link.
	if ( \Cariera\wp_job_manager_is_activated() && ( class_exists( 'WP_Job_Manager_Alerts' ) || ( class_exists( '\Cariera_Addons\Core\Job_Alerts\Job_Alerts' ) && \Cariera_Addons\Helpers::core_feature_is_enabled( 'job-alerts' ) ) ) ) {
		if ( in_array( 'candidate', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_job-alerts <?php echo absint( $job_alerts ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $job_alerts ) ); ?>">
					<i class="las la-bell"></i><span><?php esc_html_e( 'Job Alerts', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	// Resume Alerts Link.
	if ( \Cariera\wp_job_manager_is_activated() && class_exists( 'WP_Job_Manager_Resume_Alerts' ) ) {
		if ( in_array( 'employer', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_resume-alerts <?php echo absint( $resume_alerts ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $resume_alerts ) ); ?>">
					<i class="las la-bell"></i><span><?php esc_html_e( 'Resume Alerts', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	// Bookmarks Link.
	if ( \Cariera\wp_job_manager_is_activated() && ( class_exists( 'WP_Job_Manager_Bookmarks' ) || ( class_exists( '\Cariera_Addons\Core\Bookmarks\Bookmarks' ) && \Cariera_Addons\Helpers::core_feature_is_enabled( 'bookmarks' ) ) ) ) {
		?>
		<li class="dashboard-menu-item_bookmarks <?php echo absint( $bookmarks ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo esc_url( get_permalink( $bookmarks ) ); ?>">
				<i class="lar la-heart"></i><span><?php esc_html_e( 'My Bookmarks', 'cariera' ); ?></span>
			</a>
		</li>
		<?php
	}

	// Applied Jobs Link.
	if ( \Cariera\wp_job_manager_is_activated() && class_exists( 'WP_Job_Manager_Applications' ) ) {
		if ( in_array( 'candidate', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_applied-jobs <?php echo absint( $applied_jobs ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $applied_jobs ) ); ?>">
					<i class="las la-pencil-alt"></i><span><?php esc_html_e( 'Applied Jobs', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	// User Packages.
	if ( \Cariera\wp_job_manager_is_activated() && ( class_exists( 'WC_Paid_Listings' ) || class_exists( 'WP_Job_Manager_Packages' ) ) ) {
		?>
		<li class="dashboard-menu-item_user-packages <?php echo absint( $user_packages ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo esc_url( get_permalink( $user_packages ) ); ?>">
				<i class="las la-box"></i><span><?php esc_html_e( 'Packages', 'cariera' ); ?></span>
			</a>
		</li>
		<?php
	}

	// Orders Link.
	if ( \Cariera\wc_is_activated() ) {
		?>
		<li class="dashboard-menu-item_orders <?php echo is_wc_endpoint_url( 'orders' ) ? esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo esc_url( $orders ); ?>">
				<i class="lar la-credit-card"></i><span><?php esc_html_e( 'Orders', 'cariera' ); ?></span>
			</a>
		</li>
	<?php } ?>

	<?php do_action( 'cariera_dashboard_main_nav_end' ); ?>
</ul>
