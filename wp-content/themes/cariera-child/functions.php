<?php
add_action( 'wp_enqueue_scripts', 'cariera_child_enqueue_scripts', 20 );
function cariera_child_enqueue_scripts() {
	wp_enqueue_style( 'cariera-child-style', get_stylesheet_uri() );
}

add_action( 'init', 'service_post_type' );

function service_post_type() {
    register_post_type( 'php_service',
        array(
            'labels' => array(
                'name' => __( 'Services' ),
                'singular_name' => __( 'Service' ),
				'add_new' => __( 'Add New Service' ), 
                'add_new_item' => __( 'Add New Service' ), 
                'edit_item' => __( 'Edit Service' ), 
                'new_item' => __( 'New Service' ),
                'view_item' => __( 'View Service' ), 
                'view_items' => __( 'View Services' ),
                'search_items' => __( 'Search Services' ),
                'not_found' => __( 'No services found' ),
                'not_found_in_trash' => __( 'No services found in trash' ),
                'all_items' => __( 'All Services' ),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'services'),
          	'menu_icon' => 'dashicons-welcome-widgets-menus',
			'description' => 'Custom post type for services provided',
            'capability_type' => 'post',
            'taxonomies' => array( 'category', 'post_tag' ),
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        )
    );
	
}

add_action( 'init', 'training_post_type' );

function training_post_type() {
    register_post_type( 'php_training',
        array(
            'labels' => array(
                'name' => __( 'Trainings' ),
                'singular_name' => __( 'Training' ),
				'add_new' => __( 'Add New Training' ),
                'add_new_item' => __( 'Add New Training' ),
                'edit_item' => __( 'Edit Training' ),
                'new_item' => __( 'New Training' ),
                'view_item' => __( 'View Training' ),
                'view_items' => __( 'View Trainings' ),
                'search_items' => __( 'Search Trainings' ),
                'not_found' => __( 'No trainings found' ),
                'not_found_in_trash' => __( 'No trainings found in trash' ),
                'all_items' => __( 'All Trainings' ),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'trainings'),
			'menu_icon' => 'dashicons-groups',
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),

        )
    );
}

add_action( 'init', 'sponsor_post_type' );

function sponsor_post_type() {
    register_post_type( 'php_sponsor',
        array(
            'labels' => array(
                'name' => __( 'Sponsors' ),
                'singular_name' => __( 'Sponsor' ),
				'add_new' => __( 'Add New Sponsor' ),
                'add_new_item' => __( 'Add New Sponsor' ),
                'edit_item' => __( 'Edit Sponsor' ),
                'new_item' => __( 'New Sponsor' ),
                'view_item' => __( 'View Sponsor' ),
                'view_items' => __( 'View Sponsors' ),
                'search_items' => __( 'Search Sponsors' ),
                'not_found' => __( 'No sponsors found' ),
                'not_found_in_trash' => __( 'No sponsors found in trash' ),
                'all_items' => __( 'All Sponsors' ),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'sponsors'),
			'menu_icon' => 'dashicons-money',
			'supports' => array( 'title',  'thumbnail'),

        )
    );
}



add_action( 'init', 'team_post_type' );

function team_post_type() {
    register_post_type( 'php_team',
        array(
            'labels' => array(
                'name' => __( 'Teams' ),
                'singular_name' => __( 'Team' ),
				'add_new' => __( 'Add New Team' ),
                'add_new_item' => __( 'Add New Team' ),
                'edit_item' => __( 'Edit Team' ),
                'new_item' => __( 'New Team' ),
                'view_item' => __( 'View Team' ),
                'view_items' => __( 'View Teams' ),
                'search_items' => __( 'Search Teams' ),
                'not_found' => __( 'No teams found' ),
                'not_found_in_trash' => __( 'No teams found in trash' ),
                'all_items' => __( 'All Teams' ),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'teams'),
			'menu_icon' => 'dashicons-businessperson',
			'supports' => array( 'title', 'thumbnail', 'excerpt' ),

        )
    );
}

add_action( 'init', 'news_post_type' );

function news_post_type() {
    register_post_type( 'php_news',
        array(
            'labels' => array(
                'name' => __( 'News' ),
                'singular_name' => __( 'News' ),
				'add_new' => __( 'Add New News' ),
                'add_new_item' => __( 'Add New News' ),
                'edit_item' => __( 'Edit News' ),
                'new_item' => __( 'New News' ),
                'view_item' => __( 'View News' ),
                'view_items' => __( 'View News' ),
                'search_items' => __( 'Search News' ),
                'not_found' => __( 'No News found' ),
                'not_found_in_trash' => __( 'No News found in trash' ),
                'all_items' => __( 'All News' ),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'news'),
			'menu_icon' => 'dashicons-index-card',
			'supports' => array( 'title', 'thumbnail', 'excerpt' ),

        )
    );
}


