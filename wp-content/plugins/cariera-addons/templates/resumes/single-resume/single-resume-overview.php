<?php
/**
 * Single Resume - Resume Overview
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/single-resume/single-resume-overview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$education    = get_the_terms( $post->ID, 'resume_education_level' );
$experience   = get_the_terms( $post->ID, 'resume_experience' );
$expired_date = cariera_wpjm_get_meta( '_resume_expires', 'resume' );
$rate         = cariera_wpjm_get_meta( '_rate', 'resume' );
$languages    = cariera_wpjm_get_meta( '_languages', 'resume' );

global $post; ?>

<h2 class="content-title"><?php esc_html_e( 'Candidate Overview', 'cariera-addons' ); ?></h2>
<aside class="widget widget-candidate-overview">

	<?php do_action( 'single_resume_meta_start' ); ?>

	<div class="single-resume-overview-detail single-resume-overview-date-posted">
		<div class="icon">
			<i class="las la-calendar"></i>
		</div>

		<div class="content">
			<h3><?php esc_html_e( 'Date Posted', 'cariera-addons' ); ?></h3>
			<span><?php the_date(); ?></span>
		</div>
	</div>

	<?php if ( ! empty( $expired_date ) ) { ?>
		<div class="single-resume-overview-detail single-resume-overview-expiration-date">
			<div class="icon">
				<i class="las la-redo-alt"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Expiration Date', 'cariera' ); ?></h3>
				<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expired_date ) ) ); ?></span>
			</div>
		</div>
	<?php } ?>

	<div class="single-resume-overview-detail single-resume-overview-occupation">
		<div class="icon">
			<i class="las la-briefcase"></i>
		</div>

		<div class="content">
			<h3><?php esc_html_e( 'Occupation', 'cariera-addons' ); ?></h3>
			<span><?php the_candidate_title(); ?></span>
		</div>
	</div>

	<?php if ( ! empty( cariera_get_the_resume_category() ) && get_option( 'resume_manager_enable_categories' ) ) { ?>
		<div class="single-resume-overview-detail single-resume-overview-category">
			<div class="icon">
				<i class="lar la-star"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Category', 'cariera-addons' ); ?></h3>
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
				<h3><?php esc_html_e( 'Rate', 'cariera-addons' ); ?></h3>
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
				<h3><?php esc_html_e( 'Education Level', 'cariera-addons' ); ?></h3>
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
				<h3><?php esc_html_e( 'Languages', 'cariera-addons' ); ?></h3>
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
				<h3><?php esc_html_e( 'Experience', 'cariera-addons' ); ?></h3>
				<span><?php cariera_wpjm_terms_output( 'resume_experience', 'resume' ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php do_action( 'single_resume_meta_end' ); ?>
</aside>
