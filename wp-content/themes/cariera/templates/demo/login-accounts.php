<?php
/**
 * Demo login credentials
 *
 * This template can be overridden by copying it to cariera-child/templates/demo/login-accounts.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.1
 * @version     1.8.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$employer_username  = 'employer';
$candidate_username = 'candidate';
$password           = 'demo';
?>

<div class="job-manager-message account-info">
	<p>
		<?php
		// translators: %1$s is the employer username, %2$s is the candidate username.
		echo wp_kses_post( sprintf( __( 'Username: <strong>%1$s</strong> or <strong>%2$s</strong>', 'cariera' ), $employer_username, $candidate_username ) );
		?>
	</p>
	<p>
		<?php
		// translators: %s is the password.
		echo wp_kses_post( sprintf( __( 'Password: <strong>%s</strong>', 'cariera' ), $password ) );
		?>
	</p>
</div>
