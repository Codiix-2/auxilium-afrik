<?php
/**
 * Onboarding: Sidebar
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/sidebar.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<aside class="onboarding-sidebar">
	<div class="widget widget-documentation">
		<h3 class="title"><?php esc_html_e( 'Documentation', 'cariera' ); ?></h3>
		<p><?php esc_html_e( 'Explore our regularly updated knowledge base, designed to assist you in getting started with Cariera.', 'cariera' ); ?></p>
		<a href="https://docs.cariera.cc/" class="btn-main" target="_blank"><?php esc_html_e( 'Read Documentation', 'cariera' ); ?></a>
	</div>

	<div class="widget widget-notice">
		<p><?php esc_html_e( 'If you enjoy Cariera, we kindly ask for your support by rating the theme positively. Your feedback means a lot to us!', 'cariera' ); ?></p>
		<a href="https://1.envato.market/rate" class="btn-link" target="_blank"><?php esc_html_e( 'Rate Cariera', 'cariera' ); ?></a>
	</div>

	<div class="widget widget-notice">
		<p><a target="_blank" href="https://themeforest.net/licenses/standard"><?php esc_html_e( 'One standard license ', 'cariera' ); ?></a><?php printf( esc_html__( 'is valid only for %s. Running multiple websites on a single license is a copyright violation.', 'cariera' ), '<strong>1 WordPress installation</strong>' ); ?></p>
		<a href="https://1.envato.market/cariera" class="btn-link" target="_blank"><?php esc_html_e( 'Buy new license', 'cariera' ); ?></a>
	</div>

	<div class="widget widget-notice">
		<p><?php esc_html_e( 'Would you like to customize Cariera to better suit your needs? Don\'t hesitate to open a custom work ticket, and we can discuss all details.', 'cariera' ); ?></p>
		<a href="https://support.gnodesign.com" class="btn-link" target="_blank"><?php esc_html_e( 'Custom Work Ticket', 'cariera' ); ?></a>
	</div>

	<div class="widget">
		<h3 class="title"><?php esc_html_e( 'Mobile App', 'cariera' ); ?></h3>
		<a href="https://1.envato.market/cariera-flutter" target="_blank"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/flutter-app.jpg' ); ?>"></a>
	</div>

	<div class="widget">
		<h3 class="title"><?php esc_html_e( 'Recommended Hosting', 'cariera' ); ?></h3>
		<a href="https://www.cloudways.com/en/wordpress-hosting.php?id=759820&amp;a_bid=19515e01" target="_blank"><img src="//www.cloudways.com/affiliate/accounts/default1/banners/19515e01.jpg" alt="Load WordPress Sites in as fast as 37ms!" title="Load WordPress Sites in as fast as 37ms!" width="336" height="280" /></a><img style="border:0" src="https://www.cloudways.com/affiliate/scripts/imp.php?id=759820&amp;a_bid=19515e01" width="1" height="1" alt="" />
	</div>
</aside>
