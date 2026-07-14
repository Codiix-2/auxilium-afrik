<?php
/**
 * Dashboard copyright.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/copyright.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.9.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$copyright = cariera_get_option( 'cariera_copyrights' );
?>

<div class="copyrights">
	<?php echo wp_kses_post( $copyright ); ?>
</div>
