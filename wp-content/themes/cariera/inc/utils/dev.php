<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debugging helper
 *
 * @since   1.7.0
 * @version 2.0.0
 *
 * @param mixed $expression
 */
function dump( $expression ) {
	if ( ! \Cariera\is_debug_mode() || ! \Cariera\is_dev_mode() ) {
		return;
	}

	echo '<pre>';
	foreach ( func_get_args() as $expression ) {
		var_dump( $expression ); // phpcs:ignore
		echo '<hr>';
	}
	echo '</pre>';
}

/**
 * Debugging helper
 *
 * @since 1.7.0
 */
function dd() {
	foreach ( func_get_args() as $expression ) {
		dump( $expression );
	}
	die;
}

/**
 * Output on debug.log
 *
 * @since   1.7.0
 * @version 2.0.0
 *
 * @param mixed $log
 */
function write_log( $log ) {
	if ( ! \Cariera\is_debug_mode() ) {
		return;
	}

	if ( is_array( $log ) || is_object( $log ) ) {
		error_log( print_r( $log, true ) ); // phpcs:ignore
	} else {
		error_log( $log ); // phpcs:ignore
	}
}

/**
 * Start measuring performance (time and memory) for a given label.
 *
 * @since 1.9.0
 *
 * @param string $label Unique identifier for this measurement.
 */
function measure_start( $label ) {
	global $cariera_performance_measurements;

	// Time measurement.
	if ( function_exists( 'hrtime' ) ) {
		$start_time = hrtime( true ); // Nanoseconds.
	} else {
		$start_time = microtime( true ) * 1e6; // Microseconds.
	}

	// Memory measurements.
	$start_memory = memory_get_usage( true ); // Real usage in bytes.
	$start_peak   = memory_get_peak_usage( true ); // Peak at start.

	// Query count (WordPress-specific).
	global $wpdb;
	$start_queries = $wpdb->num_queries ?? 0;

	$cariera_performance_measurements[ $label ] = [
		'start_time'    => $start_time,
		'end_time'      => null,
		'time'          => null,
		'start_memory'  => $start_memory,
		'end_memory'    => null,
		'memory_diff'   => null,
		'start_peak'    => $start_peak,
		'peak_memory'   => null,
		'query_count'   => null,
		'start_queries' => $start_queries,
		'context'       => [],
	];
}

/**
 * End measuring performance (time and memory) for a given label and log the result.
 *
 * @since   1.9.0
 * @version 1.9.3
 *
 * @param string $label Unique identifier matching the start label.
 */
function measure_end( $label ) {
	global $cariera_performance_measurements;

	if ( ! isset( $cariera_performance_measurements[ $label ] ) ) {
		write_log( "Cariera Performance Error: No start time found for label '$label'" );
		return;
	}

	// Time measurement.
	if ( function_exists( 'hrtime' ) ) {
		$end_time = hrtime( true ); // Nanoseconds.
	} else {
		$end_time = microtime( true ) * 1e6; // Microseconds.
	}
	$start_time  = $cariera_performance_measurements[ $label ]['start_time'];
	$duration    = $end_time - $start_time;
	$duration_ms = function_exists( 'hrtime' ) ? $duration / 1e6 : $duration / 1e3; // Milliseconds.

	// Memory measurements.
	$end_memory    = memory_get_usage( true );
	$start_memory  = $cariera_performance_measurements[ $label ]['start_memory'];
	$memory_diff   = $end_memory - $start_memory; // Net change.
	$peak_memory   = memory_get_peak_usage( true ); // Absolute peak.
	$peak_increase = $peak_memory - $cariera_performance_measurements[ $label ]['start_peak']; // Peak increase in this block.

	// Query count (WordPress-specific).
	global $wpdb;
	$end_queries = $wpdb->num_queries ?? 0;
	$query_count = $end_queries - $cariera_performance_measurements[ $label ]['start_queries'];

	// Store results.
	$cariera_performance_measurements[ $label ]['end_time']      = $end_time;
	$cariera_performance_measurements[ $label ]['time']          = $duration_ms;
	$cariera_performance_measurements[ $label ]['end_memory']    = $end_memory;
	$cariera_performance_measurements[ $label ]['memory_diff']   = $memory_diff;
	$cariera_performance_measurements[ $label ]['peak_memory']   = $peak_memory;
	$cariera_performance_measurements[ $label ]['peak_increase'] = $peak_increase;
	$cariera_performance_measurements[ $label ]['query_count']   = $query_count;

	// Format memory for readability.
	$peak_display = $peak_memory;
	$diff_display = $memory_diff;
	$unit_peak    = $unit_diff = 'B';

	if ( $peak_memory >= 1024 * 1024 ) {
		$peak_display = $peak_memory / ( 1024 * 1024 );
		$unit_peak    = 'MB';
	} elseif ( $peak_memory >= 1024 ) {
		$peak_display = $peak_memory / 1024;
		$unit_peak    = 'KB';
	}
	$peak_display = number_format( $peak_display, 2 ) . ' ' . $unit_peak;

	if ( abs( $memory_diff ) >= 1024 * 1024 ) {
		$diff_display = $memory_diff / ( 1024 * 1024 );
		$unit_diff    = 'MB';
	} elseif ( abs( $memory_diff ) >= 1024 ) {
		$diff_display = $memory_diff / 1024;
		$unit_diff    = 'KB';
	}
	$diff_display = number_format( $diff_display, 2 ) . ' ' . $unit_diff;

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		$context     = ! empty( $cariera_performance_measurements[ $label ]['context'] )
			? '  Context:        ' . wp_json_encode( $cariera_performance_measurements[ $label ]['context'] ) . "\n"
			: '';
		$log_message = sprintf(
			"Cariera Performance log for [%s]:\n" .
			"------------------------\n" .
			"  Execution Time: %8.3f ms\n" .
			"  Peak Memory:    %8s\n" .
			"  Memory Change:  %8s\n" .
			"  DB Queries:     %8d\n" .
			'%s' .
			'------------------------',
			$label,
			$duration_ms,
			$peak_display,
			$diff_display,
			$query_count,
			$context
		);

		write_log( $log_message );
	}
}
