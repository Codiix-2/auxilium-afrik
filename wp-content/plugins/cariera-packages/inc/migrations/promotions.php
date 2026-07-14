<?php

namespace Cariera_Packages\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Promotions {

	/**
	 * Meta prefix used by Cariera Packages.
	 *
	 * @var string
	 */
	private static $prefix = 'cariera_packages_';

	/**
	 * Maps legacy Cariera promotion product types to new package types.
	 *
	 * @var array
	 */
	private static $product_type_map = [
		'job_promotion_package'     => [
			'package_type' => 'job_promotional_package',
			'duration_key' => '_job_promotion_duration',
		],
		'company_promotion_package' => [
			'package_type' => 'company_promotional_package',
			'duration_key' => '_company_promotion_duration',
		],
		'resume_promotion_package'  => [
			'package_type' => 'resume_promotional_package',
			'duration_key' => '_resume_promotion_duration',
		],
	];

	/**
	 * Migrates legacy promotion products to `cariera_package` products.
	 *
	 * Converts products in place, updates their type and meta, and tracks results.
	 * Returns a map of original product IDs to their new (or same) IDs.
	 *
	 * @since 0.9.18
	 *
	 * @param array $results Migration results passed by reference.
	 */
	public static function migrate_promotion_products( array &$results ) {
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
	 * Retrieves product IDs by legacy promotion product type.
	 *
	 * @since 0.9.18
	 *
	 * @param string $product_type Legacy WooCommerce product type slug.
	 */
	public static function get_products_by_type( $product_type ) {
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
	 * Converts a single legacy promotion product to a `cariera_package`.
	 *
	 * Updates the product in place by changing its type and assigning the
	 * appropriate package meta. Ensures idempotency by skipping already
	 * migrated products.
	 *
	 * @since   0.9.18
	 * @version 0.9.23
	 *
	 * @param int    $product_id Product ID.
	 * @param string $old_type   Legacy product type slug.
	 * @return int|false Product ID on success, false if skipped.
	 *
	 * @throws \Exception If the conversion fails.
	 */
	public static function convert_product( $product_id, $old_type ) {
		// Skip if already migrated.
		if ( get_post_meta( $product_id, '_cariera_packages_migrated', true ) ) {
			return false;
		}

		$map          = self::$product_type_map[ $old_type ];
		$package_type = $map['package_type'];
		$duration_key = $map['duration_key'];

		// Read the old duration.
		$duration = absint( get_post_meta( $product_id, '_promotion_duration', true ) );
		if ( ! $duration ) {
			// Default from the old system.
			$duration = 7;
		}

		// Update the WC product type term.
		$type_term = get_term_by( 'slug', 'cariera_package', 'product_type' );
		if ( ! $type_term ) {
			$result = wp_insert_term( 'cariera_package', 'product_type', [ 'slug' => 'cariera_package' ] );

			if ( is_wp_error( $result ) ) {
				throw new \Exception(
					esc_html__( 'Failed to create cariera_package product type term.', 'cariera-packages' )
				);
			}

			$type_term = get_term_by( 'slug', 'cariera_package', 'product_type' );
		}

		wp_set_object_terms( $product_id, 'cariera_package', 'product_type' );

		// New package meta.
		update_post_meta( $product_id, '_package_type', $package_type );
		update_post_meta( $product_id, $duration_key, $duration );

		// Mark as migrated so re-running is safe.
		update_post_meta( $product_id, '_cariera_packages_migrated', '1' );
		update_post_meta( $product_id, '_cariera_packages_migrated_from', $old_type );

		return $product_id;
	}

	/**
	 * Migrates user packages from `cariera_promotion` to `cariera_package`.
	 *
	 * Iterates over all legacy posts and converts them using the product
	 * mapping generated in Step 1.
	 *
	 * @since 0.9.18
	 *
	 * @param array $product_id_map Map of old to new product IDs.
	 * @param array $results        Migration results passed by reference.
	 */
	public static function migrate_user_packages( array $product_id_map, array &$results ) {
		$packages = get_posts(
			[
				'post_type'        => 'cariera_promotion',
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'suppress_filters' => false,
				'fields'           => 'ids',
			]
		);

		if ( empty( $packages ) ) {
			return;
		}

		foreach ( $packages as $old_package_id ) {
			try {
				$migrated = self::convert_user_package( $old_package_id, $product_id_map );
				if ( $migrated ) {
					++$results['packages_migrated'];
				} else {
					++$results['packages_skipped'];
				}
			} catch ( \Exception $e ) {
				$results['errors'][] = sprintf(
					/* translators: 1: package ID, 2: error message */
					esc_html__( 'Package #%1$d: %2$s', 'cariera-packages' ),
					$old_package_id,
					$e->getMessage()
				);
				++$results['packages_skipped'];
			}
		}
	}

	/**
	 * Converts a single `cariera_promotion` post to `cariera_package`.
	 *
	 * Creates a new package post, migrates relevant meta, and links it to the
	 * corresponding product and listing. The original post is marked as migrated
	 * and moved to trash.
	 *
	 * @since 0.9.18
	 *
	 * @param int   $old_id         Original promotion post ID.
	 * @param array $product_id_map Map of old to new product IDs.
	 * @return int|false New package ID on success, false if skipped.
	 *
	 * @throws \Exception If the package type cannot be resolved or creation fails.
	 */
	public static function convert_user_package( $old_id, array $product_id_map ) {
		// Skip if already migrated.
		if ( get_post_meta( $old_id, '_cariera_packages_migrated', true ) ) {
			return false;
		}

		$old_post = get_post( $old_id );
		if ( ! $old_post || 'cariera_promotion' !== $old_post->post_type ) {
			return false;
		}

		// Gather old meta.
		$old_user_id    = absint( get_post_meta( $old_id, '_user_id', true ) );
		$old_product_id = absint( get_post_meta( $old_id, '_product_id', true ) );
		$old_order_id   = absint( get_post_meta( $old_id, '_order_id', true ) );
		$old_duration   = absint( get_post_meta( $old_id, '_duration', true ) );
		$old_listing_id = absint( get_post_meta( $old_id, '_listing_id', true ) );
		$old_expires    = get_post_meta( $old_id, '_expires', true );

		// Resolve the new product ID (may be the same if converted in-place).
		$new_product_id = isset( $product_id_map[ $old_product_id ] ) ? $product_id_map[ $old_product_id ] : $old_product_id;

		// Determine package type from the (now converted) product meta.
		$package_type = get_post_meta( $new_product_id, '_package_type', true );

		// Fallback: derive package type from old WC product type if meta isn't set yet.
		if ( ! $package_type && $old_product_id ) {
			$terms = wp_get_object_terms( $old_product_id, 'product_type', [ 'fields' => 'slugs' ] );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$old_wc_type  = reset( $terms );
				$package_type = isset( self::$product_type_map[ $old_wc_type ] ) ? self::$product_type_map[ $old_wc_type ]['package_type'] : '';
			}
		}

		if ( ! $package_type ) {
			throw new \Exception( esc_html__( 'Could not determine package type; product may not have been migrated yet.', 'cariera-packages' ) );
		}

		// Determine post status for the new package. Preserve the meaningful states: active (publish), trashed = expired.
		$new_status = $old_post->post_status;
		if ( ! in_array( $new_status, [ 'publish', 'draft', 'trash' ], true ) ) {
			$new_status = 'draft';
		}

		// Create the new cariera_package post.
		$new_id = wp_insert_post(
			[
				'post_type'   => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
				'post_status' => $new_status,
				'post_title'  => ! empty( $old_post->post_title ) ? $old_post->post_title : sprintf( '#%d', $old_id ),
				'post_author' => $old_user_id,
				'post_date'   => $old_post->post_date,
			]
		);

		if ( ! $new_id || is_wp_error( $new_id ) ) {
			throw new \Exception( esc_html__( 'wp_insert_post failed.', 'cariera-packages' ) );
		}

		$prefix = self::$prefix;

		// Add new meta.
		update_post_meta( $new_id, "{$prefix}user_id", $old_user_id );
		update_post_meta( $new_id, "{$prefix}product_id", $new_product_id );
		update_post_meta( $new_id, "{$prefix}order_id", $old_order_id );
		update_post_meta( $new_id, "{$prefix}package_type", $package_type );
		update_post_meta( $new_id, "{$prefix}listing_promotion_duration", $old_duration );

		if ( $old_listing_id ) {
			update_post_meta( $new_id, "{$prefix}promoted_listing_id", $old_listing_id );

			// Keep the listing's back-reference up to date.
			update_post_meta( $old_listing_id, '_promo_package_id', $new_id );
		}

		if ( $old_expires ) {
			update_post_meta( $new_id, "{$prefix}promotion_expires", $old_expires );

			// Also write the listing-side expiry used by some queries.
			if ( $old_listing_id ) {
				update_post_meta( $old_listing_id, '_promotion_expires', $old_expires );
			}
		}

		// Mark old package as migrated (keeps a trail without deleting data).
		update_post_meta( $old_id, '_cariera_packages_migrated', '1' );
		update_post_meta( $old_id, '_cariera_packages_new_id', $new_id );

		// Trash the old package so it no longer pollutes queries but can be recovered manually if something went wrong.
		wp_trash_post( $old_id );

		return $new_id;
	}

	/**
	 * Remaps legacy order item meta for migrated products.
	 *
	 * Updates the `_listing_id` and `_product_id` meta for order items so
	 * they remain consistent after product migration. Only affects products
	 * that were converted into new posts (not in-place conversions).
	 *
	 * @since 0.9.18
	 *
	 * @param array $product_id_map Map of old product IDs to new product IDs.
	 * @param array $results        Migration results passed by reference.
	 */
	public static function migrate_order_meta( array $product_id_map, array &$results ) {
		// Detect products that were recreated (ID changed).
		$changed = array_filter(
			$product_id_map,
			static function ( $new_id, $old_id ) {
				return $new_id !== $old_id;
			},
			ARRAY_FILTER_USE_BOTH
		);

		if ( empty( $changed ) ) {
			return;
		}

		global $wpdb;

		foreach ( $changed as $old_product_id => $new_product_id ) {
			// Update stored product ID in order item meta.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$rows = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}woocommerce_order_itemmeta
					 SET meta_value = %d
					 WHERE meta_key   = '_product_id'
					 AND   meta_value = %d",
					$new_product_id,
					$old_product_id
				)
			);

			// Count updated rows.
			if ( $rows ) {
				$results['orders_updated'] += (int) $rows;
			}
		}
	}
}
