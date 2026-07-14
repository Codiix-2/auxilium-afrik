<?php

namespace Cariera_Core\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Content_Importer {

	/**
	 * The importer class object used for importing content.
	 *
	 * @var \Cariera_Core\Importer\WXRImporter
	 */
	private $importer;

	/**
	 * Time in seconds, marking the beginning of the current AJAX import chunk.
	 *
	 * @var float
	 */
	private $start_time;

	/**
	 * The instance of the \Cariera_Core\Importer\Import_Logger class.
	 *
	 * @var \Cariera_Core\Importer\Import_Logger
	 */
	public $logger;

	/**
	 * The instance of the \Cariera_Core\Importer\Importer class
	 *
	 * @var \Cariera_Core\Importer\Importer
	 */
	private $tmi;

	/**
	 * Constructor
	 *
	 * @param array  $importer_options Importer Options.
	 * @param object $logger Logger object.
	 */
	public function __construct( $importer_options = [], $logger = null ) {
		// Set the wp-importer v2 as the importer used in this plugin.
		$this->importer = new \Cariera_Core\Importer\WXRImporter( $importer_options );

		// Set logger to the importer.
		$this->logger = $logger;
		if ( ! empty( $this->logger ) ) {
			$this->set_logger( $this->logger );
		}

		// Get the \Cariera_Core\Importer\Importer instance.
		$this->tmi = \Cariera_Core\Importer\Importer::instance();
	}

	/**
	 * Set the logger used in the import
	 *
	 * @since 1.7.3
	 *
	 * @param object $logger logger instance.
	 */
	public function set_logger( $logger ) {
		$this->importer->set_logger( $logger );
	}

	/**
	 * Imports content from a WordPress export file.
	 *
	 * @since 1.7.3
	 *
	 * @param string $data_file path to xml file, file with WordPress export data.
	 */
	public function import( $data_file ) {
		$this->importer->import( $data_file );
	}

	/**
	 * Get all protected variables from the WXR_Importer needed for continuing the import.
	 *
	 * @since 1.7.3
	 */
	public function get_importer_data() {
		return $this->importer->get_importer_data();
	}

	/**
	 * Sets all protected variables from the WXR_Importer needed for continuing the import.
	 *
	 * @since 1.7.3
	 *
	 * @param array $data with set variables.
	 */
	public function set_importer_data( $data ) {
		$this->importer->set_importer_data( $data );
	}

	/**
	 * Import content XML
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param string $import_file_path Content.xml file path.
	 */
	public function import_content( $import_file_path ) {
		global $wpdb;

		$this->start_time = microtime( true );

		// Increase PHP max execution time. Just in case, even though the AJAX calls are only 25 sec long.
		set_time_limit( apply_filters( 'cariera_time_limit_for_demo_data_import', 300 ) );

		// Disable term counting to batch at the end.
		wp_defer_term_counting( true );

		// Disable import of authors.
		add_filter( 'wxr_importer.pre_process.user', '__return_false' );

		// Check, if we need to send another AJAX request and set the importing author to the current user.
		add_filter( 'wxr_importer.pre_process.post', [ $this, 'new_ajax_request_maybe' ] );

		// Track imported posts.
		add_action( 'wxr_importer.processed.post', [ $this, 'track_imported_post' ] );

		// Track imported terms.
		add_action( 'wxr_importer.processed.term', [ $this, 'track_imported_term' ], 10, 2 );

		// Track imported attachments separately.
		add_filter( 'wxr_importer.pre_process.post', [ $this, 'track_attachment_before_import' ], 20 );

		// Disables generation of multiple image sizes (thumbnails) in the content import step.
		if ( ! apply_filters( 'cariera_regenerate_thumbnails', false ) ) {
			add_filter( 'intermediate_image_sizes_advanced', '__return_null' );
		}

		// Import content.
		if ( ! empty( $import_file_path ) && file_exists( $import_file_path ) ) {
			ob_start();
			$this->import( $import_file_path );
			ob_get_clean();
		}

		// Re-enable term counting.
		wp_defer_term_counting( false );

		// Return any error messages for the front page output (errors, critical, alert and emergency level messages only).
		return $this->logger->error_output;
	}

