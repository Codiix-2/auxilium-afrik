<?php
/**
 * Displays all links associated with a resume inside a resume list.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/resume-links.php.
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

if ( resume_has_links() || resume_has_file() ) { ?>
	<ul class="resume-links">
		<?php foreach ( get_resume_links() as $link ) { ?>
			<?php
			get_job_manager_template(
				'resumes/content-resume-link.php',
				[
					'post' => $post,
					'link' => $link,
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);
			?>
		<?php } ?>
		<?php if ( resume_has_file() ) { ?>
			<?php get_job_manager_template( 'resumes/content-resume-file.php', [ 'post' => $post ], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' ); ?>
		<?php } ?>
	</ul>
<?php } ?>
