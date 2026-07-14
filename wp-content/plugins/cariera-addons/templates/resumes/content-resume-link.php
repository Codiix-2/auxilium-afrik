<?php
/**
 * Display a link inside the resume content of a resume list.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/content-resume-link.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     0.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$parsed_url = wp_parse_url( $link['url'] );
$host       = isset( $parsed_url['host'] ) ? current( explode( '.', $parsed_url['host'] ) ) : '';
?>
<li class="resume-link resume-link-<?php echo esc_attr( sanitize_title( $host ) ); ?>">
	<a rel="nofollow" href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['name'] ); ?></a>
</li>
