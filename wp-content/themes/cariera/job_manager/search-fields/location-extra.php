<?php
/**
 * Location extra template for search forms.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/search-fields/location-extra.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @since       1.7.5
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! get_option( 'cariera_auto_geolocate' ) && ! get_option( 'cariera_search_radius' ) ) {
	return;
}

// Get the default checked status via filter (default is false/unchecked).
$search_radius_checked = apply_filters( 'cariera_search_radius_status_checked', false );
$max_value             = get_option( 'cariera_search_radius_max' );
$default_value         = get_option( 'cariera_search_radius_default' );
$distance_unit         = get_option( 'cariera_search_radius_unit', 'km' );
?>

<?php if ( get_option( 'cariera_auto_geolocate' ) && ! get_option( 'cariera_search_radius' ) ) { ?>
	<div class="geolocation"><i class="geolocate"></i></div>
<?php } ?>

<?php if ( get_option( 'cariera_search_radius' ) ) { ?>
	<span class="location-extra-btn"><i class="las la-map-marker"></i></span>

	<div class="location-extra-fields">
		<div class="action-btns">
			<div class="checkbox">
				<input type="checkbox" name="search_radius_status" id="search_radius_status" class="search_radius_status" <?php checked( $search_radius_checked, true ); ?>>
				<label for="search_radius_status"><?php esc_html_e( 'Search by Radius', 'cariera' ); ?></label>
			</div>

			<?php if ( get_option( 'cariera_auto_geolocate' ) ) { ?>
				<div class="geolocation geolocation-advanced"><i class="geolocate"></i><?php esc_html_e( 'Get location', 'cariera' ); ?></div>
			<?php } ?>
		</div>

		<div class="search_radius">
			<div class="range-slider">
				<input 
				name="search_radius" 
				id="search_radius" 
				class="distance-radius" 
				type="range" 
				min="0" 
				max="<?php echo esc_attr( $max_value ); ?>" 
				step="1" 
				value="<?php echo esc_attr( $default_value ); ?>"
				data-unit="<?php echo esc_attr( $distance_unit ); ?>"
				data-title="<?php echo esc_attr__( 'Radius around selected location.', 'cariera' ); ?>">
			</div>
		</div>
	</div>
<?php } ?>
