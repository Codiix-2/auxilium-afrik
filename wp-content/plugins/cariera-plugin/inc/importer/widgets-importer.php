<?php

namespace Cariera_Core\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widgets_Importer {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Get available widgets in current site
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 */
	private function available_widgets() {
		global $wp_registered_widget_controls;

		$available_widgets = [];
		foreach ( $wp_registered_widget_controls as $widget ) {
			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) {
				$available_widgets[ $widget['id_base'] ] = [
					'id_base' => $widget['id_base'],
					'name'    => $widget['name'],
				];
			}
		}

		return $available_widgets;
	}

	/**
	 * Imports widgets from a json string.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param string $data Widget JSON.
	 */
	public function import( $data ) {
		global $wp_registered_sidebars;

		if ( empty( $data ) || ! is_object( $data ) ) {
			return new \WP_Error(
				'corrupted_import_data',
				esc_html__( 'Error: Widget import data could not be read.', 'cariera-core' )
			);
		}

		// Initial state setup.
		$available_widgets   = $this->available_widgets();
		$results             = [];
		$sidebars_widgets    = get_option( 'sidebars_widgets', [ 'wp_inactive_widgets' => [] ] );
		$widget_option_cache = [];
		$widget_hashes       = [];
		$id_base_cache       = [];
		$next_ids            = [];

		// Optional but recommended during imports.
		wp_suspend_cache_invalidation( true );

		foreach ( $data as $sidebar_id => $widgets ) {
			// Skip inactive widgets from export.
			if ( 'wp_inactive_widgets' === $sidebar_id ) {
				continue;
			}

			$sidebar_exists = isset( $wp_registered_sidebars[ $sidebar_id ] );
			$use_sidebar_id = $sidebar_exists ? $sidebar_id : 'wp_inactive_widgets';

			if ( ! isset( $sidebars_widgets[ $use_sidebar_id ] ) ) {
				$sidebars_widgets[ $use_sidebar_id ] = [];
			}

			$results[ $sidebar_id ] = [
				'name'         => $sidebar_exists ? $wp_registered_sidebars[ $sidebar_id ]['name'] : $sidebar_id,
				'message_type' => $sidebar_exists ? 'success' : 'error',
				'message'      => $sidebar_exists ? '' : esc_html__( 'Sidebar missing (moved to Inactive)', 'cariera-core' ),
				'widgets'      => [],
			];

			foreach ( $widgets as $widget_instance_id => $widget_data ) {
				// Fast id_base extraction with cache.
				if ( ! isset( $id_base_cache[ $widget_instance_id ] ) ) {
					$id_base_cache[ $widget_instance_id ] = preg_replace(
						'/-\d+$/',
						'',
						$widget_instance_id
					);
				}

				$id_base = $id_base_cache[ $widget_instance_id ];

				// Check if widget is supported.
				if ( ! isset( $available_widgets[ $id_base ] ) ) {
					$results[ $sidebar_id ]['widgets'][ $widget_instance_id ] = [
						'name'         => $id_base,
						'title'        => $widget_data->title ?? esc_html__( 'No Title', 'cariera-core' ),
						'message_type' => 'error',
						'message'      => esc_html__( 'Site does not support widget', 'cariera-core' ),
					];
					continue;
				}

				// Normalize widget data to array and generate hash for duplication checks.
				$widget_array = json_decode( wp_json_encode( $widget_data ), true );
				$widget_array = array_filter(
					$widget_array,
					static function ( $value ) {
						return null !== $value && '' !== $value;
					}
				);
				$widget_hash  = md5( wp_json_encode( $widget_array ) );

				// Load widget options once per widget type.
				if ( ! isset( $widget_option_cache[ $id_base ] ) ) {
					$widget_option_cache[ $id_base ] = get_option(
						'widget_' . $id_base,
						[ '_multiwidget' => 1 ]
					);

					// Build hash map for fast duplicate detection.
					$widget_hashes[ $id_base ] = [];

					foreach ( $widget_option_cache[ $id_base ] as $k => $v ) {
						if ( is_numeric( $k ) ) {
							$widget_hashes[ $id_base ][ md5( wp_json_encode( $v ) ) ] = $k;
						}
					}

					$numeric_keys         = array_filter( array_keys( $widget_option_cache[ $id_base ] ), 'is_numeric' );
					$next_ids[ $id_base ] = ! empty( $numeric_keys ) ? max( $numeric_keys ) + 1 : 1;
				}

				// Duplication Check: Identical settings within the same sidebar.
				if ( isset( $widget_hashes[ $id_base ][ $widget_hash ] ) ) {
					$existing_id = $widget_hashes[ $id_base ][ $widget_hash ];

					if ( in_array( "$id_base-$existing_id", $sidebars_widgets[ $use_sidebar_id ], true ) ) {
						$results[ $sidebar_id ]['widgets'][ $widget_instance_id ] = [
							'name'         => $available_widgets[ $id_base ]['name'],
							'title'        => $widget_array['title'] ?? esc_html__( 'No Title', 'cariera-core' ),
							'message_type' => 'info',
							'message'      => esc_html__( 'Skipped - already exists', 'cariera-core' ),
						];
						continue;
					}
				}

				// Insert widget.
				$next_id = $next_ids[ $id_base ]++;

				$widget_option_cache[ $id_base ][ $next_id ]     = $widget_array;
				$widget_option_cache[ $id_base ]['_multiwidget'] = 1;

				$widget_hashes[ $id_base ][ $widget_hash ] = $next_id;
				$sidebars_widgets[ $use_sidebar_id ][]     = "$id_base-$next_id";

				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ] = [
					'name'         => $available_widgets[ $id_base ]['name'],
					'title'        => $widget_array['title'] ?? esc_html__( 'No Title', 'cariera-core' ),
					'message_type' => $sidebar_exists ? 'success' : 'warning',
					'message'      => $sidebar_exists ? esc_html__( 'Imported', 'cariera-core' ) : esc_html__( 'Imported to Inactive', 'cariera-core' ),
				];
			}
		}

		// Persist widget options (one DB write per widget type).
		foreach ( $widget_option_cache as $id_base => $instances ) {
			update_option( 'widget_' . $id_base, $instances );
		}

		// Finalize sidebar mapping in a single database call.
		update_option( 'sidebars_widgets', $sidebars_widgets );

		wp_suspend_cache_invalidation( false );

		return $results;
	}

	/**
	 * Format results for log file
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param array $results widget import results.
	 */
	public function format_results_for_log( $results ) {
		if ( is_wp_error( $results ) ) {
			return sprintf( "Widget import failed: %s\n", $results->get_error_message() );
		}

		if ( empty( $results ) || ! is_array( $results ) ) {
			return esc_html__( 'No results for widget import!', 'cariera-core' );
		}

		$output = '';

		foreach ( $results as $sidebar ) {
			if ( empty( $sidebar['message'] ) && empty( $sidebar['widgets'] ) ) {
				continue;
			}

			$output .= sprintf(
				"%s%s\n",
				$sidebar['name'] ?? 'Unknown Sidebar',
				! empty( $sidebar['message'] ) ? ': ' . $sidebar['message'] : ''
			);

			if ( ! empty( $sidebar['widgets'] ) ) {
				foreach ( $sidebar['widgets'] as $widget ) {
					$output .= sprintf(
						" - %s: %s [%s]\n",
						$widget['name'] ?? 'Unknown Widget',
						$widget['title'] ?? 'No Title',
						$widget['message'] ?? 'No Message'
					);
				}
			}

			$output .= "\n";
		}

		return $output;
	}
}
