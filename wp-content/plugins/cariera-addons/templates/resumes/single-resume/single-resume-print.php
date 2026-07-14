<?php
/**
 * Single Resume Page - Print listing
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/single-resume/single-resume-print.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $resume_preview;

if ( $resume_preview ) {
	return;
}

$singular_label = cariera_addons_resume_cpt_singular_label();
?>

<a class="print-page" href="javascript:void(0)" onclick="window.print();" aria-label="<?php esc_attr_e( 'Print', 'cariera-addons' ); ?>"><i class="las la-print"></i><?php printf( esc_html__( 'Print %s', 'cariera-addons' ), $singular_label ); ?></a>
