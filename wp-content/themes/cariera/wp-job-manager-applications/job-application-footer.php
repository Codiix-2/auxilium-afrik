<?php
/**
 * Footer shown below a job application.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-applications/job-application-footer.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-applications
 * @category    Template
 * @version     2.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_post_statuses;

$rating = get_job_application_rating( $application->ID ); ?>

<div class="rating <?php echo cariera_get_rating_class( $rating ); ?>">
	<div class="star-rating">
		<i class="las la-star"></i>
		<i class="las la-star"></i>
		<i class="las la-star"></i>
		<i class="las la-star"></i>
		<i class="las la-star"></i>
	</div>
	<div class="star-bg">
		<i class="lar la-star"></i>
		<i class="lar la-star"></i>
		<i class="lar la-star"></i>
		<i class="lar la-star"></i>
		<i class="lar la-star"></i>
	</div>
</div>

<ul class="meta">
	<li><i class="lar la-file-alt"></i><?php echo esc_html( $wp_post_statuses[ $application->post_status ]->label ); ?></li>
	<li><i class="lar la-calendar-alt"></i> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $application->post_date ) ) ); ?></li>
</ul>
