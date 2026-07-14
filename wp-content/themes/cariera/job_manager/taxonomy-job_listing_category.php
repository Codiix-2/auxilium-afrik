<?php
/**
 * Custom: Taxonomy Template - Job Listing Category
 *
 * This template can be overridden by copying it to yourtheme/job_manager/taxonomy-job_listing_category.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.7
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$queried_object  = get_queried_object();
$taxonomy        = ! empty( $queried_object->taxonomy ) ? get_taxonomy( $queried_object->taxonomy ) : null;
$term_id         = ! empty( $queried_object->term_id ) ? (int) $queried_object->term_id : 0;
$layout          = cariera_get_option( 'cariera_job_taxonomy_layout' );
$list_layout     = cariera_get_option( 'cariera_job_taxonomy_list_version' );
$grid_layout     = cariera_get_option( 'cariera_job_taxonomy_grid_version' );
$taxonomy_layout = '';

// Taxonomy bg info.
$bg_img           = get_term_meta( $term_id, 'cariera_background_image', true );
$pageheader_class = '';
$term_bg          = '';

if ( ! empty( $bg_img ) && cariera_get_option( 'cariera_job_category_bg' ) ) {
	$pageheader_class = 'page-header-bg';
	$term_bg          = 'style="background: url(' . esc_attr( $bg_img ) . ')"';
}

// Add layout options if settings exist.
if ( ! empty( $layout ) ) {
	if ( 'list' === $layout ) {
		$taxonomy_layout = 'jobs_layout="list" jobs_list_version="' . $list_layout . '"';
	} else {
		$taxonomy_layout = 'jobs_layout="grid" jobs_grid_version="' . $grid_layout . '"  ';
	}
}
?>

<section class="page-header job-header job-taxonomy-header <?php echo esc_attr( $pageheader_class ); ?>" <?php echo wp_kses_post( $term_bg ); ?>>
	<h1 class="title">
		<?php
		echo $taxonomy ? esc_attr( $taxonomy->labels->singular_name ) . ': ' : '';
		single_term_title();
		?>
	</h1>
</section>

<main class="cariera-section-padding">
	<div class="container">
		<?php
		if ( isset( $term_id ) ) {
			$shortcode = '[jobs categories=' . $term_id . ' ' . $taxonomy_layout . ']';
			echo do_shortcode( $shortcode );
		}
		?>
	</div>
</main>
