<?php
/**
 * Custom: Single Job Page - Job Listing Overview
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/single-job-listing-overview.php.
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

$career_level  = get_the_terms( $post->ID, 'job_listing_career_level' );
$experience    = get_the_terms( $post->ID, 'job_listing_experience' );
$qualification = get_the_terms( $post->ID, 'job_listing_qualification' );
$deadline      = cariera_wpjm_get_meta( '_application_deadline' );
$expired_date  = cariera_wpjm_get_meta( '_job_expires' );
$hours         = cariera_wpjm_get_meta( '_hours' );
$rate_min      = cariera_wpjm_get_meta( '_rate_min' );
$rate_max      = cariera_wpjm_get_meta( '_rate_max' );
$salary_min    = cariera_wpjm_get_meta( '_salary_min' );
$salary_max    = cariera_wpjm_get_meta( '_salary_max' );
$job_salary    = the_job_salary( '', '', false );
$expiring_days = apply_filters( 'job_manager_application_deadline_expiring_days', 2 );
$expiring      = ( floor( ( time() - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) ) >= $expiring_days );
$expired       = ( floor( ( time() - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) ) >= 0 );


do_action( 'single_job_listing_meta_before' );
?>

<h2 class="content-title"><?php esc_html_e( 'Job Overview', 'cariera' ); ?></h2>

<aside class="widget widget-job-overview">
	<?php do_action( 'single_job_listing_meta_start' ); ?>

	<div class="single-job-overview-detail single-job-overview-date-posted">
		<div class="icon">
			<i class="las la-calendar"></i>
		</div>

		<div class="content">
			<h3><?php esc_html_e( 'Date Posted', 'cariera' ); ?></h3>
			<span><?php the_date(); ?></span>
		</div>
	</div>

	<?php
	if ( ! empty( $expired_date ) ) {
		?>
		<div class="single-job-overview-detail single-job-overview-expiration-date">
			<div class="icon">
				<i class="las la-redo-alt"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Expiration Date', 'cariera' ); ?></h3>
				<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expired_date ) ) ); ?></span>
			</div>
		</div>
		<?php
	}

	if ( ! empty( $deadline ) ) {
		?>

		<div class="single-job-overview-detail application-deadline">
			<div class="icon">
				<i class="las la-times-circle"></i>
			</div>

			<div class="content">
				<h3><?php echo $expired ? esc_html__( 'Applications Closed', 'cariera' ) : esc_html__( 'Applications Close', 'cariera' ); ?></h3>
				<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $deadline ) ) ); ?></span>
			</div>
		</div>
	<?php } ?>

	<div class="single-job-overview-detail single-job-overview-location">
		<div class="icon">
			<i class="las la-map-marker"></i>
		</div>

		<div class="content">
			<h3><?php esc_html_e( 'Location', 'cariera' ); ?></h3>
			<span class="location"><?php the_job_location(); ?></span>
		</div>
	</div>

	<?php if ( ! empty( cariera_get_the_job_listing_category() ) && get_option( 'job_manager_enable_categories' ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-category">
			<div class="icon">
				<i class="lar la-star"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Category', 'cariera' ); ?></h3>
				<span><?php cariera_the_job_listing_category_output(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( taxonomy_exists( 'job_listing_career_level' ) && ! empty( $career_level ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-career-level">
			<div class="icon">
				<i class="las la-layer-group"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Career Level', 'cariera' ); ?></h3>
				<span><?php cariera_wpjm_terms_output( 'job_listing_career_level' ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( taxonomy_exists( 'job_listing_experience' ) && ! empty( $experience ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-experience">
			<div class="icon">
				<i class="las la-rocket"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Experience', 'cariera' ); ?></h3>
				<span><?php cariera_wpjm_terms_output( 'job_listing_experience' ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( taxonomy_exists( 'job_listing_qualification' ) && ! empty( $qualification ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-qualification">
			<div class="icon">
				<i class="las la-briefcase"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Qualification', 'cariera' ); ?></h3>
				<span><?php cariera_wpjm_terms_output( 'job_listing_qualification' ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( $hours ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-hours">
			<div class="icon">
				<i class="las la-clock"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Hours', 'cariera' ); ?></h3>
				<span><?php printf( esc_html__( '%s hr/week', 'cariera' ), $hours ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( $rate_min ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-rate">
			<div class="icon">
				<i class="las la-money-bill"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Rate', 'cariera' ); ?></h3>
				<span><?php cariera_job_rate(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( $salary_min ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-salary">
			<div class="icon">
				<i class="las la-money-bill"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Salary', 'cariera' ); ?></h3>
				<span><?php cariera_job_salary(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( ! empty( $job_salary ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-salary">
			<div class="icon">
				<i class="las la-money-bill"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Salary', 'cariera' ); ?></h3>
				<span><?php echo esc_html( $job_salary ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'WP_Job_Manager_Applications' ) ) { ?>
		<div class="single-job-overview-detail single-job-overview-applications">
			<div class="icon">
				<i class="lar la-address-card"></i>
			</div>

			<div class="content">
				<h3><?php esc_html_e( 'Job Applications', 'cariera' ); ?></h3>
				<span><?php cariera_job_applications(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php do_action( 'single_job_listing_meta_end' ); ?>
</aside>

<?php
do_action( 'single_job_listing_meta_after' );
