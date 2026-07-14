<?php

namespace Cariera_Core\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WXR Importer class
 * Needed to extend the WXR_Importer class to get/set the importer protected variables,
 * for use in the multiple AJAX calls.
 */
class WXRImporter extends \Cariera_Core\Importer\WP_Importer\WXRImporter {

	/**
	 * Constructor.
	 *
	 * @param array $options Import options.
	 */
	public function __construct( $options = [] ) {
		parent::__construct( $options );

		// Set current user to $mapping variable.
		// Fixes the [WARNING] Could not find the author for ... log warning messages.
		$current_user_obj = wp_get_current_user();
		$this->mapping['user_slug'][ $current_user_obj->user_login ] = $current_user_obj->ID;

		/**
		 * Custom fix for WXR Importer on adding Elementor pages.
		 *
		 * For some kind of reason the importer still needs to unslash Elementor data
		 * the best way is to do this with a custom filter and add our own usage of 'wp_unslash'
		 *
		 * https://github.com/awesomemotive/one-click-demo-import/issues/218
		 * https://github.com/elementor/elementor/issues/10774
		 */
		add_filter( 'wxr_importer.pre_process.post_meta', [ $this, 'on_wxr_importer_pre_process_post_meta' ] );
	}

	/**
	 * Get all protected variables from the WXR_Importer needed for continuing the import.
	 *
	 * @since 1.7.3
	 */
	public function get_importer_data() {
		return [
			'mapping'            => $this->mapping,
			'requires_remapping' => $this->requires_remapping,
			'exists'             => $this->exists,
			'user_slug_override' => $this->user_slug_override,
			'url_remap'          => $this->url_remap,
			'featured_images'    => $this->featured_images,
		];
	}

	/**
	 * Sets all protected variables from the WXR_Importer needed for continuing the import.
	 *
	 * @since 1.7.3
	 *
	 * @param array $data with set variables.
	 */
	public function set_importer_data( $data ) {
		$this->mapping            = empty( $data['mapping'] ) ? [] : $data['mapping'];
		$this->requires_remapping = empty( $data['requires_remapping'] ) ? [] : $data['requires_remapping'];
		$this->exists             = empty( $data['exists'] ) ? [] : $data['exists'];
		$this->user_slug_override = empty( $data['user_slug_override'] ) ? [] : $data['user_slug_override'];
		$this->url_remap          = empty( $data['url_remap'] ) ? [] : $data['url_remap'];
		$this->featured_images    = empty( $data['featured_images'] ) ? [] : $data['featured_images'];
	}

	/**
	 * Process post meta before WXR importer.
	 *
	 * Normalize Elementor post meta on import with the new WP_importer, We need
	 * the `wp_slash` in order to avoid the unslashing during the `add_post_meta`.
	 *
	 * Fired by `wxr_importer.pre_process.post_meta` filter.
	 *
	 * @since   1.7.3
	 * @version 1.9.8
	 *
	 * @param array $post_meta Post meta.
	 * @return array Updated post meta.
	 */
	public function on_wxr_importer_pre_process_post_meta( $post_meta ) {
		// Ensure Elementor data survives the unslashing process in add_post_meta.
		if ( isset( $post_meta['key'] ) && '_elementor_data' === $post_meta['key'] ) {
			$post_meta['value'] = wp_slash( $post_meta['value'] );
		}

		return $post_meta;
	}

	/**
	 * Check whether a post already exists.
	 *
	 * @since 1.9.8
	 *
	 * @param array $data Post data to check.
	 * @return int|false Existing post ID if found, false otherwise.
	 */
	protected function post_exists( $data ) {
		$import_id  = isset( $data['post_id'] ) ? (int) $data['post_id'] : 0;
		$exists_key = $data['guid'];

		// Fast lookup if existing posts were prefetched.
		if ( ! empty( $this->options['prefill_existing_posts'] ) ) {
			$escaped_key = htmlentities( $exists_key );
			if ( isset( $this->exists['post'][ $escaped_key ] ) ) {
				return $this->exists['post'][ $escaped_key ];
			}
		}

		// Already handled during this import session.
		if ( isset( $this->exists['post'][ $exists_key ] ) ) {
			return $this->exists['post'][ $exists_key ];
		}

		global $wpdb;

		// Strategy 1: Match by stored import ID.
		if ( $import_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Import process requires direct, uncached lookups.
			$existing_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT post_id
					FROM {$wpdb->postmeta}
					WHERE meta_key = '_wxr_import_id'
					AND meta_value = %d
					LIMIT 1",
					$import_id
				)
			);

