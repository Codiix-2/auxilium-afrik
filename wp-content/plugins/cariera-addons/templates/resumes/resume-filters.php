<?php
/**
 * Filter form to display above `[resumes]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/resume-filters.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-search-forms' );
wp_enqueue_script( 'cariera-addons-resume-ajax-filters' );

if ( class_exists( '\Cariera_Core\Core\Assets' ) ) {
	Cariera_Core\Core\Assets::enqueue_location_autocomplete();
}

do_action( 'resume_manager_resume_filters_before', $atts );

$plural_label = cariera_addons_resume_cpt_plural_label();
?>

<form class="resume_filters">
	<div class="search_resumes">
		<?php do_action( 'resume_manager_resume_filters_search_resumes_start', $atts ); ?>

		<div class="search_keywords resume-filter">
			<label for="search_keywords"><?php esc_html_e( 'Keywords', 'cariera-addons' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php printf( esc_attr__( 'All %s', 'cariera-addons' ), cariera_addons_resume_cpt_plural_label() ); ?>" value="<?php echo esc_attr( $keywords ); ?>" autocomplete="off" />
		</div>

		<div class="search_location resume-filter">
			<label for="search_location"><?php esc_html_e( 'Location', 'cariera-addons' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Any Location', 'cariera-addons' ); ?>" value="<?php echo esc_attr( $location ); ?>" />

			<?php do_action( 'cariera_wprm_resume_filters_location_extra' ); ?>
		</div>

		<?php if ( $categories ) : ?>
			<?php foreach ( $categories as $category ) : ?>
				<input type="hidden" name="search_categories[]" value="<?php echo esc_attr( sanitize_title( $category ) ); ?>" />
			<?php endforeach; ?>
		<?php elseif ( $show_categories && get_option( 'resume_manager_enable_categories' ) && ! is_tax( \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY ) && get_terms( \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY ) ) : ?>
			<div class="search_categories resume-filter">
				<label for="search_categories"><?php esc_html_e( 'Category', 'cariera-addons' ); ?></label>
				<?php if ( $show_category_multiselect ) : ?>
					<?php
					job_manager_dropdown_categories(
						[
							'taxonomy'     => \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY,
							'hierarchical' => 1,
							'name'         => 'search_categories',
							'orderby'      => 'name',
							'selected'     => $selected_category,
							'hide_empty'   => false,
						]
					);
					?>
				<?php else : ?>
					<?php
					wp_dropdown_categories(
						[
							'taxonomy'        => \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY,
							'hierarchical'    => 1,
							'show_option_all' => esc_html__( 'Any category', 'cariera-addons' ),
							'name'            => 'search_categories',
							'orderby'         => 'name',
							'selected'        => $selected_category,
						]
					);
					?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php
		/**
		 * Show the submit button on the resume filters form.
		 *
		 * @param bool $show_submit_button Whether to show the button. Defaults to false.
		 */
		if ( apply_filters( 'cariera_addons_resume_filters_show_submit_button', false ) ) {
			?>
			<div class="search_submit">
				<input type="submit" class="btn btn-main" value="<?php echo esc_attr( sprintf( __( 'Search %s', 'cariera-addons' ), $plural_label ) ); ?>">
			</div>
		<?php } ?>

		<?php do_action( 'resume_manager_resume_filters_search_resumes_end', $atts ); ?>
	</div>
	<div class="showing_resumes"></div>
</form>

<?php do_action( 'resume_manager_resume_filters_after', $atts ); ?>
