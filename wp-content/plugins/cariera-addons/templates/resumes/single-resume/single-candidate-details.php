<?php
/**
 * Single Resume - Candidate Details
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/resumes/single-resume/single-candidate-details.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.5
 * @version     1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="candidate-details">
	<div class="candidate">
		<div class="candidate-photo">
			<?php cariera_the_candidate_photo(); ?>
		</div>

		<h1 class="title">
			<?php the_title(); ?>
			<?php do_action( 'single_resume_title' ); ?>
		</h1>
	</div>

	<div class="details">
		<div class="candidate-detail location">
			<i class="las la-map-marker"></i>
			<?php
			if ( get_the_candidate_location() ) {
				the_candidate_location( false );
			} else {
				esc_html_e( 'No location', 'cariera-addons' );
			}
			?>
		</div>

		<div class="candidate-detail published-date">
			<i class="las la-clock"></i><?php printf( '%s %s', esc_html__( 'Member Since ', 'cariera-addons' ), get_the_date( 'Y' ) ); ?>
		</div>

		<?php
		if ( resume_manager_user_can_view_contact_details( $post->ID ) ) {
			do_action( 'single_resume_contact_start' );

			foreach ( get_resume_links() as $link ) {
				$parsed_url = wp_parse_url( $link['url'] );
				$host       = isset( $parsed_url['host'] ) ? current( explode( '.', $parsed_url['host'] ) ) : '';
				?>
				<div class="candidate-detail links">
					<i class="las la-link"></i><a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank"><?php echo esc_html( $link['name'] ); ?></a>
				</div>
				<?php
			}

			$email = get_post_meta( $post->ID, '_candidate_email', true );
			if ( $email ) {
				?>
				<div class="candidate-detail candidate-email">
					<i class="las la-envelope"></i><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				</div>
				<?php
			}

			do_action( 'single_resume_contact_end' );
		} else {
			get_job_manager_template_part( 'resumes/access-denied', 'contact-details', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
		}
		?>
	</div>

	<div class="actions">
		<?php do_action( 'cariera_candidate_socials' ); ?>

		<?php
		if ( get_option( 'cariera_private_messages' ) && get_option( 'cariera_private_messages_resumes' ) ) {
			get_job_manager_template_part( 'resumes/single-resume/private', 'message', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
		} else {
			get_job_manager_template( 'resumes/contact-details.php', [ 'post' => $post ], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
		}
		?>

		<?php do_action( 'cariera_candidate_detail_actions' ); ?>
	</div>
</div>
