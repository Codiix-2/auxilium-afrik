<?php
/**
 * Page Header
 *
 * This template can be overridden by copying it to cariera-child/templates/content/page-header.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.9
 * @version     1.8.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( get_post_meta( get_the_ID(), 'cariera_show_page_title', 'true' ) === 'hide' ) {
	return;
}

$classes          = [ 'page-header' ];
$background_image = get_post_meta( $post->ID, 'cariera_page_header_bg', true );

if ( ! empty( $background_image ) ) {
	$classes[] = 'page-header-bg';
} ?>

<section class="<?php echo esc_attr( join( ' ', $classes ) ); ?>" <?php echo ! empty( $background_image ) ? 'style="background: url(' . esc_attr( $background_image ) . ');"' : ''; ?>>
	<h1 class="title"><?php echo \Cariera\get_the_title(); ?></h1>
	<?php echo \Cariera\breadcrumbs(); ?>
</section>
