<?php
/**
 * Candidate Portfolio
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/single-resume/candidate-portfolio.php.
 *
 * TODO: This will need to use wp_get_attachment_image_src() to show the thumbnail only but this is still not working.
 * The issue can be found here: https://github.com/Automattic/WP-Job-Manager/issues/1973
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     0.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$gallery = get_post_meta( $post->ID, '_candidate_portfolio', true );

if ( empty( $gallery ) ) {
	return;
}
?>

<div id="candidate-portfolio" class="candidate-portfolio">
	<h2 class="content-title"><?php esc_html_e( 'Candidate Portfolio', 'cariera-addons' ); ?></h2>

	<div class="gallery-wrapper">
		<?php
		$i = 1;

		foreach ( $gallery as $attach_id => $img_url ) {
			$additional_class = '';
			$more_image_class = '';
			$more_image_html  = '';

			// Hide all images after the 4th one.
			if ( $i > 4 ) {
				$additional_class = 'hidden';
			}

			// Add view more gallery if images are more than 4.
			if ( $i == 4 && count( $gallery ) > 4 ) {
				$more_image_html  = '<div class="view-more">+' . ( count( $gallery ) - 4 ) . '</div>';
				$more_image_class = 'view-image';
			}
			?>

			<div class="portfolio-item <?php echo esc_attr( $additional_class ); ?>">
				<a href="<?php echo esc_url( $img_url ); ?>" data-elementor-lightbox-slideshow="candidate-gallery" class="popup-image-gallery <?php echo esc_attr( $more_image_class ); ?>">
					<img src="<?php echo esc_attr( $img_url ); ?>" width="200" height="200" alt="<?php esc_attr_e( 'Candidate portfolio image.', 'cariera-addons' ); ?>" />
				</a>

				<?php echo wp_kses_post( $more_image_html ); ?>
			</div>
			
			<?php
			++$i;
		}
		?>
	</div>
</div>
