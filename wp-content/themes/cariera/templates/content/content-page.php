<?php
/**
 * Standard page template
 *
 * This template can be overridden by copying it to cariera-child/templates/content/content-page.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_layout = get_post_meta( $post->ID, 'cariera_page_layout', true );
$page_class  = $page_layout !== 'sidebar' ? 'col-md-12' : 'col-md-8 col-sm-12';
$sidebar     = get_post_meta( $post->ID, 'cariera_select_page_sidebar', true );

get_template_part( 'templates/content/page-header' );
?>

<main id="post-<?php the_ID(); ?>" <?php post_class( 'cariera-section-padding' ); ?>>
	<div class="container">
		<div class="row">
			<div class="<?php echo esc_attr( $page_class ); ?>">
				<?php
				do_action( 'cariera_page_content_start' );

				the_content();

				wp_link_pages(
					[
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'cariera' ),
						'after'  => '</div>',
					]
				);

				if ( comments_open() || get_comments_number() ) { // If comments are open or we have at least one comment, load up the comment template.
					comments_template();
				}

				do_action( 'cariera_page_content_end' );
				?>
			</div>

			<?php if ( 'sidebar' === $page_layout ) { ?>
				<div class="col-md-4 col-xs-12 cariera-page-extra-sidebar">
					<?php if ( is_active_sidebar( $sidebar ) ) { ?>
						<div class="sidebar">
							<?php dynamic_sidebar( $sidebar ); ?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</div>
</main>
