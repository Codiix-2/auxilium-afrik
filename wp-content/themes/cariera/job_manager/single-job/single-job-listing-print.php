<?php
/**
 * Custom: Single Job Page - Print listing
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/single-job-listing-print.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.1
 * @version     1.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $job_preview;

if ( $job_preview ) {
	return;
}
?>

<a class="print-page" href="javascript:void(0)" onclick="window.print();" aria-label="<?php esc_attr_e( 'Print', 'cariera' ); ?>"><i class="las la-print"></i><?php esc_html_e( 'Print Job Listing', 'cariera' ); ?></a>