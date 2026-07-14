<?php
/**
 * Notice to show when user is logged out.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/bookmarks/logged-out-bookmark-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.0
 * @version     0.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="job-manager-form cariera-addons-form cariera-addons-bookmarks-form">
	<div><a class="bookmark-notice" href="<?php echo apply_filters( 'job_manager_bookmark_form_login_url', wp_login_url( get_permalink() ) ); ?>"><?php printf( __( 'Login to bookmark this %s', 'cariera-addons' ), $post_type->labels->singular_name ); ?></a></div>
</div>
