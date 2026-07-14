<?php

$taxonomy      = get_taxonomy( get_queried_object()->taxonomy );
$taxonomy_name = $taxonomy->name;

get_header();

switch ( $taxonomy_name ) {
	case 'job_listing_category':
		get_job_manager_template( 'taxonomy-job_listing_category.php' );
		break;

	case 'job_listing_region':
		get_job_manager_template( 'taxonomy-job_listing_region.php' );
		break;

	case 'job_listing_tag':
		get_job_manager_template( 'taxonomy-job_listing_tag.php' );
		break;

	case 'job_listing_type':
		get_job_manager_template( 'taxonomy-job_listing_type.php' );
		break;

	case 'resume_category':
		get_job_manager_template( 'taxonomy-resume_category.php', [], 'wp-job-manager-resumes' );
		break;

	case 'company_category':
		get_job_manager_template( 'taxonomy-company_category.php', [], 'wp-job-manager-companies' );
		break;

	default:
		// Nothing here.
		break;
}

get_footer();
