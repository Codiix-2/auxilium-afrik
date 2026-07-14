<?php

namespace Cariera_Packages\Post_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Package {

	use \Cariera_Packages\Src\Traits\Singleton;

	// Post type slugs.
	const CPT_PACKAGE = 'cariera_package';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register Post Type.
		add_action( 'init', [ $this, 'register_post_type' ] );

		// Custom Columns.
		add_action( 'manage_edit-cariera_package_columns', [ $this, 'custom_columns' ] );
		add_action( 'manage_cariera_package_posts_custom_column', [ $this, 'custom_columns_manage' ] );

		// Add screens for wpjm scripts and enqueue admin scripts.
		add_filter( 'job_manager_admin_screen_ids', [ $this, 'add_screen_ids' ] );
	}

	/**
	 * Register Cariera Packages CPT.
	 *
	 * @since   0.9.0
	 * @version 0.9.8
	 */
	public function register_post_type() {
		$labels = [
			'name'               => esc_html__( 'Cariera Package', 'cariera-packages' ),
			'singular_name'      => esc_html__( 'Cariera Package', 'cariera-packages' ),
			'add_new'            => esc_html__( 'Add New Package', 'cariera-packages' ),
			'add_new_item'       => esc_html__( 'Add New Package', 'cariera-packages' ),
			'edit_item'          => esc_html__( 'Edit Package', 'cariera-packages' ),
			'new_item'           => esc_html__( 'New Package', 'cariera-packages' ),
			'all_items'          => esc_html__( 'Cariera Packages', 'cariera-packages' ),
			'view_item'          => esc_html__( 'View Package', 'cariera-packages' ),
			'search_items'       => esc_html__( 'Search Package', 'cariera-packages' ),
			'not_found'          => esc_html__( 'No Packages found', 'cariera-packages' ),
			'not_found_in_trash' => esc_html__( 'No Packages found in Trash', 'cariera-packages' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'Cariera Packages', 'cariera-packages' ),
		];

		register_post_type(
			self::CPT_PACKAGE,
			[
				'labels'             => apply_filters( 'cariera_packages_postype_field_labels', $labels ),
				'supports'           => [ 'title' ],
				'public'             => true,
				'has_archive'        => false,
				'publicly_queryable' => false,
				'show_in_menu'       => 'users.php',
			]
		);
	}

	/**
	 * Custom admin columns for post type
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public function custom_columns() {
		$fields = [
			'cb'           => '<input type="checkbox" />',
			'title'        => esc_html__( 'Title', 'cariera-packages' ),
			'package_type' => esc_html__( 'Package Type', 'cariera-packages' ),
			'user_author'  => esc_html__( 'User', 'cariera-packages' ),
			'product_id'   => esc_html__( 'Product ID', 'cariera-packages' ),
			'order_id'     => esc_html__( 'Order ID', 'cariera-packages' ),
			'date'         => esc_html__( 'Date', 'cariera-packages' ),
		];

		return $fields;
	}

	/**
	 * Custom admin columns implementation
	 *
	 * @since   0.9.0
	 * @version 0.9.21
	 *
	 * @param string $column
	 */
	public static function custom_columns_manage( $column ) {
		global $post;
		$prefix = 'cariera_packages_';

		switch ( $column ) {
			case 'package_type':
				$package_type = \Cariera_Packages\Helpers::get_package_type( $post->ID );

				// If stored as an array, take the first value.
				if ( is_array( $package_type ) && ! empty( $package_type[0] ) ) {
					$package_type = $package_type[0];
				}

				// Flatten package types in one line.
				$flat_package_types = array_merge( ...array_values( self::package_types() ) );

				echo ! empty( $flat_package_types[ $package_type ] ) ? esc_html( $flat_package_types[ $package_type ] ) : '-';
				break;
			case 'product_id':
				$product_id = get_post_meta( $post->ID, $prefix . 'product_id', true );

				if ( ! empty( $product_id ) ) {
					$url = get_edit_post_link( $product_id );
					echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $product_id ) . '</a>';
				} else {
					echo '-';
				}
				break;
			case 'order_id':
				$order_id = get_post_meta( $post->ID, $prefix . 'order_id', true );

				if ( ! empty( $order_id ) ) {
					$url = get_edit_post_link( $order_id );
					if ( $url ) {
						echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $order_id ) . '</a>';
					} else {
						echo esc_html( $order_id );
					}
				} else {
					echo '-';
				}
				break;
			case 'user_author':
				$user_id = get_post_meta( $post->ID, $prefix . 'user_id', true );

				if ( $user_id ) {
					$user_info = get_userdata( $user_id );

					if ( $user_info ) {
						echo esc_html( $user_info->display_name );
					} else {
						echo esc_html__( 'User not found', 'cariera-packages' );
					}
				} else {
					echo esc_html__( 'No user assigned', 'cariera-packages' );
				}

				break;
		}
	}

	/**
	 * All packages types.
	 *
	 * @since   0.9.0
	 * @version 0.9.15
	 *
	 * TODO: Add submission packages for the other CPTs here.
	 */
	public static function package_types() {
		$package_types = [];

		// Job Packages.
		$package_types['Job Packages'] = [
			'job_submission_package'  => esc_html__( 'Job Submission Package', 'cariera-packages' ),
			'job_promotional_package' => esc_html__( 'Job Promotional Package', 'cariera-packages' ),
			'job_view_package'        => esc_html__( 'Job View Package', 'cariera-packages' ),
		];

		// Company Packages.
		if ( \Cariera_Packages::cariera_company_manager_active() ) {
			$package_types['Company Packages'] = [
				'company_promotional_package' => esc_html__( 'Company Promotional Package', 'cariera-packages' ),
				'company_view_package'        => esc_html__( 'Company View Package', 'cariera-packages' ),
			];
		}

		// Resume Packages.
		if ( \Cariera_Packages::wprm_active() ) {
			$package_types['Resume Packages'] = [
				'resume_submission_package'  => esc_html__( 'Resume Submission Package', 'cariera-packages' ),
				'resume_promotional_package' => esc_html__( 'Resume Promotional Package', 'cariera-packages' ),
				'resume_view_package'        => esc_html__( 'Resume View Package', 'cariera-packages' ),
			];
		}

		// Event Packages.
		if ( class_exists( 'Cariera_Events' ) ) {
			$package_types['Event Packages'] = [
				'event_promotional_package' => esc_html__( 'Event Promotional Package', 'cariera-packages' ),
				'event_view_package'        => esc_html__( 'Event View Package', 'cariera-packages' ),
			];
		}

		/**
		 * Filter to allow extensions or themes to add/remove package types.
		 *
		 * @param array $package_types List of package types with labels.
		 */
		return apply_filters( 'cariera_packages_package_types', $package_types );
	}

	/**
	 * Get Post Type Groups.
	 *
	 * @since   0.9.8
	 * @version 0.9.9
	 */
	public static function get_post_type_groups() {
		$post_type_groups = [
			'job' => esc_html__( 'Job Packages', 'cariera-packages' ),
		];

		if ( \Cariera_Packages::cariera_company_manager_active() ) {
			$post_type_groups['company'] = esc_html__( 'Company Packages', 'cariera-packages' );
		}

		if ( \Cariera_Packages::wprm_active() ) {
			$post_type_groups['resume'] = esc_html__( 'Resume Packages', 'cariera-packages' );
		}

		if ( class_exists( 'Cariera_Events' ) ) {
			$post_type_groups['event'] = esc_html__( 'Event Packages', 'cariera-packages' );
		}

		// Allow filtering of post type groups.
		return apply_filters( 'cariera_packages_post_type_groups', $post_type_groups );
	}

	/**
	 * Cariera Packages fields.
	 *
	 * @since   0.9.0
	 * @version 0.9.17
	 */
	public static function get_package_fields() {
		$prefix = 'cariera_packages_';

		$package_types = array_merge( [ '' => esc_html__( 'Choose package type', 'cariera-packages' ) ], self::package_types() );

		// Get package products BEFORE using them.
		$package_products = \Cariera_Packages\Helpers::get_package_products();

		// Build select options array from products.
		$product_options = [ '' => esc_html__( 'Choose a product', 'cariera-packages' ) ];
		if ( is_array( $package_products ) ) {
			foreach ( $package_products as $product ) {
				if ( $product instanceof \WP_Post ) {
					$product_options[ $product->ID ] = $product->post_title;
				}
			}
		}

		$fields = [
			$prefix . 'general_heading'             => [
				'label'       => esc_html__( 'General Options', 'cariera-packages' ),
				'description' => '',
				'type'        => 'heading',
			],
			$prefix . 'user_id'                     => [
				'label'       => esc_html__( 'User ID', 'cariera-packages' ),
				'description' => esc_html__( 'The ID of the user that has purchased this package.', 'cariera-packages' ),
				'type'        => 'user',
				'placeholder' => '',
				'default'     => '',
			],
			$prefix . 'product_id'                  => [
				'label'       => esc_html__( 'Package Product', 'cariera-packages' ),
				'description' => '',
				'type'        => 'select',
				'options'     => $product_options,
				'default'     => '',
			],
			$prefix . 'order_id'                    => [
				'label'       => esc_html__( 'Order ID', 'cariera-packages' ),
				'description' => '',
				'type'        => 'text',
				'placeholder' => '',
				'default'     => '',
			],
			$prefix . 'package_type'                => [
				'label'       => esc_html__( 'Package Type', 'cariera-packages' ),
				'description' => esc_html__( 'Select your package type.', 'cariera-packages' ),
				'type'        => 'select',
				'options'     => $package_types,
				'default'     => '',
			],

			// Job Submission Package Options.
			$prefix . 'job_submission_heading'      => [
				'label'       => esc_html__( 'Job Submission Package Options', 'cariera-packages' ),
				'description' => '',
				'type'        => 'heading',
				'data'        => [
					'package-type' => 'job_submission_package',
				],
			],
			$prefix . 'job_submission_limit'        => [
				'label'       => esc_html__( 'Listing Limit', 'cariera-packages' ),
				'description' => esc_html__( 'Set the maximum number of job listings a user can post with this package.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Leave blank to allow unlimited listings.', 'cariera-packages' ),
				'default'     => '',
				'data'        => [
					'package-type' => 'job_submission_package',
				],
			],
			$prefix . 'job_submission_duration'     => [
				'label'       => esc_html__( 'Listing Duration', 'cariera-packages' ),
				'description' => esc_html__( 'Specify how many days each job listing will remain active before it expires.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Leave blank for no expiration.', 'cariera-packages' ),
				'default'     => '',
				'data'        => [
					'package-type' => 'job_submission_package',
				],
			],
			$prefix . 'job_submission_count'        => [
				'label'       => esc_html__( 'Listing Count', 'cariera-packages' ),
				'description' => esc_html__( 'Specify how many job listings have been submitted with this package.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '0',
				'default'     => '0',
				'data'        => [
					'package-type' => 'job_submission_package',
				],
			],
			$prefix . 'job_submission_featured'     => [
				'label'       => esc_html__( 'Featured Listing', 'cariera-packages' ),
				'description' => esc_html__( 'Enable this option to mark all listings submitted with this package as featured.', 'cariera-packages' ),
				'type'        => 'switch',
				'default'     => false,
				'data'        => [
					'package-type' => 'job_submission_package',
				],
			],

			// Job View Package Options.
			$prefix . 'job_view_heading'            => [
				'label'       => esc_html__( 'Job View Package Options', 'cariera-packages' ),
				'description' => '',
				'type'        => 'heading',
				'data'        => [
					'package-type' => 'job_view_package',
				],
			],
			$prefix . 'view_job_limit'              => [
				'label'       => esc_html__( 'View Job Limit', 'cariera-packages' ),
				'description' => esc_html__( 'The number of listings a user can view with this package.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Leave blank to allow unlimited.', 'cariera-packages' ),
				'default'     => '',
				'data'        => [
					'package-type' => 'job_view_package',
				],
			],
			$prefix . 'viewed_jobs'                 => [
				'label'       => esc_html__( 'Viewed Jobs', 'cariera-packages' ),
				'description' => esc_html__( 'Enter the listing IDs seprated by a comma.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => '',
				'data'        => [
					'package-type' => 'job_view_package',
				],
			],

			// Listing Promotional Package Options.
			$prefix . 'listing_promotional_heading' => [
				'label'       => esc_html__( 'Promotional Package Options', 'cariera-packages' ),
				'description' => '',
				'type'        => 'heading',
				'data'        => [
					'package-type' => [
						'job_promotional_package',
						'company_promotional_package',
						'resume_promotional_package',
						'event_promotional_package',
					],
				],
			],
			$prefix . 'listing_promotion_duration'  => [
				'label'       => esc_html__( 'Promotion Duration', 'cariera-packages' ),
				'description' => esc_html__( 'The number of days the listing will be promoted/featured.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => '',
				'data'        => [
					'package-type' => [
						'job_promotional_package',
						'company_promotional_package',
						'resume_promotional_package',
						'event_promotional_package',
					],
				],
			],
			$prefix . 'promoted_listing_id'         => [
				'label'       => esc_html__( 'Promoted Listing', 'cariera-packages' ),
				'description' => esc_html__( 'Enter the listing ID.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => '',
				'data'        => [
					'package-type' => [
						'job_promotional_package',
						'company_promotional_package',
						'resume_promotional_package',
						'event_promotional_package',
					],
				],
			],
			$prefix . 'promotion_expires'           => [
				'label'       => esc_html__( 'Promotion Expiration Date', 'cariera-packages' ),
				'description' => esc_html__( 'Set the date when the promoted listing should expire or lose its featured status.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => '',
				'data'        => [
					'package-type' => [
						'job_promotional_package',
						'company_promotional_package',
						'resume_promotional_package',
						'event_promotional_package',
					],
				],
			],
		];

		// Company Package Options.
		if ( \Cariera_Packages::cariera_company_manager_active() ) {
			$fields += self::get_company_package_fields();
		}

		// Resume Package Options.
		if ( \Cariera_Packages::wprm_active() ) {
			$fields += self::get_resume_package_fields();
		}

		// Event Package Options.
		if ( class_exists( 'Cariera_Events' ) ) {
			$fields += self::get_event_package_fields();
		}

		return $fields;
	}

	/**
	 * Company Package Fields
	 *
	 * @since 0.9.17
	 */
	private static function get_company_package_fields() {
		$prefix = 'cariera_packages_';
		return [
			// Company View Package Options.
			$prefix . 'company_view_heading' => [
				'label'       => esc_html__( 'Company View Package Options', 'cariera-packages' ),
				'description' => '',
				'type'        => 'heading',
				'data'        => [
					'package-type' => 'company_view_package',
				],
			],
			$prefix . 'view_company_limit'   => [
				'label'       => esc_html__( 'View Company Limit', 'cariera-packages' ),
				'description' => esc_html__( 'The number of listings a user can view with this package.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Leave blank to allow unlimited.', 'cariera-packages' ),
				'default'     => '',
				'data'        => [
					'package-type' => 'company_view_package',
				],
			],
			$prefix . 'viewed_companies'     => [
				'label'       => esc_html__( 'Viewed Companies', 'cariera-packages' ),
				'description' => esc_html__( 'Enter the listing IDs seprated by a comma.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => '',
				'data'        => [
					'package-type' => 'company_view_package',
				],
			],
		];
	}

	/**
	 * Resume Package Fields
	 *
	 * @since 0.9.17
	 */
	private static function get_resume_package_fields() {
		$prefix = 'cariera_packages_';
		return [
			// Resume Submission Package Options.
			$prefix . 'resume_submission_heading'  => [
				'label'       => esc_html__( 'Resume Submission Package Options', 'cariera-packages' ),
				'description' => '',
				'type'        => 'heading',
				'data'        => [
					'package-type' => 'resume_submission_package',
				],
			],
			$prefix . 'resume_submission_limit'    => [
				'label'       => esc_html__( 'Listing Limit', 'cariera-packages' ),
				'description' => esc_html__( 'Set the maximum number of resumes a user can post with this package.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Leave blank to allow unlimited listings.', 'cariera-packages' ),
				'default'     => '',
				'data'        => [
					'package-type' => 'resume_submission_package',
				],
			],
			$prefix . 'resume_submission_duration' => [
				'label'       => esc_html__( 'Listing Duration', 'cariera-packages' ),
				'description' => esc_html__( 'Specify how many days each resume will remain active before it expires.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Leave blank for no expiration.', 'cariera-packages' ),
				'default'     => '',
				'data'        => [
					'package-type' => 'resume_submission_package',
				],
			],
			$prefix . 'resume_submission_count'    => [
				'label'       => esc_html__( 'Listing Count', 'cariera-packages' ),
				'description' => esc_html__( 'Specify how many resumes have been submitted with this package.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '0',
				'default'     => '0',
				'data'        => [
					'package-type' => 'resume_submission_package',
				],
			],
			$prefix . 'resume_submission_featured' => [
				'label'       => esc_html__( 'Featured Listing', 'cariera-packages' ),
				'description' => esc_html__( 'Enable this option to mark all listings submitted with this package as featured.', 'cariera-packages' ),
				'type'        => 'switch',
				'default'     => false,
				'data'        => [
					'package-type' => 'resume_submission_package',
				],
			],
			// Resume View Package Options.
			$prefix . 'resume_view_heading'        => [
				'label'       => esc_html__( 'Resume View Package Options', 'cariera-packages' ),
				'description' => '',
				'type'        => 'heading',
				'data'        => [
					'package-type' => 'resume_view_package',
				],
			],
			$prefix . 'view_resume_limit'          => [
				'label'       => esc_html__( 'View Resume Limit', 'cariera-packages' ),
				'description' => esc_html__( 'The number of listings a user can view with this package.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Leave blank to allow unlimited.', 'cariera-packages' ),
				'default'     => '',
				'data'        => [
					'package-type' => 'resume_view_package',
				],
			],
			$prefix . 'viewed_resumes'             => [
				'label'       => esc_html__( 'Viewed Resumes', 'cariera-packages' ),
				'description' => esc_html__( 'Enter the listing IDs seprated by a comma.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => '',
				'data'        => [
					'package-type' => 'resume_view_package',
				],
			],
		];
	}

	/**
	 * Event Package Fields
	 *
	 * @since 0.9.17
	 */
	private static function get_event_package_fields() {
		$prefix = 'cariera_packages_';
		return [
			// Event View Package Options.
			$prefix . 'event_view_heading' => [
				'label'       => esc_html__( 'Event View Package Options', 'cariera-packages' ),
				'description' => '',
				'type'        => 'heading',
				'data'        => [
					'package-type' => 'event_view_package',
				],
			],
			$prefix . 'view_event_limit'   => [
				'label'       => esc_html__( 'View Event Limit', 'cariera-packages' ),
				'description' => esc_html__( 'The number of listings a user can view with this package.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Leave blank to allow unlimited.', 'cariera-packages' ),
				'default'     => '',
				'data'        => [
					'package-type' => 'event_view_package',
				],
			],
			$prefix . 'viewed_events'      => [
				'label'       => esc_html__( 'Viewed Events', 'cariera-packages' ),
				'description' => esc_html__( 'Enter the listing IDs seprated by a comma.', 'cariera-packages' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => '',
				'data'        => [
					'package-type' => 'event_view_package',
				],
			],
		];
	}

	/**
	 * Add screen ids
	 *
	 * @since   0.9.3
	 * @version 0.9.8
	 *
	 * @param array $screen_ids
	 */
	public function add_screen_ids( $screen_ids ) {
		$screen_ids[] = 'edit-' . self::CPT_PACKAGE;
		$screen_ids[] = self::CPT_PACKAGE;

		return $screen_ids;
	}
}
