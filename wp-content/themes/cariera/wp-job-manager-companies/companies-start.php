<?php
/**
 * Content that is shown at the start of a companies list.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/companies-start.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       1.9.3
 * @version     1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<ul class="company_listings company_listings_main <?php echo esc_attr( $companies_layout_wrapper ); ?>">
