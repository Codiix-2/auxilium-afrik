<?php
/**
 * Single Resume page
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/single-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
do_action( 'cariera_single_listing_data' );

while ( have_posts() ) {
	the_post();

	get_job_manager_template( 'resumes/content-single-resume.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
}

get_footer();
