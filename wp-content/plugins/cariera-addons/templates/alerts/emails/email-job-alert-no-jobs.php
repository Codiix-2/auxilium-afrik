<?php
/**
 * The content of the {jobs} tag when no jobs are found.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/alerts/emails/email-job-alert-no-jobs.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.2
 * @version     0.9.2
 */

?>
<div style="margin: 24px 0; padding: 24px; border: 1px solid #E6E6E6; line-height: 1.8; ">
	<?php esc_html_e( 'No jobs were found matching your search.', 'cariera-addons' ); ?>
</div>
