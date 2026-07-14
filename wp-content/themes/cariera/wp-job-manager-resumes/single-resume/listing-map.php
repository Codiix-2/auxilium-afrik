<?php
/**
 * Custom: Single Resume's location in map
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/listing-map.php.
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

$resume_map = cariera_get_option( 'cariera_resume_map' );
$lng        = $post->geolocation_long;
$lat        = $post->geolocation_lat;
$logo       = get_the_candidate_photo();

if ( ! $resume_map || empty( $lng ) || empty( $lat ) ) {
	return;
}

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
}
?>

<div id="resume-map" class="single-resume-map" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>"></div>
