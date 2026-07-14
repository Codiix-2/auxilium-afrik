<?php

namespace Cariera_Packages\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPL {

	/**
	 * Meta prefix used by Cariera Packages.
	 *
	 * @var string
	 */
	private static $prefix = 'cariera_packages_';

	/**
	 * Maps legacy WCPL product types to Cariera Packages package types and meta mappings.
	 *
	 * @var array
	 */
	private static $product_type_map = [
		'job_package'    => [
			'package_type'    => 'job_submission_package',
			'submission_type' => 'job_submission',

			'meta_map'        => [
				'_job_listing_limit'    => '_job_listing_limit',
				'_job_listing_duration' => '_job_listing_duration',
				'_job_listing_featured' => '_job_listing_featured',
			],
		],
		'resume_package' => [
			'package_type'    => 'resume_submission_package',
			'submission_type' => 'resume_submission',
			'meta_map'        => [
				'_resume_limit'    => '_job_listing_limit',
				'_resume_duration' => '_job_listing_duration',
				'_resume_featured' => '_job_listing_featured',
			],
		],
	];

	/**
	 * Migrates WCPL products to `cariera_package` in place.
	 *
	 * Updates products without changing their IDs and tracks results.
	 *
	 * @since 0.9.19
	 *
	 * @param array $results Migration results passed by reference.
	 */
	public static function migrate_products( array &$results ): array {
		$product_id_map = [];

		foreach ( array_keys( self::$product_type_map ) as $old_type ) {
			$product_ids = self::get_products_by_type( $old_type );

			foreach ( $product_ids as $product_id ) {
				try {
					$new_id = self::convert_product( $product_id, $old_type );
					if ( $new_id ) {
						$product_id_map[ $product_id ] = $new_id;
						++$results['products_migrated'];
					} else {
						++$results['products_skipped'];
					}
				} catch ( \Exception $e ) {
					$results['errors'][] = sprintf(
						/* translators: 1: product ID, 2: error message */
						esc_html__( 'Product #%1$d: %2$s', 'cariera-packages' ),
						$product_id,
						$e->getMessage()
					);
					++$results['products_skipped'];
				}
			}
		}

		return $product_id_map;
	}

	/**
	 * Returns all product IDs of a given legacy WCPL product type.
	 *
	 * @since 0.9.19
	 *
	 * @param string $product_type Legacy WooCommerce product type slug (e.g. `job_package`).
	 */
	public static function get_products_by_type( string $product_type ): array {
		$query = new \WP_Query(
			[
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'tax_query'      => [
					[
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => $product_type,
					],
				],
			]
		);

		return $query->posts ? array_map( 'intval', $query->posts ) : [];
	}

	/**
	 * Converts a WCPL product to `cariera_package` product.
	 *
	 * Updates the product type and remaps meta based on the defined mapping.
	 * Skips already migrated or unsupported product types.
	 *
	 * @since   0.9.19
	 * @version 0.9.23
	 *
	 * @param int    $product_id Product post ID.
	 * @param string $old_type   Legacy WCPL product type slug.
	 *
	 * @throws \Exception If creating the `cariera_package` term fails.
	 */
	public static function convert_product( int $product_id, string $old_type ) {
		if ( get_post_meta( $product_id, '_cariera_packages_wcpl_migrated', true ) ) {
			return false;
		}

		// Validate mapping.
		if ( ! isset( self::$product_type_map[ $old_type ] ) ) {
			return false;
		}

		$map = self::$product_type_map[ $old_type ];

		// Ensure the cariera_package product-type term exists.
		$term_type = get_term_by( 'slug', 'cariera_package', 'product_type' );

		if ( ! $term_type ) {
			$result = wp_insert_term( 'cariera_package', 'product_type', [ 'slug' => 'cariera_package' ] );

			if ( is_wp_error( $result ) ) {
				throw new \Exception(
					esc_html__( 'Failed to create the cariera_package product type term.', 'cariera-packages' )
				);
			}

			// Re-fetch for consistency.
			$term = get_term_by( 'slug', 'cariera_package', 'product_type' );

			if ( ! $term ) {
				throw new \Exception( 'cariera_package term could not be retrieved after creation.' );
			}
		}

		// Switch product type.
		$result = wp_set_object_terms( $product_id, 'cariera_package', 'product_type' );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( 'Failed to assign product type.' );
		}

		// Remove downloadable flag.
		delete_post_meta( $product_id, '_downloadable' );

		// Set package type.
		if ( ! empty( $map['package_type'] ) ) {
			update_post_meta( $product_id, '_package_type', $map['package_type'] );
		}

		// Migrate meta using mapping.
		foreach ( $map['meta_map'] as $old_key => $new_key ) {
			self::remap_product_meta( $product_id, $old_key, $new_key );
		}

		// Mark as migrated.
		update_post_meta( $product_id, '_cariera_packages_wcpl_migrated', '1' );
		update_post_meta( $product_id, '_cariera_packages_wcpl_migrated_from', $old_type );

