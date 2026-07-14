<?php
/**
 * Custom: Single Job Page - Page Header
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/page-header.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.9.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image     = get_post_meta( $post->ID, '_job_cover_image', true );
$job_types = wpjm_get_the_job_types();
?>

<section class="page-header job-header <?php echo ! empty( $image ) ? esc_attr( 'page-header-bg' ) : ''; ?>" <?php echo ! empty( $image ) ? 'style="background: url(' . esc_attr( $image ) . ');"' : ''; ?>>
	<div class="container">
		<div class="row">
			<div class="job-info">
				<div class="title">
					<h1 class="title"><?php wpjm_the_job_title(); ?></h1>
	
					<?php
					if ( ! empty( $job_types ) ) {
						foreach ( $job_types as $job_type ) {
							?>
							<span class="job-type term-<?php echo esc_attr( $job_type->term_id ); ?> <?php echo esc_attr( sanitize_title( $job_type->slug ) ); ?>"><?php echo esc_html( $job_type->name ); ?></span>
							<?php
						}
					}

					// If job is new than show the new tag.
					if ( cariera_newly_posted() ) {
						echo '<span class="job-type new-job-tag">' . esc_html__( 'New', 'cariera' ) . '</span>';
					}
					?>
				</div>
	
				<div class="listing-actions">
					<?php
					do_action( 'cariera_bookmark_hook' );

					if ( get_option( 'cariera_private_messages' ) && get_option( 'cariera_private_messages_job_listings' ) ) {
						get_job_manager_template_part( 'single-job/private', 'message' );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
