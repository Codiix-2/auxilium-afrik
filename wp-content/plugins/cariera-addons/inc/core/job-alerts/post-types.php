<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Types {

	use \Cariera_Addons\Src\Traits\Singleton;

	const CPT_ALERT      = 'job_alert';
	const META_FREQUENCY = 'alert_frequency';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_types' ], 20 );
		add_filter( 'post_types_to_delete_with_user', [ $this, 'post_types_to_delete_with_user' ] );
	}

	/**
	 * Register 'job_alert' post type.
	 *
	 * @since 0.9.2
	 */
	public function register_post_types() {
		if ( post_type_exists( self::CPT_ALERT ) ) {
			return;
		}

		$singular = esc_html__( 'Job Alert', 'cariera-addons' );
		$plural   = esc_html__( 'Job Alerts', 'cariera-addons' );

		register_post_type(
			self::CPT_ALERT,
			apply_filters(
				'cariera_addons_post_type_job_alert',
				[
					'label'               => $plural,
					'labels'              => array_merge(
						\Cariera_Addons\Helpers::create_post_type_labels( $singular, $plural ),
						[
							'all_items' => $plural,
						]
					),
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'post',
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'show_in_menu'        => 'edit.php?post_type=job_listing',
					'show_in_admin_bar'   => false,
					'supports'            => [ 'author', 'title', 'custom-fields' ],
					'has_archive'         => false,
					'show_in_nav_menus'   => false,
					'delete_with_user'    => true,
					'capabilities'        => [
						'create_posts' => false,
						'edit_post'    => 'manage_job_listings',
						'delete_post'  => 'manage_job_listings',
					],
				]
			)
		);

		if ( taxonomy_exists( 'job_listing_category' ) ) {
			register_taxonomy_for_object_type( 'job_listing_category', self::CPT_ALERT );
		}

		register_taxonomy_for_object_type( 'job_listing_type', self::CPT_ALERT );
	}

	/**
	 * Filter post types to delete when removing a user to also remove `job_alert` posts.
	 *
	 * @since 0.9.2
	 *
	 * @param array $post_types_to_delete
	 */
	public function post_types_to_delete_with_user( $post_types_to_delete ) {
		$post_types_to_delete[] = self::CPT_ALERT;

		return $post_types_to_delete;
	}

	/**
	 * Fetches the terms used in the search query for the alert.
	 *
	 * @since 0.9.2
	 *
	 * @param int $alert_id
	 */
	public static function get_alert_search_terms( $alert_id ) {
		$base_terms = [
			'categories' => [],
			'regions'    => [],
			'tags'       => [],
			'types'      => [],
		];

		if ( metadata_exists( 'post', $alert_id, 'alert_search_terms' ) ) {
			return array_merge( $base_terms, get_metadata( 'post', $alert_id, 'alert_search_terms', true ) );
		}

		return array_merge( $base_terms, self::get_legacy_search_terms( $alert_id ) );
	}

	/**
	 * Fetches the terms, populated with display names, for the search query for the alert.
	 *
	 * @since 0.9.2
	 *
	 * @param int $alert_id
	 */
	public static function get_alert_search_term_names( $alert_id ) {
		$terms = self::get_alert_search_terms( $alert_id );

		foreach ( $terms as $key => $term_ids ) {
			if ( ! empty( $term_ids ) ) {
				$term_names    = self::get_terms( $term_ids );
				$terms[ $key ] = $term_names;
			}
		}

		$keyword = get_post_meta( $alert_id, 'alert_keyword', true );

		if ( empty( $terms['regions'] ) ) {
			unset( $terms['regions'] );
			$location = get_post_meta( $alert_id, 'alert_location', true );
			if ( ! empty( $location ) ) {
				$terms = array_merge( [ 'location' => [ $location ] ], $terms );
			}
			unset( $terms['regions'] );
		}

		if ( ! empty( $keyword ) ) {
			$terms = array_merge( [ 'keywords' => [ $keyword ] ], $terms );
		}

		return $terms;
	}

	/**
	 * Fetches the legacy terms used in the search query for the alert, if they exist, and removes them from the post to migrate to the new meta structure.
	 *
	 * @since 1.1.0
	 *
	 * @param int $alert_id
	 *
	 * @return array
	 */
	private static function get_legacy_search_terms( $alert_id ) {
		$search_terms      = [];
		$taxonomy_type_map = [
			'categories' => 'job_listing_category',
			'regions'    => 'job_listing_region',
			'tags'       => 'job_listing_tag',
			'types'      => 'job_listing_type',
		];
		foreach ( $taxonomy_type_map as $key => $taxonomy_type ) {
			if ( taxonomy_exists( $taxonomy_type ) ) {
				$terms = array_filter( (array) wp_get_post_terms( $alert_id, $taxonomy_type, [ 'fields' => 'ids' ] ) );
				if ( count( $terms ) > 0 ) {
					$search_terms[ $key ] = $terms;
				}

				// Remove legacy post terms.
				wp_set_post_terms( $alert_id, [], $taxonomy_type );
			}
		}
		update_post_meta( $alert_id, 'alert_search_terms', $search_terms );

		return $search_terms;
	}

	/**
	 * Resolve term IDs to names or slugs.
	 *
	 * @since 0.9.2
	 *
	 * @param array  $term_ids Array of term IDs.
	 * @param string $fields Field to return, e.g. 'names', 'slugs'.
	 */
	public static function get_terms( $term_ids, $fields = 'names' ): array {
		if ( empty( $term_ids ) ) {
			return [];
		}

		return get_terms(
			[
				'fields'     => $fields,
				'include'    => array_map( 'absint', $term_ids ),
				'hide_empty' => false,
			]
		);
	}

	/**
	 * Get the available search term fields for alerts.
	 *
	 * @since   0.9.2
	 * @version 0.9.10
	 */
	public static function get_search_fields() {
		$term_rows = [
			'keywords' => [
				'label' => esc_html__( 'Keyword', 'cariera-addons' ),
			],
		];

		if ( get_option( 'job_manager_enable_categories' ) && wp_count_terms( 'job_listing_category' ) > 0 ) {
			$term_rows['categories'] = [
				'label' => esc_html__( 'Category', 'cariera-addons' ),
			];
		}

		if ( taxonomy_exists( \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG ) ) {
			$term_rows['tags'] = [
				'label' => esc_html__( 'Tags', 'cariera-addons' ),
			];
		}

		if ( get_option( 'job_manager_enable_types' ) && wp_count_terms( 'job_listing_types' ) > 0 ) {
			$term_rows['types'] = [
				'label' => esc_html__( 'Type', 'cariera-addons' ),
			];
		}

		if ( taxonomy_exists( 'job_listing_region' ) && wp_count_terms( 'job_listing_region' ) > 0 ) {
			$term_rows['regions'] = [
				'label' => esc_html__( 'Location', 'cariera-addons' ),
			];
		} else {
			$term_rows['location'] = [
				'label' => esc_html__( 'Location', 'cariera-addons' ),
			];
		}

		return $term_rows;
	}
}
