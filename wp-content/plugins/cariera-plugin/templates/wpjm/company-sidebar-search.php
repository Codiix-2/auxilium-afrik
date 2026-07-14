<?php
/**
 * Company sidebar search template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/wpjm/company-sidebar-search.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-search-forms' );
wp_enqueue_script( 'cariera-company-ajax-filters' );
?>

<form class="company_filters">
	<div class="search_companies">

		<div class="search_keywords">
			<?php
			$keywords = '';

			if ( isset( $_GET['search_keywords'] ) ) {
				$keywords = sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) );
			}
			?>
			<label for="search_keywords"><?php esc_html_e( 'Keywords', 'cariera-core' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'Keywords', 'cariera-core' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" />
		</div>

		<div class="search_location">
			<?php
			$location = '';

			if ( isset( $_GET['search_location'] ) ) {
				$location = sanitize_text_field( wp_unslash( $_GET['search_location'] ) );
			}
			?>
			<label for="search_location"><?php esc_html_e( 'Location', 'cariera-core' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Location', 'cariera-core' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
			<?php if ( get_option( 'cariera_auto_geolocate' ) ) { ?>
				<div class="geolocation"><i class="geolocate"></i></div>
			<?php } ?>
		</div>

		<?php do_action( 'cariera_company_filters_search_radius' ); ?>

		<?php if ( get_option( 'cariera_company_category' ) ) { ?>
			<div class="search_categories">
				<label for="search_categories"><?php echo esc_html__( 'Categories', 'cariera-core' ); ?></label>

				<?php
				$selected_category = '';

				if ( isset( $_GET['search_category'] ) ) {
					$selected_category = sanitize_text_field( wp_unslash( $_GET['search_category'] ) );
				}

				job_manager_dropdown_categories(
					[
						'taxonomy'        => 'company_category',
						'hierarchical'    => 1,
						'show_option_all' => esc_html__( 'Any category', 'cariera-core' ),
						'name'            => 'search_categories',
						'class'           => 'cariera-select2-search',
						'orderby'         => 'name',
						'selected'        => $selected_category,
						'hide_empty'      => false,
						'multiple'        => false,
					]
				);
				?>
			</div>
		<?php } ?>
	</div>

	<div class="showing_companies"></div>
</form>
