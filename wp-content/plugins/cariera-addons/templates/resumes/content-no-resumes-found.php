<?php
/**
 * Message to show when no resumes are found.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/content-no-resumes-found.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plural_label = cariera_addons_resume_cpt_plural_label();

if ( defined( 'DOING_AJAX' ) ) { ?>
	<li class="no_resumes_found"><?php esc_html_e( 'There are no listings matching your search.', 'cariera-addons' ); ?></li>
<?php } else { ?>
	<p class="no_resumes_found">
		<?php
		printf(
			/* translators: %s: plural resume label */
			esc_html__( 'There are currently no %s.', 'cariera-addons' ),
			esc_html( $plural_label )
		);
		?>
	</p>
<?php } ?>
