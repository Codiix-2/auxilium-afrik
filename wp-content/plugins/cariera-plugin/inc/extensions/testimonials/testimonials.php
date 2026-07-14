<?php

namespace Cariera_Core\Extensions\Testimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Testimonials {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Register listing post type and custom post statuses.
		add_action( 'init', [ $this, 'testimonial_cpt' ], 0 );

		if ( is_admin() ) {
			global $pagenow;

			// phpcs:ignore
			if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && esc_attr( $_GET['post_type'] ) == 'testimonial' ) {
				add_filter( 'manage_edit-testimonial_columns', [ $this, 'column_headings' ], 10, 1 );
				add_action( 'manage_posts_custom_column', [ $this, 'custom_columns' ], 10, 2 );
			}
		}
	}

	/**
	 * Testimonials Post type
	 *
	 * @since   1.2.7
	 * @version 1.5.4
	 */
	public function testimonial_cpt() {

		$singular = esc_html__( 'Testimonial', 'cariera-core' );
		$plural   = esc_html__( 'Testimonials', 'cariera-core' );

		// Testimonial Labels.
		$labels = [
			'name'               => _x( 'Testimonials', 'post type general name', 'cariera-core' ),
			'singular_name'      => _x( 'Testimonial', 'post type singular name', 'cariera-core' ),
			'add_new'            => _x( 'Add New', 'testimonial', 'cariera-core' ),
			// translators: %s is the singular label of the taxonomy.
			'add_new_item'       => sprintf( esc_html__( 'Add New %s', 'cariera-core' ), $singular ),
			// translators: %s is the singular label of the taxonomy.
			'edit_item'          => sprintf( esc_html__( 'Edit %s', 'cariera-core' ), $singular ),
			// translators: %s is the singular label of the taxonomy.
			'new_item'           => sprintf( esc_html__( 'New %s', 'cariera-core' ), $singular ),
			// translators: %s is the plural label of the taxonomy.
			'all_items'          => sprintf( esc_html__( 'All %s', 'cariera-core' ), $plural ),
			// translators: %s is the singular label of the taxonomy.
			'view_item'          => sprintf( esc_html__( 'View %s', 'cariera-core' ), $singular ),
			// translators: %s is the plural label of the taxonomy.
			'search_items'       => sprintf( esc_html__( 'Search %s', 'cariera-core' ), $plural ),
			// translators: %s is the plural label of the taxonomy.
			'not_found'          => sprintf( esc_html__( 'No %s Found', 'cariera-core' ), $plural ),
			// translators: %s is the plural label of the taxonomy.
			'not_found_in_trash' => sprintf( esc_html__( 'No %s Found In Trash', 'cariera-core' ), $plural ),
			'parent_item_colon'  => '',
			'menu_name'          => $plural,
		];

		$single_slug  = apply_filters( 'cariera_testimonials_single_slug', _x( 'testimonial', 'single post url slug', 'cariera-core' ) );
		$archive_slug = apply_filters( 'cariera_testimonials_archive_slug', _x( 'testimonials', 'post archive url slug', 'cariera-core' ) );

		// Testimonial args.
		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [
				'slug'       => $single_slug,
				'with_front' => false,
			],
			'capability_type'    => 'post',
			'has_archive'        => $archive_slug,
			'hierarchical'       => false,
			'supports'           => [ 'title', 'author', 'editor', 'thumbnail', 'page-attributes', 'publicize', 'wpcom-markdown' ],
			'menu_position'      => 5,
			'menu_icon'          => 'dashicons-testimonial',
		];

		// Register Testimonial Custom Post Type.
		register_post_type( 'testimonial', apply_filters( 'cariera_testimonials_post_type_args', $args ) );
	}

	/**
	 * Add custom columns for the "manage" screen of this post type.
	 *
	 * @since    1.2.7
	 * @version  1.5.4
	 *
	 * @param string $column_name
	 * @param int    $id
	 */
	public function custom_columns( $column_name, $id ) {
		global $wpdb, $post;

		$meta = get_post_custom( $id );

		switch ( $column_name ) {
			case 'image':
				$value = '';
				$value = $this->get_image( $id, 40 );
				echo $value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			default:
				// Do nothing.
				break;
		}
	}

	/**
	 * Get the image for the given ID. If no featured image, check for Gravatar e-mail.
	 *
	 * @since    1.2.7
	 * @version  1.5.4
	 *
	 * @param int $id
	 * @param int $size
	 */
	public function get_image( $id, $size ) {
		$response = '';

		if ( has_post_thumbnail( $id ) ) {
			// If not a string or an array, and not an integer, default to 150x9999.
			if ( ( is_int( $size ) || ( 0 < intval( $size ) ) ) && ! is_array( $size ) ) {
				$size = [ intval( $size ), intval( $size ) ];
			} elseif ( ! is_string( $size ) && ! is_array( $size ) ) {
				$size = [ 50, 50 ];
			}
			$response = get_the_post_thumbnail( intval( $id ), $size, [ 'class' => 'avatar' ] );
		} else {
			$gravatar_email = get_post_meta( $id, '_gravatar_email', true );
			if ( '' !== $gravatar_email && is_email( $gravatar_email ) ) {
				$response = get_avatar( $gravatar_email, $size );
			}
		}

		return $response;
	}

	/**
	 * Add custom column headings for the "manage" screen of this post type.
	 *
	 * @since    1.2.7
	 * @version  1.5.4
	 *
	 * @param array $defaults
	 */
	public function column_headings( $defaults ) {
		$new_columns = [ 'image' => esc_html__( 'Image', 'cariera-core' ) ];

		$last_item = '';

		if ( isset( $defaults['date'] ) ) {
			unset( $defaults['date'] );
		}

		if ( count( $defaults ) > 2 ) {
			$last_item = array_slice( $defaults, -1 );

			array_pop( $defaults );
		}
		$defaults = array_merge( $defaults, $new_columns );

		if ( $last_item !== '' ) {
			foreach ( $last_item as $k => $v ) {
				$defaults[ $k ] = $v;
				break;
			}
		}

		return $defaults;
	}
}
