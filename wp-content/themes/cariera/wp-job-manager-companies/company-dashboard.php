<?php
/**
 * Custom: Company - Company Dashboard
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.4
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_Job_Manager\UI\Notice;
use WP_Job_Manager\UI\UI_Elements;

wp_enqueue_style( 'cariera-wpjm-dashboards' );
wp_enqueue_style( 'wp-job-manager-ui' );

$submission_limit    = get_option( 'cariera_company_submission_limit' );
$submit_company_page = get_option( 'cariera_submit_company_page' );
$singular            = cariera_get_company_manager_singular_label();
$plural              = cariera_get_company_manager_plural_label();
$total_companies     = cariera_count_user_companies();

$classes = [ 'cariera-wpjm-dashboard-new' ];
?>

<div id="cariera-company-dashboard" class="alignwide jm-dashboard jm-ui <?php echo esc_attr( join( ' ', $classes ) ); ?>">
	
	<div class="jm-dashboard__intro">
		<div class="jm-dashboard__filters">
			<form method="GET" action="" class="jm-form">
				<div style="display: flex; gap: 12px;">
					<input type="search" name="search" class="jm-ui-input--search-icon" placeholder="<?php esc_attr_e( 'Search', 'cariera' ); ?>" value="<?php echo esc_attr( $search_input ); ?>" aria-label="<?php esc_attr_e( 'Search', 'cariera' ); ?>" />
				</div>
			</form>
		</div>

		<div class="jm-dashboard__actions">
			<?php if ( $submit_company_page && ( $total_companies < $submission_limit || ! $submission_limit ) ) { ?>
				<a href="<?php echo esc_url( get_permalink( $submit_company_page ) ); ?>" class="btn btn-main btn-effect"><?php esc_html_e( 'Add Company', 'cariera' ); ?></a>
			<?php } ?>
		</div>
	</div>

	<?php $table_class = count( $company_dashboard_columns ) > 4 ? 'jm-dashboard-table--large' : ''; ?>
	<div class="job-manager-listings job-manager-companies jm-dashboard-table <?php echo esc_attr( $table_class ); ?>">
		<?php if ( ! $companies ) { ?>
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
		<?php } else { ?>
			<div class="jm-dashboard-header">
				<?php foreach ( $company_dashboard_columns as $key => $column ) { ?>
					<div class="jm-dashboard-listing-column jm-dashboard-listing-column-label <?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $column ); ?>
					</div>
				<?php } ?>
				<div class="jm-dashboard-listing-column jm-dashboard-listing-column-label actions">
					<?php esc_html_e( 'Actions', 'cariera' ); ?>
				</div>
			</div>
			<div class="jm-dashboard-rows">
				<?php foreach ( $companies as $company ) { ?>
					<div class="jm-dashboard-listing jm-dashboard-company">
						<?php foreach ( $company_dashboard_columns as $key => $column ) { ?>
							<div class="jm-dashboard-listing-column <?php echo esc_attr( $key ); ?>" aria-label="<?php echo esc_attr( $column ); ?>">
								<?php do_action( 'cariera_company_dashboard_column_' . $key, $company ); ?>

								<?php
								switch ( $key ) {
									case 'company-logo':
										$logo_img = '';

										if ( has_post_thumbnail( $company ) ) {
											$logo_img = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
										} else {
											$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
										}

										printf(
											'<img class="company_logo" src="%s" alt="%s" />',
											esc_url( $logo_img ),
											esc_attr( $company->post_title ?: __( 'Default Company Logo', 'cariera' ) )
										);
										break;

									case 'listing-title':
										$company_title = get_the_title( $company );
										$permalink     = ( 'publish' === $company->post_status ) ? get_permalink( $company->ID ) : '#';
										?>
										<a href="<?php echo esc_url( $permalink ); ?>" class="company-name"><?php echo esc_html( $company_title ); ?></a>
										<?php
										\WP_Job_Manager\Job_Dashboard_Shortcode::the_status( $company );
										break;

									case 'company-jobs':
										$job_count = cariera_get_the_company_job_listing_active_count( $company->ID );
										printf(
											/* translators: 1: job count */
											esc_html( _n( '%s Job', '%s Jobs', $job_count, 'cariera' ) ),
											esc_html( $job_count )
										);
										break;

									case 'date':
										echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $company->post_date ) ) );
										$expiration = get_post_meta( $company->ID, '_company_expires', true );
										if ( 'publish' === $company->post_status && ! empty( $expiration ) ) {
											// translators: Placeholder is the expiration date of the company listing.
											echo '<div class="company-expires"><small>' . UI_Elements::rel_time( $expiration, __( 'Expires in %s', 'cariera' ) ) . '</small></div>';
										}
										break;

									default:
										do_action( 'cariera_company_dashboard_column_' . $key, $company );
										break;
								}
								?>
							</div>
						<?php } ?>

						<div class="jm-dashboard-listing-column actions company-dashboard-listing-actions">
							<?php do_action( 'cariera_company_dashboard_column_actions', $company, $company_actions[ $company->ID ] ?? [] ); ?>
						</div>

						<?php
						$actions_html = '';
						if ( ! empty( $company_actions[ $company->ID ] ) ) {
							foreach ( $company_actions[ $company->ID ] as $action => $value ) {
								$action_url = add_query_arg(
									[
										'action'     => $action,
										'company_id' => $company->ID,
									]
								);
								if ( $value['nonce'] ) {
									$action_url = wp_nonce_url( $action_url, $value['nonce'] );
								}
								$actions_html .= '<a href="' . esc_url( $action_url ) . '" class="jm-dashboard-action jm-ui-button--link company-dashboard-action-' . esc_attr( $action ) . '">' . esc_html( $value['label'] ) . '</a>';
							}
						}

						echo UI_Elements::actions_menu( $actions_html );
						?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>

	<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $max_num_pages ] ); ?>
</div>
