<?php
/**
 * Packages template
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/packages/packages.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.0
 * @version     0.9.20
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
if ( empty( $packages ) ) {
	return;
}
?>

<li class="package-section">
	<?php esc_html_e( 'Purchase Package:', 'cariera-packages' ); ?>
</li>

<?php
foreach ( $packages as $key => $package ) {
	$product    = wc_get_product( $package );
	$repurchase = get_post_meta( $product->get_id(), '_cariera_disable_repurchase', true );

	// Early exit if the product is not of type 'cariera_package' or 'cariera_package_subscription' or not purchasable.
	if ( ! $product->is_type( [ 'cariera_package', 'cariera_package_subscription' ] ) || ! $product->is_purchasable() ) {
		continue;
	}

	// Get the correct post based on product type (variation or parent).
	$post = $product->is_type( 'variation' ) ? get_post( $product->get_parent_id() ) : get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	?>
	<li class="single-package">
		<div class="package-button"></div>
		<div class="package-details">
			<input type="radio" <?php checked( $checked, 1 ); $checked = 0; ?> name="cariera_package" value="<?php echo esc_attr( $product->get_id() ); ?>" id="package-<?php echo esc_attr( $product->get_id() ); ?>" />
			<label for="package-<?php echo esc_attr( $product->get_id() ); ?>">
				<?php echo esc_html( $product->get_title() ); ?>
				<?php if ( 'yes' === $repurchase ) { ?>
					<span class="repurchase">
						<?php esc_html_e( 'One-time purchase', 'cariera-packages' ); ?>
					</span>
				<?php } ?>
			</label>

			<div class="package-desc">
				<?php
				$package_id = $package;
				do_action( 'cariera_packages_package_description', $package_id, $package_type );
				$checked = 0;
				?>
			</div>
		</div>

		<div class="package-footer">
			<?php do_action( 'cariera_packages_package_footer', $package_id, $package_type ); ?>
		</div>
	</li>
	<?php
}
