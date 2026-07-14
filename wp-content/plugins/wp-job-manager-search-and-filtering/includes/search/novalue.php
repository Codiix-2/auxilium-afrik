<?php

namespace WPJMSF\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NoValue
 *
 * @package WPJMSF
 *
 * @since   1.1.9
 *
 */
class NoValue {

	/**
	 * @var \WPJMSF\Search
	 */
	public $search;

	/**
	 * @var array
	 */
	public $query;

	/**
	 * @var array Config from fields to include empty values array( 'meta_key' => array(..config) )
	 */
	public $include_empties = array();

	/**
	 * @param $search \WPJMSF\Search
	 */
	public function __construct( $search ){
		$this->search = $search;
	}

	/**
	 * Add No Value "Empty" Queries
	 *
	 * @param $query
	 *
	 * @return array|mixed
	 * @since 1.1.14
	 *
	 */
	public function add_queries( $query ) {
		$this->query = $query;
		if( ! $this->has_novalue_fields() ){
			return $query;
		}

		$meta = new NoValue\Meta( $this );
		$meta->process();

		$taxonomies = new NoValue\Taxonomies( $this );
		$taxonomies->process();

		return $this->query;
	}

	/**
	 * Generate Fields to Process
	 *
	 * @since 1.1.14
	 *
	 */
	public function generate_include_empties() {

		$this->include_empties = array();

		$configs = $this->search->get_search_config();

		foreach ( $configs as $meta_key => $config ) {
			if ( ! isset( $config['search_include_empty'] ) || $config['search_include_empty'] == false ) {
				continue;
			}

			$this->include_empties[ $meta_key ] = $config;
		}

	}

	/**
	 * Has No Value "Empty" Fields?
	 *
	 * @return bool
	 * @since 1.1.14
	 *
	 */
	public function has_novalue_fields() {
		$this->generate_include_empties();
		return ! empty( $this->include_empties );
	}
}