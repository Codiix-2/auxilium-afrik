<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rest_Api {

	/**
	 * Sets up initial hooks.
	 *
	 * @since 0.9.5
	 */
	public static function init() {
		add_filter( 'rest_prepare_resume', [ __CLASS__, 'prepare_resume' ], 10, 2 );
	}

	/**
	 * Filters the resume data for a REST API response.
	 *
	 * @since 0.9.5
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post          $post     Post object.
	 */
	public static function prepare_resume( $response, $post ) {
		$current_user = wp_get_current_user();
		$fields       = Post_Types::get_resume_fields();
		$data         = $response->get_data();

		foreach ( $data['meta'] as $meta_key => $meta_value ) {
			if ( isset( $fields[ $meta_key ] ) && is_callable( $fields[ $meta_key ]['auth_view_callback'] ) ) {
				$is_viewable = call_user_func( $fields[ $meta_key ]['auth_view_callback'], false, $meta_key, $post->ID, $current_user->ID );
				if ( ! $is_viewable ) {
					unset( $data['meta'][ $meta_key ] );
				}
			}
		}

		$response->set_data( $data );

		return $response;
	}
}
