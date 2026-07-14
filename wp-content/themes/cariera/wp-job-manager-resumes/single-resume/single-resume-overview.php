<?php
/**
 * Custom: Single Resume - Resume Overview
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/single-resume-overview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.6
 * @version     1.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$education  = get_the_terms( $post->ID, 'resume_education_level' );
$experience = get_the_terms( $post->ID, 'resume_experience' );
$rate       = cariera_wpjm_get_meta( '_rate', 'resume' );
$languages  = cariera_wpjm_get_meta( '_languages', 'resume' );

global $post; ?>

<h2 class="content-title"><?php esc_html_e( 'Candidate Overview', 'cariera' ); ?></h2>
<aside class="widget widget-candidate-overview">

	<?php do_action( 'single_resume_meta_start' ); ?>

	<div class="single-resume-overview-detail single-resume-overview-occupation">
		<div class="icon">
			<i class="las la-briefcase"></i>
		</div>

		<div class="content">
			<h3><?php esc_html_e( 'Occupation', 'cariera' ); ?></h3>
			<span><?php the_candidate_title(); ?></span>
		</div>
	</div>

	<?php if ( ! empty( cariera_get_the_resume_category() ) && get_option( 'resume_manager_enable_categories' ) ) { ?>
		<div class="single-resume-overview-detail single-resume-overview-category">
			<div class="icon">
				<i class="lar la-star"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Category', 'cariera' ); ?></h3>
				<span><?php cariera_the_resume_category_output(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( $rate ) ) { ?>
		<div class="single-resume-overview-detail single-resume-overview-rate">
			<div class="icon">
				<i class="las la-hourglass-end"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Rate', 'cariera' ); ?></h3>
				<span><?php cariera_resume_rate(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( taxonomy_exists( 'resume_education_level' ) && ! empty( $education ) ) { ?>
		<div class="single-resume-overview-detail single-resume-overview-education">
			<div class="icon">
				<i class="las la-graduation-cap"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Education Level', 'cariera' ); ?></h3>
				<span><?php cariera_wpjm_terms_output( 'resume_education_level', 'resume' ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( $languages ) ) { ?>
		<div class="single-resume-overview-detail single-resume-overview-languages">
			<div class="icon">
				<i class="las la-language"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Languages', 'cariera' ); ?></h3>
				<span><?php cariera_wpjm_meta_output( '_languages', 'resume' ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( taxonomy_exists( 'resume_experience' ) && ! empty( $experience ) ) { ?>
		<div class="single-resume-overview-detail single-resume-overview-experience">
			<div class="icon">
				<i class="las la-rocket"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Experience', 'cariera' ); ?></h3>
				<span><?php cariera_wpjm_terms_output( 'resume_experience', 'resume' ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php do_action( 'single_resume_meta_end' ); ?>
</aside>
