<?php

if ( post_password_required() ) {
	return;
}
?>

<?php if ( ! comments_open() && get_comments_number() ) { ?>
	<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'cariera' ); ?></p>
<?php } ?>

<?php
$commenter = wp_get_current_commenter();
$req       = get_option( 'require_name_email' );
$aria_req  = $req ? " aria-required='true'" : '';

$fields = [
	'author' =>
		'<p class="comment-form-author">' .
		'<label for="author">' . esc_html__( 'Name', 'cariera' ) .
		( $req ? '<span class="required">*</span>' : '' ) . '</label>' .
		'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .
		'" size="30"' . $aria_req . ' /></p>',

	'email'  =>
		'<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'cariera' ) .
		( $req ? '<span class="required">*</span>' : '' ) . '</label>' .
		'<input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) .
		'" size="30"' . $aria_req . ' /></p>',
];

$comment_args = [
	'fields'        => apply_filters( 'cariera_comment_form_fields', $fields ),
	'comment_field' =>
		'<p class="comment-form-comment"><label for="comment">' .
		esc_html__( 'Comment', 'cariera' ) . '</label>' .
		'<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
	'title_reply'   => esc_html__( 'Leave a Comment', 'cariera' ),
	'class_submit'  => 'btn btn-main',
];
?>

<?php if ( have_comments() ) { ?>
	<h4 class="comments-title">
		<?php
		comments_number(
			esc_html__( 'No comments', 'cariera' ),
			esc_html__( '1 Comment', 'cariera' ),
			esc_html__( '% Comments', 'cariera' )
		);
		?>
	</h4>

	<?php the_comments_navigation(); ?>

	<ul class="comment-list">
		<?php
		wp_list_comments(
			[
				'style'       => 'ul',
				'short_ping'  => true,
				'avatar_size' => 50,
				'type'        => 'comment',
			]
		);
		?>
	</ul>

	<?php the_comments_navigation(); ?>
<?php } ?>

<?php
comment_form( $comment_args );
