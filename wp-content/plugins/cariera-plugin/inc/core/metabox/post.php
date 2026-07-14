<?php

namespace Cariera_Core\Core\Metabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post extends \Cariera_Core\Core\Metabox {

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
			'cariera_post_data',
			_x( 'Post Settings', 'Posts data options in wp-admin', 'cariera-core' ),
			[ $this, 'meta_boxes_post' ],
			'post',
			'normal',
			'high'
		);
	}

	/**
	 * Displays metadata fields for Posts.
	 *
	 * @since   1.5.3
	 * @version 1.8.8
	 *
	 * @param mixed $post
	 */
	public function meta_boxes_post( $post ) {
		$this->render_meta_fields( $post, 'meta_fields' );
	}

	/**
	 * Fields for posts meta
	 *
	 * @since   1.5.3
	 * @version 1.9.2
	 */
	public function meta_fields() {

		$fields = apply_filters(
			'cariera_post_meta_fields',
			[
				// Audio Post.
				'cariera_post_audio_heading'   => [
					'label'       => esc_html__( 'Audio Post Options', 'cariera-core' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_blog_audio'           => [
					'label'       => esc_html__( 'Audio Embed Code', 'cariera-core' ),
					'placeholder' => '',
					'description' => esc_html__( 'Please enter the Audio Embed Code here.', 'cariera-core' ),
					'type'        => 'textarea',
				],

				// Gallery Test.
				'cariera_post_gallery_heading' => [
					'label'       => esc_html__( 'Gallery Post Options', 'cariera-core' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_blog_gallery'         => [
					'label'       => esc_html__( 'Gallery Images', 'cariera-core' ),
					'placeholder' => '',
					'description' => esc_html__( 'You can upload multiple gallery images for a slideshow', 'cariera-core' ),
					'type'        => 'file',
					'multiple'    => 1,
				],

				// Quote Post.
				'cariera_post_quote_heading'   => [
					'label'       => esc_html__( 'Quote Post Options', 'cariera-core' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_blog_quote_author'    => [
					'label'       => esc_html__( 'Quote Author', 'cariera-core' ),
					'placeholder' => '',
					'description' => '',
				],
				'cariera_blog_quote_source'    => [
					'label'       => esc_html__( 'Quote Source', 'cariera-core' ),
					'placeholder' => '',
					'description' => esc_html__( 'Please enter the source (URL) of the quote here.', 'cariera-core' ),
				],
				'cariera_blog_quote_content'   => [
					'label'       => esc_html__( 'Quote Content', 'cariera-core' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'textarea',
				],

				// Video Post.
				'cariera_post_video_heading'   => [
					'label'       => esc_html__( 'Video Post Options', 'cariera-core' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_blog_video_embed'     => [
					'label'       => esc_html__( 'Video Embed Code', 'cariera-core' ),
					'placeholder' => '',
					'description' => esc_html__( 'Add the full embed code here or the URL of a WordPress supported video site.', 'cariera-core' ),
					'type'        => 'textarea',
				],
			]
		);

		return $fields;
	}

	/**
	 * Save Post Meta Data
	 *
	 * @since   1.5.3
	 * @version 1.9.2
	 *
	 * @param int   $post_id
	 * @param mixed $post
	 */
	public function save_meta( $post_id, $post ) {
		$this->save_meta_fields( $post_id, $post, 'post', 'meta_fields' );
	}
}
