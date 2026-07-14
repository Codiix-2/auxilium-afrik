<?php

namespace WPJMSF\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Taxonomies
 *
 * @package WPJMSF
 *
 * @since   1.1.6
 *
 */
class Taxonomies {

	/**
	 * @var
	 */
	public $taxonomy;
	/**
	 * @var bool
	 */
	public $is_slug_taxonomy = array();
	/**
	 * @var string
	 */
	public $pad_string = '&nbsp;';
	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * Taxonomies constructor.
	 */
	public function __construct( $taxonomy, $is_slug_taxonomy = false, $type = false ) {
		$this->type = $type;
		$this->taxonomy = $taxonomy;
		$this->is_slug_taxonomy = $is_slug_taxonomy;
		$this->pad_string = apply_filters( 'search_and_filtering_get_taxonomy_data_options_pad_string', '&nbsp;', $this->taxonomy, $this );
	}

	/**
	 * Recursively get taxonomy and its children
	 *
	 * @param int    $parent Parent term ID (0 for top level)
	 * @param array  $args   Array of arguments to pass to get_terms (to override default)
	 *
	 * @return array
	 */
	public function get_taxonomy_hierarchy( $parent = 0, $args = array( 'hide_empty' => false ) ) {

		$defaults = array(
			'parent'     => $parent,
			'hide_empty' => false
		);

		if( empty( $this->taxonomy ) || ! taxonomy_exists( $this->taxonomy ) ){
			return array();
		}

		if( ! isset( $args['taxonomy' ] ) ){
			$args['taxonomy'] = $this->taxonomy;
		}

		$r = wp_parse_args( $args, $defaults );

		// get all direct decendants of the $parent
		$terms = get_terms( $r );

		if( is_wp_error( $terms ) ){
			return array();
		}

		$include_child_terms = apply_filters( 'search_and_filtering_get_taxonomy_data_options_include_child_terms', $args, $this->taxonomy, $this );

		// prepare a new array.  these are the children of $parent
		// we'll ultimately copy all the $terms into this new array, but only after they
		// find their own children
		$children = array();
		// go through all the direct decendants of $parent, and gather their children
		foreach ( $terms as $term ) {

			if( ! $term || ! isset( $term->term_id ) ){
				continue;
			}

			if( $include_child_terms ){
				// recurse to get the direct decendants of "this" term
				$term->children = $this->get_taxonomy_hierarchy( $term->term_id, $args );
			}

			// add the term to our new array
			$children[ $term->term_id ] = $term;
		}

		// send the results back to the caller
		return $children;
	}

	/**
	 * Get Taxonomy Data Options (for Frontend Output)
	 *
	 * This method returns the taxonomies in the order required by the frontend, sorting and setting up
	 * children taxonomies as needed.
	 *
	 * @return array
	 * @since 1.1.6
	 *
	 */
	public function get_data_options() {
		$hide_empty = false;

		if( $this->type ){
			$settings = get_option( $this->type->settings_option, array( 'hide_empty_tax' => 0 ) );
			$hide_empty = ! empty( $settings['hide_empty_tax'] );
		}

		$args = apply_filters( 'search_and_filtering_get_taxonomy_data_options_args', array(
			'taxonomy'    => $this->taxonomy,
			'hide_empty'  => $hide_empty,
			'order'       => 'ASC',
			'orderby'     => 'name',
			'value_field' => $this->is_slug_taxonomy ? 'slug' : 'term_id',
		) );

		$parent = 0;

		/**
		 * Set parent to child_of value, if set through filter.
		 *
		 * We do this because when generating the hierarchy, we specifically use parent value,
		 * and since parent always overrides child_of in get_terms, we have to manually set the
		 * parent value we use to the child_of value to work correctly.
		 */
		if ( isset( $args['child_of'] ) && absint( $args['child_of'] ) > -1 ) {
			$parent = absint( $args['child_of'] );
		}

		/**
		 * Also support customizing parent value in filter
		 *
		 * Parent always takes priority over child_of (why below child_of check),
		 * and to support that argument we set if the value is configured.
		 */
		if( isset( $args['parent'] ) && absint( $args['parent'] ) > -1 ){
			$parent = absint( $args['parent'] );
		}

		$tax_terms = $this->get_taxonomy_hierarchy( $parent, $args );
		return $this->get_tax_options( $tax_terms );
	}

	/**
	 * Build and Get Taxonomy Options
	 *
	 * @param     $terms
	 * @param int $depth
	 *
	 * @return array
	 * @since 1.1.6
	 *
	 */
	private function get_tax_options( $terms, $depth = 0 ) {
		$options = array();

		if( empty( $terms ) ){
			return $options;
		}

		foreach( (array) $terms as $term ){
			$options[] = $this->build_tax_option( $term, $depth );

			if( isset( $term->children ) && ! empty( $term->children ) ){
				$child_options = $this->get_tax_options( $term->children, $depth + 1 );
				$options = array_merge( $options, $child_options );
			}
		}

		return $options;
	}

	/**
	 * Build Taxonomy Option
	 *
	 * Build taxonomy option in format required by the frontend
	 *
	 * @param     $term
	 * @param int $depth
	 *
	 * @return array
	 * @since 1.1.6
	 *
	 */
	private function build_tax_option( $term, $depth = 0 ) {

		$pad = $depth > 0 ? str_repeat( $this->pad_string, $depth * 3 ) : '';
		$name = $pad . esc_html( $term->name );

		return array(
			'value' => $this->is_slug_taxonomy ? $term->slug : $term->term_id,
			'label' => $name,
			'slug'  => $term->slug,
			'count' => $term->count
		);
	}
}