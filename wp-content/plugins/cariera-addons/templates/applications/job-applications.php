<?php
/**
 * Lists the job applications for a particular job listing.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/applications/job-applications.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.3
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-applications-dashboard' );
?>

<div id="job-manager-job-applications">
	<div class="applications-info">
		<a href="<?php echo esc_url( add_query_arg( 'download-csv', true ) ); ?>" class="job-applications-download-csv">
		<?php esc_html_e( 'Download CSV', 'cariera-addons' ); ?>
		</a>
		<p><?php printf( esc_html__( 'The job applications for "%s" are listed below.', 'cariera-addons' ), '<a href="' . esc_url( get_permalink( $job_id ) ) . '"><strong>' . esc_html( get_the_title( $job_id ) ) . '</strong></a>' ); ?></p>
	</div>

	<div class="job-applications">
		<!-- Search Form -->
		<form class="filter-job-applications" method="GET">
			<div class="search-field status">
				<select name="application_status" class="cariera-select2">
					<option value=""><?php esc_html_e( 'Filter by status...', 'cariera-addons' ); ?></option>
					<?php foreach ( get_job_application_statuses() as $name => $label ) { ?>
						<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $application_status, $name ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="search-field orderby">
				<select name="application_orderby" class="cariera-select2">
					<option value=""><?php esc_html_e( 'Newest first', 'cariera-addons' ); ?></option>
					<option value="name" <?php selected( $application_orderby, 'name' ); ?>><?php esc_html_e( 'Sort by name', 'cariera-addons' ); ?></option>
					<option value="rating" <?php selected( $application_orderby, 'rating' ); ?>><?php esc_html_e( 'Sort by rating', 'cariera-addons' ); ?></option>
				</select>
				<input type="hidden" name="action" value="show_applications" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( absint( $_GET['job_id'] ) ); ?>" />
				<?php if ( ! empty( $_GET['page_id'] ) ) { ?>
					<input type="hidden" name="page_id" value="<?php echo esc_attr( absint( $_GET['page_id'] ) ); ?>" />
				<?php } ?>
			</div>
		</form>

		<?php if ( ! empty( $applications ) ) { ?>
			<!-- Applications -->
			<ul class="job-applications">
				<?php foreach ( $applications as $application ) { ?>
					<li class="job-application" id="application-<?php echo esc_attr( $application->ID ); ?>">
						<header>
							<?php job_application_header( $application ); ?>
						</header>

						<?php do_action( 'job_application_content_start' ); ?>

						<section class="job-application-content">
							<?php job_application_meta( $application ); ?>
						</section>
						<section class="job-application-edit">
							<?php job_application_edit( $application ); ?>
						</section>
						<section class="job-application-notes">
							<?php job_application_notes( $application ); ?>
						</section>

						<?php do_action( 'job_application_content_end' ); ?>

						<footer>
							<?php job_application_footer( $application ); ?>
						</footer>
					</li>
				<?php } ?>
			</ul>
			<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $max_num_pages ] ); ?>
		<?php } else { ?>
			<div class="job-manager-message error">
				<?php esc_html_e( 'No applications are found.', 'cariera-addons' ); ?>
			</div>
		<?php } ?>
	</div>
</div>
