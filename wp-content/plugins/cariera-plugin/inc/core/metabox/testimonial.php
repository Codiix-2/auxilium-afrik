<?php

namespace Cariera_Core\Core\Metabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Testimonial extends \Cariera_Core\Core\Metabox {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register Metaboxes.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Save Data.
		add_action( 'save_post', [ $this, 'save_meta' ], 10, 2 );
	}

	/**
	 * Register Metaboxes
	 *
	 * @since   1.5.3
	 * @version 1.8.8
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'cariera_testimonial_data',
			_x( 'Testimonial Settings', 'Testimonials data options in wp-admin', 'cariera-core' ),
			[ $this, 'meta_boxes_testimonial' ],
			'testimonial',
			'normal',
			'high'
		);
	}

	/**
	 * Displays metadata fields for Pages.
	 *
	 * @since   1.5.3
	 * @version 1.9.2
	 *
	 * @param mixed $post
	 */
	public function meta_boxes_testimonial( $post ) {
		$this->render_meta_fields( $post, 'meta_fields' );
	}

	/**
	 * Fields for testimonial meta
	 *
	 * @since   1.5.3
	 * @version 1.9.7
	 */
	public function meta_fields() {
		$fields = apply_filters(
			'cariera_testimonial_meta_fields',
			[
				'cariera_testimonial_gravatar' => [
					'label'       => esc_html__( 'Gravatar E-mail Address', 'cariera-core' ),
					'placeholder' => '',
					'description' => esc_html__( 'Enter in an e-mail address, to use a Gravatar, instead of using the "Featured Image".', 'cariera-core' ),
					'column'      => '4',
				],
				'cariera_testimonial_byline'   => [
					'label'       => esc_html__( 'Byline', 'cariera-core' ),
					'placeholder' => '',
					'description' => esc_html__( 'Enter a byline for the customer giving this testimonial (for example: "CEO of Cariera").', 'cariera-core' ),
					'column'      => '4',
				],
				'cariera_testimonial_url'      => [
					'label'       => esc_html__( 'URL', 'cariera-core' ),
					'placeholder' => '',
					'description' => esc_html__( 'Enter a URL that applies to this customer (for example: http://cariera.cc/).', 'cariera-core' ),
					'column'      => '4',
				],
			]
		);

		return $fields;
	}

	/**
	 * Save Testimonial Meta Data
	 *
	 * @since   1.5.3
	 * @version 1.9.2
	 *
	 * @param int   $post_id
	 * @param mixed $post
	 */
	public function save_meta( $post_id, $post ) {
		$this->save_meta_fields( $post_id, $post, 'testimonial', 'meta_fields' );
	}
}
