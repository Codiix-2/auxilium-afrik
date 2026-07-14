<?php
/**
 * General archive
 *
 * This template can be overridden by copying it to cariera-child/templates/archive.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.7
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blog_layout     = cariera_get_option( 'cariera_blog_layout' );
$has_page_header = cariera_get_option( 'cariera_blog_page_header', 'true' );
$layout_class    = ( 'fullwidth' === $blog_layout ) ? 'col-md-12 articles-wrapper' : 'col-md-8 col-xs-12 articles-wrapper';

if ( $has_page_header ) { ?>
	<section class="page-header">
		<h1 class="title"><?php echo wp_kses_post( \Cariera\get_the_title() ); ?></h1>
	</section>
<?php } ?>

<main class="cariera-section-padding">
	<div class="container">
		<div class="row">
			<div class="<?php echo esc_attr( $layout_class ); ?>">
				<?php
				if ( have_posts() ) {
					while ( have_posts() ) :
						the_post();
						get_template_part( 'templates/content/content', get_post_format() );
					endwhile;

					\Cariera\pagination_nav();
				} else {
					get_template_part( 'templates/content/content', 'none' );
				}
				?>
			</div>

			<?php
			if ( 'fullwidth' !== $blog_layout ) {
				get_sidebar();
			}
			?>
		</div>
	</div>
</main>
