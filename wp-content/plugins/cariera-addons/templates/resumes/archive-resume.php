<?php
/**
 * Archive Template - Resume
 *
 * This template can be overridden by copying it to yourtheme/cariera-addonsresumes/archive-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$singular_label = cariera_addons_resume_cpt_singular_label();
$plural_label   = cariera_addons_resume_cpt_plural_label();
$count          = wp_count_posts( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME )->publish;

get_header();
?>

<section class="page-header">
	<h1 class="title">
		<?php
		echo apply_filters(
			'cariera_addons_resumes_archive_title',
			wp_kses_post(
				sprintf(
					/* translators: %s: number of resumes found */
					_n(
						'We found %1$s %2$s in our database',
						'We found %1$s %2$s in our database',
						$count,
						'cariera-addons'
					),
					'<span class="listing-count">' . absint( $count ) . '</span>',
					esc_html( 1 === absint( $count ) ? $singular_label : $plural_label )
				)
			)
		);
		?>
	</h1>
</section>

<?php
if ( cariera_get_option( 'cariera_resume_search_map' ) ) {
	echo do_shortcode( '[cariera-map type="resume" class="resume_page"]' );
}
?>

<main class="cariera-section-padding">
	<div class="container">
		<div class="col-md-12">
			<?php echo do_shortcode( '[resumes]' ); ?>
		</div>
	</div>
</main>

<?php
get_footer();
