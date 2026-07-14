<?php
/*
 * Template Name: Job Listing
 */
?>
<?php if ( apply_filters( 'search_and_filtering_specialty_use_template-listing-jobs', false ) || ! file_exists( TEMPLATEPATH . '/template-listing-jobs.php' ) ): ?>
<?php get_header(); ?>

<form action="" class="listing-filters-form">

	<?php get_template_part( 'part-hero-large' ); ?>

	<main class="main">
		<div class="container">
			<div class="row">
				<?php if ( class_exists( 'WP_Job_Manager' ) ) : ?>
					<?php
						$content_classes = '';
						$sidebar_classes = '';

						$sidebar = get_post_meta( get_queried_object_id(), 'specialty_job_listing_sidebar', true );
						switch ( $sidebar ) {
							case 'left':
								$content_classes = 'col-xl-9 push-xl-3 col-lg-8 push-lg-4 col-xs-12';
								$sidebar_classes = 'col-xl-3 pull-xl-9 col-lg-4 pull-lg-8 col-xs-12';
								break;
							case 'full':
								$content_classes = 'col-xs-12';
								$sidebar_classes = 'col-xl-3 col-lg-4 col-xs-12';
								break;
							case '':
							default:
								$content_classes = 'col-xl-9 col-lg-8 col-xs-12';
								$sidebar_classes = 'col-xl-3 col-lg-4 col-xs-12';
						}

						wp_enqueue_script( 'wp-job-manager-ajax-filters' );
					?>
					<div class="<?php echo esc_attr( $content_classes ); ?>">
						<?php
						$title = '<h3 class="section-title"><span class="jobs-found-no"></span></h3>';
						?>

						<?php if ( 'full' === $sidebar ) : ?>
							<div class="section-title-wrap">
								<?php echo $title; ?>

								<span class="section-title-compliment">
											<a href="#" class="sidebar-wrap-trigger">
												<i class="fa fa-navicon"></i> <?php esc_html_e( 'Filters', 'specialty', 'wp-job-manager-search-and-filtering' ); ?>
											</a>
										</span>
							</div>
						<?php else : ?>
							<?php echo $title; ?>
						<?php endif; ?>

						<?php echo do_shortcode( '[jobs]'); ?>
					</div>

					<div class="<?php echo esc_attr( $sidebar_classes ); ?>">
						<div class="sidebar-wrap <?php echo esc_attr( 'full' === $sidebar ? 'sidebar-fixed-default' : '' ); ?>">
							<div class="sidebar-wrap-header">
								<a href="#" class="sidebar-wrap-dismiss">&times;</a>
							</div>

							<div class="sidebar">
								<?php do_action( 'search_and_filtering_specialty_job_listings_sidebar' ); ?>
								<?php dynamic_sidebar( 'jobs' ); ?>
							</div>
						</div>
					</div>
				<?php endif; // class_exists 'WP_Job_Manager' ?>
			</div>
		</div>
	</main>

	<div class="mobile-triggers">
		<a href="#" class="mobile-trigger form-filter-trigger">
			<i class="fa fa-search"></i> <?php esc_html_e( 'Search', 'specialty', 'wp-job-manager-search-and-filtering' ); ?>
		</a>

		<a href="#" class="mobile-trigger sidebar-wrap-trigger">
			<i class="fa fa-navicon"></i> <?php esc_html_e( 'Filters', 'specialty', 'wp-job-manager-search-and-filtering' ); ?>
		</a>
	</div>

</form>

<?php get_footer(); ?>
<?php else: ?>
<?php require_once TEMPLATEPATH . '/template-listing-jobs.php'; ?>
<?php endif; ?>
