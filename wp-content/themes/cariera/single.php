<?php

get_header();
do_action( 'cariera_single_listing_data' );

while ( have_posts() ) {
	the_post();

	switch ( get_post_type() ) {
		case 'job_listing':
			do_action( 'cariera_single_job_listing_start' );
			get_job_manager_template( 'content-single-job_listing.php' );
			do_action( 'cariera_single_job_listing_end' );
			break;

		case 'company':
			do_action( 'cariera_single_company_start' );
			get_job_manager_template( 'content-single-company.php', [], 'wp-job-manager-companies' );
			do_action( 'cariera_single_company_end' );
			break;

		case 'resume':
			do_action( 'cariera_single_resume_start' );
			get_job_manager_template( 'content-single-resume.php', [], 'wp-job-manager-resumes' );
			do_action( 'cariera_single_resume_end' );
			break;

		case 'elementor_library':
			the_content();
			break;

		default:
			get_template_part( 'templates/content/single' );
			break;
	}
}

get_footer();
