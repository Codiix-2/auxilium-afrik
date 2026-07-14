<?php
/**
 * Job listing in the loop.
 *
 * Custom: Job listing content - list version 1
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @since       1.0.0
 * @version     1.34.0
 *
 * @cariera-since   1.2.5
 * @cariera-version 1.8.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

wp_enqueue_style( 'cariera-job-listings' );

$job_class = 'job-list single_job_listing_1';
$job_id    = get_the_ID();
$company   = '';

if ( $job_id ) {
	$post_title = get_post_meta( $job_id, '_company_name', true );
	if ( ! empty( $post_title ) ) {
		$company = \Cariera\get_page_by_title( $post_title, 'company' );
	}
}

$logo = get_the_company_logo();

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
} ?>

<li <?php job_listing_class( esc_attr( $job_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>">
	<a href="<?php the_job_permalink(); ?>">
		<div class="job-content-wrapper">

			<!-- Job Company -->
			<div class="job-content-company">
				<div class="job-company">
					<?php
					// Company Logo.
					if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
						$logo = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
						echo '<img class="company_logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_title( $company ) ) . '" />';
					} else {
						cariera_the_company_logo();
					}
					?>
				</div>
			</div>

			<!-- Job Title & Info -->
			<div class="job-content-main">
				<div class="job-title">
					<h2 class="title">
						<?php the_title(); ?>
						<?php do_action( 'cariera_job_listing_title_after' ); ?>
					</h2>
				</div>

				<div class="job-info">
					<?php do_action( 'job_listing_info_start' ); ?>

					<span class="company">
						<?php the_company_name( '<i class="lar la-building"></i>' ); ?>
					</span>

					<span class="location">
						<i class="las la-map-marker"></i>
						<?php the_job_location( false ); ?>
					</span>

					<?php
					$rate_min = get_post_meta( $post->ID, '_rate_min', true );
					if ( $rate_min ) {
						$rate_max = get_post_meta( $post->ID, '_rate_max', true );
						?>

						<span class="rate">
							<i class="las la-money-bill"></i> 
							<?php cariera_job_rate(); ?>
						</span>
					<?php } ?>

					<?php
					$salary_min = get_post_meta( $post->ID, '_salary_min', true );
					if ( $salary_min ) {
						$salary_max = get_post_meta( $post->ID, '_salary_max', true );
						?>
						<span class="salary">
							<i class="las la-money-bill"></i>
							<?php cariera_job_salary(); ?>
						</span>
					<?php } ?>

					<?php do_action( 'job_listing_info_end' ); ?>
				</div>
			</div>

			<!-- Job Category -->
			<div class="job-content-meta">
				<ul class="meta">
					<?php do_action( 'job_listing_meta_start' ); ?>

					<?php
					if ( get_option( 'job_manager_enable_types' ) ) {
						$types = wpjm_get_the_job_types();
						if ( ! empty( $types ) ) {
							echo '<li class="job-type-wrapper">';

							foreach ( $types as $type ) {
								?>
								<span class="job-type term-<?php echo esc_attr( $type->term_id ); ?> <?php echo esc_attr( sanitize_title( $type->slug ) ); ?>"><?php echo esc_html( $type->name ); ?></span>
								<?php
							}

							if ( cariera_newly_posted() ) {
								echo '<span class="job-item-badge new-job">' . esc_html__( 'New', 'cariera' ) . '</span>';
							}
							echo '</li>';
						}
					}
					?>

					<li class="date"><?php the_job_publish_date(); ?></li>

					<?php do_action( 'job_listing_meta_end' ); ?>
				</ul>
			</div>
		</div>
	</a>
</li>
