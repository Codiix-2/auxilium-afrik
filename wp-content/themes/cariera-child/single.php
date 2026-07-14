<?php /*
 * Template Name: Custom Service Template
 * Template Post Type: Post_typename 
 */ ?>

<?php

get_header();


if (have_posts()) {
    while (have_posts()) {
        the_post(); ?>
        <div class="container">
            <h1><?php the_title(); ?></h1>
            <div><?php the_content(); ?></div>
        </div>
    <?php }
}


get_footer();
