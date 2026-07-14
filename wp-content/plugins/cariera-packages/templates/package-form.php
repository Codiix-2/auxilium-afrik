<?php
/**
 * Package form template
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/package-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.0
 * @version     0.9.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<ul class="cariera_packages <?php echo esc_attr( $package_type ); ?>">
	<?php
	get_job_manager_template(
		'packages/user-packages.php',
		[
			'package_type'  => $package_type,
			'user_packages' => $user_packages,
			'checked'       => 1,
		],
		'cariera-packages',
		CARIERA_PACKAGES_PATH . '/templates/'
	);

	get_job_manager_template(
		'packages/packages.php',
		[
			'package_type' => $package_type,
			'packages'     => $packages,
			'checked'      => empty( $user_packages ) ? 1 : 0,
		],
		'cariera-packages',
		CARIERA_PACKAGES_PATH . '/templates/'
	);
	?>
</ul>
