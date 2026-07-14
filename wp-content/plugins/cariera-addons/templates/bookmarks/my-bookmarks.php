<?php
/**
 * Lists a user's bookmarks.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/bookmarks/my-bookmarks.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.0
 * @version     1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( ! empty( $bookmarks ) ) { ?>
	<div class="cariera-addons-bookmarks-dashboard">
		<?php foreach ( $bookmarks as $bookmark ) { ?>
			<?php
				$post_id   = absint( $bookmark->post_id );
				$post_type = get_post_type( $post_id );
				$title     = get_the_title( $post_id );
				$permalink = get_permalink( $post_id );

				// Actions.
				$actions = apply_filters(
					'job_manager_bookmark_actions',
					[
						'delete' => [
							'label' => esc_html__( 'Delete', 'cariera-addons' ),
							'url'   => wp_nonce_url( add_query_arg( 'remove_bookmark', $post_id ), 'remove_bookmark' ),
						],
					],
					$bookmark
				);
			?>

			<div class="listing-bookmarked">
				<div class="header">
					<div class="logo">
						<?php
						switch ( $post_type ) {
							case 'job_listing':
								if ( function_exists( 'the_company_logo' ) ) {
									if ( get_option( 'cariera_company_manager_integration' ) ) {
										$company = cariera_get_the_company( $post_id );
										the_company_logo( 'thumbnail', null, $company );
									} else {
										the_company_logo( 'thumbnail', null, $post_id );
									}
								}
								break;

							case 'resume':
								if ( function_exists( 'the_candidate_photo' ) ) {
									the_candidate_photo( 'thumbnail', null, $post_id );
								}
								break;

							case 'company':
								if ( \Cariera\cariera_core_is_activated() ) {
									the_company_logo( 'thumbnail', null, $post_id );
								}
								break;

							case 'cariera_event':
								if ( function_exists( 'cariera_events_the_logo' ) ) {
									cariera_events_the_logo( 'thumbnail', null, $post_id );
								}
								break;

							default:
								// No image output.
								break;
						}
						?>
					</div>

					<?php if ( ! empty( $post_id ) && 'publish' === get_post_status( $post_id ) ) { ?>
						<a href="<?php echo esc_url( $permalink ); ?>" class="listing-title" target="_blank">
							<?php echo esc_html( $title ); ?>
						</a>
					<?php } else { ?>
						<span class="listing-title">
							<?php echo esc_html( $title ); ?>
						</span>
					<?php } ?>
				</div>

				<?php if ( ! empty( $bookmark->bookmark_note ) ) { ?>
					<div id="bookmark-content-<?php echo esc_attr( $post_id ); ?>" class="bookmark-content">
						<?php echo wpautop( wp_kses_post( $bookmark->bookmark_note ) ); ?>
					</div>
				<?php } ?>

				<?php if ( ! empty( $actions ) ) { ?>
					<div class="actions">
						<a href="#bookmark-popup" class="view-bookmark popup-with-zoom-anim" data-title="<?php echo esc_attr( $title ); ?>" data-id="<?php echo esc_attr( $post_id ); ?>">
							<?php esc_html_e( 'View Note', 'cariera-addons' ); ?>
						</a>

						<?php foreach ( $actions as $action => $value ) { ?>
							<a href="<?php echo esc_url( $value['url'] ); ?>" class="action-<?php echo esc_attr( $action ); ?>">
								<?php echo esc_html( $value['label'] ); ?>
							</a>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
<?php } else { ?>
	<div class="job-manager-message"><?php esc_html_e( 'You currently have no bookmarks.', 'cariera-addons' ); ?></div>
<?php } ?>

<div id="bookmark-popup" class="small-dialog zoom-anim-dialog mfp-hide">
	<div class="small-dialog-headline">
		<h3 class="title"></h3>
	</div>
	<div class="small-dialog-content">
		<h6><?php esc_html_e( 'Bookmark Note:', 'cariera-addons' ); ?></h6>
		<div class="bookmark-content"></div>
	</div>
</div>

<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => absint( $max_num_pages ) ] ); ?>
