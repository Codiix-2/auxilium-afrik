<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php
	wp_body_open();
	do_action( 'cariera_body_start' );
	?>

	<!-- Start Website wrapper -->
	<div class="wrapper">
		<?php
		get_template_part( 'templates/extra/preloader' );

		// Add main header.
		\Cariera\print_header();
