<?php

namespace Cariera_Core\Extensions\Social_Share;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sharer {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		add_action( 'cariera_social_share', [ $this, 'social_share' ] );
	}

	/**
	 * Sharing output function
	 *
	 * @since   1.4.2
	 * @version 1.9.6
	 */
	public function social_share() {
		echo '<div class="social-sharer-wrapper"><a href="#social-share-modal" class="btn btn-main popup-with-zoom-anim">' . esc_html__( 'share', 'cariera-core' ) . '</a></div>';

		add_action( 'wp_footer', [ $this, 'sharing_modal' ] );
	}

	/**
	 * Sharing modal
	 *
	 * @since 1.4.2
	 *
	 * @param null $post The post object.
	 */
	public function sharing_modal( $post = null ) {
		?>
		<div id="social-share-modal" class="small-dialog zoom-anim-dialog mfp-hide">
			<div class="small-dialog-headline">
				<h3 class="title"><?php esc_html_e( 'Share', 'cariera-core' ); ?></h3>
			</div>

			<div class="small-dialog-content">
				<?php $this->sharing_options_output( $post ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * An array of all social media options.
	 *
	 * @since   1.7.9
	 * @version 1.9.1
	 *
	 * @param null $post The post object.
	 */
	public function social_media( $post = null ) {
		$title = get_the_title( $post );
		$link  = get_permalink( $post );

		$social = apply_filters(
			'cariera_sharing_social_media_options',
			[
				'facebook'  => [
					'id'    => 'facebook',
					'link'  => 'https://www.facebook.com/sharer.php?u=' . urlencode( $link ) . '&title=' . urlencode( $title ),
					'title' => esc_html__( 'Facebook', 'cariera-core' ),
					'icon'  => '<i class="social-btn-icon lab la-facebook-f"></i>',
				],
				'twitter-x' => [
					'id'       => 'twitter-x',
					'link'     => 'http://twitter.com/share?text=' . urlencode( $title ) . '&url=' . urlencode( $link ),
					'title'    => esc_html__( 'X', 'cariera-core' ),
					'icon_svg' => '<svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>',
				],
				'linkedin'  => [
					'id'    => 'linkedin',
					'link'  => 'http://www.linkedin.com/shareArticle?url=' . urlencode( $link ) . '&title=' . urlencode( $title ),
					'title' => esc_html__( 'LinkedIn', 'cariera-core' ),
					'icon'  => '<i class="social-btn-icon lab la-linkedin-in"></i>',
				],
				'telegram'  => [
					'id'    => 'telegram',
					'link'  => 'https://telegram.me/share/url?url=' . urlencode( $link ) . '&text=' . urlencode( $title ),
					'title' => esc_html__( 'Telegram', 'cariera-core' ),
					'icon'  => '<i class="social-btn-icon lab la-telegram"></i>',
				],
				'tumblr'    => [
					'id'    => 'tumblr',
					'link'  => 'http://www.tumblr.com/share?v=3&u=' . urlencode( $link ) . '&t=' . urlencode( $title ),
					'title' => esc_html__( 'Tumblr', 'cariera-core' ),
					'icon'  => '<i class="social-btn-icon lab la-tumblr"></i>',
				],
				'whatsapp'  => [
					'id'    => 'whatsapp',
					'link'  => 'https://api.whatsapp.com/send?text=' . urlencode( $link ),
					'title' => esc_html__( 'Whatsapp', 'cariera-core' ),
					'icon'  => '<i class="social-btn-icon lab la-whatsapp"></i>',
				],
				'vk'        => [
					'id'    => 'vk',
					'link'  => 'http://vk.com/share.php?url=' . urlencode( $link ) . '&title=' . urlencode( $title ),
					'title' => esc_html__( 'VK', 'cariera-core' ),
					'icon'  => '<i class="social-btn-icon lab la-vk"></i>',
				],
				'bluesky'   => [
					'id'       => 'bluesky',
					'link'     => 'https://bsky.app/intent/compose?text=' . rawurlencode( $title . ' ' . $link ),
					'title'    => esc_html__( 'Bluesky', 'cariera-core' ),
					'icon_svg' => '<svg viewBox="0 0 64 57"><path fill="#0085ff" d="M13.873 3.805C21.21 9.332 29.103 20.537 32 26.55v15.882c0-.338-.13.044-.41.867-1.512 4.456-7.418 21.847-20.923 7.944-7.111-7.32-3.819-14.64 9.125-16.85-7.405 1.264-15.73-.825-18.014-9.015C1.12 23.022 0 8.51 0 6.55 0-3.268 8.579-.182 13.873 3.805ZM50.127 3.805C42.79 9.332 34.897 20.537 32 26.55v15.882c0-.338.13.044.41.867 1.512 4.456 7.418 21.847 20.923 7.944 7.111-7.32 3.819-14.64-9.125-16.85 7.405 1.264 15.73-.825 18.014-9.015C62.88 23.022 64 8.51 64 6.55c0-9.818-8.578-6.732-13.873-2.745Z"></path></svg>',
				],
				'threads'   => [
					'id'       => 'threads',
					'link'     => 'https://www.threads.net/intent/post?text=' . rawurlencode( $title . ' ' . $link ),
					'title'    => esc_html__( 'Threads', 'cariera-core' ),
					'icon_svg' => '<svg viewBox="0 0 16 16"><path d="M6.321 6.016c-.27-.18-1.166-.802-1.166-.802c.756-1.081 1.753-1.502 3.132-1.502c.975 0 1.803.327 2.394.948s.928 1.509 1.005 2.644q.492.207.905.484c1.109.745 1.719 1.86 1.719 3.137c0 2.716-2.226 5.075-6.256 5.075C4.594 16 1 13.987 1 7.994C1 2.034 4.482 0 8.044 0C9.69 0 13.55.243 15 5.036l-1.36.353C12.516 1.974 10.163 1.43 8.006 1.43c-3.565 0-5.582 2.171-5.582 6.79c0 4.143 2.254 6.343 5.63 6.343c2.777 0 4.847-1.443 4.847-3.556c0-1.438-1.208-2.127-1.27-2.127c-.236 1.234-.868 3.31-3.644 3.31c-1.618 0-3.013-1.118-3.013-2.582c0-2.09 1.984-2.847 3.55-2.847c.586 0 1.294.04 1.663.114c0-.637-.54-1.728-1.9-1.728c-1.25 0-1.566.405-1.967.868ZM8.716 8.19c-2.04 0-2.304.87-2.304 1.416c0 .878 1.043 1.168 1.6 1.168c1.02 0 2.067-.282 2.232-2.423a6.2 6.2 0 0 0-1.528-.161" fill="currentColor"/></svg>',
					'color'    => '#000',
				],
				'mail'      => [
					'id'    => 'mail',
					'link'  => 'mailto:?subject=' . urlencode( $link ) . '&body=' . urlencode( $title ) . ' - ' . urlencode( $link ),
					'title' => esc_html__( 'Mail', 'cariera-core' ),
					'icon'  => '<i class="social-btn-icon lar la-envelope"></i>',
				],
			]
		);

		return $social;
	}

	/**
	 * Outputting the markup with all the social media options.
	 *
	 * @since   1.7.9
	 * @version 1.9.1
	 *
	 * @param null $post The post object.
	 */
	public function sharing_options_output( $post = null ) {
		$socials = $this->social_media( $post );
		?>

		<ul class="social-btns">
			<?php foreach ( $socials as $social ) { ?>
				<li class="share-<?php echo esc_attr( $social['id'] ); ?>">
					<a href="<?php echo esc_url( $social['link'] ); ?>" target="_blank">
						<div class="social-btn <?php echo esc_attr( $social['id'] ); ?>" <?php echo ! empty( $social['color'] ) ? 'style="background-color: ' . esc_attr( $social['color'] ) . '"' : ''; ?>>
							<?php
							if ( ! empty( $social['icon_svg'] ) ) {
								echo wp_kses_post( $social['icon_svg'] );
							} else {
								echo wp_kses_post( $social['icon'] );
							}
							?>
						</div>
						<h4 class="title"><?php echo esc_html( $social['title'] ); ?></h4>
					</a>
				</li>
			<?php } ?>
		</ul>

		<?php
	}
}
