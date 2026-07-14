<?php
/**
 * Debug Log
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/debug-log.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.8.0
 * @version     1.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<div class="notice notice-info">
		<h3><?php esc_html_e( 'WP Config Requirements:', 'cariera-core' ); ?></h3>
		<p><?php esc_html_e( 'To view your debug log, ensure your wp-config.php file contains the following settings:', 'cariera-core' ); ?></p>

		<pre>
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 0 );
		</pre>
	</div>

	<h3><?php esc_html_e( 'Cariera Debug Log', 'cariera-core' ); ?></h3>

	<?php
	if ( ! function_exists( '\Cariera\is_debug_mode' ) || ! function_exists( '\Cariera\is_debug_log' ) ) {
		echo '<div class="error notice"><p>' . esc_html__( 'Cariera theme is not activated.', 'cariera-core' ) . '</p></div>';
		return;
	}

	if ( ! \Cariera\is_debug_mode() ) {
		echo '<div class="error notice"><p>' . esc_html__( 'WP_DEBUG should be enabled in wp-config.php file for the debug-log to get generated.', 'cariera-core' ) . '</p></div>';
		return;
	}

	if ( ! \Cariera\is_debug_log() ) {
		echo '<div class="error notice"><p>' . esc_html__( 'WP_DEBUG_LOG should be enabled in wp-config.php file for the debug-log to get generated.', 'cariera-core' ) . '</p></div>';
		return;
	}

	// Log file does not exist.
	if ( ! file_exists( $log_file_path ) ) {
		echo '<div class="error notice"><p>' . esc_html__( 'Debug.log file does not exist.', 'cariera-core' ) . '</p></div>';
		return;
	}

	// Log file is not readable.
	if ( ! is_readable( $log_file_path ) ) {
		echo '<div class="error notice"><p>' . esc_html__( 'Debug.log file is not readable.', 'cariera-core' ) . '</p></div>';
		return;
	}
	?>

	<!-- Display the content in a textarea or preformatted text. -->
	<textarea class="cariera-debug-log" readonly><?php echo esc_textarea( $log_content ); ?></textarea>
</div>
