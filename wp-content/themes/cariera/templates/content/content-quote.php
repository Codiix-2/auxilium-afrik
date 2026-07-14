<?php
/**
 * Blog posts - Quote post template
 *
 * This template can be overridden by copying it to cariera-child/templates/content/content-quote.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class[] = 'blog-post blog-post-quote';

if ( is_sticky( $post->ID ) ) {
	$class[] = 'sticky-post';
}

wp_enqueue_style( 'cariera-blog-feed' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $class ); ?>>
	<?php
	$quote_content = get_post_meta( $post->ID, 'cariera_blog_quote_content', true );
	$quote_author  = get_post_meta( $post->ID, 'cariera_blog_quote_author', true );
	$quote_source  = get_post_meta( $post->ID, 'cariera_blog_quote_source', true );
	$allowed_tags  = wp_kses_allowed_html( 'post' );

	if ( ! empty( $quote_content ) && ! empty( $quote_author ) ) {
		?>
		<figure class="post-quote">
			<span class="icon"></span>
			<blockquote>
				<h2 class="quote"><?php echo esc_html( $quote_content ); ?></h2>

				<?php if ( ! empty( $quote_source ) ) { ?>
					<a href="<?php echo esc_url( $quote_source ); ?>">
				<?php } ?>
					<h3 class="author">
					<?php
					echo esc_html( '- ' );
					echo wp_kses( $quote_author, $allowed_tags );
					?>
					</h3>
				<?php if ( ! empty( $quote_source ) ) { ?>
					</a> 
				<?php } ?>
			</blockquote>
		</figure>
	<?php } ?>

	<div class="blog-desc">
		<h2 class="blog-post-title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h2>

		<?php get_template_part( 'templates/content/content-meta' ); ?>

		<div class="blog-post-exerpt">
			<?php the_excerpt(); ?>
		</div>

		<a href="<?php the_permalink(); ?>" class="btn btn-main btn-effect">
			<?php esc_html_e( 'Read More', 'cariera' ); ?>
		</a>
	</div>
</article>
