<?php
/**
 * Onboarding: Import Requirements
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/import-requirements.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$php_min_ver            = defined( 'CARIERA_MIN_PHP_VERSION' ) ? CARIERA_MIN_PHP_VERSION : '';
$php_max_ver            = defined( 'CARIERA_MAX_PHP_VERSION' ) ? CARIERA_MAX_PHP_VERSION : '';
$php_cur_ver            = PHP_VERSION;
$max_execution_time_cur = ini_get( 'max_execution_time' );
$max_execution_time_sug = 300;
$memory_limit_cur       = \WP_Site_Health::get_instance()->php_memory_limit;
$memory_limit_sug       = 256;

// Determine status for each requirement.
$php_status       = ( version_compare( $php_cur_ver, $php_min_ver, '<' ) || version_compare( $php_cur_ver, $php_max_ver, '>' ) ) ? 'notok' : 'ok';
$memory_status    = intval( $memory_limit_cur ) >= $memory_limit_sug ? 'ok' : 'notok';
$execution_status = $max_execution_time_cur >= $max_execution_time_sug ? 'ok' : 'notok';
?>

<div class="theme-requirements-wrapper">
	<div class="intro">
		<p><?php esc_html_e( 'To ensure a successful demo import, please verify that your server meets the following requirements. If any of these requirements are not met, kindly reach out to your hosting provider for assistance.', 'cariera' ); ?></p>
	</div>

	<div class="requirements-container">
		<!-- PHP Version Card -->
		<div class="requirement status-<?php echo esc_attr( 'ok' === $php_status ? 'success' : 'error' ); ?>">
			<div class="header">
				<h3 class="title"><?php echo esc_html( 'PHP Version' ); ?></h3>
				<span class="requirement-priority high"><?php echo esc_html( 'High' ); ?></span>
			</div>
			
			<div class="body">
				<span class="label"><?php esc_html_e( 'Required', 'cariera' ); ?></span>
				<span class="value"><?php echo esc_html( $php_min_ver . ' - ' . $php_max_ver ); ?></span>
			</div>

			<div class="status">
				<div class="status-indicator <?php echo esc_attr( $php_status ); ?>"></div>
				<div>
					<div class="status-value"><?php echo esc_html( $php_cur_ver ); ?></div>
					<div class="status-label"><?php esc_html_e( 'Current Value', 'cariera' ); ?></div>
				</div>
			</div>
		</div>

		<!-- Memory Limit Card -->
		<div class="requirement status-<?php echo esc_attr( 'ok' === $memory_status ? 'success' : 'error' ); ?>">
			<div class="header">
				<h3 class="title"><?php echo esc_html( 'memory_limit' ); ?></h3>
				<span class="requirement-priority high"><?php echo esc_html( 'High' ); ?></span>
			</div>
			
			<div class="body">
				<span class="label"><?php esc_html_e( 'Required', 'cariera' ); ?></span>
				<span class="value"><?php echo esc_html( $memory_limit_sug ); ?>M</span>
			</div>

			<div class="status">
				<div class="status-indicator <?php echo esc_attr( $memory_status ); ?>"></div>
				<div>
					<div class="status-value"><?php echo esc_html( $memory_limit_cur ); ?></div>
					<div class="status-label"><?php esc_html_e( 'Current Value', 'cariera' ); ?></div>
				</div>
			</div>
		</div>

		<!-- Max Execution Time Card -->
		<div class="requirement status-<?php echo esc_attr( 'ok' === $execution_status ? 'success' : 'error' ); ?>">
			<div class="header">
				<h3 class="title"><?php echo esc_html( 'max_execution_time' ); ?></h3>
				<span class="requirement-priority medium"><?php esc_html_e( 'Medium', 'cariera' ); ?></span>
			</div>
			
			<div class="body">
				<span class="label"><?php esc_html_e( 'Required', 'cariera' ); ?></span>
				<span class="value"><?php echo esc_html( $max_execution_time_sug ); ?></span>
			</div>

			<div class="status">
				<div class="status-indicator <?php echo esc_attr( $execution_status ); ?>"></div>
				<div>
					<div class="status-value"><?php echo esc_html( $max_execution_time_cur ); ?></div>
					<div class="status-label"><?php esc_html_e( 'Current Value', 'cariera' ); ?></div>
				</div>
			</div>
		</div>
	</div>

	<?php if ( intval( $memory_limit_cur ) < $memory_limit_sug || $max_execution_time_cur < $max_execution_time_sug ) { ?>
		<div class="error-notice">
			<?php esc_html_e( 'Your "max execution time" is lower than recommended. Please contact your hosting provider to increase it to the recommended value in order to fully import the demo.', 'cariera' ); ?>
		</div>
	<?php } ?>

	<div class="actions">
		<a href="<?php echo esc_url( admin_url( 'site-health.php?tab=debug' ) ); ?>" class="button button-primary" target="_blank">
			<?php esc_html_e( 'Site Health Info', 'cariera' ); ?>
		</a>
	</div>
</div>
