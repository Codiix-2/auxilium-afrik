<?php
/**
 * Content for a single resume.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/content-single-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     0.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

wp_enqueue_style( 'cariera-single-resume' );
wp_enqueue_script( 'cariera-single-resume' );

$layout = cariera_single_resume_layout();

if ( resume_manager_user_can_view_resume( $post->ID ) ) {
	get_job_manager_template_part( 'resumes/single-resume/single', 'resume-' . $layout, 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
} else {
	get_job_manager_template_part( 'resumes/access-denied', 'single-resume', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
}
