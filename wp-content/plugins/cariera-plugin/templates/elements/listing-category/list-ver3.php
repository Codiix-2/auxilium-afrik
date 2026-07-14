<?php
/**
 * Elementor Element: Listing Category List Version 3
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing-category/list-ver3.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<ul class="listing-categories <?php echo esc_attr( $settings['listing'] ); ?>-categories list-layout3">
	<?php foreach ( $chunk as $term ) { ?>
		<li>
			<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
				<h4 class="title"><?php echo esc_html( $term->name ); ?></h4>

				<?php if ( 'job_listing' === $settings['listing'] && 'show' === $settings['job_counter'] ) { ?>
					<span class="category-count"><?php echo esc_html( $term->count ); ?></span>
				<?php } ?>
				
				<?php if ( 'resume' === $settings['listing'] && 'show' === $settings['resume_counter'] ) { ?>
					<span class="category-count"><?php echo esc_html( $term->count ); ?></span>
				<?php } ?>

				<?php if ( 'company' === $settings['listing'] && 'show' === $settings['company_counter'] ) { ?>
					<span class="category-count"><?php echo esc_html( $term->count ); ?></span>
				<?php } ?>
			</a>
		</li>
	<?php } ?>
</ul>
