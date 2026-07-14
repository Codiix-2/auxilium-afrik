<?php
/**
 * Elementor Element: Listing Search Box
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing-search/listing-searchbox.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.5
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-search-forms' );

// Enqueue the location autocomplete script if enabled.
\Cariera_Core\Core\Assets::enqueue_location_autocomplete();

$listing_submission = '';
$category_taxonomy  = '';
$region_taxonomy    = '';

if ( 'job_listing' === $settings['listing_search'] ) {
	$listing_submission = get_permalink( get_option( 'job_manager_jobs_page_id' ) );
	$region_taxonomy    = 'job_listing_region';
	$category_taxonomy  = 'job_listing_category';
}

if ( 'resume' === $settings['listing_search'] ) {
	$listing_submission = get_permalink( get_option( 'resume_manager_resumes_page_id' ) );
	$region_taxonomy    = 'resume_region';
	$category_taxonomy  = 'resume_category';
}

if ( 'company' === $settings['listing_search'] ) {
	$listing_submission = get_permalink( get_option( 'cariera_companies_page' ) );
	$region_taxonomy    = '';
	$category_taxonomy  = 'company_category';
}
?>

<form class="listing-search-box <?php echo esc_attr( $settings['custom_class'] ); ?>" method="get" action="<?php echo esc_url( $listing_submission ); ?>">
	<div class="form-title">
		<h4 class="title"><?php echo esc_html( $settings['title'] ); ?></h4>
	</div>
	
	<?php if ( ! empty( $settings['keywords'] ) ) { ?>
		<div class="search-keywords">
			<label for="search_keywords"><?php esc_html_e( 'Keywords', 'cariera-core' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera-core' ); ?>" value="" autocomplete="off">
		</div>
	<?php } ?>

	<?php if ( ! empty( $settings['location'] ) ) { ?>
		<div class="search-location">
			<label for="search_location"><?php esc_html_e( 'Location', 'cariera-core' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera-core' ); ?>" value="">
			<?php if ( get_option( 'cariera_auto_geolocate' ) ) { ?>
				<div class="geolocation"><i class="geolocate"></i></div>
			<?php } ?>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'Astoundify_Job_Manager_Regions' ) && ! empty( $settings['region'] ) && ! empty( $region_taxonomy ) ) { ?>
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
						'taxonomy'        => $region_taxonomy,
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
			<label for="search_category"><?php esc_html_e( 'Categories', 'cariera-core' ); ?></label>

			<?php
			cariera_job_manager_dropdown_category(
				[
					'taxonomy'        => $category_taxonomy,
					'hierarchical'    => 1,
					'name'            => 'search_category',
					'id'              => 'search_category',
					'orderby'         => 'name',
					'selected'        => '',
					'multiple'        => false,
					'show_option_all' => true,
				]
			);
			?>
		</div>
	<?php } ?>

	<div class="search-submit">
		<button type="submit" class="btn btn-main btn-icon btn-effect"><i class="las la-search"></i><?php esc_html_e( 'search', 'cariera-core' ); ?></button>
	</div>
</form>
