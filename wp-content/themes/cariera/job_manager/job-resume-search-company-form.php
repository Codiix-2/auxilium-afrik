<?php
/**
 * Custom: Job Resume Tabs Search - Resume Form
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-resume-search-resume-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form method="GET" action="<?php echo esc_url( get_permalink( get_option( 'cariera_companies_page' ) ) ); ?>" class="listing-search-form company-search-form">
	<div class="search-keywords">
		<label for="search_keywords_companies"><?php esc_html_e( 'Keywords', 'cariera' ); ?></label>
		<input type="text" id="search_keywords_companies" name="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera' ); ?>" autocomplete="off">
	</div>

	<div class="search-location">
		<label for="search_location_companies"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
		<input type="text" id="search_location_companies" name="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera' ); ?>" autocomplete="off">
		<?php if ( get_option( 'cariera_auto_geolocate' ) ) { ?>
			<div class="geolocation"><i class="geolocate"></i></div>
		<?php } ?>
	</div>

	<?php if ( get_option( 'cariera_company_category' ) ) { ?>
		<div class="search-categories">
			<label for="search_category_companies"><?php esc_html_e( 'Category', 'cariera' ); ?></label>
			<?php
			cariera_job_manager_dropdown_category(
				[
					'taxonomy'        => 'company_category',
					'hierarchical'    => 1,
					'name'            => 'search_category',
					'id'              => 'search_category_companies',
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
		<label for="search-companies"><?php esc_html_e( 'Button', 'cariera' ); ?></label>
		<input type="submit" id="search-companies" class="btn btn-main btn-effect" value="<?php esc_attr_e( 'Search', 'cariera' ); ?>">
	</div>
</form>
