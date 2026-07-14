<?php
/**
 * Resume sidebar search template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/wpjm/resume-sidebar-search.php.
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
?>

<form class="resume_filters">
	<div class="search_resumes">

		<div class="search_keywords resume-filter">
			<?php
			$keywords = '';

			if ( isset( $_GET['search_keywords'] ) ) {
				$keywords = sanitize_text_field( wp_unslash( $_GET['search_keywords'] ) );
			}
			?>
			<label for="search_keywords"><?php esc_html_e( 'Keywords', 'cariera-core' ); ?></label>
			<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'All Resumes', 'cariera-core' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" />
		</div>

		<div class="search_location resume-filter">
			<?php
			$location = '';

			if ( isset( $_GET['search_location'] ) ) {
				$location = sanitize_text_field( wp_unslash( $_GET['search_location'] ) );
			}
			?>
			<label for="search_location"><?php esc_html_e( 'Location', 'cariera-core' ); ?></label>
			<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Any Location', 'cariera-core' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
			<?php if ( get_option( 'cariera_auto_geolocate' ) ) { ?>
				<div class="geolocation"><i class="geolocate"></i></div>
			<?php } ?>
		</div>

		<?php do_action( 'cariera_wprm_resume_filters_search_radius' ); ?>

		<?php
		if ( get_option( 'resume_manager_enable_categories' ) && ! is_tax( 'resumes_category' ) && get_terms( [ 'taxonomy' => 'resumes_category' ] ) ) {
			$show_category_multiselect = get_option( 'resume_manager_enable_default_category_multiselect', false );
			$selected_category         = '';

			if ( isset( $_GET['search_category'] ) ) {
				$selected_category = sanitize_text_field( wp_unslash( $_GET['search_category'] ) );
			}
			?>

			<div class="search_categories resume-filter">
				<label for="search_categories"><?php esc_html_e( 'Category', 'cariera-core' ); ?></label>
				<?php if ( $show_category_multiselect ) : ?>
					<?php
					job_manager_dropdown_categories(
						[
							'taxonomy'     => 'resume_category',
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
					job_manager_dropdown_categories(
						[
							'taxonomy'        => 'resume_category',
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
				<?php endif; ?>
			</div>
			<?php
		}

		do_action( 'cariera_wprm_sidebar_job_filters_search_jobs_end' );
		?>
	</div>

	<div class="showing_resumes"></div>
</form>
