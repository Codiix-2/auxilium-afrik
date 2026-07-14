<?php

get_header(); ?>

<main class="page-not-found">    
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h1 class="title"><?php esc_html_e( 'Page not found!', 'cariera' ); ?></h1>
				<p><?php esc_html_e( 'We\'re sorry, but the page you were looking for doesn\'t exist.', 'cariera' ); ?></p>
				<?php get_search_form(); ?>
	
				<a href="<?php echo esc_url( home_url() ); ?>" class="btn btn-main btn-effect"><?php esc_html_e( 'Back Home', 'cariera' ); ?></a>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
