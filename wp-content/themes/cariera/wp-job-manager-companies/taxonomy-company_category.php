<?php
/**
 * Custom: Taxonomy Template - Company Category
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/taxonomy-company_category.php.
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

$queried_object = get_queried_object();
$taxonomy       = ! empty( $queried_object->taxonomy ) ? get_taxonomy( $queried_object->taxonomy ) : null;
$term_id        = ! empty( $queried_object->term_id ) ? (int) $queried_object->term_id : 0;
?>

<section class="page-header company-taxonomy-header">
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
			$shortcode = '[companies categories=' . $term_id . ']';
			echo do_shortcode( $shortcode );
		}
		?>
	</div>
</main>
