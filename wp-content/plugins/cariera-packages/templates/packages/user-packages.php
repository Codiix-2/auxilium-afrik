<?php
/**
 * User Packages template
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/packages/user-packages.php.
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

// Early exit if no user packages are available.
if ( empty( $user_packages ) ) {
	return;
}
?>

<li class="package-section">
	<?php esc_html_e( 'Your Packages:', 'cariera-packages' ); ?>
</li>

<?php
foreach ( $user_packages as $key => $package_id ) {
	// Retrieve package details.
	$package = get_post( $package_id );

	// Early exit for invalid package.
	if ( ! $package ) {
		continue;
	}

	$package_title = esc_html( $package->post_title );
	?>
	<li class="user-single-package">
		<div class="package-button"></div>
		<div class="package-details">
			<input type="radio" <?php checked( $checked, 1 ); ?> name="cariera_package" value="user-<?php echo esc_attr( $package_id ); ?>" id="user-package-<?php echo esc_attr( $package_id ); ?>" />
			<label for="user-package-<?php echo esc_attr( $package_id ); ?>">
				<?php echo esc_html( $package_title ); ?>
			</label>

			<div class="package-desc">
				<?php do_action( 'cariera_packages_user_package_description', $package_id, $package_type ); ?>
				<?php $checked = 0; ?>
			</div>
		</div>

		<div class="package-footer">
			<?php do_action( 'cariera_packages_user_package_footer', $package_id, $package_type ); ?>
		</div>
	</li>
	<?php
}
