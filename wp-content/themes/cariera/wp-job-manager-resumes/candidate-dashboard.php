<?php
/**
 * Template for the candidate dashboard (`[candidate_dashboard]`) shortcode.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/candidate-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Resume Manager
 * @category    Template
 * @version     1.13.0
 */

/**
 * Modified by Cariera
 *
 * @since   1.9.2
 * @version 1.9.3
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

$classes = [ 'cariera-wpjm-dashboard-new' ];
?>

<div id="cariera-resume-dashboard" class="alignwide jm-dashboard jm-ui <?php echo esc_attr( join( ' ', $classes ) ); ?>">
	<div class="jm-dashboard__intro">
		<p><?php echo esc_html( _n( 'Your resume can be viewed, edited or removed below.', 'Your resumes can be viewed, edited or removed below.', resume_manager_count_user_resumes(), 'cariera' ) ); ?></p>
		<!-- <div class="jm-dashboard__filters"></div> -->

		<div class="jm-dashboard__actions">
			<?php if ( $submit_resume_form_page_id && ( resume_manager_count_user_resumes() < $submission_limit || ! $submission_limit ) ) { ?>
				<a href="<?php echo esc_url( get_permalink( $submit_resume_form_page_id ) ); ?>" class="btn btn-main btn-effect"><?php esc_html_e( 'Add Resume', 'cariera' ); ?></a>
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
						'message' => esc_html__( 'You do not have any active resume listings.', 'cariera' ),
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
					<?php esc_html_e( 'Actions', 'cariera' ); ?>
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

									case 'resume_image':
										$logo_img = get_the_candidate_photo( $resume, apply_filters( 'cariera_resume_logo_size', 'thumbnail' ) );

										if ( ! $logo_img ) {
											$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
										}

										// Output the candidate photo.
										printf(
											'<img class="candidate_photo" src="%s" alt="%s" />',
											esc_url( $logo_img ),
											esc_attr( $resume ? get_the_title( $resume ) : esc_attr__( 'Default Candidate Photo', 'cariera' ) )
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
											esc_html_e( 'Title not specified', 'cariera' );
										} else {
											echo the_candidate_title( '', '', true, $resume );
										}
										break;

									case 'date':
										echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $resume->post_date ) ) );
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
						$actions = [];

						switch ( $resume->post_status ) {
							case 'publish':
								if ( resume_manager_user_can_edit_published_submissions() ) {
									$actions['edit'] = [
										'label' => esc_html__( 'Edit', 'cariera' ),
										'nonce' => false,
									];
								}
								$actions['hide'] = [
									'label' => esc_html__( 'Hide', 'cariera' ),
									'nonce' => true,
								];
								break;
							case 'hidden':
								if ( resume_manager_user_can_edit_published_submissions() ) {
									$actions['edit'] = [
										'label' => esc_html__( 'Edit', 'cariera' ),
										'nonce' => false,
									];
								}
								$actions['publish'] = [
									'label' => esc_html__( 'Publish', 'cariera' ),
									'nonce' => true,
								];
								break;
							case 'pending_payment':
							case 'pending':
								if ( resume_manager_user_can_edit_pending_submissions() ) {
									$actions['edit'] = [
										'label' => esc_html__( 'Edit', 'cariera' ),
										'nonce' => false,
									];
								}
								break;
							case 'expired':
								if ( get_option( 'resume_manager_submit_resume_form_page_id' ) ) {
									$actions['relist'] = [
										'label' => esc_html__( 'Relist', 'cariera' ),
										'nonce' => true,
									];
								}
								break;
						}

						$actions['delete'] = [
							'label' => esc_html__( 'Delete', 'cariera' ),
							'nonce' => true,
						];

						$actions = apply_filters( 'resume_manager_my_resume_actions', $actions, $resume );

						$actions_html = '';
						foreach ( $actions as $action => $value ) {
							$action_url = add_query_arg(
								[
									'action'    => $action,
									'resume_id' => $resume->ID,
								]
							);
							if ( $value['nonce'] ) {
								$action_url = wp_nonce_url( $action_url, 'resume_manager_my_resume_actions' );
							}
							$actions_html .= '<a href="' . esc_url( $action_url ) . '" class="jm-dashboard-action jm-ui-button--link candidate-dashboard-action-' . esc_attr( $action ) . '">' . esc_html( $value['label'] ) . '</a>';
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
