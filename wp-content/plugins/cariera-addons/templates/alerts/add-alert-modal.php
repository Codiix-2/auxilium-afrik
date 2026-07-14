<?php
/**
 * Form used when creating a new job listing alert.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/job-alerts/alert-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.2
 * @version     1.1.0
 *
 * @var string $page Alerts page URL.
 * @var string $alert_email The current user's e-mail address.
 */

use Cariera_Addons\Core\Job_Alerts\Alert_Form_Fields;
use Cariera_Addons\Core\Job_Alerts\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields = new Alert_Form_Fields();
?>

<form method="post" class="jm-form" action="<?php echo esc_attr( $page ); ?>" method="post">
	<?php wp_nonce_field( 'cariera_addons_alert_actions' ); ?>
	<div class="jm-form-large-field job-alert-keyword"><?php esc_html_e( 'Keyword', 'cariera-addons' ); ?></div>
	<div class="job-alert-search-terms" hidden></div>

	<?php if ( empty( $alert_email ) ) { ?>
		<p><?php esc_html_e( 'Get e-mails about new jobs matching this search.', 'cariera-addons' ); ?></p>
		<div class="jm-form-field">
			<input type="email" name="alert_email" required autocomplete="email" placeholder="<?php esc_attr_e( 'Email address', 'cariera-addons' ); ?>" aria-label="<?php esc_attr_e( 'Email address', 'cariera-addons' ); ?>" />
		</div>
	<?php } else { ?>
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s is the user's email */
					__( 'Send e-mails to <strong>%s</strong> about new jobs matching this search.', 'cariera-addons' ),
					esc_html( $alert_email )
				),
				[
					'strong' => [],
				]
			);
			?>
		</p>
	<?php } ?>

	<div class="job-alert-frequency">
		<label for="alert_frequency"><?php esc_html_e( 'Frequency: ', 'cariera-addons' ); ?></label>
		<?php echo $fields->alert_frequency( [ 'class' => 'jm-form-input--inline' ] ); ?>
	</div>

	<div class="jm-form-field jm-form-fine-print checkbox">
		<?php echo $fields->alert_permission(); ?>
	</div>

	<input type="hidden" name="submit-job-alert" value="1" />
	<input type="submit" hidden />

	<div class="jm-ui-actions">
		<a href="#" class="jm-ui-button" <?php echo ! empty( $alert_email ) ? 'autofocus' : ''; ?> onclick="this.closest('form').querySelector('input[type=submit]').click(); return false;"><span><?php esc_html_e( 'Subscribe', 'cariera-addons' ); ?></span></a>
		<a href="#" class="jm-ui-button--link" onclick="{close}"><?php esc_html_e( 'Cancel', 'cariera-addons' ); ?></a>
	</div>
</form>
