<?php
/**
 * Single Post - Content
 *
 * This template can be overridden by copying it to cariera-child/templates/content/single.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blog_layout = cariera_get_option( 'cariera_blog_layout' );
$layout      = ( 'fullwidth' === $blog_layout ) ? 'col-lg-12' : 'col-lg-8';

wp_enqueue_style( 'cariera-single-blog' );

if ( cariera_get_option( 'cariera_blog_page_header', 'true' ) ) {
	get_template_part( 'templates/content/page-header' );
} ?>

<main id="post-<?php the_ID(); ?>" <?php post_class( 'cariera-single-post' ); ?>>
	<div class="container">
		<div class="row single-post-main cariera-section-padding">
			<div class="<?php echo esc_attr( $layout ); ?>">
				<?php \Cariera\single_post_thumbnail(); ?>

				<div class="blog-desc">
					<?php
					// Post Meta Info.
					get_template_part( 'templates/content/single-meta' );

					the_content();

					wp_link_pages();

					// Get sharing options.
					if ( cariera_get_option( 'cariera_post_share' ) ) {
						do_action( 'cariera_social_share' );
					}
					?>
				</div>

				<div class="single-post-post-changer">
					<?php
					the_post_navigation(
						[
							'prev_text'          => '<h6>' . esc_html__( 'Previous Post', 'cariera' ) . '</h6><span>%title</span>',
							'next_text'          => '<h6>' . esc_html__( 'Next Post', 'cariera' ) . '</h6><span>%title</span>',
							'screen_reader_text' => esc_html__( 'Post navigation', 'cariera' ),
						]
					);
					?>
				</div>

				<div class="single-post-comments" id="comments">
					<?php
					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
			</div>

			<?php
			if ( 'fullwidth' !== $blog_layout ) {
				get_sidebar();
			}
			?>
		</div>
</main>
