<?php

namespace WPJMSF\Search\NoValue;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Taxonomies
 *
 * @package WPJMSF
 *
 * @since   1.1.9
 *
 */
class Taxonomies {

	/**
	 * @var \WPJMSF\Search\NoValue
	 */
	public $novalue;

	/**
	 * @var array Meta keys found during processing for meta (used to check if we need to combine queries after)
	 */
	private $modified_queries = array();

	/**
	 * @param $novalue \WPJMSF\Search\NoValue
	 */
	public function __construct( $novalue ) {
		$this->novalue = $novalue;
	}

	public function process() {

		if ( ! isset( $this->novalue->query['tax_query'] ) || empty( $this->novalue->query['tax_query'] ) ) {
			return false;
		}

		// TODO: handle adding empty query when no value is submitted (meaning no query exists)
		// for now is handled in Search/Taxonomies
	}

	/**
	 * Add "Empty" Values to Meta Query
	 *
	 * @param array  $meta_query
	 * @param string $_meta_key
	 *
	 * @return array
	 * @since 1.1.9
	 *
	 */
	public function add_empty( $meta_query, $_meta_key ) {

		$with_empty = array(
			'relation' => 'OR',
			$meta_query,
			array(
				'key'     => $_meta_key,
				'compare' => 'NOT EXISTS'
			),
			array(
				'key'   => $_meta_key,
				'value' => ''
			)
		);

		return $with_empty;
	}
}