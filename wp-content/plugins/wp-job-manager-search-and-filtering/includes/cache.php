<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cache
 *
 * @package WPJMSF
 */
class Cache {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * Cache constructor.
	 *
	 * @param $type     \WPJMSF\Job|\WPJMSF\Resume
	 */
	public function __construct( $type ) {
		$this->type = $type;
		add_action( 'set_object_terms', array( $this, 'clear_transient' ) );
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
	}

	/**
	 * Clear Transients
	 *
	 * @since 1.0.0
	 *
	 */
	public function clear_transient() {
		delete_transient( 'search_filtering_tag_clouds' );
	}

	/**
	 * Check Post Status Transition to Clear Transients
	 *
	 * We only want to trigger when it's one of the associated post types, and only
	 * when the status is changed TO publish, or FROM publish, meaning results would
	 * change in search queries.
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 *
	 * @since 1.0.0
	 *
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if( $post->post_type !== $this->type->core_post_type || $old_status === $new_status ){
			return;
		}

		// $cache_bust = '4eBeRwGwSCyumXQrQDVPwwEE';

		if( $new_status === 'publish' || $old_status === 'publish' ){
			$this->clear_transient();
		}
	}
}