<?php

get_header();

$post_type = get_query_var( 'post_type' );

switch ( $post_type ) {
	case 'job_listing':
		get_job_manager_template( 'archive-job_listing.php' );
		break;

	case 'resume':
		get_job_manager_template( 'archive-resume.php', [], 'wp-job-manager-resumes' );
		break;

	case 'company':
		get_job_manager_template( 'archive-company.php', [], 'wp-job-manager-companies' );
		break;

	default:
		get_template_part( 'templates/archive' );
		break;
}

get_footer();
