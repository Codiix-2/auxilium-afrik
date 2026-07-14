<?php
/**
 * Cariera Dashboard - Cards template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard/cards.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();

// Jobs.
$active_jobs  = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'publish' );
$pending_jobs = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'pending' );
$expired_jobs = cariera_count_user_posts_by_status( $current_user->ID, 'job_listing', 'expired' );

// Companies.
$active_companies  = cariera_count_user_posts_by_status( $current_user->ID, 'company', 'publish' );
$pending_companies = cariera_count_user_posts_by_status( $current_user->ID, 'company', 'pending' );
$expired_companies = cariera_count_user_posts_by_status( $current_user->ID, 'company', 'expired' );

// Resumes.
$active_resumes  = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'publish' );
$pending_resumes = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'pending' );
$expired_resumes = cariera_count_user_posts_by_status( $current_user->ID, 'resume', 'expired' );

if ( in_array( 'administrator', (array) $current_user->roles, true ) ) {
	$listing_name = esc_html__( 'Listings', 'cariera-core' );

	$active_listings  = $active_jobs + $active_companies + $active_resumes;
	$pending_listings = $pending_jobs + $pending_companies + $pending_resumes;
	$expired_listings = $expired_jobs + $expired_companies + $expired_resumes;
} elseif ( in_array( 'employer', (array) $current_user->roles, true ) ) {
	$listing_name = esc_html__( 'Listings', 'cariera-core' );

	$active_listings  = $active_jobs + $active_companies;
	$pending_listings = $pending_jobs + $pending_companies;
	$expired_listings = $expired_jobs + $expired_companies;
} elseif ( in_array( 'candidate', (array) $current_user->roles, true ) ) {
	$listing_name = esc_html__( 'Resumes', 'cariera-core' );

	$active_listings  = $active_resumes;
	$pending_listings = $pending_resumes;
	$expired_listings = $expired_resumes;
} else {
	return;
} ?>

<div class="dashboard-cards">
	<div class="dashboard-widget published-listings">
		<div class="card-statistics style-1">
			<div class="statistics-content">
				<h3 class="counter" data-from="0" data-to="<?php echo esc_attr( $active_listings ); ?>"><?php echo esc_html( '0' ); ?></h3>
				<span>
					<?php
					// translators: %s is the listing name.
					printf( esc_html__( 'Published %s', 'cariera-core' ), $listing_name );
					?>
				</span>
			</div>
			<div class="statistics-icon">
				<i class="las la-check-circle"></i>
			</div>
		</div>
	</div>

	<div class="dashboard-widget pending-listings">
		<div class="card-statistics style-2">
			<div class="statistics-content">
				<h3 class="counter" data-from="0" data-to="<?php echo esc_attr( $pending_listings ); ?>"><?php echo esc_html( '0' ); ?></h3>
				<span>
					<?php
					// translators: %s is the listing name.
					printf( esc_html__( 'Pending %s', 'cariera-core' ), $listing_name );
					?>
				</span>
			</div>
			<div class="statistics-icon">
				<i class="las la-pencil-alt"></i>
			</div>
		</div>
	</div>

	<div class="dashboard-widget expired-listings">
		<div class="card-statistics style-3">
			<div class="statistics-content">
				<h3 class="counter" data-from="0" data-to="<?php echo esc_attr( $expired_listings ); ?>"><?php echo esc_html( '0' ); ?></h3>
				<span>
					<?php
					// translators: %s is the listing name.
					printf( esc_html__( 'Expired %s', 'cariera-core' ), $listing_name );
					?>
				</span>
			</div>
			<div class="statistics-icon">
				<i class="las la-clock"></i>
			</div>
		</div>
	</div>

	<div class="dashboard-widget monthly-views-stats">
		<div class="card-statistics style-4">
			<div class="statistics-content">
				<h3 class="counter" data-from="0"><?php echo esc_html( '0' ); ?></h3>
				<span><?php esc_html_e( 'Monthly Views', 'cariera-core' ); ?></span>
			</div>
			<div class="statistics-icon">
				<i class="las la-eye"></i>
			</div>
		</div>
	</div>
</div>
