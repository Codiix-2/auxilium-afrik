<?php
/**
 * Lists job listing alerts for the `[job_alerts]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-alerts/my-alerts.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Alerts
 * @category    Template
 * @version     3.0.0
 *
 * @var WP_User $user
 * @var WP_Post[] $alerts
 */

use WP_Job_Manager_Alerts\Alert;
use WP_Job_Manager_Alerts\Post_Types;
use WP_Job_Manager_Alerts\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-alerts' );
?>

<div id="job-manager-alerts" class="jm-alerts__my-alerts">
	<div class="jm-alerts__my-alerts__email-info">
		<p><?php printf( __( 'Your job alerts are shown in the list below and will be emailed to %s.', 'cariera' ), $user->user_email ); ?></p>
	</div>
	<div class="jm-alerts__alert-list">
		<?php foreach ( $alerts as $alert_post ) : ?>
			<?php
			$alert = Alert::load( $alert_post->ID );

			$search_terms = $alert->get_search_terms();
			$disabled     = ! $alert->is_enabled();
			?>
			<div class="jm-alert alert-<?php echo esc_attr( $disabled ? 'disabled' : 'enabled' ); ?>">
				<div class="jm-alert__header">
					<h3 class="jm-alert__title"><?php echo esc_html( $alert->get_name() ); ?></h3>

					<?php if ( $disabled ) : ?>
						<div class="jm-alert__disabled"><?php esc_html_e( 'Disabled', 'cariera' ); ?></div>
					<?php else : ?>
						<div class="jm-alert__frequency alert_frequency">
							<?php
							$next_scheduled = $alert->get_next_scheduled();
							if ( ! empty( $next_scheduled ) ) {
								echo ' <span class="jm-alert__frequency__next">' . sprintf( __( '(Next: %s)', 'cariera' ), $next_scheduled ) . '</span>';
							}
							?>
						</div>
					<?php endif; ?>
				</div>

				<?php
				$term_rows = Post_Types::get_search_fields();

				foreach ( $term_rows as $term => $row ) :
					$terms = $search_terms[ $term ] ?? [];
					if ( empty( $terms ) ) {
						continue;
					}
					?>
					<div class="jm-alert__terms alert_<?php echo esc_attr( $term ); ?>">
						<span class="jm-alert__term-label"><?php echo esc_html( $row['label'] ); ?>:</span>
						<span class="jm-alert__term-list"><?php foreach ( $terms as $i => $term_value ) : ?>
								<span class="jm-alert__term"><?php echo esc_html( $term_value ); ?></span>
								<?php
								if ( array_key_last( $terms ) !== $i ) {
									echo '<span class="jm-alert__term-separator">, </span>';
								}
								?>
							<?php endforeach; ?>
						</span>
					</div>
					<?php
				endforeach;
				?>

				<div class="jm-alert__actions job-alert-actions">
					<ul>
						<?php
						$actions = Shortcodes::get_alert_actions( $alert );

						foreach ( $actions as $action => ['url' => $url, 'label' => $label] ) {
							echo '<li><a href="' . esc_url( $url ) . '" class="jm-alert__action job-alerts-action-' . esc_attr( $action ) . '">' . esc_html( $label ) . '</a></li>';
						}
						?>
					</ul>
				</div>
			</div>
		<?php endforeach; ?>
		<?php
		$query_args = [
			'action'         => 'add_alert',
			'updated'        => null,
			'alert_name'     => false,
			'alert_job_type' => false,
			'alert_location' => false,
			'alert_cats'     => false,
			'alert_keyword'  => false,
			'alert_regions'  => false,
			'alert_id'       => false,
			'user_id'        => false,
			'token'          => false,
		];
		?>
		<div class="jm-alerts__add-new">
			<a href="<?php echo esc_url( add_query_arg( $query_args ) ); ?>" class="btn btn-main">
				<?php esc_html_e( 'Add alert', 'cariera' ); ?>
			</a>
		</div>
	</div>
</div>
