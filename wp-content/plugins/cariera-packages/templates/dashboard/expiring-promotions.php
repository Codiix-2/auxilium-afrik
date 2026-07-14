<?php
/**
 * Cariera Dashboard: Expiring Promotions template
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/dashboard/expiring-promotions.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.9
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="dashboard-card-box cariera-packages dashboard-content-packages">
	<div class="dashboard-card-title">
		<h3 class="title"><?php esc_html_e( 'Expiring Promotions', 'cariera-packages' ); ?></h3>
	</div>

	<div class="dashboard-card-box-inner">
		<ul class="cariera-dashboard-list expiring-promotions">
			<?php
			if ( $promotions ) {
				foreach ( $promotions as $promotion ) {
					$promotion        = get_post( $promotion );
					$promotion_expire = $promotion->cariera_packages_promotion_expires;
					$listing          = get_post( $promotion->cariera_packages_promoted_listing_id );
					?>

					<li>
						<i class="las la-bolt"></i>
						<div class="content">
							<a href="<?php echo esc_url( get_permalink( $listing->ID ) ); ?>" target="_blank"><h6 class="listing-title"><?php echo esc_html( $listing->post_title ); ?></h6></a>
							<div class="listing-expires"><small><?php echo \WP_Job_Manager\UI\UI_Elements::rel_time( $promotion_expire, esc_html__( 'Expires in %s', 'cariera-packages' ) ); ?></small></div>
						</div>
					</li>
					<?php
				}
			} else {
				?>
				<li><?php esc_html_e( 'There are no expiring promotions.', 'cariera-packages' ); ?></li>
			<?php } ?>
		</ul>
	</div>
</div>
