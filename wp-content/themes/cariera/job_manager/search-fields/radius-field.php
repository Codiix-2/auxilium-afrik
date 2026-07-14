<?php
/**
 * Radius field for search forms.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/search-fields/radius-field.php.
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

if ( ! get_option( 'cariera_search_radius' ) ) {
	return;
}

// Get the default checked status via filter (default is false/unchecked).
$search_radius_checked = apply_filters( 'cariera_search_radius_status_checked', false );
$max_value             = get_option( 'cariera_search_radius_max' );
$default_value         = get_option( 'cariera_search_radius_default' );
$distance_unit         = get_option( 'cariera_search_radius_unit', 'km' );
?>

<div class="search_radius">
	<div class="checkbox">
		<input type="checkbox" name="search_radius_status" id="search_radius_status" class="search_radius_status" <?php checked( $search_radius_checked, true ); ?>>
		<label for="search_radius_status"><?php esc_html_e( 'Search by Radius', 'cariera' ); ?></label>
	</div>
	
	<div class="range-slider">
		<input 
			type="range" 
			name="search_radius" 
			id="search_radius" 
			class="distance-radius" 
			min="0" 
			max="<?php echo esc_attr( $max_value ); ?>"
			step="1" 
			value="<?php echo esc_attr( $default_value ); ?>"
			data-unit="<?php echo esc_attr( $distance_unit ); ?>"
			data-title="<?php echo esc_attr__( 'Radius around selected location.', 'cariera' ); ?>">
	</div>
</div>
