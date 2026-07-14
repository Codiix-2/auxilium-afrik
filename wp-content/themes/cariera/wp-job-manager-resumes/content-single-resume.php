<?php
/**
 * Content for a single resume.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/content-single-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-resumes
 * @category    Template
 * @since       1.7.0
 * @version     1.8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

wp_enqueue_style( 'cariera-single-resume' );
wp_enqueue_script( 'cariera-single-resume' );

$layout = cariera_single_resume_layout();

if ( resume_manager_user_can_view_resume( $post->ID ) ) :
	get_job_manager_template_part( 'single-resume/single', 'resume-' . $layout, 'wp-job-manager-resumes' );
else :
	get_job_manager_template_part( 'access-denied', 'single-resume', 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
endif;
