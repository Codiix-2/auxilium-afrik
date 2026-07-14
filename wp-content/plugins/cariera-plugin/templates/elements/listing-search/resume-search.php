<?php
/**
 * Elementor Element: Resume Search
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing-search/resume-search.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-search-forms' );

// Enqueue the location autocomplete script if enabled.
\Cariera_Core\Core\Assets::enqueue_location_autocomplete();

$classes = [ 'listing-search-form', 'resume-search-form', $settings['search_style'], $settings['custom_class'] ];
?>

<form method="GET" action="<?php echo esc_url( get_permalink( get_option( 'resume_manager_resumes_page_id' ) ) ); ?>" class="<?php echo esc_attr( join( ' ', $classes ) ); ?>" data-listing-type="<?php echo esc_attr( $settings['listing_search'] ); ?>">
	<div class="search-keywords">
		<label for="search_keywords"><?php esc_html_e( 'Keywords', 'cariera-core' ); ?></label>
		<input type="text" id="search_keywords" name="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera-core' ); ?>" autocomplete="off" data-keyword-autocomplete="<?php echo esc_attr( $settings['keyword_autocomple'] ); ?>">
		<div class="search-results"><div class="search-loader"><span></span></div><div class="listings cariera-scroll"></div></div>
	</div>

	<?php if ( ! empty( $settings['location'] ) ) { ?>
		<div class="search-location">
			<label for="search_location"><?php esc_html_e( 'Location', 'cariera-core' ); ?></label>
			<input type="text" id="search_location" name="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera-core' ); ?>" autocomplete="off">
			<?php if ( get_option( 'cariera_auto_geolocate' ) ) { ?>
				<div class="geolocation"><i class="geolocate"></i></div>
			<?php } ?>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'Astoundify_Job_Manager_Regions' ) && ! empty( $settings['region'] ) ) { ?>
		<div class="search-region">
			<label for="search_region"><?php esc_html_e( 'Region', 'cariera-core' ); ?></label>
			<?php
			wp_dropdown_categories(
				apply_filters(
					'job_manager_regions_dropdown_args',
					[
						'show_option_all' => esc_html__( 'All Regions', 'cariera-core' ),
						'hierarchical'    => true,
						'orderby'         => 'name',
						'taxonomy'        => 'resume_region',
						'name'            => 'search_region',
						'class'           => 'search_region cariera-select2-search',
						'hide_empty'      => 0,
						'selected'        => isset( $atts['selected_region'] ) ? $atts['selected_region'] : '',
					]
				)
			);
			?>
		</div>
	<?php } ?>

	<?php if ( ! empty( $settings['categories'] ) ) { ?>
		<div class="search-categories">
			<label for="search_category_resumes"><?php esc_html_e( 'Category', 'cariera-core' ); ?></label>              
			<?php
			cariera_job_manager_dropdown_category(
				[
					'taxonomy'        => 'resume_category',
					'hierarchical'    => 1,
					'name'            => 'search_category',
					'id'              => 'search_category_resumes',
					'orderby'         => 'name',
					'selected'        => '',
					'multiple'        => false,
					'show_option_all' => true,
				]
			);
			?>
		</div>
	<?php } ?>
	
	<div class="search-submit"><input type="submit" class="btn btn-main btn-effect" value="<?php esc_attr_e( 'Search', 'cariera-core' ); ?>"></div>
</form>
