<?php
/**
 *
 * @package Cariera
 *
 * @since    1.4.8
 * @version  1.9.6
 *
 * ========================
 * Template Name: Fullwidth Page
 * ========================
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part( 'templates/content/page-header' ); ?>

	<main id="post-<?php the_ID(); ?>" <?php post_class( 'cariera-section-padding' ); ?>>
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
	</main>

	<?php
endwhile;

get_footer();