	/**
	 * Check if we need to create a new AJAX request, so that server does not timeout.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param array $data current post data.
	 */
	public function new_ajax_request_maybe( $data ) {
		$duration = microtime( true ) - $this->start_time;

		// Trigger new AJAX if we exceed the threshold (default 25s).
		if ( $duration > apply_filters( 'cariera_time_for_one_ajax_call', 25 ) ) {

			// Capture any buffered output.
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				$this->tmi->append_to_frontend_error_messages( $buffer );
			}

			// Log the chunking event.
			\Cariera_Core\Importer\Import_Logger::append_to_file(
				// translators: %s: duration in seconds.
				sprintf( esc_html__( 'AJAX chunk limit reached (%ss). Starting new request.', 'cariera-core' ), round( $duration, 2 ) ),
				$this->tmi->get_log_file_path(),
				'INFO'
			);

			// Save state.
			$this->save_importer_state();

			// Send the response back to JS to trigger the next call.
			wp_send_json(
				[
					'status'  => 'newAJAX',
					'message' => esc_html__( 'Continuing import in next request...', 'cariera-core' ),
				]
			);
		}

		// Set importing author to the current user.
		// Fixes the [WARNING] Could not find the author for ... log warning messages.
		$current_user_obj    = wp_get_current_user();
		$data['post_author'] = $current_user_obj->user_login;

