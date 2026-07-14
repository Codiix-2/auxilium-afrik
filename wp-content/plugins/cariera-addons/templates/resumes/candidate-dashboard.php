<?php
/**
 * Template for the candidate dashboard (`[candidate_dashboard]`) shortcode.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/candidate-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Job_Manager\UI\Notice;
use WP_Job_Manager\UI\UI_Elements;

wp_enqueue_style( 'cariera-wpjm-dashboards' );
wp_enqueue_style( 'wp-job-manager-ui' );

$submission_limit           = get_option( 'resume_manager_submission_limit' );
$submit_resume_form_page_id = get_option( 'resume_manager_submit_resume_form_page_id' );
$plural_label               = cariera_addons_resume_cpt_plural_label();

$classes = [ 'cariera-wpjm-dashboard-new' ];
?>

<div id="cariera-resume-dashboard" class="alignwide jm-dashboard jm-ui <?php echo esc_attr( join( ' ', $classes ) ); ?>">
	<div class="jm-dashboard__intro">
		<div class="jm-dashboard__filters">
			<form method="GET" action="" class="jm-form">
				<div style="display: flex; gap: 12px;">
					<input type="search" name="search" class="jm-ui-input--search-icon" placeholder="<?php esc_attr_e( 'Search', 'cariera-addons' ); ?>" value="<?php echo esc_attr( $search_input ); ?>" aria-label="<?php esc_attr_e( 'Search', 'cariera-addons' ); ?>" />
				</div>
			</form>
		</div>

		<div class="jm-dashboard__actions">
			<?php if ( $submit_resume_form_page_id && ( resume_manager_count_user_resumes() < $submission_limit || ! $submission_limit ) ) { ?>
				<a href="<?php echo esc_url( get_permalink( $submit_resume_form_page_id ) ); ?>" class="btn btn-main btn-effect"><?php printf( esc_html__( 'Add %s', 'cariera-addons' ), cariera_addons_resume_cpt_singular_label() ); ?></a>
			<?php } ?>
		</div>
	</div>

	<?php $table_class = count( $candidate_dashboard_columns ) > 4 ? 'jm-dashboard-table--large' : ''; ?>
	<div class="job-manager-listings job-manager-resumes jm-dashboard-table <?php echo esc_attr( $table_class ); ?>">
		<?php if ( ! $resumes ) { ?>
			<div class="jm-dashboard-empty">
				<?php
				echo Notice::dialog(
					[
						'message' => $search_input
							// translators: Placeholder is the search term.
							? sprintf( __( 'No results found for "%s".', 'cariera-addons' ), $search_input )
							: sprintf(
								/* translators: %s: plural resume label */
								esc_html__( 'You do not have any active %s.', 'cariera-addons' ),
								esc_html( $plural_label )
							),
					]
				);
				?>
			</div>
		<?php } else { ?>
			<div class="jm-dashboard-header">
				<?php foreach ( $candidate_dashboard_columns as $key => $column ) { ?>
					<div class="jm-dashboard-listing-column jm-dashboard-listing-column-label <?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $column ); ?>
					</div>
				<?php } ?>
				<div class="jm-dashboard-listing-column jm-dashboard-listing-column-label actions">
					<?php esc_html_e( 'Actions', 'cariera-addons' ); ?>
				</div>
			</div>
			<div class="jm-dashboard-rows">
				<?php foreach ( $resumes as $resume ) { ?>
					<div class="jm-dashboard-listing jm-dashboard-resume">
						<?php foreach ( $candidate_dashboard_columns as $key => $column ) { ?>
							<div class="jm-dashboard-listing-column <?php echo esc_attr( $key ); ?>" aria-label="<?php echo esc_attr( $column ); ?>">
								<?php do_action( 'resume_manager_candidate_dashboard_column_' . $key, $resume ); ?>

								<?php
								switch ( $key ) {

									case 'resume-image':
										$logo_img = get_the_candidate_photo( $resume, apply_filters( 'cariera_resume_logo_size', 'thumbnail' ) );

										if ( ! $logo_img ) {
											$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
										}

										// Output the candidate photo.
										printf(
											'<img class="candidate_photo" src="%s" alt="%s" />',
											esc_url( $logo_img ),
											esc_attr( $resume ? get_the_title( $resume ) : esc_attr__( 'Default Candidate Photo', 'cariera-addons' ) )
										);
										break;

									case 'resume-title':
										echo get_the_candidate_photo();
										$resume_title = get_the_title( $resume );
										$permalink    = ( 'publish' === $resume->post_status ) ? get_permalink( $resume->ID ) : '#';
										?>
										<a href="<?php echo esc_url( $permalink ); ?>" class="resume-title"><?php echo esc_html( $resume_title ); ?></a>
										<?php
										\WP_Job_Manager\Job_Dashboard_Shortcode::the_status( $resume );
										break;

									case 'candidate-title':
										if ( empty( get_the_candidate_title( $resume ) ) ) {
											esc_html_e( 'Title not specified', 'cariera-addons' );
										} else {
											echo the_candidate_title( '', '', true, $resume );
										}
										break;

									case 'date':
										echo $resume->_resume_expires ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $resume->_resume_expires ) ) ) : esc_html__( 'No expiration date', 'cariera-addons' );
										break;

									case 'expiration-date':
										break;

									default:
										do_action( 'resume_manager_candidate_dashboard_column_' . $key, $resume );
										break;
								}
								?>
							</div>
						<?php } ?>

						<div class="jm-dashboard-listing-column actions resume-dashboard-listing-actions">
							<?php do_action( 'resume_manager_candidate_dashboard_column_actions', $resume, $resume_actions[ $resume->ID ] ?? [] ); ?>
						</div>

						<?php
						$actions_html = '';
						if ( ! empty( $resume_actions[ $resume->ID ] ) ) {
							foreach ( $resume_actions[ $resume->ID ] as $action ) {
								$actions_html .= '<a href="' . esc_url( $action['url'] ) . '" class=" jm-dashboard-action jm-ui-button--link candidate-dashboard-action-' . esc_attr( $action['name'] ) . '">' . esc_html( $action['label'] ) . '</a>' . "\n";
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
