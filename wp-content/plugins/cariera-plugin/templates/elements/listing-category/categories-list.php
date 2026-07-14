<?php
/**
 * Elementor Element: Listing Category List Layout
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/elements/listing-category/categories-list.php.
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

<div class="listing-category-wrapper list-layout <?php echo esc_attr( $settings['custom_class'] ); ?>">
	<?php foreach ( $chunks as $chunk ) { ?>
		<div class="<?php echo esc_attr( $column_class ); ?>">
			<?php
			// Category List Layout 1.
			if ( 'layout1' === $settings['category_layout'] ) {
				cariera_get_template(
					'elements/listing-category/list-ver1.php',
					[
						'settings' => $settings,
						'chunk'    => $chunk,
					]
				);
			}

			// Category List Layout 2.
			if ( 'layout2' === $settings['category_layout'] ) {
				cariera_get_template(
					'elements/listing-category/list-ver2.php',
					[
						'settings' => $settings,
						'chunk'    => $chunk,
					]
				);
			}

			// Category List Layout 3.
			if ( 'layout3' === $settings['category_layout'] ) {
				cariera_get_template(
					'elements/listing-category/list-ver3.php',
					[
						'settings' => $settings,
						'chunk'    => $chunk,
					]
				);
			}
			?>
		</div>
	<?php } ?>
</div>
