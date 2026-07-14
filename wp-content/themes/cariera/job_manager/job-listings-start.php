<?php
/**
 * Content shown before job listings in `[jobs]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-listings-start.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.15.0
 *
 * @cariera-since   1.7.0
 * @cariera-version 1.9.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<ul class="job_listings job-listings-main <?php echo esc_attr( $jobs_layout_wrapper ); ?>">