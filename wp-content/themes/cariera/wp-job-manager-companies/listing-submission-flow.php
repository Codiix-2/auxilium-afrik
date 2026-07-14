<?php
/**
 * Custom: Listing submission flow
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/listing-submission-flow.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.8
 * @version     1.8.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$current_page_id         = get_queried_object_id();
$company_submission_page = apply_filters( 'cariera_dashboard_company_submit_page', get_option( 'cariera_submit_company_page', false ) );
$action                  = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

if ( empty( $company_submission_page ) && absint( $company_submission_page ) !== $current_page_id ) {
	return;
}
?>

<div class="submission-flow company-submission-flow">
	<ul>
		<li class="listing-details"><?php esc_html_e( 'Company Details', 'cariera' ); ?></li>

		<?php if ( 'edit' !== $action ) { ?>
			<li class="preview-listing"><?php esc_html_e( 'Preview Company', 'cariera' ); ?></li>
		<?php } ?>
	</ul>
</div>
