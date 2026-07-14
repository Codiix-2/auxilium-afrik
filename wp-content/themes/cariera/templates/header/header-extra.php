<?php
/**
 * Header Extra template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/header-extra.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.0
 * @version     1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! cariera_get_option( 'cariera_header_extra' ) ) {
	return;
}
?>

<div class="extra-menu">
	<?php
	get_template_part( 'templates/header/extra/cart' );
	get_template_part( 'templates/header/extra/account' );
	get_template_part( 'templates/header/extra/header-cta' );
	?>
</div>
