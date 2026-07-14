<?php
/**
 * Footer Social Media template
 *
 * This template can be overridden by copying it to cariera-child/templates/footer/social-media.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.0
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footericons = cariera_get_option( 'cariera_footer_socials', [] );

if ( empty( $footericons ) ) {
	return;
}

$social_svgs = [
	'bluesky'   => '<svg class="social-btn-roll-icon" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg"><path d="M13.873 7.305C21.21 12.832 29.103 23.537 32 29.55v15.882c0-.338-.13.044-.41.867-1.512 4.456-7.418 21.847-20.923 7.944-7.111-7.32-3.819-14.64 9.125-16.85-7.405 1.264-15.73-.825-18.014-9.015C1.12 26.022 0 11.51 0 9.55 0-.268 8.579 2.818 13.873 7.305ZM50.127 7.305C42.79 12.832 34.897 23.537 32 29.55v15.882c0-.338.13.044.41.867 1.512 4.456 7.418 21.847 20.923 7.944 7.111-7.32 3.819-14.64-9.125-16.85 7.405 1.264 15.73-.825 18.014-9.015C62.88 26.022 64 11.51 64 9.55c0-9.818-8.578-6.732-13.873-2.745Z"/></svg>',
	'threads'   => '<svg class="social-btn-roll-icon" viewBox="0 0 16 16"><path d="M6.321 6.016c-.27-.18-1.166-.802-1.166-.802c.756-1.081 1.753-1.502 3.132-1.502c.975 0 1.803.327 2.394.948s.928 1.509 1.005 2.644q.492.207.905.484c1.109.745 1.719 1.86 1.719 3.137c0 2.716-2.226 5.075-6.256 5.075C4.594 16 1 13.987 1 7.994C1 2.034 4.482 0 8.044 0C9.69 0 13.55.243 15 5.036l-1.36.353C12.516 1.974 10.163 1.43 8.006 1.43c-3.565 0-5.582 2.171-5.582 6.79c0 4.143 2.254 6.343 5.63 6.343c2.777 0 4.847-1.443 4.847-3.556c0-1.438-1.208-2.127-1.27-2.127c-.236 1.234-.868 3.31-3.644 3.31c-1.618 0-3.013-1.118-3.013-2.582c0-2.09 1.984-2.847 3.55-2.847c.586 0 1.294.04 1.663.114c0-.637-.54-1.728-1.9-1.728c-1.25 0-1.566.405-1.967.868ZM8.716 8.19c-2.04 0-2.304.87-2.304 1.416c0 .878 1.043 1.168 1.6 1.168c1.02 0 2.067-.282 2.232-2.423a6.2 6.2 0 0 0-1.528-.161"/></svg>',
	'tiktok'    => '<svg class="social-btn-roll-icon" viewBox="0 0 16 16"><path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/></svg>',
	'twitter-x' => '<svg class="social-btn-roll-icon" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>',
];
?>

<ul class="social-btns text-right">
	<?php
	foreach ( $footericons as $icon ) {
		$social_type = esc_attr( $icon['social_type'] );
		$link_url    = esc_url( $icon['link_url'] );
		$aria_label  = sprintf(
			// translators: %s: social media platform name.
			esc_attr__( '%s social media link', 'cariera' ),
			ucfirst( $social_type )
		);
		?>
		<li class="list-inline-item">
			<a class="social-btn-roll <?php echo esc_attr( $social_type ); ?>" href="<?php echo esc_url( $link_url ); ?>" target="_blank" aria-label="<?php echo esc_attr( $aria_label ); ?>">
				<div class="social-btn-roll-icons">
					<?php
					if ( isset( $social_svgs[ $social_type ] ) ) {
						echo wp_kses_post( $social_svgs[ $social_type ] . $social_svgs[ $social_type ] );
					} else {
						printf( '<i class="social-btn-roll-icon lab la-%1$s"></i><i class="social-btn-roll-icon lab la-%1$s"></i>', esc_attr( $social_type ) );
					}
					?>
				</div>
			</a>
		</li>
	<?php } ?>
</ul>
