<?php

namespace Cariera_Core\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uninstaller extends \Cariera_Core\Importer\Importer {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Batch size for processing posts
	 *
	 * @var int
	 */
	private $batch_size = 75;

	/**
	 * Constructor
	 */
	public function __construct() {
		// AJAX Handlers.
		$ajax_actions = [
			'fetch_uninstall_confirmation',
			'start_uninstall',
			'delete_posts',
			'delete_terms',
			'delete_media',
			'reset_widgets',
			'reset_menus',
			// 'reset_theme_mods',
			'reset_options',
		];

		foreach ( $ajax_actions as $action ) {
			add_action( "wp_ajax_{$action}", [ $this, $action ] );
		}
	}

	/**
	 * Get and validate demo slug from AJAX.
	 *
	 * @since 1.9.8
	 */
	private function get_demo_slug_from_post() {
		if ( empty( $_POST['demo_slug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			wp_send_json_error( esc_html__( 'Demo slug is missing.', 'cariera-core' ) );
		}

		$demo_slug       = sanitize_text_field( wp_unslash( $_POST['demo_slug'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$this->demo_slug = $demo_slug;

		return $demo_slug;
	}

	/**
	 * Fetch uninstall confirmation popup
	 *
	 * @since 1.9.8
	 */
	public function fetch_uninstall_confirmation() {
		$this->verify_before_call_ajax( 'fetch_uninstall_confirmation' );

		$demo_slug   = $this->demo_slug;
		$option_key  = $this->theme_slug . '_' . $demo_slug . '_import_data';
		$import_data = get_option( $option_key, [] );

		if ( empty( $import_data ) ) {
			wp_send_json_error( esc_html__( 'No tracked import data found for this demo.', 'cariera-core' ) );
		}

		ob_start();
		cariera_get_template(
			'backend/importer/popup-uninstall-confirmation.php',
			[
				'demo_slug'   => $demo_slug,
				'import_data' => $import_data,
			]
		);
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * Start the uninstall process
	 *
	 * @since 1.9.8
	 */
	public function start_uninstall() {
		// Get demo slug first (this will verify nonce internally).
		$demo_slug = $this->get_demo_slug_from_post();

		// Now verify the nonce for this specific action.
		if ( ! cariera_verify_nonce( 'start_uninstall' ) ) {
			wp_send_json_error( esc_html__( 'Invalid security token', 'cariera-core' ) );
		}

		$option_key  = $this->theme_slug . '_' . $demo_slug . '_import_data';
		$import_data = get_option( $option_key, [] );

		if ( empty( $import_data ) ) {
			wp_send_json_error( esc_html__( 'No import data found for this demo.', 'cariera-core' ) );
		}

		// Define all available uninstall steps.
		$available_steps = [
			'posts'      => [
				'action' => 'delete_posts',
				'label'  => esc_html__( 'Delete Content (Posts, Pages, CPTs)', 'cariera-core' ),
			],
			'terms'      => [
				'action' => 'delete_terms',
				'label'  => esc_html__( 'Delete Terms', 'cariera-core' ),
			],
			'media'      => [
				'action' => 'delete_media',
				'label'  => esc_html__( 'Delete Media Files', 'cariera-core' ),
			],
			'widgets'    => [
				'action' => 'reset_widgets',
				'label'  => esc_html__( 'Reset Widgets', 'cariera-core' ),
			],
			'menus'      => [
				'action' => 'reset_menus',
				'label'  => esc_html__( 'Reset Menus', 'cariera-core' ),
			],
			'theme_mods' => [
				'action' => 'reset_theme_mods',
				'label'  => esc_html__( 'Reset Customizer Settings', 'cariera-core' ),
			],
			'options'    => [
				'action' => 'reset_options',
				'label'  => esc_html__( 'Reset Theme Options', 'cariera-core' ),
			],
		];

		$uninstall_steps = [];

		foreach ( $available_steps as $import_key => $step ) {
			if ( ! empty( $import_data[ $import_key ] ) ) {
				$uninstall_steps[ $step['action'] ] = $step['label'];
			}
		}

		// If no steps, something went wrong.
		if ( empty( $uninstall_steps ) ) {
			wp_send_json_error( esc_html__( 'No uninstall steps found.', 'cariera-core' ) );
		}

		ob_start();
		cariera_get_template(
			'backend/importer/popup-uninstall-progress.php',
			[
				'demo_slug'       => $demo_slug,
				'uninstall_steps' => $uninstall_steps,
			]
		);
		wp_send_json_success( ob_get_clean() );
	}

	/**
	 * Delete imported posts
	 *
	 * @since 1.9.8
	 */
	public function delete_posts() {
		$this->verify_before_call_ajax( 'delete_posts' );

		$demo_slug   = $this->demo_slug;
		$option_key  = $this->theme_slug . '_' . $demo_slug . '_import_data';
		$import_data = get_option( $option_key, [] );

		// Get batch offset and counters from request.
		$offset        = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$deleted_count = isset( $_POST['deleted_count'] ) ? absint( $_POST['deleted_count'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$failed_count  = isset( $_POST['failed_count'] ) ? absint( $_POST['failed_count'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$skipped_count = isset( $_POST['skipped_count'] ) ? absint( $_POST['skipped_count'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Get the default application form ID.
		$default_application_form_id = class_exists( '\Cariera_Addons\Core\Applications\Default_Form' ) ? \Cariera_Addons\Core\Applications\Default_Form::get_default_form_id() : 0;

		if ( ! empty( $import_data['posts'] ) ) {
			$total_posts = count( $import_data['posts'] );
			$batch_posts = array_slice( $import_data['posts'], $offset, $this->batch_size );

			foreach ( $batch_posts as $post_id ) {
				$post_id = absint( $post_id );
				$post    = get_post( $post_id );

				if ( ! $post ) {
					++$failed_count;
					continue;
				}

				// Skip the default application form.
				if ( $default_application_form_id && $post_id === $default_application_form_id ) {
					++$skipped_count;
					continue;
				}

				// Optional: skip Elementor Kits.
				if ( in_array( $post->post_type, [ 'elementor_library' ], true ) ) {
					++$skipped_count;
					continue;
				}

				// Force delete normal posts.
				if ( wp_delete_post( $post_id, true ) ) {
					++$deleted_count;
				} else {
					++$failed_count;
				}
			}

			// Free memory after batch.
			wp_cache_flush();

			$new_offset = $offset + $this->batch_size;

			// If there are more posts to process, continue with next batch.
			if ( $new_offset < $total_posts ) {
				wp_send_json(
					[
						'continue'      => true,
						'action'        => 'delete_posts',
						'offset'        => $new_offset,
						'total'         => $total_posts,
						'processed'     => min( $new_offset, $total_posts ),
						'deleted_count' => $deleted_count,
						'failed_count'  => $failed_count,
						'skipped_count' => $skipped_count,
						'_wpnonce'      => wp_create_nonce( 'delete_posts' ),
						'message'       => sprintf(
							/* translators: 1: processed, 2: total */
							esc_html__( 'Deleting posts... %1$d of %2$d', 'cariera-core' ),
							min( $new_offset, $total_posts ),
							$total_posts
						),
					]
				);
			}

			// Clear the posts from import data.
			$import_data['posts'] = [];
			update_option( $this->theme_slug . '_' . $demo_slug . '_import_data', $import_data );
		}

		// Check if there's a next step.
		if ( ! empty( $_POST['uninstall_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$uninstall_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['uninstall_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$next_step       = $this->get_next_step( 'delete_posts', $uninstall_steps );

			if ( false !== $next_step ) {
				wp_send_json(
					[
						'next_step' => $next_step,
						'_wpnonce'  => wp_create_nonce( $next_step ),
						'message'   => sprintf(
							/* translators: 1: deleted, 2: failed, 3: skipped */
							esc_html__( 'Deleted %1$d posts (%2$d failed, %3$d skipped)', 'cariera-core' ),
							$deleted_count,
							$failed_count,
							$skipped_count
						),
					]
				);
			}
		}

		$this->send_uninstall_complete_response();
	}

	/**
	 * Delete imported terms
	 *
	 * @since 1.9.8
	 */
	public function delete_terms() {
		$this->verify_before_call_ajax( 'delete_terms' );

		$demo_slug     = $this->demo_slug;
		$import_data   = get_option( $this->theme_slug . '_' . $demo_slug . '_import_data', [] );
		$deleted_count = 0;
		$failed_count  = 0;
		$skipped_count = 0;

		if ( ! empty( $import_data['terms'] ) ) {
			foreach ( $import_data['terms'] as $term_data ) {
				if ( ! isset( $term_data['term_id'], $term_data['taxonomy'] ) ) {
					++$failed_count;
					continue;
				}

				$term_id  = absint( $term_data['term_id'] );
				$taxonomy = sanitize_text_field( $term_data['taxonomy'] );

				// Check if term exists.
				$term = get_term( $term_id, $taxonomy );
				if ( is_wp_error( $term ) || ! $term ) {
					++$skipped_count;
					continue;
				}

				// Delete the term.
				$result = wp_delete_term( $term_id, $taxonomy );

				if ( $result && ! is_wp_error( $result ) ) {
					++$deleted_count;
				} else {
					++$failed_count;
				}
			}

			// Clear the terms from import data.
			$import_data['terms'] = [];
			update_option( $this->theme_slug . '_' . $demo_slug . '_import_data', $import_data );
		}

		// Check if there's a next step.
		if ( ! empty( $_POST['uninstall_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$uninstall_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['uninstall_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$next_step       = $this->get_next_step( 'delete_terms', $uninstall_steps );

			if ( false !== $next_step ) {
				wp_send_json(
					[
						'next_step' => $next_step,
						'_wpnonce'  => wp_create_nonce( $next_step ),
						'message'   => sprintf(
							// translators: 1: deleted, 2: failed, 3: skipped.
							esc_html__( 'Deleted %1$d terms (%2$d failed, %3$d skipped)', 'cariera-core' ),
							$deleted_count,
							$failed_count,
							$skipped_count
						),
					]
				);
			}
		}

		$this->send_uninstall_complete_response();
	}

	/**
	 * Delete imported media
	 *
	 * @since 1.9.8
	 */
	public function delete_media() {
		$this->verify_before_call_ajax( 'delete_media' );

		$demo_slug     = $this->demo_slug;
		$import_data   = get_option( $this->theme_slug . '_' . $demo_slug . '_import_data', [] );
		$deleted_count = 0;
		$failed_count  = 0;
		$skipped_count = 0;

		if ( ! empty( $import_data['media'] ) ) {
			foreach ( $import_data['media'] as $attachment_id ) {
				$attachment_id = absint( $attachment_id );

				// Verify it's an attachment.
				if ( 'attachment' !== get_post_type( $attachment_id ) ) {
					++$skipped_count;
					continue;
				}

				// Force delete (also removes physical files).
				if ( wp_delete_attachment( $attachment_id, true ) ) {
					++$deleted_count;
				} else {
					++$failed_count;
				}
			}

			// Clear the media from import data.
			$import_data['media'] = [];
			update_option( $this->theme_slug . '_' . $demo_slug . '_import_data', $import_data );
		}

		// Check if there's a next step.
		if ( ! empty( $_POST['uninstall_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$uninstall_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['uninstall_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$next_step       = $this->get_next_step( 'delete_media', $uninstall_steps );

			if ( false !== $next_step ) {
				wp_send_json(
					[
						'next_step' => $next_step,
						'_wpnonce'  => wp_create_nonce( $next_step ),
						'message'   => sprintf(
							/* translators: 1: deleted, 2: failed, 3: skipped */
							esc_html__( 'Deleted %1$d media files (%2$d failed, %3$d skipped)', 'cariera-core' ),
							$deleted_count,
							$failed_count,
							$skipped_count
						),
					]
				);
			}
		}

		$this->send_uninstall_complete_response();
	}

	/**
	 * Reset widgets
	 *
	 * @since 1.9.8
	 */
	public function reset_widgets() {
		$this->verify_before_call_ajax( 'reset_widgets' );

		$demo_slug   = $this->demo_slug;
		$import_data = get_option( $this->theme_slug . '_' . $demo_slug . '_import_data', [] );
		$reset_count = 0;

		if ( ! empty( $import_data['widgets'] ) ) {
			// Reset all tracked sidebars to empty.
			$sidebars_widgets = wp_get_sidebars_widgets();

			foreach ( $import_data['widgets'] as $sidebar_id ) {
				if ( isset( $sidebars_widgets[ $sidebar_id ] ) ) {
					$sidebars_widgets[ $sidebar_id ] = [];
					++$reset_count;
				}
			}

			wp_set_sidebars_widgets( $sidebars_widgets );

			// Clear the widgets from import data.
			$import_data['widgets'] = [];
			update_option( $this->theme_slug . '_' . $demo_slug . '_import_data', $import_data );
		}

		// Check if there's a next step.
		if ( ! empty( $_POST['uninstall_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$uninstall_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['uninstall_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$next_step       = $this->get_next_step( 'reset_widgets', $uninstall_steps );

			if ( false !== $next_step ) {
				wp_send_json(
					[
						'next_step' => $next_step,
						'_wpnonce'  => wp_create_nonce( $next_step ),
						'message'   => sprintf(
							// translators: %d is the number of widget areas.
							esc_html__( 'Reset %d widget areas', 'cariera-core' ),
							$reset_count
						),
					]
				);
			}
		}

		$this->send_uninstall_complete_response();
	}

	/**
	 * Reset menu locations
	 *
	 * @since 1.9.8
	 */
	public function reset_menus() {
		$this->verify_before_call_ajax( 'reset_menus' );

		$demo_slug   = $this->demo_slug;
		$import_data = get_option( $this->theme_slug . '_' . $demo_slug . '_import_data', [] );
		$reset_count = 0;

		if ( ! empty( $import_data['menus'] ) ) {
			// Get current menu locations.
			$locations = get_theme_mod( 'nav_menu_locations', [] );

			// Remove only the imported menu locations.
			foreach ( $import_data['menus'] as $location ) {
				if ( isset( $locations[ $location ] ) ) {
					unset( $locations[ $location ] );
					++$reset_count;
				}
			}

			set_theme_mod( 'nav_menu_locations', $locations );

			// Clear the menus from import data.
			$import_data['menus'] = [];
			update_option( $this->theme_slug . '_' . $demo_slug . '_import_data', $import_data );
		}

		// Check if there's a next step.
		if ( ! empty( $_POST['uninstall_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$uninstall_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['uninstall_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$next_step       = $this->get_next_step( 'reset_menus', $uninstall_steps );

			if ( false !== $next_step ) {
				wp_send_json(
					[
						'next_step' => $next_step,
						'_wpnonce'  => wp_create_nonce( $next_step ),
						'message'   => sprintf(
							// translators: %d is the number of menu locations.
							esc_html__( 'Reset %d menu locations', 'cariera-core' ),
							$reset_count
						),
					]
				);
			}
		}

		$this->send_uninstall_complete_response();
	}

	/**
	 * Reset theme mods (customizer settings)
	 *
	 * @since 1.9.8
	 */
	public function reset_theme_mods() {
		$this->verify_before_call_ajax( 'reset_theme_mods' );

		$demo_slug   = $this->demo_slug;
		$import_data = get_option( $this->theme_slug . '_' . $demo_slug . '_import_data', [] );
		$reset_count = 0;

		if ( ! empty( $import_data['theme_mods'] ) ) {
			foreach ( $import_data['theme_mods'] as $mod_name ) {
				// Remove each theme mod.
				remove_theme_mod( sanitize_text_field( $mod_name ) );
				++$reset_count;
			}

			// Clear the theme_mods from import data.
			$import_data['theme_mods'] = [];
			update_option( $this->theme_slug . '_' . $demo_slug . '_import_data', $import_data );
		}

		// Check if there's a next step.
		if ( ! empty( $_POST['uninstall_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$uninstall_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['uninstall_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$next_step       = $this->get_next_step( 'reset_theme_mods', $uninstall_steps );

			if ( false !== $next_step ) {
				wp_send_json(
					[
						'next_step' => $next_step,
						'_wpnonce'  => wp_create_nonce( $next_step ),
						'message'   => sprintf(
							// translators: %d is the number of customizer settings.
							esc_html__( 'Reset %d customizer settings', 'cariera-core' ),
							$reset_count
						),
					]
				);
			}
		}

		$this->send_uninstall_complete_response();
	}

	/**
	 * Reset theme options
	 *
	 * @since 1.9.8
	 */
	public function reset_options() {
		$this->verify_before_call_ajax( 'reset_options' );

		$demo_slug     = $this->demo_slug;
		$import_data   = get_option( $this->theme_slug . '_' . $demo_slug . '_import_data', [] );
		$deleted_count = 0;

		if ( ! empty( $import_data['options'] ) ) {
			foreach ( $import_data['options'] as $option_name ) {
				if ( delete_option( sanitize_text_field( $option_name ) ) ) {
					++$deleted_count;
				}
			}

			// Clear the options from import data.
			$import_data['options'] = [];
			update_option( $this->theme_slug . '_' . $demo_slug . '_import_data', $import_data );
		}

		// Check if there's a next step.
		if ( ! empty( $_POST['uninstall_steps'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$uninstall_steps = explode( ',', sanitize_text_field( wp_unslash( $_POST['uninstall_steps'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$next_step       = $this->get_next_step( 'reset_options', $uninstall_steps );

			if ( false !== $next_step ) {
				wp_send_json(
					[
						'next_step' => $next_step,
						'_wpnonce'  => wp_create_nonce( $next_step ),
						'message'   => sprintf(
							/* translators: %d: deleted count */
							esc_html__( 'Reset %d theme options', 'cariera-core' ),
							$deleted_count
						),
					]
				);
			}
		}

		$this->send_uninstall_complete_response();
	}

	/**
	 * Send final uninstall response
	 *
	 * @since 1.9.8
	 */
	private function send_uninstall_complete_response() {
		$demo_slug = $this->demo_slug;

		// Clean up import data.
		delete_option( $this->theme_slug . '_' . $demo_slug . '_import_data' );
		delete_option( $this->theme_slug . '_' . $demo_slug . '_imported' );

		// Trigger uninstall completed action.
		do_action( 'cariera_uninstall_completed', $demo_slug );

		ob_start();
		cariera_get_template_part( 'backend/importer/popup-uninstall-success' );
		wp_send_json_success( ob_get_clean() );
	}
}
