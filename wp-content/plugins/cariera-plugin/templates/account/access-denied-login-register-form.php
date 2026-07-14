<?php
/**
 * Access denied for Login & Register form when user is not logged in.
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/access-denied-login-register-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="job-manager-message"><?php esc_html_e( 'You can not view this element because you are currently loggedin. ', 'cariera-core' ); ?></div>