<?php
/**
 * Footer: copyright template
 *
 * This template can be overridden by copying it to cariera-child/templates/footer/copyright.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.7
 * @version     1.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$copyright = cariera_get_option( 'cariera_copyrights' );
?>

<div class="copyright">
	<div class="container">
		<div class="row">
			<div class="col-md-6 col-sm-6 col-xs-12">
				<h3 class="copyright-text"><?php echo wp_kses_post( $copyright ); ?></h3>
			</div>

			<div class="col-md-6 col-sm-6 col-xs-12">
				<?php get_template_part( 'templates/footer/social-media' ); ?>
			</div>                    
		</div>
	</div>
</div>
