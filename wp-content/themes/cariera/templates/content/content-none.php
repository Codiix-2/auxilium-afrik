<?php
/**
 * Blog posts - Displaying a message when no post can be found
 *
 * This template can be overridden by copying it to cariera-child/templates/content/content-none.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.9.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<h3><?php esc_html_e( 'Nothing found!', 'cariera' ); ?></h3>
<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please search again.', 'cariera' ); ?></p>
