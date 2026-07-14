<?php

namespace Cariera_Addons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpers {

	/**
	 * Create post type labels.
	 *
	 * @since 0.9.3
	 *
	 * @param string $singular Post type singular label.
	 * @param string $plural   Post type plural label.
	 */
	public static function create_post_type_labels( $singular, $plural ) {
		$lower_case_plural = function_exists( 'mb_strtolower' ) ? mb_strtolower( $plural, 'UTF-8' ) : strtolower( $plural );

		$labels = [
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			'add_new'            => esc_html__( 'Add New', 'cariera-addons' ),
			// translators: Placeholder is the singular post type label.
			'add_new_item'       => sprintf( __( 'Add New %s', 'cariera-addons' ), $singular ),
			'edit'               => esc_html__( 'Edit', 'cariera-addons' ),
			// translators: Placeholder is the item title/name.
			'edit_item'          => sprintf( __( 'Edit %s', 'cariera-addons' ), $singular ),
			// translators: Placeholder is the singular post type label.
			'new_item'           => sprintf( __( 'New %s', 'cariera-addons' ), $singular ),
			// translators: Placeholder is the plural post type label.
			'all_items'          => sprintf( __( 'All %s', 'cariera-addons' ), $plural ),
			// translators: Placeholder is the singular post type label.
			'view'               => sprintf( __( 'View %s', 'cariera-addons' ), $singular ),
			// translators: Placeholder is the singular post type label.
			'view_item'          => sprintf( __( 'View %s', 'cariera-addons' ), $singular ),
			// translators: Placeholder is the plural post type label.
			'search_items'       => sprintf( __( 'Search %s', 'cariera-addons' ), $plural ),
			// translators: Placeholder is the lower-case plural post type label.
			'not_found'          => sprintf( __( 'No %s found', 'cariera-addons' ), $lower_case_plural ),
			// translators: Placeholder is the lower-case plural post type label.
			'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'cariera-addons' ), $lower_case_plural ),
			// translators: Placeholder is the singular post type label.
			'parent'             => sprintf( __( 'Parent %s', 'cariera-addons' ), $singular ),
		];

		return $labels;
	}

	/**
	 * Generate taxonomy labels.
	 *
	 * @since 0.9.9
	 *
	 * @param string $singular
	 * @param string $plural
	 */
	public static function create_taxonomy_labels( $singular, $plural ) {
		return [
			'name'              => $plural,
			'singular_name'     => $singular,
			// translators: %s: plural taxonomy name.
			'search_items'      => sprintf( __( 'Search %s', 'cariera-addons' ), $plural ),
			// translators: %s: plural taxonomy name.
			'all_items'         => sprintf( __( 'All %s', 'cariera-addons' ), $plural ),
			// translators: %s: singular taxonomy name.
			'parent_item'       => sprintf( __( 'Parent %s', 'cariera-addons' ), $singular ),
			// translators: %s: singular taxonomy name.
			'parent_item_colon' => sprintf( __( 'Parent %s:', 'cariera-addons' ), $singular ),
			// translators: %s: singular taxonomy name.
			'edit_item'         => sprintf( __( 'Edit %s', 'cariera-addons' ), $singular ),
			// translators: %s: singular taxonomy name.
			'update_item'       => sprintf( __( 'Update %s', 'cariera-addons' ), $singular ),
			// translators: %s: singular taxonomy name.
			'add_new_item'      => sprintf( __( 'Add New %s', 'cariera-addons' ), $singular ),
			// translators: %s: singular taxonomy name.
			'new_item_name'     => sprintf( __( 'New %s Name', 'cariera-addons' ), $singular ),
		];
	}

	/**
	 * Load and render a template, and return it's content.
	 *
	 * @since   0.9.2
	 * @version 1.1.0
	 *
	 * @param string $name Template file name.
	 * @param array  $args Variables for the template.
	 */
	public static function get_template( $name, $args ) {
		ob_start();

		get_job_manager_template( $name, $args, 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );

		return ob_get_clean();
	}

	/**
	 * Get core features list.
	 *
	 * @since 1.1.0
	 */
	public static function get_core_features() {
		return apply_filters(
			'cariera_addons_core_features',
			[
				'application-deadline' => esc_html__( 'Application Deadline', 'cariera-addons' ),
				'applications'         => esc_html__( 'Applications', 'cariera-addons' ),
				'bookmarks'            => esc_html__( 'Bookmarks', 'cariera-addons' ),
				'job-alerts'           => esc_html__( 'Job Alerts', 'cariera-addons' ),
				'resumes'              => esc_html__( 'Resumes', 'cariera-addons' ),
				'tags'                 => esc_html__( 'Tags', 'cariera-addons' ),
			]
		);
	}

	/**
	 * Checks if a core feature is enabled.
	 *
	 * @since 0.9.11
	 *
	 * @param string $feature Feature key to check (e.g., 'resumes').
	 */
	public static function core_feature_is_enabled( $feature ) {
		if ( empty( $feature ) || ! is_string( $feature ) ) {
			return false;
		}

		$core_features = get_option( 'cariera_addons_core_features', [] );

		// Feature must exist and be truthy (not 0, '', false).
		return ! empty( $core_features[ $feature ] );
	}
}
