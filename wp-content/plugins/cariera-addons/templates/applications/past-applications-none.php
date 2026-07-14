<?php
/**
 * Notice shown on `[past_applications]` shortcode when a user hasn't applied to any jobs.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/applications/past-applications-none.php.
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

<div class="job-manager-message"><?php esc_html_e( 'You haven\'t made any applications yet!', 'cariera-addons' ); ?></div>
