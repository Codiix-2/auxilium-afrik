<?php
/**
 * Custom: Single Company's location in map
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/listing-map.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.6
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Enqueue the maps script if enabled.
if ( class_exists( '\Cariera_Core\Core\Assets' ) ) {
	Cariera_Core\Core\Assets::enqueue_maps();
}

$company_map = cariera_get_option( 'cariera_company_map' );
$lng         = $post->geolocation_long;
$lat         = $post->geolocation_lat;
$logo        = get_the_company_logo( $post->ID, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );

if ( ! $company_map || empty( $lng ) || empty( $lat ) ) {
	return;
}

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
}
?>

<div id="company-map" class="single-company-map" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>"></div>
