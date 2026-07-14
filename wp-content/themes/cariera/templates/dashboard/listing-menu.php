<?php
/**
 * Listing menu of the dashboard menu.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/listing-menu.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.9.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$user = wp_get_current_user();

// Pages for the Dashboard Listing Menu.
$post_job     = apply_filters( 'cariera_dashboard_job_submit_page', get_option( 'job_manager_submit_job_form_page_id' ) );
$post_company = apply_filters( 'cariera_dashboard_company_submit_page', get_option( 'cariera_submit_company_page' ) );
$post_resume  = apply_filters( 'cariera_dashboard_resume_submit_page', get_option( 'resume_manager_submit_resume_form_page_id' ) );
?>

<ul class="dashboard-nav-listing" data-submenu-title="<?php esc_attr_e( 'Listing', 'cariera' ); ?>">

	<?php
	// Post Job Link.
	if ( \Cariera\wp_job_manager_is_activated() ) {
		if ( in_array( 'employer', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_post-job <?php echo absint( $post_job ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $post_job ) ); ?>">
					<i class="las la-plus-circle"></i><span><?php esc_html_e( 'Post Job', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	// Submit Company Link.
	if ( \Cariera\wp_job_manager_is_activated() && \Cariera\company_manager_is_activated() ) {
		if ( in_array( 'employer', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_submit-company <?php echo absint( $post_company ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $post_company ) ); ?>">
					<i class="las la-plus-circle"></i><span><?php esc_html_e( 'Submit Company', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	/**
	 * Action to add submission listing menu items via third-party plugins
	 *
	 * @param WP_USER $user
	 * @param WP_POST $post
	 */
	do_action( 'cariera_dashboard_submission_menu_item', $user, $post );

	// Submit Resume Link.
	if ( \Cariera\wp_job_manager_is_activated() && \Cariera\wp_resume_manager_is_activated() ) {
		if ( in_array( 'candidate', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_submit-resume <?php echo absint( $post_resume ) === $post->ID ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $post_resume ) ); ?>">
					<i class="las la-plus-circle"></i><span><?php esc_html_e( 'Submit Resume', 'cariera' ); ?></span>
				</a>
			</li>
			<?php
		}
	}

	do_action( 'cariera_dashboard_listing_nav_end' );
	?>
</ul>
