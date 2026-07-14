<?php
/**
 * Notice shown when a user has already applied for a job.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/applications/applied-notice.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.3
 * @version     0.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="job-manager-applications-applied-notice">
	<?php esc_html_e( 'You have already applied for this job.', 'cariera-addons' ); ?>
</div>
