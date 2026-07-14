<?php
/**
 * Taxonomy Template - Resume Category
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/taxonomy-resume_category.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$queried_object = get_queried_object();

$taxonomy        = ! empty( $queried_object->taxonomy ) ? get_taxonomy( $queried_object->taxonomy ) : null;
$term_id         = ! empty( $queried_object->term_id ) ? (int) $queried_object->term_id : 0;
$layout          = cariera_get_option( 'cariera_resume_taxonomy_layout' );
$list_layout     = cariera_get_option( 'cariera_resume_taxonomy_list_version' );
$grid_layout     = cariera_get_option( 'cariera_resume_taxonomy_grid_version' );
$taxonomy_layout = '';

// Add layout option if settings exist.
if ( 'list' === $layout && $list_layout ) {
	$taxonomy_layout = 'resumes_layout="list" resumes_list_version="' . esc_attr( $list_layout ) . '"';
} elseif ( 'grid' === $layout && $grid_layout ) {
	$taxonomy_layout = 'resumes_layout="grid" resumes_grid_version="' . esc_attr( $grid_layout ) . '"';
}

get_header();
?>

<section class="page-header resume-header">
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
			$shortcode = '[resumes categories=' . $term_id . ' ' . $taxonomy_layout . ']';
			echo do_shortcode( $shortcode );
		}
		?>
	</div>
</main>

<?php
get_footer();
