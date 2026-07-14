<?php
/**
 * Bookmark Trigger Button
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/bookmarks/bookmark-trigger.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.0
 * @version     0.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$post_id                 = get_the_ID();
$is_company              = ( 'company' === $post_type );
$bookmark_text           = $is_bookmarked ? esc_html__( 'Bookmarked', 'cariera-addons' ) : esc_html__( 'Bookmark', 'cariera-addons' );
$login_registration      = get_option( 'cariera_login_register_layout' );
$login_register_page_id  = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_register_page_url = get_permalink( $login_register_page_id );

$popup_target     = '#bookmark-popup-' . esc_attr( $post_id );
$popup_class      = $is_company ? 'company-bookmark popup-with-zoom-anim' : 'listing-bookmark btn btn-main btn-effect popup-with-zoom-anim';
$logged_out_class = $is_company ? 'company-bookmark' : 'listing-bookmark btn btn-main btn-effect';
$aria_label       = esc_attr__( 'Bookmark company', 'cariera-addons' );

if ( $is_bookmarked ) {
	$popup_class .= ' listing-bookmarked';
}
?>

<?php if ( is_user_logged_in() ) { ?>
	<a href="<?php echo esc_attr( $popup_target ); ?>" class="<?php echo esc_attr( $popup_class ); ?>" <?php echo $is_company ? 'aria-label="' . esc_attr( $aria_label ) . '"' : ''; ?>>
		<?php echo $is_company ? '<i class="lar la-heart"></i>' : esc_html( $bookmark_text ); ?>
	</a>
<?php } else { ?>
	<?php if ( 'popup' === $login_registration ) { ?>
		<a href="#login-register-popup" class="<?php echo esc_attr( $logged_out_class ); ?> popup-with-zoom-anim">
			<?php echo $is_company ? '<i class="lar la-heart"></i>' : esc_html__( 'Login to bookmark', 'cariera-addons' ); ?>
		</a>
	<?php } else { ?>
		<a href="<?php echo esc_url( $login_register_page_url ); ?>" class="<?php echo esc_attr( $logged_out_class ); ?>">
			<?php echo $is_company ? '<i class="lar la-heart"></i>' : esc_html__( 'Login to bookmark', 'cariera-addons' ); ?>
		</a>
	<?php } ?>
<?php } ?>
