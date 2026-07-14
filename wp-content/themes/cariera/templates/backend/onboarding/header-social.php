<?php
/**
 * Onboarding: Header Social Media
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/header-social.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'font-awesome-5' );

$social_media = [
	'facebook'  => [
		'link'  => 'https://www.facebook.com/gnodesign/',
		'class' => 'facebook',
		'icon'  => 'fab fa-facebook-f',
	],
	'instagram' => [
		'link'  => 'https://www.instagram.com/gno_design/',
		'class' => 'instagram',
		'icon'  => 'fab fa-instagram',
	],
	'youtube'   => [
		'link'  => 'https://www.youtube.com/channel/UCgHmCnZC7L8ggXTBYP28spQ',
		'class' => 'youtube',
		'icon'  => 'fab fa-youtube',
	],
	'dribbble'  => [
		'link'  => 'https://dribbble.com/gnodesign',
		'class' => 'dribbble',
		'icon'  => 'fab fa-dribbble',
	],
	'linkedin'  => [
		'link'  => 'https://www.linkedin.com/in/gino-aliaj-9061452a3/',
		'class' => 'linkedin',
		'icon'  => 'fab fa-linkedin',
	],
];
?>

<ul class="social-buttons">
	<?php foreach ( $social_media as $social ) { ?>
		<li>
			<a href="<?php echo esc_url( $social['link'] ); ?>" class="<?php echo esc_attr( $social['class'] ); ?>" target="_blank">
				<i class="<?php echo esc_attr( $social['icon'] ); ?>"></i>
			</a>
		</li>
	<?php } ?>

	<li>
		<a href="https://1.envato.market/gnodesign" class="envato" target="_blank">
			<svg height="2290" viewBox="-8.214 -1.546 140.222 134.432" width="2500"><path d="M0 0h128v128H0z" fill="none"/><path clip-rule="evenodd" d="M102.953.702c5.732 2.812 29.055 53.144 9.158 96.98-15.979 35.2-56.093 35.204-76.427 22.8-17.368-10.6-43.898-43.996-14.528-84.776 1.243-1.548 4.225-1.408 3.552 3.268-.476 3.32-4.736 27.156 3.395 37.512 3.714 5.18 4.583 1.608 4.583 1.608s-.633-34.684 25.736-61.016C75.16 1.022 98.374-1.546 102.953.702" fill="#fff" fill-rule="evenodd"/></svg>
		</a>
	</li>
</ul>
