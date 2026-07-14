<?php
/**
 * Company - Company Filters
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-filters.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.3.0
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_style( 'cariera-wpjm-search-forms' );
wp_enqueue_script( 'cariera-company-ajax-filters' );

// Enqueue the location autocomplete script if enabled.
if ( class_exists( '\Cariera_Core\Core\Assets' ) ) {
	Cariera_Core\Core\Assets::enqueue_location_autocomplete();
}

do_action( 'cariera_company_filters_before', $atts ); ?>

<form class="company_filters">
	<div class="search_companies">
		<?php do_action( 'cariera_company_search_filters_start', $atts ); ?>

		<div class="search_keywords">
			<label for="search_keywords"><?php echo esc_html__( 'Keywords', 'cariera' ); ?></label>
			<input type="text" id="search_keywords" class="search-field" placeholder="<?php echo esc_attr__( 'Keywords', 'cariera' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" name="search_keywords" autocomplete="off" />
		</div>

		<div class="search_location">
			<label for="search_location"><?php echo esc_html__( 'Location', 'cariera' ); ?></label>
			<input type="text" id="search_location" class="location-search-field" placeholder="<?php echo esc_attr__( 'Locations', 'cariera' ); ?>" value="<?php echo esc_attr( $location ); ?>" name="search_location" />

			<?php do_action( 'cariera_company_filters_location_extra' ); ?>
		</div>

		<?php if ( $categories ) { ?>
			<?php foreach ( $categories as $category ) { ?>
				<input type="hidden" name="search_categories[]" value="<?php echo esc_attr( sanitize_title( $category ) ); ?>" />
			<?php } ?>
		<?php } elseif ( $show_categories && get_option( 'cariera_company_category' ) && ! is_tax( 'company_category' ) && get_terms( 'company_category' ) ) { ?>
			<div class="search_categories company-filter">
				<label for="search_categories"><?php esc_html_e( 'Category', 'cariera' ); ?></label>
				<?php
				if ( $show_category_multiselect ) {
					job_manager_dropdown_categories(
						[
							'taxonomy'     => 'company_category',
							'hierarchical' => 1,
							'name'         => 'search_categories',
							'class'        => 'cariera-select2-search',
							'orderby'      => 'name',
							'selected'     => $selected_category,
							'hide_empty'   => true,
						]
					);
				} else {
					job_manager_dropdown_categories(
						[
							'taxonomy'        => 'company_category',
							'hierarchical'    => 1,
							'show_option_all' => esc_html__( 'Any category', 'cariera' ),
							'name'            => 'search_categories',
							'class'           => 'cariera-select2-search',
							'orderby'         => 'name',
							'selected'        => $selected_category,
							'hide_empty'      => false,
							'multiple'        => false,
						]
					);
				}
				?>
			</div>
		<?php } ?>

		<?php
		/**
		 * Show the submit button on the job filters form.
		 *
		 * @since 1.7.5
		 *
		 * @param bool $show_submit_button Whether to show the button. Defaults to true.
		 * @return bool
		 */
		if ( apply_filters( 'cariera_company_filters_show_submit_button', false ) ) :
			?>
			<div class="search_submit">
				<input type="submit" class="btn btn-main" value="<?php esc_attr_e( 'Search Companies', 'cariera' ); ?>">
			</div>
		<?php endif; ?>

		<?php do_action( 'cariera_company_search_filters_end', $atts ); ?>
	</div>

	<div class="showing_companies"></div>
</form>

<?php do_action( 'cariera_company_filters_after', $atts ); ?>
