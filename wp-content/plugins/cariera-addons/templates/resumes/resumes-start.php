<?php
/**
 * Content that is shown at the start of a resume list.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/resumes-start.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.7
 * @version     0.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<ul class="resumes resumes_main <?php echo esc_attr( $resumes_layout_wrapper ); ?>">
