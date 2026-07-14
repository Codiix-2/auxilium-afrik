<?php
/**
 * Custom: Company Access Denied Submit Companies
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/access-denied-submit-companies.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.8.3
 * @version     1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>

<p class="job-manager-message error"><?php esc_html_e( 'Sorry, you do not have permission to submit a company.', 'cariera' ); ?></p>
