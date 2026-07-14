<?php
/**
 * Generates a single job item as part of the {jobs} list in job alert e-mails.
 *
 * WARNING: This template should only be used as plaintext e-mail content.
 * User content is not escaped to securely display on the site as HTML content.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/alerts/content-email_job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.2
 * @version     0.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Exit if not being rendered in the context of an e-mail.

global $job_manager_doing_email;

if ( empty( $job_manager_doing_email ) ) {
	wp_die( esc_html__( 'Invalid template usage: This template can only be used as part of an e-mail.', 'cariera-addons' ) );
}

global $post;

$types    = wpjm_get_the_job_types();
$location = get_the_job_location();
$company  = get_the_company_name();

echo "\n";

// Job title.
echo wp_specialchars_decode( $post->post_title );

// Job types.
if ( $types && count( $types ) > 0 ) {
	$names = wp_list_pluck( $types, 'name' );

	$types_str = implode( ', ', $names );

	echo ' (' . wp_specialchars_decode( $types_str ) . ')';
}

echo "\n";
echo esc_url( get_the_job_permalink() ) . "\n";

// Location and company.
if ( $location ) {
	printf( __( 'Location: %s', 'cariera-addons' ) . "\n", wp_specialchars_decode( $location ) );
}
if ( $company ) {
	printf( __( 'Company: %s', 'cariera-addons' ) . "\n", wp_specialchars_decode( $company ) );
}
