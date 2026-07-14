<?php
/**
 * Cariera Package Template for WooCommerce Product Type
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/admin/packages/wc-product-cariera-package.php.
 *
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.0
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$package_product_id = get_the_ID();

// Get all packages grouped.
$all_packages     = \Cariera_Packages\Post_Types\Cariera_Package::package_types();
$post_type_groups = \Cariera_Packages\Post_Types\Cariera_Package::get_post_type_groups();

// Get saved values.
$selected_post_type    = get_post_meta( $package_product_id, '_package_post_type', true );
$selected_package_type = get_post_meta( $package_product_id, '_package_type', true );
?>

<div class="options_group show_if_cariera_package show_if_cariera_package_subscription">
	<!-- Post Type -->
	<p class="form-field">
		<label for="_package_post_type"><?php esc_html_e( 'Post Type', 'cariera-packages' ); ?></label>
		<select id="_package_post_type" name="_package_post_type" class="select short">
			<option value=""><?php esc_html_e( 'Choose post type', 'cariera-packages' ); ?></option>
			<?php foreach ( $post_type_groups as $post_type_key => $group_name ) { ?>
				<option value="<?php echo esc_attr( $post_type_key ); ?>" <?php selected( $selected_post_type, $post_type_key ); ?>>
					<?php echo esc_html( ucfirst( $post_type_key ) ); ?>
				</option>
			<?php } ?>
		</select>
	</p>

	<!-- Package Type -->
	<p class="form-field">
		<label for="_package_type"><?php esc_html_e( 'Package Type', 'cariera-packages' ); ?></label>
		<select id="_package_type" name="_package_type" class="select short">
			<option value=""><?php esc_html_e( 'Choose package type', 'cariera-packages' ); ?></option>
		</select>
	</p>

	<hr />

	<!-- Package-specific options -->
	<?php
	// Job Listing Options.
	get_job_manager_template_part( 'admin/packages/post-types/job-listing', '', 'cariera-packages', CARIERA_PACKAGES_PATH . '/templates/' );

	// Company Options.
	if ( \Cariera_Packages::cariera_company_manager_active() ) {
		get_job_manager_template_part( 'admin/packages/post-types/company', '', 'cariera-packages', CARIERA_PACKAGES_PATH . '/templates/' );
	}

	// Resume Options.
	if ( \Cariera_Packages::wprm_active() ) {
		get_job_manager_template_part( 'admin/packages/post-types/resume', '', 'cariera-packages', CARIERA_PACKAGES_PATH . '/templates/' );
	}

	// Event Options.
	if ( class_exists( 'Cariera_Events' ) ) {
		get_job_manager_template_part( 'admin/packages/post-types/event', '', 'cariera-packages', CARIERA_PACKAGES_PATH . '/templates/' );
	}
	?>

	<!-- Other Options -->
	<?php
	woocommerce_wp_checkbox(
		[
			'id'          => '_cariera_disable_repurchase',
			'label'       => esc_html__( 'Disable repeat purchase?', 'cariera-packages' ),
			'description' => esc_html__( 'This package can only be bought once per user if checked. Useful for free packages.', 'cariera-packages' ),
			'value'       => get_post_meta( $package_product_id, '_cariera_disable_repurchase', true ),
		]
	);

	woocommerce_wp_checkbox(
		[
			'id'          => '_package_use_sd',
			'label'       => esc_html__( 'Use Short Description', 'cariera-packages' ),
			'description' => esc_html__( 'Enable to use the short description for custom wording on the package selection form.', 'cariera-packages' ),
			'value'       => get_post_meta( $package_product_id, '_package_use_sd', true ),
		]
	);

	do_action( 'cariera_packages_cariera_package_options_product_tab_content' );
	?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
	var $wrapper = $('.options_group.show_if_cariera_package');

	var postTypePackages = 
	<?php
	$js_map = [];
	foreach ( $post_type_groups as $pt_key => $group_name ) {
		$js_map[ $pt_key ] = isset( $all_packages[ $group_name ] ) ? $all_packages[ $group_name ] : [];
	}
	echo wp_json_encode( $js_map );
	?>
	;

	var selectedPackage = "<?php echo esc_js( $selected_package_type ); ?>";

	function populatePackages(postType){
		var $packageSelect = $wrapper.find('#_package_type');
		$packageSelect.empty();
		$packageSelect.append('<option value=""><?php echo esc_js( 'Choose package type', 'cariera-packages' ); ?></option>');

		if(postTypePackages[postType]){
			$.each(postTypePackages[postType], function(key, label){
				var selected = (key === selectedPackage) ? 'selected' : '';
				$packageSelect.append('<option value="'+ key +'" '+ selected +'>'+ label +'</option>');
			});
		}

		$packageSelect.trigger('change');
	}

	// Initial population
	var initialPostType = $wrapper.find('#_package_post_type').val();
	if(!initialPostType && selectedPackage){
		$.each(postTypePackages, function(pt, packages){
			if(packages[selectedPackage]){
				initialPostType = pt;
				$wrapper.find('#_package_post_type').val(pt);
				return false;
			}
		});
	}

	if(initialPostType) populatePackages(initialPostType);

	// On change
	$wrapper.find('#_package_post_type').on('change', function(){
		populatePackages($(this).val());
	});

	function toggleOptionsBasedOnPackageType() {
		var packageType = $wrapper.find('#_package_type').val();
		$wrapper.find('.cariera-packages-options').hide();
		if(packageType) {
			$wrapper.find('#' + packageType).show();
		}
	}

	toggleOptionsBasedOnPackageType();
	$wrapper.find('#_package_type').on('change', toggleOptionsBasedOnPackageType);

	// ---- Force WooCommerce pricing fields visible ONLY for Cariera Package ----
	function togglePricingFields() {
		if( $('#product-type').val() === 'cariera_package' ) {
			$('#general_product_data .pricing').show();
		}
	}

	// On page load
	togglePricingFields();

	// On product type change
	$('#product-type').on('change', togglePricingFields);
});
</script>
