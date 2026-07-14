<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HR_Manager {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Meta key for storing HR user IDs on a company post.
	 */
	const META_KEY = '_company_hr_users';

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( ! self::is_enabled() ) {
			return;
		}

		// Register backend HR field.
		add_filter( 'cariera_company_manager_fields', [ $this, 'register_hr_field' ], 99 );

		// AJAX handlers.
		add_action( 'wp_ajax_cariera_search_users', [ $this, 'ajax_search_users' ] );
		add_filter( 'cariera_select_ajax_label', [ $this, 'get_ajax_label' ], 10, 3 );

		// Frontend: Add HR-assigned companies to the AJAX select.
		add_filter( 'cariera_company_allowed_ids', [ $this, 'frontend_hr_companies' ], 10, 3 );
	}

	/**
	 * Checks if HR Manager is enabled.
	 *
	 * @since 2.0.0
	 */
	public static function is_enabled() {
		return '1' === get_option( 'cariera_hr_company_users', '0' );
	}

	/**
	 * Register HR field into company fields
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields
	 */
	public function register_hr_field( $fields ) {
		$fields[ self::META_KEY ] = [
			'label'         => esc_html__( 'HR Users', 'cariera-core' ),
			'description'   => esc_html__( 'Assign HR managers to this company.', 'cariera-core' ),
			'type'          => 'select_ajax',
			'priority'      => 24,
			'data_type'     => 'array',
			'multiple'      => true,
			'ajax_action'   => 'cariera_search_users',
			'placeholder'   => esc_html__( 'Search users...', 'cariera-core' ),
			'show_in_admin' => true,
		];

		return $fields;
	}

	/**
	 * Search users ajax.
	 *
	 * @since 2.0.0
	 */
	public function ajax_search_users() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error();
		}

		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : ''; // phpcs:ignore

		$args = [
			'number'         => 20,
			'search'         => '*' . $search . '*',
			'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
		];

		$users = get_users( $args );

		$results = [];

		foreach ( $users as $user ) {
			$results[] = [
				'id'   => $user->ID,
				'text' => sprintf(
					'%s (%s)',
					$user->display_name,
					$user->user_email
				),
			];
		}

		wp_send_json(
			[
				'results' => $results,
			]
		);
	}

	/**
	 * Label for the preselected user in the AJAX select field
	 *
	 * @since 2.0.0
	 *
	 * @param string $label
	 * @param int    $value
	 * @param array  $field
	 */
	public function get_ajax_label( $label, $value, $field ) {

		// Only handle our specific field.
		if ( empty( $field['ajax_action'] ) ) {
			return $label;
		}

		if ( 'cariera_search_users' !== $field['ajax_action'] ) {
			return $label;
		}

		$user_id = absint( $value );

		if ( ! $user_id ) {
			return $label;
		}

		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return $label;
		}

		$display_name = $user->display_name;
		$user_email   = $user->user_email;

		// The label will be "Display Name (email)".
		$label = sprintf( '%s (%s)', $display_name, $user_email );

		return apply_filters( 'cariera_company_hr_user_label', $label, $user, $field );
	}

	/**
	 * Add HR-assigned companies to the list of allowed company IDs.
	 *
	 * @since 2.0.0
	 *
	 * @param array $company_ids Existing allowed company IDs.
	 * @param int   $user_id    Current user ID.
	 * @param array $args       AJAX request arguments.
	 */
	public function frontend_hr_companies( $company_ids, $user_id, $args ) {
		$hr_companies = get_posts(
			[
				'post_type'      => \Cariera_Core\Core\Company_Manager\CPT::CPT_COMPANY,
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_query'     => [ // phpcs:ignore
					[
						'key'     => self::META_KEY,
						'value'   => '"' . $user_id . '"',
						'compare' => 'LIKE',
					],
				],
			]
		);

		if ( ! empty( $hr_companies ) ) {
			$company_ids = array_merge( $company_ids, $hr_companies );
		}

		return array_unique( $company_ids );
	}
}
