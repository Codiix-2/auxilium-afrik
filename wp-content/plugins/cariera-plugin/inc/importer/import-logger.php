<?php

namespace Cariera_Core\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Import_Logger extends \Cariera_Core\Importer\WP_Importer\WPImporterLoggerCLI {

	/**
	 * Variable for front-end error display.
	 *
	 * @var string
	 */
	public $error_output = '';

	/**
	 * Holds the date and time string for demo import and log file.
	 *
	 * @var string
	 */
	public static $demo_import_start_time = '';

	/**
	 * Check if logging is enabled.
	 *
	 * @since 1.9.8
	 *
	 * @return bool True if logging should be enabled, false otherwise.
	 */
	private static function is_logging_enabled() {
		$enabled = \Cariera\is_debug_mode();

		return apply_filters( 'cariera_importer_logging_enabled', $enabled );
	}

	/**
	 * Overwritten log function from WP_Importer_Logger_CLI.
	 *
	 * Logs with an arbitrary level.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param mixed  $level level of reporting.
	 * @param string $message log message.
	 * @param array  $context context to the log message.
	 */
	public function log( $level, $message, array $context = [] ) {
		if ( ! self::is_logging_enabled() ) {
			return;
		}

		// Save error messages for front-end display.
		$this->error_output( $level, $message, $context = [] );

		if ( $this->level_to_numeric( $level ) < $this->level_to_numeric( $this->min_level ) ) {
			return;
		}

		printf(
			'[%s] [%s] %s' . PHP_EOL,
			esc_html( gmdate( 'H:i:s' ) ),
			esc_html( strtoupper( $level ) ),
			esc_html( $message )
		);

		// Append to the physical log file.
		$log_file = Importer::instance()->get_log_file_path();
		if ( $log_file ) {
			self::append_to_file( $message, $log_file, strtoupper( $level ) );
		}
	}

	/**
	 * Save messages for error output.
	 * Only the messages greater then Error.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param mixed  $level level of reporting.
	 * @param string $message log message.
	 * @param array  $context context to the log message.
	 */
	public function error_output( $level, $message, array $context = [] ) {
		if ( $this->level_to_numeric( $level ) < $this->level_to_numeric( 'error' ) ) {
			return;
		}

		$this->error_output .= sprintf(
			'[%s] %s<br>',
			esc_html( strtoupper( $level ) ),
			esc_html( $message )
		);
	}

	/**
	 * Set the $demo_import_start_time class variable with the current date and time string.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	public static function set_demo_import_start_time() {
		if ( ! empty( self::$demo_import_start_time ) ) {
			return;
		}

		self::$demo_import_start_time = gmdate( apply_filters( 'cariera_date_format_for_file_names', 'Y-m-d__H-i-s' ) );

		// Set the performance start time if not already set (used for end-of-import metrics).
		if ( false === get_transient( 'cariera_import_performance_start' ) ) {
			set_transient( 'cariera_import_performance_start', microtime( true ), DAY_IN_SECONDS );
		}
	}

	/**
	 * Get log file path
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @return string, path to the log file
	 */
	public static function get_log_path() {
		if ( ! self::is_logging_enabled() ) {
			return;
		}

		if ( empty( self::$demo_import_start_time ) ) {
			self::set_demo_import_start_time();
		}

		$upload_dir  = wp_upload_dir();
		$upload_path = trailingslashit( $upload_dir['basedir'] ) . 'cariera-importer-logs/';

		// Ensure the directory exists.
		if ( ! file_exists( $upload_path ) ) {
			wp_mkdir_p( $upload_path );
		}

		$log_path = $upload_path . apply_filters( 'cariera_log_file_prefix', 'importer_log_' ) . self::$demo_import_start_time . apply_filters( 'cariera_log_file_suffix_and_file_extension', '.txt' );

		return $log_path;
	}

	/**
	 * Append content to the file.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param string $content content to be saved to the file.
	 * @param string $file_path file path where the content should be saved.
	 * @param string $separator_text separates the existing content of the file with the new content.
	 * @return boolean|WP_Error, path to the saved file or WP_Error object with error message.
	 */
	public static function append_to_file( $content, $file_path, $separator_text = '' ) {
		if ( ! self::is_logging_enabled() ) {
			return;
		}

		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$is_new_file = ! $wp_filesystem->exists( $file_path );

		// Initialize file with a header if it doesn't exist.
		if ( $is_new_file ) {
			$header  = str_repeat( '=', 60 ) . PHP_EOL;
			$header .= ' CARIERA DEMO IMPORTER LOG' . PHP_EOL;
			$header .= ' Date: ' . gmdate( 'Y-m-d H:i:s' ) . ' (GMT)' . PHP_EOL;
			$header .= str_repeat( '=', 60 ) . PHP_EOL . PHP_EOL;

			if ( ! $wp_filesystem->put_contents( $file_path, $header, FS_CHMOD_FILE ) ) {
				return new \WP_Error( 'file_creation_failed', __( 'Could not create log file.', 'cariera-core' ) );
			}
		}

		$existing_data = $wp_filesystem->get_contents( $file_path );
		$timestamp     = '[' . gmdate( 'H:i:s' ) . '] ';

		// Better Formatting for Separator Entries.
		if ( ! empty( $separator_text ) ) {
			$entry = PHP_EOL . $timestamp . "--- {$separator_text} ---" . PHP_EOL . $content . PHP_EOL;
		} else {
			$entry = $timestamp . $content . PHP_EOL;
		}

		return $wp_filesystem->put_contents( $file_path, $existing_data . $entry, FS_CHMOD_FILE );
	}

	/**
	 * Finalize the log with performance metrics.
	 * Calculated at the end of the import to provide insights on duration and memory usage.
	 *
	 * @since 1.9.8
	 *
	 * @param string $log_path  Path to the log file.
	 * @param string $demo_slug Slug of the demo being imported.
	 */
	public static function finalize_log( $log_path, $demo_slug ) {
		if ( ! self::is_logging_enabled() ) {
			return;
		}

		$start_time = get_transient( 'cariera_import_performance_start' );
		$end_time   = microtime( true );
		$duration   = $start_time ? $end_time - (float) $start_time : 0;

		if ( $duration > 60 ) {
			$minutes      = (int) floor( $duration / 60 );
			$seconds      = round( fmod( $duration, 60 ), 2 );
			$display_time = sprintf( '%dm %ss', $minutes, $seconds );
		} else {
			$display_time = sprintf( '%s seconds', round( $duration, 2 ) );
		}

		$memory_usage = round( memory_get_usage( true ) / 1024 / 1024, 2 );
		$peak_memory  = round( memory_get_peak_usage( true ) / 1024 / 1024, 2 );
		$memory_limit = ini_get( 'memory_limit' );

		$footer  = str_repeat( '=', 60 ) . PHP_EOL;
		$footer .= sprintf( ' DEMO IMPORT FINISHED: %s', strtoupper( $demo_slug ) ) . PHP_EOL;
		$footer .= sprintf( ' Total Execution Time: %s', $display_time ) . PHP_EOL;
		$footer .= sprintf( ' Memory Usage: %s MB | Peak Memory: %s MB', $memory_usage, $peak_memory ) . PHP_EOL;
		$footer .= sprintf( ' PHP Memory Limit: %s', $memory_limit ) . PHP_EOL;
		$footer .= str_repeat( '=', 60 ) . PHP_EOL;

		self::append_to_file( $footer, $log_path, 'FINISH' );

		delete_transient( 'cariera_import_performance_start' );
	}
}
