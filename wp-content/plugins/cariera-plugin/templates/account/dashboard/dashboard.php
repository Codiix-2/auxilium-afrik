<?php
/**
 * Cariera Dashboard template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Dashboard Cards.
cariera_get_template_part( 'account/dashboard/cards' );
?>

<!-- Start of Charts & Packages -->
<div class="row dashboard-main">
	<div class="col-lg-8 col-md-12">
		<?php
		cariera_get_template_part( 'account/dashboard/views-charts' );
		cariera_get_template_part( 'account/dashboard/active-packages' );
		?>
	</div>
	
	<div class="col-lg-4 col-md-12">
		<?php
		cariera_get_template_part( 'account/dashboard/expiring-listings' );
		do_action( 'cariera_dashboard_sidebar_expiring' );
		cariera_get_template_part( 'account/dashboard/applications' );
		?>
	</div>
</div>
