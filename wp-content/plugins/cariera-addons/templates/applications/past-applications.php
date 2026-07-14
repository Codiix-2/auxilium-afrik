<?php
/**
 * Past Applications shortcode content [past_applications]
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/applications/past-applications.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.3
 * @version     1.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-addons-past-applications' );
wp_enqueue_script( 'cariera-addons-past-applications' );
?>

<div class="cariera-addons-past-applications">
	<?php
	foreach ( $applications as $application ) {
		global $wp_post_statuses;

		$application_id = $application->ID;
		$job_id         = wp_get_post_parent_id( $application_id );
		$job            = get_post( $job_id );
		$job_title      = get_post_meta( $application_id, '_job_applied_for', true );

		// Status.
		$status       = get_post_status( $application_id );
		$status_label = isset( $wp_post_statuses[ $status ] ) ? $wp_post_statuses[ $status ]->label : ucfirst( $status );
		$status_class = 'status-' . sanitize_title( $status_label );
		$date         = get_the_date( get_option( 'date_format' ), $application_id );

		// Application Content.
		$application_content = wpautop( $application->post_content );

		// Logo.
		if ( get_option( 'cariera_company_manager_integration', false ) ) {
			$company = cariera_get_the_company( $job_id );
			$logo    = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
		} else {
			$logo = get_the_company_logo( $job_id, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
		}
		?>

		<div class="past-application">
			<div class="header">
				<div class="company-logo">
					<?php
					// Company Logo.
					if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
						echo '<img class="company_logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_title( $company ) ) . '" />';
					} else {
						cariera_the_company_logo();
					}
					?>
				</div>

				<?php if ( ! empty( $job ) && 'publish' === $job->post_status ) { ?>
					<a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>" class="job-title" target="_blank"><?php echo esc_html( $job_title ); ?></a>
				<?php } else { ?>
					<span class="job-title"><?php echo esc_html( $job_title ); ?></span>
				<?php } ?>
			</div>

			<div class="meta">
				<span class="date"><?php echo esc_html( $date ); ?></span>
				<span class="status-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
			</div>

			<div class="actions">
				<a href="#application-popup" class="view-application popup-with-zoom-anim" data-title="<?php echo esc_attr( $job_title ); ?>" data-id="<?php echo esc_attr( $application_id ); ?>">
					<?php esc_html_e( 'View Application', 'cariera-addons' ); ?>
				</a>
				<!-- <a href="#" class="delete"><?php // esc_html_e( 'Delete', 'cariera-addons' ); ?></a> -->
			</div>

			<div id="application-content-<?php echo esc_attr( $application_id ); ?>" class="application-content">
				<?php echo wp_kses_post( $application_content ); ?>
			</div>
		</div>
	<?php } ?>
</div>

<div id="application-popup" class="small-dialog zoom-anim-dialog mfp-hide">
	<div class="small-dialog-headline">
		<h3 class="title"></h3>
	</div>
	<div class="small-dialog-content">
		<h6><?php esc_html_e( 'Application Content:', 'cariera-addons' ); ?></h6>
		<div class="application-content"></div>
	</div>
</div>

<?php
get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $max_num_pages ] );

wp_reset_postdata();
