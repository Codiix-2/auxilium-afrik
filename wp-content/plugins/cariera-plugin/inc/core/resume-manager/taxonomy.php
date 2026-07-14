<?php

namespace Cariera_Core\Core\Resume_Manager;

use Cariera_Core\Core\Resume_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Taxonomy extends Resume_Manager {

	use \Cariera_Core\Src\Traits\Terms;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register resume taxonomies.
		add_action( 'init', [ $this, 'register_taxonomies' ] );

		// Add field.
		add_action( 'resume_category_add_form_fields', [ $this, 'add_new_meta_field' ], 10, 2 );

		// Edit Field.
		add_action( 'resume_category_edit_form_fields', [ $this, 'edit_meta_field' ], 10, 2 );

		// Save field.
		add_action( 'edited_resume_category', [ $this, 'save_taxonomy_custom_meta' ] );
		add_action( 'create_resume_category', [ $this, 'save_taxonomy_custom_meta' ] );
	}

	/**
	 * Register Resume taxonomies
	 *
	 * @since   1.4.6
	 * @version 1.6.2
	 */
	public function register_taxonomies() {
		if ( ! post_type_exists( 'resume' ) ) {
			return;
		}

		$admin_capability = 'manage_resumes';

		// Taxonomies.
		$taxonomies_args = apply_filters(
			'cariera_resume_taxonomies_list',
			[
				'resume_education_level' => [
					'singular' => esc_html__( 'Candidate Education', 'cariera-core' ),
					'plural'   => esc_html__( 'Candidate Education', 'cariera-core' ),
					'slug'     => esc_html_x( 'resume-education', 'Candidate education permalink - resave permalinks after changing this', 'cariera-core' ),
					'enable'   => get_option( 'cariera_resume_manager_enable_education', true ),
				],
				'resume_experience'      => [
					'singular' => esc_html__( 'Candidate Experience', 'cariera-core' ),
					'plural'   => esc_html__( 'Candidate Experience', 'cariera-core' ),
					'slug'     => esc_html_x( 'resume-experience', 'Candidate experience permalink - resave permalinks after changing this', 'cariera-core' ),
					'enable'   => get_option( 'cariera_resume_manager_enable_experience', true ),
				],
			]
		);

		foreach ( $taxonomies_args as $taxonomy_name => $taxonomy_args ) {
			if ( $taxonomy_args['enable'] ) {
				$singular = $taxonomy_args['singular'];
				$plural   = $taxonomy_args['plural'];
				$slug     = $taxonomy_args['slug'];

				$args = apply_filters(
					"register_taxonomy_{$taxonomy_name}_args",
					[
						'hierarchical'          => true,
						'update_count_callback' => '_update_post_term_count',
						'label'                 => $plural,
						'labels'                => [
							'name'              => $plural,
							'singular_name'     => $singular,
							'menu_name'         => ucwords( $plural ),
							// translators: %s is the plural label of the taxonomy.
							'search_items'      => sprintf( esc_html__( 'Search %s', 'cariera-core' ), $plural ),
							// translators: %s is the plural label of the taxonomy.
							'all_items'         => sprintf( esc_html__( 'All %s', 'cariera-core' ), $plural ),
							// translators: %s is the singular label of the taxonomy.
							'parent_item'       => sprintf( esc_html__( 'Parent %s', 'cariera-core' ), $singular ),
							// translators: %s is the singular label of the taxonomy.
							'parent_item_colon' => sprintf( esc_html__( 'Parent %s:', 'cariera-core' ), $singular ),
							// translators: %s is the singular label of the taxonomy.
							'edit_item'         => sprintf( esc_html__( 'Edit %s', 'cariera-core' ), $singular ),
							// translators: %s is the singular label of the taxonomy.
							'update_item'       => sprintf( esc_html__( 'Update %s', 'cariera-core' ), $singular ),
							// translators: %s is the singular label of the taxonomy.
							'add_new_item'      => sprintf( esc_html__( 'Add New %s', 'cariera-core' ), $singular ),
							// translators: %s is the singular label of the taxonomy.
							'new_item_name'     => sprintf( esc_html__( 'New %s Name', 'cariera-core' ), $singular ),
						],
						'show_ui'               => true,
						'show_in_rest'          => true,
						'show_tagcloud'         => false,
						'public'                => true,
						'capabilities'          => [
							'manage_terms' => $admin_capability,
							'edit_terms'   => $admin_capability,
							'delete_terms' => $admin_capability,
							'assign_terms' => $admin_capability,
						],
						'rewrite'               => [
							'slug'         => $slug,
							'with_front'   => false,
							'hierarchical' => true,
						],
					]
				);

				register_taxonomy( $taxonomy_name, 'resume', $args );
			}
		}
	}

	/**
	 * Add meta fields
	 *
	 * @since   1.2.0
	 * @version 1.7.3
	 */
	public function add_new_meta_field() {
		wp_enqueue_media();
		?>

		<div class="form-field term-background-image">
			<label for="cariera_background_image"><?php esc_html_e( 'Background Image', 'cariera-core' ); ?></label>

			<?php $this->background_img(); ?>
		</div>


		<div class="form-field term-image-icon">
			<label for="cariera_image_icon"><?php esc_html_e( 'Custom Image Icon', 'cariera-core' ); ?></label>

			<?php $this->image_icon(); ?>
		</div>

		<div class="form-field term-font-icon">
			<label for="cariera_font_icon"><?php esc_html_e( 'Category Font Icon', 'cariera-core' ); ?></label>
			
			<?php $this->font_icon(); ?>
		</div>

		<?php
	}

	/**
	 * Edit Term Page
	 *
	 * @since   1.2.0
	 * @version 1.7.3
	 *
	 * @param mixed $term
	 */
	public function edit_meta_field( $term ) {
		wp_enqueue_media();

		$term_background_value = get_term_meta( $term->term_id, 'cariera_background_image', true );
		$term_img_icon_value   = get_term_meta( $term->term_id, 'cariera_image_icon', true );
		$term_font_icon_value  = get_term_meta( $term->term_id, 'cariera_font_icon', true );
		?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="cariera_background_image"><?php esc_html_e( 'Background Image', 'cariera-core' ); ?></label>
			</th>
			<td>
				<?php $this->background_img( $term_background_value ); ?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="cariera-image-icon"><?php esc_html_e( 'Custom Image Icon', 'cariera-core' ); ?></label>
			</th>
			<td>
				<?php $this->image_icon( $term_img_icon_value ); ?>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="cariera_font_icon"><?php esc_html_e( 'Category Font Icon', 'cariera-core' ); ?></label>
			</th>
			<td>
				<?php $this->font_icon( $term_font_icon_value ); ?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Save extra taxonomy meta fields callback function.
	 *
	 * @since   1.2.0
	 * @version 1.8.1
	 *
	 * @param mixed $term_id
	 */
	public function save_taxonomy_custom_meta( $term_id ) {
		$term_bg_img    = isset( $_POST['cariera_background_image'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_background_image'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$term_img_icon  = isset( $_POST['cariera_image_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_image_icon'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$term_font_icon = isset( $_POST['cariera_font_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_font_icon'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		update_term_meta( $term_id, 'cariera_background_image', $term_bg_img );
		update_term_meta( $term_id, 'cariera_image_icon', $term_img_icon );
		update_term_meta( $term_id, 'cariera_font_icon', $term_font_icon );
	}
}
