<?php
/**
 * Cariera Dashboard - Expiring Listings template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard/expiring-listings.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.8.0
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager' ) ) {
	return;
}

$listings = \Cariera_Core\Core\Job_Manager::get_dashboard_expiring_listings();
?>

<div class="dashboard-card-box dashboard-content-expiring-listings">
	<div class="dashboard-card-title">
		<h3 class="title"><?php esc_html_e( 'Expiring Soon Listings', 'cariera-core' ); ?></h3>
	</div>

	<div class="dashboard-card-box-inner">
		<ul class="cariera-dashboard-list expiring-listings">
			<?php
			if ( $listings ) {
				foreach ( $listings as $listing ) {
					?>
					<li>
						<i class="<?php echo esc_attr( $listing['icon'] ); ?>"></i>
						<div class="content">
							<a href="<?php echo esc_url( $listing['url'] ); ?>" target="_blank"><h6 class="listing-title"><?php echo esc_html( $listing['title'] ); ?></h6></a>
							<div class="listing-expires"><small><?php echo \WP_Job_Manager\UI\UI_Elements::rel_time( $listing['expiration'], esc_html__( 'Expires in %s', 'cariera-core' ) ); ?></small></div>
						</div>
					</li>
					<?php
				}
			} else {
				?>
				<li><?php esc_html_e( 'No listings are expiring soon.', 'cariera-core' ); ?></li>
			<?php } ?>
		</ul>
	</div>
</div>
