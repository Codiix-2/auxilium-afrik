<?php
/**
 * Notice shown when a user has successfully applied for a job.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/applications/application-submitted.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.3
 * @version     0.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="job-manager-message success">
	<?php esc_html_e( 'Your job application has been submitted successfully!', 'cariera-addons' ); ?>
</p>
