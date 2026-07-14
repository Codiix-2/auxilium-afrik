<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CPT
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class CPT {
	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	private $last_output = false;
	private $sections_by_output = false;

	private $sections;

	/**
	 * CPT constructor.
	 *
	 * @param $type
	 */
	function __construct( $type ) {
		$this->type = $type;
		add_action( 'init', array( $this, 'custom_post_setup' ), 1 );
	}

	function get_posts_by_output( $output, $args = array() ){
		$args = apply_filters( "wpjmsf_get_posts_by_output_args", $args, $output, $this );
		$args = wp_parse_args( $args,
		                       array(
			                       'post_type'      => $this->type->post_type,
			                       'post_status'    => array( 'publish', 'enabled', 'disabled' ),
			                       'pagination'     => false,
			                       'posts_per_page' => - 1,
			                       'meta_key'       => 'output',
			                       'meta_value'     => $output
		                       )
		);

		$posts_array = get_posts( $args );
		return apply_filters( 'wpjmsf_get_posts_by_output', $posts_array, $output, $args, $this );
	}

	/**
	 * Get Section Custom Posts
	 *
	 *
	 * @param array $args
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	function get_posts( $args = array() ){

		$args = apply_filters( "wpjmsf_get_sections_args", $args, $this );

		$args = wp_parse_args( $args,
		                       array(
			                       'post_type'      => $this->type->post_type,
			                       'post_status'    => array( 'publish', 'enabled', 'disabled' ),
			                       'pagination'     => false,
			                       'posts_per_page' => - 1,
		                       )
		);

		$posts_array = get_posts( $args );
		return apply_filters( 'wpjmsf_get_posts', $posts_array, $args, $this );
	}

	/**
	 * Set Custom Post Status (Enable/Disable)
	 *
	 * @since 1.0.0
	 */
	function custom_post_setup() {

		if ( post_type_exists( $this->type->post_type ) ) {
			return;
		}

		$admin_capability = $this->type->get_capability();

		register_post_type( $this->type->post_type, array(
			'labels'              => array(
				'name'          => $this->type->labels['name'],
				'singular_name' => $this->type->labels['singular_name']
			),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'can_export'          => true,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'author', 'custom-fields' ),
			'capabilities'        => array(
				'publish_posts'       => $admin_capability,
				'edit_posts'          => $admin_capability,
				'edit_others_posts'   => $admin_capability,
				'delete_posts'        => $admin_capability,
				'delete_others_posts' => $admin_capability,
				'read_private_posts'  => $admin_capability,
				'edit_post'           => $admin_capability,
				'delete_post'         => $admin_capability,
				'read_post'           => $admin_capability
			),
		) );

		$disabled_args = array(
			'label'                     => _x( 'disabled', 'Disabled Section Status', 'wp-job-manager-search-and-filtering' ),
			'label_count'               => _n_noop( 'Disabled (%s)', 'Disabled (%s)', 'wp-job-manager-search-and-filtering' ),
			'public'                    => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
			'exclude_from_search'       => true,
		);

		$enabled_args = array(
			'label'                     => _x( 'enabled', 'Enabled Section Status', 'wp-job-manager-search-and-filtering' ),
			'label_count'               => _n_noop( 'Enabled (%s)', 'Enabled (%s)', 'wp-job-manager-search-and-filtering' ),
			'public'                    => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
			'exclude_from_search'       => true,
		);

		register_post_status( 'disabled', $disabled_args );
		register_post_status( 'enabled', $enabled_args );
	}
}