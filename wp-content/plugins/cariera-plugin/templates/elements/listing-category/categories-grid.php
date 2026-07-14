<?php
/**
 * Elementor Element: Listing Category Grid Layout
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing-category/categories-grid.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="listing-category-wrapper grid-layout <?php echo esc_attr( $settings['custom_class'] ); ?>">
	<?php foreach ( $chunks as $chunk ) { ?>

		<?php
		// Category Grid Layout 1.
		if ( 'layout1' === $settings['category_layout'] ) {
			cariera_get_template(
				'elements/listing-category/grid-ver1.php',
				[
					'settings' => $settings,
					'chunk'    => $chunk,
				]
			);
		}

		// Category Grid Layout 2.
		if ( 'layout2' === $settings['category_layout'] ) {
			cariera_get_template(
				'elements/listing-category/grid-ver2.php',
				[
					'settings' => $settings,
					'chunk'    => $chunk,
				]
			);
		}

		// Category Grid Layout 3.
		if ( 'layout3' === $settings['category_layout'] ) {
			cariera_get_template(
				'elements/listing-category/grid-ver3.php',
				[
					'settings' => $settings,
					'chunk'    => $chunk,
				]
			);
		}

		// Category Grid Layout 4.
		if ( 'layout4' === $settings['category_layout'] ) {
			cariera_get_template(
				'elements/listing-category/grid-ver4.php',
				[
					'settings' => $settings,
					'chunk'    => $chunk,
				]
			);
		}
	}
	?>
</div>
