<?php
/**
 * Header - User Menu Items template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/extra/user-menu-items.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.9
 * @version     1.8.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user        = wp_get_current_user();
$user_id             = get_current_user_id();
$user_img            = get_avatar( get_the_author_meta( 'ID', $user_id ), 40 );
$dashboard_title     = \Cariera\get_page_by_title( 'Dashboard' );
$dashboard_page      = apply_filters( 'cariera_user_menu_dashboard_page_id', get_option( 'cariera_dashboard_page' ) );
$employer_dashboard  = apply_filters( 'cariera_user_menu_employer_dashboard_page_id', get_option( 'job_manager_job_dashboard_page_id' ) );
$company_dashboard   = apply_filters( 'cariera_user_menu_company_dashboard_page_id', get_option( 'cariera_company_dashboard_page' ) );
$candidate_dashboard = apply_filters( 'cariera_user_menu_candidate_dashboard_page_id', get_option( 'resume_manager_candidate_dashboard_page_id' ) );
$user_packages       = apply_filters( 'cariera_user_menu_user_packages_page_id', get_option( 'cariera_user_packages_page' ) );
$profile             = apply_filters( 'cariera_user_menu_profile_dashboard_page_id', get_option( 'cariera_dashboard_profile_page' ) );
$roles               = $current_user->roles;
$role                = array_shift( $roles );

if ( \Cariera\wc_is_activated() ) {
	$orders = wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) );
}

if ( $dashboard_page ) {
	$account_link = get_permalink( $dashboard_page );
} else {
	$account_link = get_permalink( $dashboard_title );
}
?>

<ul class="account-nav">
	<li class="header-widget-menu-item_dashboard">
		<a href="<?php echo esc_url( $account_link ); ?>">
			<i class="las la-cog"></i><?php esc_html_e( 'Dashboard', 'cariera' ); ?>
		</a>
	</li>

	<?php
	// Employer Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() ) {
		if ( in_array( 'employer', (array) $current_user->roles, true ) || in_array( 'administrator', (array) $current_user->roles, true ) ) {
			?>
			<li class="header-widget-menu-item_employer-dashboard">
				<a href="<?php echo esc_url( get_permalink( $employer_dashboard ) ); ?>">
					<i class="las la-briefcase"></i><?php esc_html_e( 'My Jobs', 'cariera' ); ?>
				</a>
			</li>
			<?php
		}
	}

	// Company Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() && \Cariera\company_manager_is_activated() ) {
		if ( in_array( 'employer', (array) $current_user->roles, true ) || in_array( 'administrator', (array) $current_user->roles, true ) ) {
			?>
			<li class="header-widget-menu-item_company-dashboard">
				<a href="<?php echo esc_url( get_permalink( $company_dashboard ) ); ?>">
					<i class="lar la-building"></i><?php esc_html_e( 'My Companies', 'cariera' ); ?>
				</a>
			</li>
			<?php
		}
	}

	// Candidate Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() && \Cariera\wp_resume_manager_is_activated() ) {
		if ( in_array( 'candidate', (array) $current_user->roles, true ) || in_array( 'administrator', (array) $current_user->roles, true ) ) {
			?>
			<li class="header-widget-menu-item_candidate-dashboard">
				<a href="<?php echo esc_url( get_permalink( $candidate_dashboard ) ); ?>">
					<i class="las la-user-tie"></i><?php esc_html_e( 'My Resumes', 'cariera' ); ?>
				</a>
			</li>
			<?php
		}
	}

	// User Packages Link.
	if ( \Cariera\wp_job_manager_is_activated() && ( class_exists( 'WC_Paid_Listings' ) || class_exists( 'WP_Job_Manager_Packages' ) ) ) {
		?>
		<li class="header-widget-menu-item_user-packages">
			<a href="<?php echo esc_url( get_permalink( $user_packages ) ); ?>">
				<i class="las la-box"></i><?php esc_html_e( 'Packages', 'cariera' ); ?>
			</a>            
		</li>
		<?php
	}

	// Orders Link.
	if ( \Cariera\wc_is_activated() ) {
		?>
		<li class="header-widget-menu-item_orders">
			<a href="<?php echo esc_url( $orders ); ?>">
				<i class="lar la-credit-card"></i><?php esc_html_e( 'Orders', 'cariera' ); ?>
			</a>            
		</li>
		<?php
	}

	// My Profile.
	?>
	<li class="header-widget-menu-item_my-profile">
		<a href="<?php echo esc_url( get_permalink( $profile ) ); ?>">
			<i class="las la-user-cog"></i><?php esc_html_e( 'My Profile', 'cariera' ); ?>
		</a>
	</li>

	<?php do_action( 'cariera_header_widget_nav_end' ); ?>
</ul>
