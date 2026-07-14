<?php

namespace Cariera_Packages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Settings option prefix.
	 *
	 * @var string
	 */
	const SETTINGS_PREFIX = 'cariera_packages_';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'cariera_settings', [ $this, 'settings' ] );
	}

	/**
	 * Get post types that support view packages.
	 *
	 * @since   0.9.7
	 * @version 0.9.9
	 *
	 * @return array Associative array of post types and their labels.
	 */
	public static function get_post_types() {
		$post_types = [
			'job_listing' => esc_html__( 'Job Listing', 'cariera-packages' ),
		];

		if ( \Cariera_Packages::cariera_company_manager_active() ) {
			$post_types['company'] = esc_html__( 'Company', 'cariera-packages' );
		}

		if ( \Cariera_Packages::wprm_active() ) {
			$post_types['resume'] = esc_html__( 'Resume', 'cariera-packages' );
		}

		if ( class_exists( 'Cariera_Events' ) ) {
			$post_types['cariera_event'] = esc_html__( 'Event', 'cariera-packages' );
		}

		return apply_filters( 'cariera_packages_settings_post_types', $post_types );
	}

	/**
	 * Cariera Packages settings.
	 *
	 * @since   0.9.0
	 * @version 0.9.24
	 *
	 * @param array $settings
	 */
	public function settings( $settings ) {
		$prefix            = self::SETTINGS_PREFIX;
		$post_type_options = self::get_post_types();

		// Add new settings under a new group or existing group.
		$settings['packages'] = [
			esc_html__( 'Packages', 'cariera-packages' ),
			'version' => CARIERA_PACKAGES_VERSION,
			[
				[
					'id'            => $prefix . 'header_submission_packages',
					'label'         => '',
					'description'   => esc_html__( 'Settings for Listing Submission Packages.', 'cariera-packages' ),
					'type'          => 'title',
					'title'         => esc_html__( 'Submission Packages', 'cariera-packages' ),
					'class_wrapper' => '',
					'attributes'    => [],
				],
				[
					'id'            => $prefix . 'submission_package',
					'label'         => esc_html__( 'Submission Packages', 'cariera-packages' ),
					'description'   => esc_html__( 'Select the post type that will be using submission packages.', 'cariera-packages' ),
					'type'          => 'multi_switch',
					'options'       => array_diff_key( $post_type_options, array_flip( [ 'company', 'cariera_event' ] ) ),
					'default'       => [
						'job_listing' => 1,
						'resume'      => 1,
					],
					'class_wrapper' => '',
					'attributes'    => [],
				],
				[
					'id'            => $prefix . 'header_promotional_packages',
					'label'         => '',
					'description'   => esc_html__( 'Settings for Listing Promotional Packages.', 'cariera-packages' ),
					'type'          => 'title',
					'title'         => esc_html__( 'Promotional Packages', 'cariera-packages' ),
					'class_wrapper' => '',
					'attributes'    => [],
				],
				[
					'id'            => $prefix . 'promotional_package',
					'label'         => esc_html__( 'Promotional Packages', 'cariera-packages' ),
					'description'   => esc_html__( 'Select the post type that will be using promotional packages.', 'cariera-packages' ),
					'type'          => 'multi_switch',
					'options'       => $post_type_options,
					'default'       => [],
					'class_wrapper' => '',
					'attributes'    => [],
				],
				[
					'id'            => $prefix . 'header_view_packages',
					'label'         => '',
					'description'   => esc_html__( 'Settings for Single Listing View Packages.', 'cariera-packages' ),
					'type'          => 'title',
					'title'         => esc_html__( 'View Packages', 'cariera-packages' ),
					'class_wrapper' => '',
					'attributes'    => [],
				],
				[
					'id'            => $prefix . 'require_view_package',
					'label'         => esc_html__( 'View Packages', 'cariera-packages' ),
					'description'   => esc_html__( 'Select the post type that requires an active view package to access and view its single listing page.', 'cariera-packages' ),
					'type'          => 'multi_switch',
					'options'       => $post_type_options,
					'default'       => [],
					'class_wrapper' => '',
					'attributes'    => [],
				],
				[
					'id'            => $prefix . 'view_featured_listing',
					'label'         => esc_html__( 'Featured Listing', 'cariera-packages' ),
					'description'   => esc_html__( 'Select the post type that feature listings can be viewed without requiring a view package.', 'cariera-packages' ),
					'type'          => 'multi_switch',
					'options'       => $post_type_options,
					'default'       => [],
					'class_wrapper' => '',
					'attributes'    => [],
				],
				[
					'id'            => $prefix . 'admin_require_view_package',
					'label'         => esc_html__( 'Admin Package Required', 'cariera-packages' ),
					'description'   => esc_html__( 'Select the post type for which administrator accounts will also require a view package to access listings.', 'cariera-packages' ),
					'type'          => 'multi_switch',
					'options'       => $post_type_options,
					'default'       => [],
					'class_wrapper' => '',
					'attributes'    => [],
				],
			],
		];

		return $settings;
	}
}