		return $data;
	}

	/**
	 * Set current state of the content importer, so we can continue the import with new AJAX request.
	 *
	 * @since 1.7.3
	 */
	public function save_importer_state() {
		$current_state = array_merge(
			$this->tmi->get_current_importer_data(),
			$this->get_importer_data()
		);
		set_transient( 'cariera_importer_data', $current_state, HOUR_IN_SECONDS );
	}

	/**
	 * Track imported post
	 *
	 * @since 1.9.8
	 *
	 * @param int|array $post_data The imported post data (can be ID or array with post data).
	 */
	public function track_imported_post( $post_data ) {
		// Handle both ID and array formats.
		$post_id = is_array( $post_data ) && isset( $post_data['post_id'] ) ? $post_data['post_id'] : $post_data;

		if ( ! $post_id || ! is_numeric( $post_id ) ) {
			return;
		}

		$demo_slug   = $this->tmi->demo_slug;
		$theme_slug  = $this->tmi->theme_slug;
		$option_name = $theme_slug . '_' . $demo_slug . '_import_data';
		$import_data = get_option( $option_name, [] );

		if ( ! isset( $import_data['posts'] ) ) {
			$import_data['posts'] = [];
		}

		// Avoid duplicates.
		if ( ! in_array( $post_id, $import_data['posts'], true ) ) {
			$import_data['posts'][] = absint( $post_id );
			update_option( $option_name, $import_data, false );
		}
	}

	/**
	 * Track imported term - handles multiple parameter formats
	 *
	 * @since 1.9.8
	 *
	 * @param int|array|object $term_data Term ID, term array, or term object.
	 * @param string|null      $taxonomy  Optional. Taxonomy name (used when $term_data is just an ID).
	 */
	public function track_imported_term( $term_data, $taxonomy = null ) {
		$term_id       = 0;
		$term_taxonomy = '';

		// Handle different input formats.
		if ( is_numeric( $term_data ) ) {
			// Format 1: Term ID passed directly.
			$term_id = absint( $term_data );

			// Try provided taxonomy first.
			if ( ! empty( $taxonomy ) && is_string( $taxonomy ) ) {
				$term_taxonomy = sanitize_text_field( $taxonomy );
			} else {
				// Fetch term to get taxonomy.
				$term = get_term( $term_id );
				if ( ! is_wp_error( $term ) && $term && isset( $term->taxonomy ) ) {
					$term_taxonomy = $term->taxonomy;
				}
			}
		} elseif ( is_array( $term_data ) || is_object( $term_data ) ) {
			// Format 2 & 3: Term array or object passed.
			$data_array = (array) $term_data;

			// Extract term ID.
			$term_id = isset( $data_array['term_id'] ) ? absint( $data_array['term_id'] ) : 0;

			// Extract taxonomy from data.
			if ( isset( $data_array['taxonomy'] ) && ! empty( $data_array['taxonomy'] ) ) {
				$term_taxonomy = sanitize_text_field( $data_array['taxonomy'] );
			}

			// Fall back to provided taxonomy parameter.
			if ( empty( $term_taxonomy ) && ! empty( $taxonomy ) && is_string( $taxonomy ) ) {
				$term_taxonomy = sanitize_text_field( $taxonomy );
			}
		}

		// Validate we have required data.
		if ( empty( $term_id ) || empty( $term_taxonomy ) ) {
			return;
		}

		// Get import data from options.
		$demo_slug   = $this->tmi->demo_slug;
		$theme_slug  = $this->tmi->theme_slug;
		$option_name = $theme_slug . '_' . $demo_slug . '_import_data';
		$import_data = get_option( $option_name, [] );

		// Initialize terms array if not set.
		if ( ! isset( $import_data['terms'] ) || ! is_array( $import_data['terms'] ) ) {
			$import_data['terms'] = [];
		}

		// Check if term already tracked to avoid duplicates.
		$term_exists = false;
		foreach ( $import_data['terms'] as $tracked_term ) {
			if ( ! isset( $tracked_term['term_id'], $tracked_term['taxonomy'] ) ) {
				continue;
			}

			if ( absint( $tracked_term['term_id'] ) === $term_id &&
				$tracked_term['taxonomy'] === $term_taxonomy ) {
				$term_exists = true;
				break;
			}
		}

		// Add term if not already tracked.
		if ( ! $term_exists ) {
			$import_data['terms'][] = [
				'term_id'  => $term_id,
				'taxonomy' => $term_taxonomy,
			];
			update_option( $option_name, $import_data );
		}
	}

	/**
	 * Track attachments before they're imported
	 * This runs BEFORE the post is created, so we can identify it as an attachment
	 *
	 * @since 1.9.8
	 *
	 * @param array $data Post data being processed.
	 * @return array Unchanged post data.
	 */
	public function track_attachment_before_import( $data ) {
		// Check if this is an attachment.
		if ( isset( $data['post_type'] ) && 'attachment' === $data['post_type'] ) {
			// After the post is created, track it as media.
			// We'll use a one-time action hook to capture the ID.
			add_action( 'wxr_importer.processed.post', [ $this, 'track_as_media' ], 5 );
		}

		return $data;
	}

	/**
	 * Track a post as media (attachment)
	 * This is called after an attachment is imported
	 *
	 * @since 1.9.8
	 *
	 * @param int|array $post_data The imported post data.
	 */
	public function track_as_media( $post_data ) {
		// Remove this one-time hook immediately.
		remove_action( 'wxr_importer.processed.post', [ $this, 'track_as_media' ], 5 );

		// Handle both ID and array formats.
		$post_id = is_array( $post_data ) && isset( $post_data['post_id'] ) ? $post_data['post_id'] : $post_data;

		if ( ! $post_id || ! is_numeric( $post_id ) ) {
			return;
		}

		// Verify it's an attachment.
		if ( get_post_type( $post_id ) !== 'attachment' ) {
			return;
		}

		$demo_slug   = $this->tmi->demo_slug;
		$theme_slug  = $this->tmi->theme_slug;
		$import_data = get_option( $theme_slug . '_' . $demo_slug . '_import_data', [] );

		if ( ! isset( $import_data['media'] ) ) {
			$import_data['media'] = [];
		}

		// Avoid duplicates.
		if ( ! in_array( $post_id, $import_data['media'], true ) ) {
			$import_data['media'][] = absint( $post_id );
			update_option( $theme_slug . '_' . $demo_slug . '_import_data', $import_data );
		}
	}
}