			if ( $existing_id && get_post( $existing_id ) ) {
				$this->exists['post'][ $exists_key ] = $existing_id;
				$this->mapping['post'][ $import_id ] = $existing_id;
				return $existing_id;
			}
		}

		// Strategy 2: Match by slug and post type.
		if ( ! empty( $data['post_name'] ) && ! empty( $data['post_type'] ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for performant imports.
			$existing_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID
					FROM {$wpdb->posts}
					WHERE post_name = %s
					AND post_type = %s
					AND post_status <> 'trash'
					LIMIT 1",
					$data['post_name'],
					$data['post_type']
				)
			);

			if ( $existing_id ) {
				$this->exists['post'][ $exists_key ] = $existing_id;

				if ( $import_id ) {
					$this->mapping['post'][ $import_id ] = $existing_id;
					update_post_meta( $existing_id, '_wxr_import_id', $import_id );
				}

				return $existing_id;
			}
		}

		// Strategy 3: Core fallback for standard post types only.
		if ( in_array( $data['post_type'], [ 'post', 'page', 'attachment' ], true ) ) {
			$existing_id = post_exists(
				$data['post_title'],
				$data['post_content'],
				$data['post_date']
			);

			if ( $existing_id ) {
				$this->exists['post'][ $exists_key ] = $existing_id;

				if ( $import_id ) {
					$this->mapping['post'][ $import_id ] = $existing_id;
					update_post_meta( $existing_id, '_wxr_import_id', $import_id );
				}

				return $existing_id;
			}
		}

		// Cache negative result to avoid repeated queries.
		$this->exists['post'][ $exists_key ] = false;
		return false;
	}

	/**
	 * Mark a post as existing and store its import ID.
	 *
	 * @since 1.9.8
	 *
	 * @param array $data    Post data.
	 * @param int   $post_id Existing post ID.
	 */
	protected function mark_post_exists( $data, $post_id ) {
		$exists_key                          = $data['guid'];
		$this->exists['post'][ $exists_key ] = (int) $post_id;

		$import_id = isset( $data['post_id'] ) ? (int) $data['post_id'] : 0;

		if ( $import_id ) {
			update_post_meta( $post_id, '_wxr_import_id', $import_id );
			$this->mapping['post'][ $import_id ] = (int) $post_id;
		}
	}

	/**
	 * Prefill existing post lookups and import ID mappings.
	 *
	 * @since 1.9.8
	 */
	protected function prefill_existing_posts() {
		global $wpdb;

		// Prefill GUID-based lookups.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk import optimization.
		$posts = $wpdb->get_results(
			"SELECT ID, guid
			FROM {$wpdb->posts}
			WHERE post_status <> 'trash'"
		);

		foreach ( $posts as $post ) {
			$this->exists['post'][ htmlentities( $post->guid ) ] = (int) $post->ID;
		}

		// Prefill import ID mappings.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for importer performance.
		$import_meta = $wpdb->get_results(
			"SELECT post_id, meta_value
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wxr_import_id'"
		);

		foreach ( $import_meta as $meta ) {
			$this->mapping['post'][ (int) $meta->meta_value ] = (int) $meta->post_id;
		}
	}

	/**
	 * Process menu item meta.
	 *
	 * Intentionally overridden for future extensibility.
	 *
	 * @since 1.9.8
	 *
	 * @param int   $post_id Menu item post ID.
	 * @param array $data    Post data.
	 * @param array $meta    Post meta.
	 */
	protected function process_menu_item_meta( $post_id, $data, $meta ) {
		parent::process_menu_item_meta( $post_id, $data, $meta );
	}
}