function custom_default_featured_image() {

    if ( 'testimonial' === get_post_type() && !has_post_thumbnail() ) {
        $default_image_url = 'https://auxilium-afrik.com/wp-content/uploads/2024/04/default_user.jpeg'; 
        $default_image_id = attachment_url_to_postid( $default_image_url );
        
        if ( $default_image_id ) {
            set_post_thumbnail( get_the_ID(), $default_image_id );
        }
    }
}
add_action( 'the_post', 'custom_default_featured_image' );
add_action( 'save_post', 'custom_default_featured_image' );



function get_php_training_count() {
    $args = array(
        'post_type' => 'php_training',
        'posts_per_page' => -1,
    );
    $php_training_query = new WP_Query($args);
    $count = $php_training_query->post_count;
    wp_reset_postdata();
    return $count;
}

add_shortcode('php_training_count', 'get_php_training_count');


function custom_php_service_dropdown() {
    $args = array(
        'post_type' => 'php_service',
        'posts_per_page' => -1, 
    );

    $query = new WP_Query($args);

    $select = '<select name="service" id="php_service_select" class="form-control">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $select .= '<option value="' . get_the_title() . '">' . get_the_title() . '</option>';
        }
        wp_reset_postdata();
    }
	
    $select .= '</select>';

    return $select;
}
add_shortcode('custom_php_service_dropdown', 'custom_php_service_dropdown');


function custom_php_training_dropdown() {
    $args = array(
        'post_type' => 'php_training',
        'posts_per_page' => -1, 
    );

    $query = new WP_Query($args);

    $select = '<select name="training" id="php_training_select" class="form-control">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $select .= '<option value="' . get_the_title() . '">' . get_the_title() . '</option>';
        }
        wp_reset_postdata();
    }
	
    $select .= '</select>';

    return $select;
}
add_shortcode('custom_php_training_dropdown', 'custom_php_training_dropdown');



//ENABLE EDITOR ROLE TO TRANSLATE
add_filter('wpml_user_can_translate', function ($user_can_translate, $user) {
    if (in_array('editor', (array) $user->roles, true) && user_can($user, 'translate')) {
        return true;
    }

    return $user_can_translate;
}, 10, 2);


/**
 * Barre utilitaire au-dessus de l'en-tête.
 * Gauche : heure de Kinshasa (mise à jour en direct côté client).
 * Droite : adresse e-mail de contact.
 *
 * Accrochée à wp_body_open() : dans header.php ce hook s'exécute avant
 * l'ouverture de .wrapper, la barre se place donc bien au-dessus de la
 * navigation.
 */
add_action( 'wp_body_open', 'auxilium_top_bar', 5 );

function auxilium_top_bar() {
    // Heure initiale rendue côté serveur (fallback si le JavaScript est
    // désactivé) — toujours calculée sur le fuseau de Kinshasa.
    try {
        $now = new DateTime( 'now', new DateTimeZone( 'Africa/Kinshasa' ) );
        $initial_time = $now->format( 'H:i' );
    } catch ( Exception $e ) {
        $initial_time = '--:--';
    }
    ?>
    <div class="aa-top-bar" aria-label="<?php esc_attr_e( 'Informations de contact', 'cariera' ); ?>">
        <div class="aa-top-bar__inner">
            <span class="aa-top-bar__time">
                <svg class="aa-top-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg>
                <span class="aa-top-bar__label">Kinshasa</span>
                <time class="aa-top-bar__clock" data-aa-clock><?php echo esc_html( $initial_time ); ?></time>
            </span>
            <a class="aa-top-bar__email" href="mailto:Contact@auxilium-afrik.com">
                <svg class="aa-top-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><polyline points="3 7 12 13 21 7"></polyline></svg>
                <span>Contact@auxilium-afrik.com</span>
            </a>
        </div>
    </div>
    <script>
    (function () {
        var el = document.querySelector('[data-aa-clock]');
        if (!el) { return; }
        function tick() {
            try {
                el.textContent = new Intl.DateTimeFormat('fr-FR', {
                    timeZone: 'Africa/Kinshasa',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }).format(new Date());
            } catch (e) { /* Intl / fuseau indisponible : on garde l'heure serveur */ }
        }
        tick();
        setInterval(tick, 15000);
    })();
    </script>
    <?php
}

