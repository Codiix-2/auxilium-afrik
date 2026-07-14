<?php
/**
 * Admin: Ajax response in the backend
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/admin/ajax-response.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.8.4
 * @version     1.8.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="cariera-ajax-msg">
	<div class="wrapper">
		<div class="message"></div>
		<div class="actions">
			<button class="dismiss-btn"><?php esc_html_e( 'Dismiss', 'cariera' ); ?></button>
		</div>
	</div>
</div>
