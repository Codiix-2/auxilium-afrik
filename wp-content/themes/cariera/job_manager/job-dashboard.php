<?php
/**
 * Job dashboard shortcode content.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     2.3.0
 *
 * @since 2.3.0 Switched to a responsive layout. job_manager_job_dashboard_column_{$key} action is called for all columns.
 * @since 1.34.4 Available job actions are passed in an array (`$job_actions`, keyed by job ID) and not generated in the template.
 * @since 1.35.0 Switched to new date functions.
 *
 * @var array     $job_dashboard_columns Array of the columns to show on the job dashboard page.
 * @var int       $max_num_pages Maximum number of pages
 * @var WP_Post[] $jobs Array of job post results.
 * @var array     $job_actions Array of actions available for each job.
 * @var string    $search_input Search input.
 */

use WP_Job_Manager\Job_Overlay;
use WP_Job_Manager\UI\Notice;
use WP_Job_Manager\UI\UI_Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_style( 'cariera-wpjm-dashboards' );

$submit_job_form_page_id = get_option( 'job_manager_submit_job_form_page_id' );

// Custom.
$statistics = get_option( 'job_manager_stats_enable' );
$classes    = [ 'cariera-wpjm-dashboard-new' ];

if ( ! empty( $statistics ) ) {
	$classes[] = 'statistics-enabled';
}

$view_stats = $statistics ? '<div class="stats-info">' . esc_html__( 'View Job Statistics', 'cariera' ) . '</div>' : '';
?>

<div id="job-manager-job-dashboard" class="alignwide jm-dashboard jm-ui <?php echo esc_attr( join( ' ', $classes ) ); ?>">
	<div class="jm-dashboard__intro">
		<div class="jm-dashboard__filters">
			<form method="GET" action="" class="jm-form">
				<div style="display: flex; gap: 12px;">
					<input type="search" name="search" class="jm-ui-input--search-icon"
						placeholder="<?php esc_attr_e( 'Search', 'cariera' ); ?>"
						value="<?php echo esc_attr( $search_input ); ?>"
						aria-label="<?php esc_attr_e( 'Search', 'cariera' ); ?>"
						autocomplete="off" />
				</div>
			</form>
		</div>
		<div class="jm-dashboard__actions">
			<?php if ( job_manager_user_can_submit_job_listing() ) : ?>
				<a class="jm-ui-button"
					href="<?php echo esc_url( get_permalink( $submit_job_form_page_id ) ); ?>"><span><?php esc_html_e( 'Add Job', 'cariera' ); ?></span></a>
			<?php endif; ?>
		</div>
	</div>

	<?php $table_class = count( $job_dashboard_columns ) > 4 ? 'jm-dashboard-table--large' : ''; ?>
	<div class="job-manager-listings job-manager-jobs jm-dashboard-table <?php echo esc_attr( $table_class ); ?>">
		<?php if ( ! $jobs ) : ?>
			<div class="jm-dashboard-empty">
				<?php
				echo Notice::dialog(
					[
						'message' => $search_input
							// translators: Placeholder is the search term.
							? sprintf( __( 'No results found for "%s".', 'cariera' ), $search_input )
							: esc_html__( 'You do not have any active listings.', 'cariera' ),
					]
				);
				?>
			</div>
		<?php else : ?>
			<div class="jm-dashboard-header">
				<?php foreach ( $job_dashboard_columns as $key => $column ) : ?>
					<div class="jm-dashboard-listing-column jm-dashboard-listing-column-label <?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $column ); ?>
					</div>
				<?php endforeach; ?>
				<div class="jm-dashboard-listing-column jm-dashboard-listing-column-label actions">
					<?php esc_html_e( 'Actions', 'cariera' ); ?>
				</div>
			</div>
			<div class="jm-dashboard-rows">
				<?php foreach ( $jobs as $job ) : ?>
					<div class="jm-dashboard-listing jm-dashboard-job">
						<?php echo wp_kses_post( $view_stats ); ?>

						<?php foreach ( $job_dashboard_columns as $key => $column ) : ?>
							<div class="jm-dashboard-listing-column <?php echo esc_attr( $key ); ?>"
								aria-label="<?php echo esc_attr( $column ); ?>">
								<div class="jm-dashboard-listing-column-label"><?php echo esc_html( $column ); ?></div>
								<?php do_action( 'job_manager_job_dashboard_column_' . $key, $job ); ?>
							</div>
						<?php endforeach; ?>
						<div class="jm-dashboard-listing-column actions job-dashboard-listing-actions">
							<?php do_action( 'job_manager_job_dashboard_column_actions', $job, $job_actions[ $job->ID ] ?? [] ); ?>
						</div>
						<?php
						$actions_html = '';
						if ( ! empty( $job_actions[ $job->ID ] ) ) {
							foreach ( $job_actions[ $job->ID ] as $action ) {
								$actions_html .= '<a href="' . esc_url( $action['url'] ) . '" class=" jm-dashboard-action jm-ui-button--link job-dashboard-action-' . esc_attr( $action['name'] ) . '">' . esc_html( $action['label'] ) . '</a>' . "\n";
							}
						}

						echo UI_Elements::actions_menu( $actions_html );
						?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $max_num_pages ] ); ?>
</div>