		return $product_id;
	}

	/**
	 * Copies a product meta value from one key to another when the keys differ.
	 *
	 * @since 0.9.19
	 *
	 * @param int    $product_id Product post ID.
	 * @param string $old_key    Source meta key.
	 * @param string $new_key    Destination meta key.
	 */
	private static function remap_product_meta( int $product_id, string $old_key, string $new_key ): void {
		// Skip if identical keys (no-op).
		if ( $old_key === $new_key ) {
			return;
		}

		$value = get_post_meta( $product_id, $old_key, true );

		if ( '' === $value ) {
			return;
		}

		update_post_meta( $product_id, $new_key, $value );
	}

	/**
	 * Migrates WCPL user packages to `cariera_package` posts.
	 *
	 * Creates one CPT entry per unmigrated row and maps it to the new product IDs from Step 1.
	 *
	 * @since 0.9.19
	 *
	 * @param array $product_id_map Map of old → new product IDs from Step 1.
	 * @param array $results        Migration results passed by reference.
	 */
	public static function migrate_user_packages( array $product_id_map, array &$results ): void {
		$rows = self::get_wcpl_user_packages();

		if ( empty( $rows ) ) {
			return;
		}

		foreach ( $rows as $row ) {
			try {
				$migrated = self::convert_user_package( $row, $product_id_map );
				if ( $migrated ) {
					++$results['packages_migrated'];
				} else {
					++$results['packages_skipped'];
				}
			} catch ( \Exception $e ) {
				$results['errors'][] = sprintf(
					/* translators: 1: WCPL package row ID, 2: error message */
					esc_html__( 'WCPL package #%1$d: %2$s', 'cariera-packages' ),
					(int) $row->id,
					$e->getMessage()
				);
				++$results['packages_skipped'];
			}
		}
	}

	/**
	 * Retrieves all WCPL user package rows.
	 *
	 * @since   0.9.19
	 * @version 0.9.23
	 */
	private static function get_wcpl_user_packages(): array {
		global $wpdb;

		$table = $wpdb->prefix . 'wcpl_user_packages';

		// Check if table exists.
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( empty( $exists ) ) {
			return [];
		}

		$rows = $wpdb->get_results( "SELECT * FROM {$table}" ); // phpcs:ignore

		return is_array( $rows ) ? $rows : [];
	}

	/**
	 * Converts a WCPL user package row to a `cariera_package` post.
	 *
	 * Skips already migrated rows using `_wcpl_row_id` as an idempotency key.
	 *
	 * @since 0.9.19
	 *
	 * @param object $row            WCPL row object.
	 * @param array  $product_id_map Map of old → new product IDs.
	 *
	 * @throws \Exception If package type resolution or post creation fails.
	 */
	public static function convert_user_package( object $row, array $product_id_map ) {
		$wcpl_row_id = (int) $row->id;

		// Check whether the post exists.
		$existing = get_posts(
			[
				'post_type'      => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => [
					[
						'key'   => '_wcpl_row_id',
						'value' => $wcpl_row_id,
					],
				],
			]
		);

		if ( ! empty( $existing ) ) {
			return false;
		}

		// Resolve product ID.
		$old_product_id = (int) $row->product_id;
		$new_product_id = $product_id_map[ $old_product_id ] ?? $old_product_id;

		// Determine Cariera Packages package type from the converted product meta.
		$package_type = get_post_meta( $new_product_id, '_package_type', true );

		// Fallback: derive from current WC product type term if meta isn't set yet.
		if ( ! $package_type && $old_product_id ) {
			$terms = wp_get_object_terms( $old_product_id, 'product_type', [ 'fields' => 'slugs' ] );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$old_wc_type  = reset( $terms );
				$package_type = self::$product_type_map[ $old_wc_type ]['package_type'] ?? '';
			}
		}

		if ( ! $package_type ) {
			throw new \Exception(
				esc_html__( 'Could not resolve package type; run Step 1 (product migration) first.', 'cariera-packages' )
			);
		}

		// Resolve the submission_type (e.g. 'job_submission') from the package_type.
		$submission_type = self::submission_type_from_package_type( $package_type );

		// Gather row values.
		$user_id  = (int) $row->user_id;
		$order_id = (int) $row->order_id;
		$limit    = (int) $row->package_limit;
		$count    = (int) $row->package_count;
		$duration = (int) $row->package_duration;
		$featured = (int) $row->package_featured;

		// Derive a meaningful post title from the linked product.
		$product    = wc_get_product( $new_product_id );
		$post_title = $product ? $product->get_title() : sprintf(
			/* translators: %d: WCPL row ID */
			esc_html__( 'Migrated Package #%d', 'cariera-packages' ),
			$wcpl_row_id
		);

		// Create the cariera_package post.
		$new_id = wp_insert_post(
			[
				'post_type'   => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
				'post_status' => 'publish',
				'post_title'  => $post_title,
				'post_author' => $user_id,
			]
		);

		if ( ! $new_id || is_wp_error( $new_id ) ) {
			throw new \Exception( esc_html__( 'wp_insert_post failed.', 'cariera-packages' ) );
		}

		$prefix = self::$prefix;

		// General package meta.
		update_post_meta( $new_id, "{$prefix}user_id", $user_id );
		update_post_meta( $new_id, "{$prefix}product_id", $new_product_id );
		update_post_meta( $new_id, "{$prefix}order_id", $order_id );
		update_post_meta( $new_id, "{$prefix}package_type", $package_type );

		// Submission-specific meta (e.g. cariera_packages_job_submission_limit).
		update_post_meta( $new_id, "{$prefix}{$submission_type}_limit", $limit );
		update_post_meta( $new_id, "{$prefix}{$submission_type}_duration", $duration );
		update_post_meta( $new_id, "{$prefix}{$submission_type}_count", $count );
		update_post_meta( $new_id, "{$prefix}{$submission_type}_featured", $featured );

		// Idempotency marker – ties this CPT post back to its source row.
		update_post_meta( $new_id, '_wcpl_row_id', $wcpl_row_id );
		update_post_meta( $new_id, '_wcpl_to_cariera_packages_migrated', '1' );

		// Update any listings that reference this WCPL row ID so they point to the new package.
		self::repoint_listing_meta( $wcpl_row_id, $new_id, $new_product_id );

		// Delete the original WCPL row.
		self::delete_wcpl_user_package( $wcpl_row_id );

		return $new_id;
	}

	/**
	 * Resolves submission type from a package type slug.
	 *
	 * @since 0.9.19
	 *
	 * @param string $package_type Package type slug.
	 * @return string Submission type (e.g. `job_submission`).
	 */
	private static function submission_type_from_package_type( string $package_type ): string {
		foreach ( self::$product_type_map as $map ) {
			if ( $map['package_type'] === $package_type ) {
				return $map['submission_type'];
			}
		}

		// Generic fallback: strip '_package' suffix.
		return str_replace( '_package', '', $package_type );
	}

	/**
	 * Updates listings to reference the new package and product IDs.
	 *
	 * Replaces WCPL `_user_package_id` (row ID) with the new CPT ID.
	 *
	 * @since 0.9.19
	 *
	 * @param int $wcpl_row_id    Original WCPL row ID.
	 * @param int $new_package_id New `cariera_package` post ID.
	 * @param int $new_product_id Product ID linked to the package.
	 */
	private static function repoint_listing_meta( int $wcpl_row_id, int $new_package_id, int $new_product_id ): void {
		global $wpdb;

		// Find all listings that still carry the old WCPL row ID.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$listing_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				 WHERE meta_key = '_user_package_id'
				 AND meta_value = %s",
				(string) $wcpl_row_id
			)
		);

		if ( empty( $listing_ids ) ) {
			return;
		}

		foreach ( $listing_ids as $listing_id ) {
			$listing_id = (int) $listing_id;

			// Point to the new CPT post.
			update_post_meta( $listing_id, '_user_package_id', $new_package_id );

			// Keep _package_id consistent with the (possibly same) product.
			update_post_meta( $listing_id, '_package_id', $new_product_id );
		}
	}

	/**
	 * Deletes a WCPL user package row.
	 *
	 * @since 0.9.19
	 *
	 * @param int $row_id WCPL row ID.
	 */
	private static function delete_wcpl_user_package( int $row_id ): void {
		global $wpdb;

		// phpcs:ignore
		$wpdb->delete( "{$wpdb->prefix}wcpl_user_packages", [ 'id' => $row_id ], [ '%d' ] );
	}

	/**
	 * Updates WooCommerce order item meta to reflect migrated product IDs.
	 *
	 * Because WCPL product conversion is in-place (IDs stay the same), this
	 * step is typically a no-op. It is kept for forward-compatibility with any
	 * scenario in which products are recreated with new IDs.
	 *
	 * @since 0.9.19
	 *
	 * @param array $product_id_map Map of old product IDs → new product IDs.
	 * @param array $results        Migration results passed by reference.
	 */
	public static function migrate_order_meta( array $product_id_map, array &$results ): void {
		// Only process products whose ID actually changed.
		$changed = array_filter(
			$product_id_map,
			static function ( int $new_id, int $old_id ): bool {
				return $new_id !== $old_id;
			},
			ARRAY_FILTER_USE_BOTH
		);

		if ( empty( $changed ) ) {
			return;
		}

		global $wpdb;

		foreach ( $changed as $old_product_id => $new_product_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$rows = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}woocommerce_order_itemmeta
					 SET   meta_value = %d
					 WHERE meta_key   = '_product_id'
					 AND   meta_value = %d",
					$new_product_id,
					$old_product_id
				)
			);

			if ( $rows ) {
				$results['orders_updated'] += (int) $rows;
			}
		}
	}
}
