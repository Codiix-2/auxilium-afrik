<?php
/**
 *
 * @package Cariera
 *
 * @since    1.4.5
 * @version  1.9.6
 *
 * ========================
 * Template Name: Login - Register
 * ========================
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If user is not logged.
if ( ! is_user_logged_in() ) {

	get_header( 'empty' ); ?>

	<main class="login-register-page">
		<div class="container-fluid">
			<div class="row align-items-center">

				<!-- Title Wrapper -->
				<div class="col-xl-8 col-lg-6 col-md-6 title-wrapper" style="background-image:url( <?php echo esc_attr( cariera_get_option( 'login_page_image' ) ); ?> )">
					<div class="content">
						<h2 class="title"><?php echo esc_html( cariera_get_option( 'login_page_text' ) ); ?></h2>
					</div>
				</div>

				<!-- Form Wrapper -->
				<div class="col-xl-4 col-lg-6 col-md-6 form-wrapper cariera-scroll">
					<div class="content">

						<!-- ====== Start of Logo ====== -->
						<div class="logo">            
							<?php if ( cariera_get_option( 'logo' ) ) { ?>
								<a class="navbar-brand logo-wrapper" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr( bloginfo( 'name' ) ); ?>" rel="home">
									<!-- Logo -->
									<img src="<?php echo esc_url( cariera_get_option( 'logo' ) ); ?>" class="logo" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" />
								</a>
							<?php } elseif ( cariera_get_option( 'logo_text' ) ) { ?>
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="logo-text">
									<?php echo esc_html( cariera_get_option( 'logo_text' ) ); ?>
								</a>
							<?php } else { ?>
								<a class="navbar-brand logo-wrapper" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr( bloginfo( 'name' ) ); ?>" rel="home">
									<!-- INSERT YOUR LOGO HERE -->
									<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.svg' ); ?>" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" width="150" class="logo">
								</a>
							<?php } ?>
						</div>
						<!-- ====== End of Logo ====== -->

						<!-- Start of Signin wrapper -->
						<div class="signin-wrapper">
							<h3 class="title"><?php esc_html_e( 'Sign in', 'cariera' ); ?></h3>

							<?php echo do_shortcode( '[cariera_login_form]' ); // Add login form. ?>

							<div class="bottom-links">
								<a href="#" class="signup-trigger"><i class="las la-user"></i><?php esc_html_e( 'Don\'t have an account?', 'cariera' ); ?></a>
								<a href="#" class="forget-password-trigger"><i class="las la-lock"></i><?php esc_html_e( 'Forgot Password?', 'cariera' ); ?></a>
							</div>

							<?php do_action( 'cariera_social_login' ); ?>
						</div>
						<!-- End of Signin wrapper -->

						<!-- Start of Signup wrapper -->
						<div class="signup-wrapper">
							<h3 class="title"><?php esc_html_e( 'Sign Up', 'cariera' ); ?></h3>

							<?php echo do_shortcode( '[cariera_registration_form]' ); // Add registration form. ?>

							<div class="bottom-links">
								<a href="#" class="signin-trigger"><i class="las la-user"></i><?php esc_html_e( 'Already registered?', 'cariera' ); ?></a>
								<a href="#" class="forget-password-trigger"><i class="las la-lock"></i><?php esc_html_e( 'Forgot Password?', 'cariera' ); ?></a>
							</div>

							<?php do_action( 'cariera_social_login' ); ?>
						</div>
						<!-- End of Signup wrapper -->

						<!-- Start of Forget Password wrapper -->
						<div class="forgetpassword-wrapper">
							<h3 class="title"><?php esc_html_e( 'Forgot Password', 'cariera' ); ?></h3>

							<?php echo do_shortcode( '[cariera_forgetpass_form]' ); // Add forget password form. ?>

							<div class="bottom-links">
								<a href="#" class="signin-trigger"><i class="las la-arrow-left"></i><?php esc_html_e( 'Sign in', 'cariera' ); ?></a>
							</div>
						</div>
						<!-- End of Forget Password wrapper -->

						<div class="back-home">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="lar la-arrow-alt-circle-left"></i><?php esc_html_e( 'Back to home', 'cariera' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

	<?php
	get_footer( 'empty' );

	// Else user is logged.
} else {
	// Get the dashboard page ID from the option.
	$dashboard_page = get_option( 'cariera_dashboard_page' );

	// Get the URL of that page.
	$dashboard_url = get_permalink( $dashboard_page );

	// Redirect to the dashboard.
	wp_safe_redirect( esc_url( $dashboard_url ) );
	exit;
}
