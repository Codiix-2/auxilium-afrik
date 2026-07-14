<?php
/**
 * Single company template
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/single-company.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.4
 * @version     0.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $job_id;

get_header();

while ( have_posts() ) :
	the_post();

	get_job_manager_template_part( 'content', 'single-company', 'cariera-packages', CARIERA_PACKAGES_PATH . '/templates/' );
endwhile;

get_footer();
