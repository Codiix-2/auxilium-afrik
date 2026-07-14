<?php
/**
 * Bookmark Popup
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/bookmarks/bookmark-popup.php.
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
<div id="bookmark-popup-<?php echo esc_attr( get_the_ID() ); ?>" class="small-dialog zoom-anim-dialog mfp-hide">
	<div class="bookmarks-popup">
		<div class="small-dialog-headline">
			<h3 class="title"><?php esc_html_e( 'Bookmark Details', 'cariera-addons' ); ?></h3>
		</div>

		<div class="small-dialog-content text-left">
			<?php do_action( 'cariera_addons_bookmark_popup_form' ); ?>            
		</div>
	</div>
</div>
