<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TagCloud
 *
 * @package WPJMSF
 */
class TagCloud {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var array
	 */
	public $config;
	/**
	 * @var string
	 */
	public  $taxonomy;
	/**
	 * @var array
	 */
	private $only_in_posts;

	/**
	 * TagCloud constructor.
	 *
	 * @param $type \WPJMSF\Job|\WPJMSF\Resume
	 */
	public function __construct( $type, $taxonomy = '' ) {
		$this->type = $type;
		$this->taxonomy = $taxonomy;
	}

	/**
	 * Set Tag Cloud Configuration Parameters
	 *
	 * @param $config
	 *
	 * @since 1.0.0
	 *
	 */
	public function set_config( $config ) {
		$this->config = $config;
	}

	/**
	 * Get Tag Cloud HTML
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 *
	 */
	public function get_html() {

		if( isset( $this->config['separator'] ) && ! empty( $this->config['separator'] ) ){
			$this->config['separator'] = stripslashes( html_entity_decode( $this->config['separator'] ) );
		}

		$html = '';

		$defaults = array(
			'smallest'   => 8,
			'largest'    => 22,
			'unit'       => 'pt',
			'number'     => 0,
			'format'     => 'flat',
			'separator'  => "\n",
			'orderby'    => 'name',
			'order'      => 'ASC',
			'show_count' => 0,
			'link'       => 'view',
			'taxonomy'   => $this->taxonomy,
			'echo'       => false,
		);

		$includes = $this->get_includes();

		if( $includes !== false ){
			$defaults['include'] = $includes;
		}

		$args = shortcode_atts( $defaults, $this->config );
		$args = apply_filters( "search_and_filtering_{$this->type->slug}_tag_cloud_get_html_args", $args, $this );

		/**
		 * Only generate tag cloud HTML if include is not set, or if it is, only if there are values set
		 * for include.  Reason being is that $this->get_includes() returns empty array when $this->only_in_posts
		 * has no terms in any of those posts
		 */
		if( ! isset( $args['include'] ) || ( isset( $args['include'] ) && ! empty( $args['include'] ) ) ){
			add_filter( 'wp_generate_tag_cloud_data', array( $this, 'add_tag_data_attr' ) );
			$html = wp_tag_cloud( $args );
			remove_filter( 'wp_generate_tag_cloud_data', array( $this, 'add_tag_data_attr' ) );
		}

		return apply_filters( "search_and_filtering_{$this->type->slug}_tag_cloud_get_html", $html, $args, $this );
	}

	/**
	 * Add data attribute to tag cloud links
	 *
	 * We need this for the frontend to pull the tag ID to send with search queries instead of the value itself,
	 * as different languages can cause issues with searching.
	 *
	 * @param $tags
	 *
	 * @return array
	 * @since 1.1.32
	 *
	 */
	public function add_tag_data_attr( $tags ){

		foreach( (array) $tags as $index => $tag ){
			/**
			 * We add on to the aria_label as this is added at the end of the tag and is only location this would work
			 */
			$tags[ $index ][ 'aria_label' ] = $tag['aria_label'] . ' data-tag-id="' . $tag['id'] . '"';
		}

		return $tags;
	}

	/**
	 * Get Term IDs to Only Include
	 *
	 * @return array|false|mixed|string
	 * @since 1.0.0
	 *
	 */
	public function get_includes() {

		if( empty( $this->only_in_posts ) ){
			return false;
		}

		$include_tag_ids = array();

		sort( $this->only_in_posts );
		$transient_key = md5( implode( ',', $this->only_in_posts ) . $this->taxonomy );
		$transient     = array_filter( (array) get_transient( 'search_filtering_tag_clouds' ) );

		if ( $transient && isset( $transient[ $transient_key ] ) ) {
			return $transient[ $transient_key ];
		}

		foreach ( $this->only_in_posts as $listing_id ) {
			$terms = wp_get_post_terms( $listing_id, $this->taxonomy, array( 'fields' => 'ids' ) );

			if ( is_array( $terms ) ) {
				$include_tag_ids = array_merge( $include_tag_ids, $terms );
			}

			$include_tag_ids = array_unique( $include_tag_ids );
		}

		$transient[ $transient_key ] = $include_tag_ids;
		set_transient( 'search_filtering_tag_clouds', $transient, DAY_IN_SECONDS * 30 );

		return $include_tag_ids;
	}

	/**
	 * Set Only in Posts/Listings
	 *
	 * This method sets the post/listing IDs that tags should only be returned if they
	 * are set on these specific post ids.
	 *
	 * @param $posts
	 *
	 * @since 1.0.0
	 *
	 */
	public function set_only_in_posts( $posts ) {
		$this->only_in_posts = $posts;
	}
}