<?php
/**
 * Form for adding and removing a bookmark.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/bookmarks/bookmark-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.0
 * @version     0.9.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp;

$action_url = '';
if ( ! defined( 'DOING_AJAX' ) ) {
	$action_url = remove_query_arg(
		[ 'page', 'paged' ],
		add_query_arg( $wp->query_string, '', home_url( $wp->request ) )
	);
}

$form_classes = 'job-manager-form cariera-addons-form cariera-addons-bookmarks-form';
if ( $is_bookmarked ) {
	$form_classes .= ' has-bookmark';
}

$remove_bookmark_url = wp_nonce_url(
	add_query_arg( 'remove_bookmark', absint( $post->ID ), get_permalink() ),
	'remove_bookmark'
);

$submit_text        = $is_bookmarked ? esc_html__( 'Update Bookmark', 'cariera-addons' ) : esc_html__( 'Add Bookmark', 'cariera-addons' );
$post_type_label    = $post_type->labels->singular_name;
$post_type_label_uc = ucwords( $post_type_label );
?>

<form method="post" action="<?php echo esc_url( $action_url ); ?>" class="<?php echo esc_attr( $form_classes ); ?>">
	<div class="remove-bookmark-wrapper">
		<p class="bookmark-notice bookmarked"><?php printf( __( 'This %s is bookmarked!', 'cariera-addons' ), esc_html( $post_type_label ) ); ?></p>
		<a class="remove-bookmark" href="<?php echo esc_url( $remove_bookmark_url ); ?>"><?php esc_html_e( 'Remove Bookmark', 'cariera-addons' ); ?></a>
	</div>

	<div class="bookmark-details">
		<label for="bookmark_notes"><?php esc_html_e( 'Notes:', 'cariera-addons' ); ?></label>
		<textarea name="bookmark_notes" id="bookmark_notes" cols="25" rows="3"><?php echo esc_textarea( $note ); ?></textarea>

		<?php wp_nonce_field( 'update_bookmark' ); ?>
		<input type="hidden" name="bookmark_post_id" value="<?php echo absint( $post->ID ); ?>" />
		<input type="submit" class="submit-bookmark-button" name="submit_bookmark" value="<?php echo esc_attr( $submit_text ); ?>" />
		<span class="spinner" style="background-image: url(<?php echo esc_url( includes_url( 'images/spinner.gif' ) ); ?>);"></span>
	</div>
</form>
