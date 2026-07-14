<?php
/**
 * Onboarding: Main Page
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/main.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'vue' );
wp_enqueue_script( 'cariera-onboarding' );
wp_enqueue_style( 'cariera-onboarding' );
?>

<div class="cariera-onboarding" id="cariera-onboarding">
	<!-- Navigation -->
	<?php get_template_part( 'templates/backend/onboarding/header', null, [ 'status' => $args['status'] ] ); ?>
	
	<!-- Content -->
	<section class="onboarding-content">
		<!-- Welcome -->
		<div id="welcome" class="content-page" :class="{ active: activeTab === 'welcome' }" v-if="activeTab === 'welcome'">
			<?php get_template_part( 'templates/backend/onboarding/theme-info' ); ?>

			<?php if ( ! $args['status'] ) { ?>
				<h2><?php esc_html_e( 'Get Started!', 'cariera' ); ?></h2>
				<p><?php echo wp_kses_post( __( 'To activate the theme, please utilize your <strong>Envato Purchase Code</strong>. Remember, each license is valid for a single <strong>WordPress installation</strong>. Should you wish to transfer your license to a different domain, ensure to deregister it first and then activate it on your new domain.', 'cariera' ) ); ?></p>
			<?php } ?>

			<div class="license-container">
				<?php do_action( 'cariera_onboarding_license' ); ?>
				<?php do_action( 'cariera_onboarding_license_sidebar' ); ?>
			</div>

			<?php get_template_part( 'templates/backend/onboarding/installation-video' ); ?>
		</div>

		<!-- Required Plugins -->
		<div id="plugins" class="content-page" :class="{ active: activeTab === 'plugins' }" v-if="activeTab === 'plugins'">
			<h2 class="title">
				<?php esc_html_e( 'Required Plugins', 'cariera' ); ?>
				
				<button href="#" id="get-plugins-url" class="title-btn onboarding-btn" :class="getPlugins ? 'loading' : ''" @click="getPluginsURL">
					<span class="text"><?php esc_html_e( 'Refresh Plugin URLs', 'cariera' ); ?></span>
					<span class="btn-loader"></span>
				</button>
			</h2>

			<?php do_action( 'cariera_onboarding_plugins' ); ?>
		</div>

		<!-- Import Demo Content -->
		<div id="import" class="content-page" :class="{ active: activeTab === 'import' }" v-if="activeTab === 'import'">
			<h2 class="title"><?php esc_html_e( 'Import Demo Content', 'cariera' ); ?></h2>

			<?php do_action( 'cariera_onboarding_import' ); ?>
		</div>

		<!-- Compatible Plugins -->
		<div id="addons" class="content-page" :class="{ active: activeTab === 'addons' }" v-if="activeTab === 'addons'">
			<?php get_template_part( 'templates/backend/onboarding/addons' ); ?>
		</div>
		
		<!-- Gnodesign Themes -->
		<div id="themes" class="content-page" :class="{ active: activeTab === 'themes' }" v-if="activeTab === 'themes'">
			<?php get_template_part( 'templates/backend/onboarding/gnodesign-themes' ); ?>
		</div>
	</section>

	<!-- Sidebar -->
	<?php get_template_part( 'templates/backend/onboarding/sidebar' ); ?>
</div>

<?php get_template_part( 'templates/backend/admin/ajax-response' ); ?>
<?php get_template_part( 'templates/backend/admin/support-link' ); ?>
