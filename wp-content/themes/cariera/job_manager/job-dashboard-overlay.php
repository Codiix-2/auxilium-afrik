<?php
/**
 * Job dashboard overlay.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     2.3.0
 *
 * @var WP_Post $job Array of job post results.
 */

use WP_Job_Manager\Job_Dashboard_Shortcode;
use WP_Job_Manager\UI\UI_Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$submit_job_form_page_id = get_option( 'job_manager_submit_job_form_page_id' );

// Cariera Company Manager handling.
$company = '';
$logo    = get_the_company_logo();

// If Cariera Company manager exists and company integration check.
if ( \Cariera\cariera_core_is_activated() && get_option( 'cariera_company_manager_integration', false ) && function_exists( 'cariera_get_the_company' ) ) {
	$company = get_post( cariera_get_the_company( $job->ID ) );
}

// Logo if there is an active company.
if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
	$logo = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
}
?>

<div class="jm-job-overlay jm-dashboard">
	<div class="jm-job-overlay-header">
		<div class="">
			<div class="job_title" role="heading"><?php echo esc_html( get_the_title( $job ) ?? $job->ID ); ?></div>
			<?php Job_Dashboard_Shortcode::the_status( $job ); ?>
		</div>
		<div class="actions">
			<?php
			echo UI_Elements::button(
				[
					'url'   => get_permalink( $job->ID ),
					'label' => esc_html__( 'View', 'cariera' ),
				],
				'jm-ui-button--link'
			);
			?>
		</div>
	</div>
	<div class="jm-job-overlay-content">
		<div class="jm-job-overlay-details-box">

			<div class="jm-ui-row" style="justify-content: space-between; align-items: flex-start">
				<div class="jm-ui-col">
					<?php Job_Dashboard_Shortcode::the_location( $job ); ?>
					<div class="jm-ui-row">
						<?php
						// Company Logo.
						if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
							echo '<img class="company_logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_title( $company ) ) . '" />';
						} else {
							// cariera_the_company_logo();
							the_company_logo( 'thumbnail', '', $job );
						}
						?>
						<?php echo esc_html( get_the_company_name( $job ) ); ?>
					</div>
				</div>
				<div class="jm-ui-col">
					<?php do_action( 'job_manager_job_dashboard_column_date', $job ); ?>
				</div>
			</div>

		</div>
		<?php do_action( 'job_manager_job_overlay_content', $job ); ?>

	</div>
	<div class="jm-job-overlay-footer"><?php do_action( 'job_manager_job_overlay_footer', $job ); ?></div>
</div>
