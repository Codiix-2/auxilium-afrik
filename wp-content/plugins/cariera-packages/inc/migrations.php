<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Migrations {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * The addon name.
	 *
	 * @var string
	 */
	private $addon = 'Cariera Packages';

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Inject our migration items into Cariera Core's migration list.
		add_filter( 'cariera_migration_items', [ $this, 'migration_items' ] );

		// AJAX actions.
		add_action( 'wp_ajax_cariera_packages_migrate_wcpl_packages', [ $this, 'migrate_wcpl' ] );
		add_action( 'wp_ajax_cariera_packages_migrate_promotion_packages', [ $this, 'migrate_cariera_promotions' ] );
	}

	/**
	 * Check if migrations are enabled.
	 *
	 * @since 0.9.18
	 */
	protected function is_enabled() {
		return get_option( 'cariera_migrations' );
	}

	/**
	 * Add Cariera Packages migration items to the Cariera migration list.
	 *
	 * @since 0.9.18
	 *
	 * @param array $items Existing migration items from Cariera Core.
	 */
	public function migration_items( $items ) {
		// WCPL Migration.
		$items[] = [
			'name'        => esc_html__( 'Migrate WCPL Packages', 'cariera-packages' ),
			'action'      => 'cariera_packages_migrate_wcpl_packages',
			'type'        => '',
			'link'        => '',
			'addon'       => $this->addon,
			'btn_title'   => esc_html__( 'Migrate Packages', 'cariera-packages' ),
			'description' => esc_html__( 'Migrate existing WCPL data (WC products, user packages and orders) to the new Cariera Packages system.', 'cariera-packages' ),
		];

		// Cariera Promotions Migration.
		$items[] = [
			'name'        => esc_html__( 'Migrate Cariera Promotions', 'cariera-packages' ),
			'action'      => 'cariera_packages_migrate_promotion_packages',
			'type'        => '',
			'link'        => '',
			'addon'       => $this->addon,
			'btn_title'   => esc_html__( 'Migrate Promotions', 'cariera-packages' ),
			'description' => esc_html__( 'Migrate existing Cariera Promotion data (WC products, user packages and orders) to the new Cariera Packages system.', 'cariera-packages' ),
		];

		return $items;
	}

	/**
	 * Migrate WCPL to Cariera Packages.
	 *
	 * @since   0.9.18
	 * @version 0.9.19
	 */
	public function migrate_wcpl() {
		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_admin_nonce', 'nonce' );

		$results = [
			'products_migrated' => 0,
			'products_skipped'  => 0,
			'packages_migrated' => 0,
			'packages_skipped'  => 0,
			'orders_updated'    => 0,
			'errors'            => [],
		];

		// Step 1: WC Products.
		$product_id_map = \Cariera_Packages\Migrations\WCPL::migrate_products( $results );

		// Step 2: User packages (wcpl_user_packages DB table → cariera_package CPT).
		\Cariera_Packages\Migrations\WCPL::migrate_user_packages( $product_id_map, $results );

		// Step 3: WC Order line-item meta (only relevant when product IDs changed).
		\Cariera_Packages\Migrations\WCPL::migrate_order_meta( $product_id_map, $results );

		// Build a human-readable summary message.
		$summary = sprintf(
			/* translators: 1: products migrated, 2: packages migrated, 3: orders updated */
			esc_html__( 'Migration complete. Products migrated: %1$d, User packages migrated: %2$d, Orders updated: %3$d.', 'cariera-packages' ),
			$results['products_migrated'],
			$results['packages_migrated'],
			$results['orders_updated']
		);

		if ( ! empty( $results['errors'] ) ) {
			wp_send_json(
				[
					'status'  => 'warning',
					'message' => $summary,
					'data'    => $results,
				]
			);
			return;
		}

		wp_send_json(
			[
				'status'  => 'success',
				'message' => $summary,
				'data'    => $results,
			]
		);
	}

	/**
	 * Migrate Cariera Promotions to Cariera Packages.
	 *
	 * @since 0.9.18
	 */
	public function migrate_cariera_promotions() {
		// Verify the nonce for security.
		check_ajax_referer( '_cariera_core_admin_nonce', 'nonce' );

		$results = [
			'products_migrated' => 0,
			'products_skipped'  => 0,
			'packages_migrated' => 0,
			'packages_skipped'  => 0,
			'orders_updated'    => 0,
			'errors'            => [],
		];

		// Step 1: WC Products.
		$product_id_map = \Cariera_Packages\Migrations\Promotions::migrate_promotion_products( $results );

		// Step 2: User packages (cariera_promotion CPT).
		\Cariera_Packages\Migrations\Promotions::migrate_user_packages( $product_id_map, $results );

		// Step 3: WC Order line-item meta.
		\Cariera_Packages\Migrations\Promotions::migrate_order_meta( $product_id_map, $results );

		// Build a human-readable summary message.
		$summary = sprintf(
			/* translators: 1: products migrated, 2: packages migrated, 3: orders updated */
			esc_html__( 'Migration complete. Products migrated: %1$d, User packages migrated: %2$d, Orders updated: %3$d.', 'cariera-packages' ),
			$results['products_migrated'],
			$results['packages_migrated'],
			$results['orders_updated']
		);

		if ( ! empty( $results['errors'] ) ) {
			wp_send_json(
				[
					'status'  => 'warning',
					'message' => $summary,
					'data'    => $results,
				]
			);
			return;
		}

		wp_send_json(
			[
				'status'  => 'success',
				'message' => $summary,
				'data'    => $results,
			]
		);
	}
}
