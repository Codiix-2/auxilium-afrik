<?php
/**
 * Header default template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/header-default.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$header_top         = get_post_meta( get_the_ID(), 'cariera_header1_fixed_top', 'true' );
$header_transparent = get_post_meta( get_the_ID(), 'cariera_header1_transparent', 'true' );
$header_white       = get_post_meta( get_the_ID(), 'cariera_header1_white', 'true' );
$login_registration = get_option( 'cariera_login_register_layout' );

$header_classes = [ 'cariera-main-header', 'main-header', 'header1' ];

if ( 1 === absint( $header_top ) ) {
	$header_classes[] = 'header-fixed-top';
}

if ( 1 === absint( $header_transparent ) ) {
	$header_classes[] = 'header-transparent';
}

if ( 1 === absint( $header_white ) ) {
	$header_classes[] = 'header-white';
}

if ( cariera_get_option( 'cariera_sticky_header', ) ) {
	$header_classes[] = 'sticky-header';
}

if ( cariera_get_option( 'cariera_sticky_mobile_header' ) ) {
	$header_classes[] = 'sticky-mobile-header';
}

if ( cariera_get_option( 'cariera_fullwidth_header' ) ) {
	$header_width = 'container-fluid';
} else {
	$header_width = 'container';
} ?>

<header class="<?php echo esc_attr( join( ' ', $header_classes ) ); ?>">
	<div class="header-container <?php echo esc_attr( $header_width ); ?>">

		<!-- ====== Start of Logo ====== -->
		<div class="logo">
			<?php if ( cariera_get_option( 'logo' ) ) { ?>
				<a class="navbar-brand logo-wrapper" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr( bloginfo( 'name' ) ); ?>" rel="home">
					<!-- Logo -->
					<img src="<?php echo esc_url( cariera_get_option( 'logo' ) ); ?>" class="logo" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" />

					<?php if ( cariera_get_option( 'logo-white' ) ) { ?>
						<!-- White Logo -->
						<img src="<?php echo esc_url( cariera_get_option( 'logo-white' ) ); ?>" class="logo-white" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" />
					<?php } ?>
				</a>
			<?php } elseif ( cariera_get_option( 'logo_text' ) ) { ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="logo-text">
					<?php echo esc_html( cariera_get_option( 'logo_text' ) ); ?>
				</a>
			<?php } else { ?>
				<a class="navbar-brand logo-wrapper" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr( bloginfo( 'name' ) ); ?>" rel="home">
					<!-- INSERT YOUR LOGO HERE -->
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.svg' ); ?>" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" width="150" class="logo">

					<!-- INSERT YOUR WHITE LOGO HERE -->
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-white.svg' ); ?>" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" width="150" class="logo-white">
				</a>
			<?php } ?>
		</div>
		<!-- ====== End of Logo ====== -->

		<!-- ====== Start of Mobile Navigation ====== -->
		<div class="mmenu-trigger">
			<button id="mobile-nav-toggler" class="hamburger hamburger--collapse" type="button" aria-label="<?php esc_attr_e( 'Mobile navigation toggler', 'cariera' ); ?>">
				<span class="hamburger-box">
					<span class="hamburger-inner"></span>
				</span>
			</button>
		</div>
		<!-- ====== Endo of Mobile Navigation ====== -->

		<!-- ====== Start of Main Menu ====== -->
		<nav class="main-nav-wrapper">
			<?php
			$menu_args = [
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'main-menu main-nav',
				'fallback_cb'    => '\Cariera\menu_fallback',
			];

			if ( has_nav_menu( 'primary' ) && class_exists( '\Cariera\Mega_Menu' ) ) {
				$menu_args['walker'] = new \Cariera\Mega_Menu();
			}

			wp_nav_menu( $menu_args );
			?>
		</nav>
		<!-- ====== End of Main Menu ====== -->

		<?php get_template_part( 'templates/header/header-extra' ); ?>
	</div>
</header>
