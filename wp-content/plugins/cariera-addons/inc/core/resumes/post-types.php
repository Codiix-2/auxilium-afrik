<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Types {

	use \Cariera_Addons\Src\Traits\Singleton;

	const CPT_RESUME            = 'resume';
	const TAX_CATEGORY          = 'resume_category';
	const TAX_SKILL             = 'resume_skill';
	const PERMALINK_OPTION_NAME = 'cariera_addons_resume_permalinks';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Post Types, Taxonomies and Meta.
		add_action( 'init', [ $this, 'register_post_types' ], 0 );
		add_action( 'init', [ $this, 'register_taxonomies' ], 0 );
		add_action( 'init', [ $this, 'register_meta_fields' ] );

		// Frontend hooks.
		add_action( 'wp', [ $this, 'download_resume_handler' ] );
		add_action( 'wp', [ $this, 'maybe_add_yoast_filters' ] );

		// Admin UI.
		add_filter( 'admin_head', [ $this, 'pending_resumes' ] );

		// Resume Title and content filters.
		add_filter( 'the_title', [ $this, 'resume_title' ], 10, 2 );
		add_filter( 'single_post_title', [ $this, 'resume_title' ], 10, 2 );
		add_filter( 'the_content', [ $this, 'resume_content' ] );

		// Add noindex to resumes if indexing is discouraged.
		if ( resume_manager_discourage_resume_search_indexing() ) {
			add_action( 'wp_head', [ $this, 'add_no_robots' ], 0 );
		}

		// Resume Description Content Filters.
		add_filter( 'the_resume_description', 'wptexturize' );
		add_filter( 'the_resume_description', 'convert_smilies' );
		add_filter( 'the_resume_description', 'convert_chars' );
		add_filter( 'the_resume_description', 'wpautop' );
		add_filter( 'the_resume_description', 'shortcode_unautop' );
		add_filter( 'the_resume_description', 'prepend_attachment' );

		// Allow for oEmbeds to work on Resume content.
		if ( ! empty( $GLOBALS['wp_embed'] ) ) {
			add_filter( 'the_resume_description', [ $GLOBALS['wp_embed'], 'run_shortcode' ], 8 );
			add_filter( 'the_resume_description', [ $GLOBALS['wp_embed'], 'autoembed' ], 8 );
		}

		// Contact details section.
		add_action( 'resume_manager_contact_details', [ $this, 'contact_details_email' ] );

		// Resume Auto-Hiding After Publish.
		$transitions = [
			'pending_to_publish',
			'preview_to_publish',
			'draft_to_publish',
			'auto-draft_to_publish',
			'hidden_to_publish',
			'expired_to_publish',
		];
		foreach ( $transitions as $transition ) {
			add_action( $transition, [ $this, 'setup_autohide_cron' ] );
		}
		add_action( 'save_post', [ $this, 'setup_autohide_cron' ] );
		add_action( 'auto-hide-resume', [ $this, 'hide_resume' ] );

		// Resume Expiration Setup.
		foreach ( $transitions as $transition ) {
			add_action( $transition, [ $this, 'set_expiry' ] );
		}
		add_action( 'pending_payment_to_publish', [ $this, 'set_expiry' ] );
		add_action( 'cariera_addons_check_for_expired_resumes', [ $this, 'check_for_expired_resumes' ] );

		// Post Save and cache.
		add_action( 'save_post', [ $this, 'flush_get_resume_listings_cache' ] );
		add_action( 'delete_post', [ $this, 'flush_get_resume_listings_cache' ] );
		add_action( 'trash_post', [ $this, 'flush_get_resume_listings_cache' ] );
		add_action( 'save_post_resume', [ $this, 'save_postmeta' ] );

		// Miscellaneous.
		add_action( 'resume_manager_my_resume_do_action', [ $this, 'resume_manager_my_resume_do_action' ] );
		add_action( 'update_post_meta', [ $this, 'maybe_update_menu_order' ], 10, 4 );
		add_filter( 'wp_insert_post_data', [ $this, 'fix_post_name' ], 10, 2 );

		// Include admin files conditionally.
		add_action( 'current_screen', [ $this, 'conditional_includes' ] );
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since   0.9.5
	 * @version 1.0.1
	 */
	public function register_post_types() {
		if ( post_type_exists( self::CPT_RESUME ) ) {
			return;
		}

		$admin_capability    = 'manage_resumes';
		$permalink_structure = self::get_permalink_structure();

		/**
		 * Main Post types
		 */
		$singular = cariera_addons_resume_cpt_singular_label();
		$plural   = cariera_addons_resume_cpt_plural_label();

		$args = [
			'labels'                => \Cariera_Addons\Helpers::create_post_type_labels( $singular, $plural ),
			// translators: %s placeholder is the plural lable of company cpt.
			'description'           => sprintf( esc_html__( 'This is where you can create and manage %s.', 'cariera-addons' ), $plural ),
			'public'                => true,
			'show_ui'               => class_exists( 'WP_Job_Manager' ),
			'menu_icon'             => 'dashicons-building',
			'capability_type'       => 'post',
			'capabilities'          => [
				'publish_posts'       => $admin_capability,
				'edit_posts'          => $admin_capability,
				'edit_others_posts'   => $admin_capability,
				'delete_posts'        => $admin_capability,
				'delete_others_posts' => $admin_capability,
				'read_private_posts'  => $admin_capability,
				'edit_post'           => $admin_capability,
				'delete_post'         => $admin_capability,
				'read_post'           => $admin_capability,
			],
			'publicly_queryable'    => true,
			'exclude_from_search'   => true,
			'hierarchical'          => false,
			'rewrite'               => [
				'slug'       => $permalink_structure['resume_rewrite_slug'],
				'with_front' => false,
				'feeds'      => false,
				'pages'      => false,
			],
			'query_var'             => true,
			'supports'              => [ 'title', 'editor', 'custom-fields', 'author' ],
			'has_archive'           => $permalink_structure['resume_archive_rewrite_slug'],
			'show_in_nav_menus'     => false,
			'menu_position'         => 32,
			'show_in_rest'          => true,
			'rest_base'             => 'resumes',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		];

		register_post_type( self::CPT_RESUME, $args );

		add_filter( 'wp_sitemaps_post_types', [ $this, 'disable_sitemap' ] );

		register_post_status(
			'hidden',
			[
				'label'                     => _x( 'Hidden', 'post status', 'cariera-addons' ),
				'public'                    => true,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Hidden <span class="count">(%s)</span>', 'Hidden <span class="count">(%s)</span>', 'cariera-addons' ),
			]
		);
	}

	/**
	 * Filters the resume post type from sitemap.
	 *
	 * @since 0.9.5
	 *
	 * @param array $post_types  The public post types.
	 */
	public function disable_sitemap( $post_types ) {
		unset( $post_types[ self::CPT_RESUME ] );
		return $post_types;
	}

	/**
	 * Register listing taxonomies.
	 *
	 * @since   0.9.5
	 * @version 1.0.1
	 */
	public function register_taxonomies() {
		if ( ! post_type_exists( self::CPT_RESUME ) ) {
			return;
		}

		$admin_capability    = 'manage_resumes';
		$permalink_structure = self::get_permalink_structure();
		$singular_cpt_label  = cariera_addons_resume_cpt_singular_label();

		if ( get_option( 'resume_manager_enable_categories' ) ) {
			/* translators: %s: singular resume CPT label */
			$singular = sprintf( esc_html__( '%s Category', 'cariera-addons' ), esc_html( $singular_cpt_label ) );

			/* translators: %s: singular resume CPT label */
			$plural = sprintf( esc_html__( '%s Categories', 'cariera-addons' ), esc_html( $singular_cpt_label ) );

			$rewrite = [
				'slug'         => $permalink_structure['resume_category_rewrite_slug'],
				'with_front'   => false,
				'hierarchical' => false,
			];

			register_taxonomy(
				self::TAX_CATEGORY,
				[ self::CPT_RESUME ],
				[
					'hierarchical'          => true,
					'update_count_callback' => '_update_post_term_count',
					'label'                 => $plural,
					'labels'                => \Cariera_Addons\Helpers::create_taxonomy_labels( $singular, $plural ),
					'show_ui'               => true,
					'query_var'             => true,
					'capabilities'          => [
						'manage_terms' => $admin_capability,
						'edit_terms'   => $admin_capability,
						'delete_terms' => $admin_capability,
						'assign_terms' => $admin_capability,
					],
					'rewrite'               => $rewrite,
					'show_in_rest'          => true,
					'rest_base'             => 'resume-categories',
				]
			);
		}

		if ( get_option( 'resume_manager_enable_skills' ) ) {
			$singular = esc_html__( 'Candidate Skill', 'cariera-addons' );
			$plural   = esc_html__( 'Candidate Skills', 'cariera-addons' );

			$rewrite = [
				'slug'         => $permalink_structure['resume_skill_rewrite_slug'],
				'with_front'   => false,
				'hierarchical' => false,
			];

			register_taxonomy(
				self::TAX_SKILL,
				[ self::CPT_RESUME ],
				[
					'hierarchical'          => false,
					'update_count_callback' => '_update_post_term_count',
					'label'                 => $plural,
					'labels'                => \Cariera_Addons\Helpers::create_taxonomy_labels( $singular, $plural ),
					'show_ui'               => true,
					'query_var'             => true,
					'capabilities'          => [
						'manage_terms' => $admin_capability,
						'edit_terms'   => $admin_capability,
						'delete_terms' => $admin_capability,
						'assign_terms' => $admin_capability,
					],
					'rewrite'               => $rewrite,
					'show_in_rest'          => true,
					'rest_base'             => 'resume-skill',
				]
			);
		}
	}

	/**
	 * Registers resume meta fields.
	 *
	 * @since 0.9.5
	 */
	public function register_meta_fields() {
		$fields = self::get_resume_fields();

		foreach ( $fields as $meta_key => $field ) {
			register_meta(
				'post',
				$meta_key,
				[
					'type'              => $field['data_type'],
					'show_in_rest'      => $field['show_in_rest'],
					'description'       => $field['label'],
					'sanitize_callback' => $field['sanitize_callback'],
					'auth_callback'     => $field['auth_edit_callback'],
					'single'            => true,
					'object_subtype'    => self::CPT_RESUME,
				]
			);
		}
	}

	/**
	 * Returns configuration for custom fields on Resume posts.
	 *
	 * @since 0.9.5
	 */
	public static function get_resume_fields() {
		$default_field = [
			'label'              => null,
			'placeholder'        => null,
			'description'        => null,
			'priority'           => 10,
			'value'              => null,
			'default'            => null,
			'classes'            => [],
			'type'               => 'text',
			'data_type'          => 'string',
			'show_in_admin'      => true,
			'show_in_rest'       => false,
			'auth_edit_callback' => [ __CLASS__, 'auth_check_can_edit_resumes' ],
			'auth_view_callback' => null,
			'sanitize_callback'  => [ __CLASS__, 'sanitize_meta_field_based_on_input_type' ],
		];

		$fields = [
			'_candidate_title'    => [
				'label'         => esc_html__( 'Professional Title', 'cariera-addons' ),
				'placeholder'   => '',
				'description'   => '',
				'priority'      => 1,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_candidate_email'    => [
				'label'         => esc_html__( 'Contact Email', 'cariera-addons' ),
				'placeholder'   => esc_html__( 'you@yourdomain.com', 'cariera-addons' ),
				'description'   => '',
				'priority'      => 2,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_candidate_location' => [
				'label'         => esc_html__( 'Candidate Location', 'cariera-addons' ),
				'placeholder'   => esc_html__( 'e.g. "London, UK", "New York", "Houston, TX"', 'cariera-addons' ),
				'description'   => '',
				'priority'      => 3,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_candidate_photo'    => [
				'label'         => esc_html__( 'Photo', 'cariera-addons' ),
				'placeholder'   => esc_html__( 'URL to the candidate photo', 'cariera-addons' ),
				'type'          => 'file',
				'priority'      => 4,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_candidate_video'    => [
				'label'             => esc_html__( 'Video', 'cariera-addons' ),
				'placeholder'       => esc_html__( 'URL to the candidate video', 'cariera-addons' ),
				'type'              => 'text',
				'priority'          => 5,
				'data_type'         => 'string',
				'show_in_admin'     => true,
				'show_in_rest'      => true,
				'sanitize_callback' => [ 'WP_Job_Manager_Post_Types', 'sanitize_meta_field_url' ],
			],
			'_resume_file'        => [
				'label'         => esc_html__( 'Resume File', 'cariera-addons' ),
				'placeholder'   => esc_html__( 'URL to the candidate\'s resume file', 'cariera-addons' ),
				'type'          => 'file',
				'priority'      => 6,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_featured'           => [
				'label'              => esc_html__( 'Feature this Resume?', 'cariera-addons' ),
				'type'               => 'checkbox',
				'description'        => esc_html__( 'Featured resumes will be sticky during searches, and can be styled differently.', 'cariera-addons' ),
				'priority'           => 7,
				'data_type'          => 'integer',
				'show_in_admin'      => true,
				'show_in_rest'       => true,
				'auth_edit_callback' => [ __CLASS__, 'auth_check_can_manage_resumes' ],
			],
			'_resume_expires'     => [
				'label'              => esc_html__( 'Expires', 'cariera-addons' ),
				'placeholder'        => esc_html__( 'yyyy-mm-dd', 'cariera-addons' ),
				'priority'           => 8,
				'data_type'          => 'string',
				'show_in_admin'      => true,
				'show_in_rest'       => true,
				'auth_edit_callback' => [ __CLASS__, 'auth_check_can_manage_resumes' ],
				'auth_view_callback' => [ __CLASS__, 'auth_check_can_edit_resumes' ],
				'sanitize_callback'  => [ 'WP_Job_Manager_Post_Types', 'sanitize_meta_field_date' ],
			],
		];

		if ( ! get_option( 'resume_manager_enable_resume_upload' ) ) {
			unset( $fields['_resume_file'] );
		}

		/**
		 * Filters resume data fields.
		 *
		 * For the REST API, do not pass fields you don't want to be visible to the current visitor when `show_in_rest`
		 * is `true`. To add values and other data when generating the WP admin form, use filter
		 * `resume_manager_resume_wp_admin_fields` which should have `$post_id` in context.
		 *
		 * @since 0.9.5
		 */
		$fields = apply_filters( 'resume_manager_resume_fields', $fields );

		// Ensure default fields are set.
		foreach ( $fields as $key => $field ) {
			$fields[ $key ] = array_merge( $default_field, $field );
		}

		return $fields;
	}

	/**
	 * Checks if user can manage resumes.
	 *
	 * @since 0.9.5
	 *
	 * @param bool   $allowed   Whether the user can edit the resume meta.
	 * @param string $meta_key  The meta key.
	 * @param int    $post_id   Resume's post ID.
	 * @param int    $user_id   User ID.
	 */
	public static function auth_check_can_manage_resumes( $allowed, $meta_key, $post_id, $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return false;
		}

		return $user->has_cap( 'manage_resumes' );
	}

	/**
	 * Checks if user can edit resumes.
	 *
	 * @since 0.9.5
	 *
	 * @param bool   $allowed   Whether the user can edit the resume meta.
	 * @param string $meta_key  The meta key.
	 * @param int    $post_id   Resume's post ID.
	 * @param int    $user_id   User ID.
	 */
	public static function auth_check_can_edit_resumes( $allowed, $meta_key, $post_id, $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return false;
		}

		if ( empty( $post_id ) ) {
			return current_user_can( 'edit_posts' );
		}

		return resume_manager_user_can_edit_resume( $post_id );
	}

	/**
	 * Checks if user can edit other's resumes.
	 *
	 * @since 0.9.5
	 *
	 * @param bool   $allowed   Whether the user can edit the resume meta.
	 * @param string $meta_key  The meta key.
	 * @param int    $post_id   Resume's post ID.
	 * @param int    $user_id   User ID.
	 */
	public static function auth_check_can_edit_others_resumes( $allowed, $meta_key, $post_id, $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return false;
		}

		return $user->has_cap( 'edit_others_posts' );
	}

	/**
	 * Sanitize meta fields based on input type.
	 *
	 * @since 0.9.5
	 *
	 * @param mixed  $meta_value Value of meta field that needs sanitization.
	 * @param string $meta_key   Meta key that is being sanitized.
	 */
	public static function sanitize_meta_field_based_on_input_type( $meta_value, $meta_key ) {
		$fields = self::get_resume_fields();

		if ( is_string( $meta_value ) ) {
			$meta_value = trim( $meta_value );
		}

		$type = 'text';
		if ( isset( $fields[ $meta_key ] ) ) {
			$type = $fields[ $meta_key ]['type'];
		}

		if ( 'textarea' === $type || 'wp_editor' === $type ) {
			return wp_kses_post( wp_unslash( $meta_value ) );
		}

		if ( 'checkbox' === $type ) {
			if ( $meta_value && '0' !== $meta_value ) {
				return 1;
			}

			return 0;
		}

		if ( is_array( $meta_value ) ) {
			return array_filter( array_map( 'sanitize_text_field', $meta_value ) );
		}

		return sanitize_text_field( $meta_value );
	}

	/**
	 * Download resume file handler.
	 *
	 * @since 0.9.5
	 */
	public function download_resume_handler() {
		global $post, $is_IE;

		if ( empty( $_GET['download-resume'] ) ) {
			return;
		}

		$resume_id = absint( $_GET['download-resume'] );

		if ( $resume_id && resume_manager_user_can_view_resume( $resume_id ) && apply_filters( 'resume_manager_user_can_download_resume_file', true, $resume_id ) ) {
			$files = get_resume_attachments( $resume_id );

			if ( empty( $files['attachments'] ) ) {
				// This should never happen as the link to download the resume does not appear when there are no files.
				return;
			}

			$file_id   = ! empty( $_GET['file-id'] ) ? absint( $_GET['file-id'] ) : 0;
			$file_path = $files['attachments'][ $file_id ];

			$file_extension = strtolower( substr( strrchr( $file_path, '.' ), 1 ) );
			$ctype          = 'application/force-download';

			foreach ( get_allowed_mime_types() as $mime => $type ) {
				$mimes = explode( '|', $mime );
				if ( in_array( $file_extension, $mimes, true ) ) {
					$ctype = $type;
					break;
				}
			}

			// Start setting headers.
			if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
				@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- No output wanted during download.
			}

			if ( function_exists( 'apache_setenv' ) ) {
				@apache_setenv( 'no-gzip', 1 );
			}

			@session_write_close();
			@ini_set( 'zlib.output_compression', 'Off' );
			/**
			 * Prevents errors, for example: transfer closed with 3 bytes remaining to read
			 */
			@ob_end_clean(); // Clear the output buffer.

			if ( ob_get_level() ) {

				$levels = ob_get_level();

				for ( $i = 0; $i < $levels; $i++ ) {
					@ob_end_clean(); // Zip corruption fix.
				}
			}

			if ( $is_IE && is_ssl() ) {
				// IE bug prevents download via SSL when Cache Control and Pragma no-cache headers set.
				header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
				header( 'Cache-Control: private' );
			} else {
				nocache_headers();
			}

			$filename = basename( $file_path );

			if ( strstr( $filename, '?' ) ) {
				$filename = current( explode( '?', $filename ) );
			}

			header( 'X-Robots-Tag: noindex, nofollow', true );
			header( 'Content-Type: ' . $ctype );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
			header( 'Content-Transfer-Encoding: binary' );

			if ( $size = @filesize( $file_path ) ) {
				header( 'Content-Length: ' . $size );
			}

			$this->readfile_chunked( $file_path ) or wp_die( esc_html__( 'File not found', 'cariera-addons' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . esc_html__( 'Go to homepage', 'cariera-addons' ) . '</a>' );

			exit;
		}
	}

	/**
	 * Reads file in chunks so big downloads are possible without changing PHP.INI - http://codeigniter.com/wiki/Download_helper_for_large_files/
	 *
	 * @since 0.9.5
	 *
	 * @param string $file
	 * @param bool   $retbytes
	 *
	 * @todo Meaning of the return value? Last return is status of fclose?
	 */
	public static function readfile_chunked( $file, $retbytes = true ) {
		$chunksize = 1 * ( 1024 * 1024 );
		$buffer    = '';
		$cnt       = 0;

		$handle = @fopen( $file, 'r' );
		if ( $handle === false ) {
			return false;
		}

		while ( ! feof( $handle ) ) {
			$buffer = fread( $handle, $chunksize );
			echo $buffer;
			@ob_flush();
			@flush();

			if ( $retbytes ) {
				$cnt += strlen( $buffer );
			}
		}

		$status = fclose( $handle );

		if ( $retbytes && $status ) {
			return $cnt;
		}

		return $status;
	}

	/**
	 * Don't include resume name in Yoast page markup if the user isn't able to view the resume.
	 *
	 * @since 0.9.5
	 */
	public function maybe_add_yoast_filters() {
		$post = get_post();

		if ( ! is_null( $post ) ) {
			if ( self::CPT_RESUME === get_post_type( $post->ID ) && ! resume_manager_user_can_view_resume_name( $post->ID ) ) {
				add_filter( 'wpseo_title', '__return_empty_string' );
				add_filter( 'wpseo_opengraph_title', '__return_empty_string' );
				add_filter( 'wpseo_schema_graph_pieces', 'remove_webpage_from_schema', 11, 2 );
			}
		}
	}

	/**
	 * Change label
	 *
	 * @since   0.9.5
	 * @version 1.0.2
	 */
	public function pending_resumes() {
		global $menu;

		$plural          = esc_html__( 'Resumes', 'cariera-addons' );
		$count_resumes   = wp_count_posts( self::CPT_RESUME, 'readable' );
		$pending_resumes = $count_resumes->pending;

		foreach ( $menu as $key => $menu_item ) {
			if ( strpos( $menu_item[0], $plural ) === 0 ) {
				if ( $pending_resumes ) {
					// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Only way to add pending listing count.
					$menu[ $key ][0] .= " <span class='awaiting-mod update-plugins count-$pending_resumes'><span class='pending-count'>" . number_format_i18n( $pending_resumes ) . '</span></span>';
				}
				break;
			}
		}
	}

	/**
	 * Hide resume titles from users without access
	 *
	 * @since   0.9.5
	 * @version 1.0.6
	 *
	 * @param  string $title
	 * @param  int    $post_or_id
	 */
	public function resume_title( $title, $post_or_id = null ) {
		// Bail early if no post ID or not a resume.
		if ( ! $post_or_id || self::CPT_RESUME !== get_post_type( $post_or_id ) ) {
			return $title;
		}

		// Check if user can view the resume name.
		if ( resume_manager_user_can_view_resume_name( $post_or_id ) ) {
			return $title;
		}

		// Mask the title except the first word.
		$words        = explode( ' ', $title );
		$first_word   = array_shift( $words );
		$masked_words = array_map( fn( $word ) => str_repeat( '*', strlen( $word ) ), $words );

		$hidden_title = implode( ' ', array_merge( [ $first_word ], $masked_words ) );

		return apply_filters( 'resume_manager_hidden_resume_title', $hidden_title, $title, $post_or_id );
	}

	/**
	 * Add extra content when showing resumes
	 *
	 * @since 0.9.5
	 *
	 * @param string $content
	 */
	public function resume_content( $content ) {
		global $post;

		if ( ! is_singular( self::CPT_RESUME ) || ! in_the_loop() ) {
			return $content;
		}

		remove_filter( 'the_content', [ $this, 'resume_content' ] );

		if ( self::CPT_RESUME === $post->post_type ) {
			ob_start();

			get_job_manager_template_part( 'resumes/content-single', 'resume', 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );

			$content = ob_get_clean();
		}

		add_filter( 'the_content', [ $this, 'resume_content' ] );

		return $content;
	}

	/**
	 * Adds robots `noindex` meta tag to discourage search indexing.
	 *
	 * @since   0.9.5
	 * @version 1.1.0
	 */
	public function add_no_robots() {
		if ( ! is_single() ) {
			return;
		}

		$post = get_post();
		if ( ! $post || self::CPT_RESUME !== $post->post_type ) {
			return;
		}

		if ( function_exists( 'wp_robots_no_robots' ) ) {
			add_filter( 'wp_robots', 'wp_robots_no_robots' );
		} else {
			wp_no_robots(); // phpcs:ignore
		}
	}

	/**
	 * The application content when the application method is an email
	 *
	 * @since   0.9.5
	 * @version 0.9.7
	 */
	public function contact_details_email() {
		global $post;

		$email   = get_post_meta( $post->ID, '_candidate_email', true );
		$subject = sprintf(
			/* translators: 1: resume title, 2: site URL */
			__( 'Contact via the resume for "%1$s" on %2$s', 'cariera-addons' ),
			single_post_title( '', false ),
			home_url()
		);

		get_job_manager_template(
			'resumes/contact-details-email.php',
			[
				'email'   => $email,
				'subject' => $subject,
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}

	/**
	 * Setup event to hide a resume after X days
	 *
	 * @since 0.9.5
	 *
	 * @param object $post
	 */
	public function setup_autohide_cron( $post ) {
		if ( ! is_object( $post ) ) {
			$post = get_post( $post );
		}
		if ( self::CPT_RESUME !== $post->post_type ) {
			return;
		}

		add_post_meta( $post->ID, '_featured', 0, true );
		wp_clear_scheduled_hook( 'auto-hide-resume', [ $post->ID ] );

		$resume_manager_autohide = get_option( 'resume_manager_autohide' );

		if ( $resume_manager_autohide ) {
			wp_schedule_single_event( strtotime( "+{$resume_manager_autohide} day" ), 'auto-hide-resume', [ $post->ID ] );
		}
	}

	/**
	 * Hide a resume
	 *
	 * @since   0.9.5
	 * @version 0.9.7
	 *
	 * @param int $resume_id
	 */
	public function hide_resume( $resume_id ) {
		$resume = get_post( $resume_id );
		if ( 'publish' === $resume->post_status ) {
			$update_resume = [
				'ID'          => $resume_id,
				'post_status' => 'hidden',
			];
			wp_update_post( $update_resume );
			wp_clear_scheduled_hook( 'auto-hide-resume', [ $resume_id ] );
		}
	}

	/**
	 * Maybe set menu_order if the featured status of a resume is changed
	 *
	 * @since   0.9.5
	 * @version 1.1.0
	 *
	 * @param int    $meta_id
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $_meta_value
	 */
	public function maybe_update_menu_order( $meta_id, $object_id, $meta_key, $_meta_value ) {
		if ( '_featured' !== $meta_key || self::CPT_RESUME !== get_post_type( $object_id ) ) {
			return;
		}
		global $wpdb;

		if ( 1 === intval( $_meta_value ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Update post menu order without firing actions.
			$wpdb->update( $wpdb->posts, [ 'menu_order' => -1 ], [ 'ID' => $object_id ] );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Update post menu order without firing actions.
			$wpdb->update(
				$wpdb->posts,
				[ 'menu_order' => 0 ],
				[
					'ID'         => $object_id,
					'menu_order' => -1,
				]
			);
		}

		clean_post_cache( $object_id );
	}

	/**
	 * Fix post name when wp_update_post changes it
	 *
	 * @since 0.9.5
	 *
	 * @param array $data
	 * @param array $postarr
	 */
	public function fix_post_name( $data, $postarr ) {
		if ( self::CPT_RESUME === $data['post_type'] && 'pending' === $data['post_status'] && ! current_user_can( 'publish_posts' ) ) {
			$data['post_name'] = $postarr['post_name'];
		}
		return $data;
	}

	/**
	 * Set expirey date when resume status changes
	 *
	 * @since   0.9.5
	 * @version 1.1.0
	 *
	 * @param object $post
	 * TODO: improve like WPJM in the future
	 */
	public function set_expiry( $post ) {
		if ( self::CPT_RESUME !== $post->post_type ) {
			return;
		}

		// See if it is already set.
		if ( metadata_exists( 'post', $post->ID, '_resume_expires' ) ) {
			$expires = get_post_meta( $post->ID, '_resume_expires', true );
			if ( $expires && strtotime( $expires ) < current_time( 'timestamp' ) ) {
				update_post_meta( $post->ID, '_resume_expires', '' );
				$_POST['_resume_expires'] = '';
			}
			return;
		}

		// No metadata set so we can generate an expiry date.
		// See if the user has set the expiry manually.
		if ( ! empty( $_POST['_resume_expires'] ) ) {
			update_post_meta( $post->ID, '_resume_expires', date( 'Y-m-d', strtotime( sanitize_text_field( $_POST['_resume_expires'] ) ) ) );

			// No manual setting? Lets generate a date.
		} else {
			$expires = calculate_resume_expiry( $post->ID );
			update_post_meta( $post->ID, '_resume_expires', $expires );

			// In case we are saving a post, ensure post data is updated so the field is not overridden.
			if ( isset( $_POST['_resume_expires'] ) ) {
				$_POST['_resume_expires'] = $expires;
			}
		}
	}

	/**
	 * Expire resumes
	 *
	 * @since   0.9.5
	 * @version 0.9.10
	 *
	 * TODO: Check this and make it like WPJM via get_posts().
	 */
	public function check_for_expired_resumes() {
		global $wpdb;

		$post_type = self::CPT_RESUME;

		// Change status to expired.
		$resume_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT postmeta.post_id
				FROM {$wpdb->postmeta} AS postmeta
				LEFT JOIN {$wpdb->posts} AS posts ON postmeta.post_id = posts.ID
				WHERE postmeta.meta_key = '_resume_expires'
				AND postmeta.meta_value > 0
				AND postmeta.meta_value < %s
				AND posts.post_status = 'publish'
				AND posts.post_type = %s
				",
				date( 'Y-m-d', current_time( 'timestamp' ) ),
				$post_type
			)
		);

		if ( $resume_ids ) {
			foreach ( $resume_ids as $resume_id ) {
				$data                = [];
				$data['ID']          = $resume_id;
				$data['post_status'] = 'expired';
				wp_update_post( $data );
			}
		}

		// Delete old expired resumes.
		if ( apply_filters( 'resume_manager_delete_expired_resumes', true ) ) {
			$resume_ids = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT posts.ID FROM {$wpdb->posts} as posts
					WHERE posts.post_type = %s
					AND posts.post_modified < %s
					AND posts.post_status = 'expired'
					",
					$post_type,
					date( 'Y-m-d', strtotime( '-' . apply_filters( 'resume_manager_delete_expired_resumes_days', 30 ) . ' days', current_time( 'timestamp' ) ) )
				)
			);

			if ( $resume_ids ) {
				foreach ( $resume_ids as $resume_id ) {
					wp_trash_post( $resume_id );
				}
			}
		}
	}

	/**
	 * Flush the cache
	 *
	 * @since 0.9.5
	 *
	 * @param int $post_id
	 */
	public function flush_get_resume_listings_cache( $post_id ) {
		if ( self::CPT_RESUME === get_post_type( $post_id ) ) {
			\WP_Job_Manager_Cache_Helper::get_transient_version( 'get_resume_listings', true );
		}
	}

	/**
	 * Flush the cache
	 *
	 * @since 0.9.5
	 *
	 * @param string $action
	 */
	public function resume_manager_my_resume_do_action( $action ) {
		\WP_Job_Manager_Cache_Helper::get_transient_version( 'get_resume_listings', true );
	}

	/**
	 * Save Resume Skills to post meta.
	 *
	 * @since   0.9.5
	 * @version 0.9.10
	 *
	 * @param int $post_id
	 */
	public function save_postmeta( $post_id ) {
		if ( is_admin() ) {
			$term_list  = wp_get_post_terms( $post_id, self::TAX_SKILL );
			$term_names = wp_list_pluck( $term_list, 'name' );
			$new_terms  = implode( ',', $term_names );
			update_post_meta( $post_id, '_resume_skills', $new_terms );
		}
	}

	/**
	 * Check if a resume is editable.
	 *
	 * @since 0.9.7
	 *
	 * @param int $resume_id
	 */
	public static function resume_is_editable( $resume_id ) {
		$resume_is_editable = true;
		$post_status        = get_post_status( $resume_id );

		if (
			( 'publish' === $post_status && ! resume_manager_user_can_edit_published_submissions() )
			|| ( 'publish' !== $post_status && ! resume_manager_user_can_edit_pending_submissions() )
		) {
			$resume_is_editable = false;
		}

		/**
		 * Allows filtering on whether a resume can be edited after it has gone past the `preview` stage.
		 */
		return apply_filters( 'cariera_addons_resume_is_editable', $resume_is_editable, $resume_id );
	}

	/**
	 * Include admin files conditionally.
	 *
	 * @since   0.9.10
	 * @version 1.0.6
	 */
	public function conditional_includes() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		switch ( $screen->id ) {
			case 'options-permalink':
				\Cariera_Addons\Core\Resumes\Permalinks::instance();
				break;
		}
	}

	/**
	 * Get the permalink settings directly from the option.
	 *
	 * @since 0.9.10
	 */
	public static function get_raw_permalink_settings() {
		return (array) get_option( self::PERMALINK_OPTION_NAME, [] );
	}

	/**
	 * Retrieves permalink settings.
	 *
	 * @since 0.9.10
	 */
	public static function get_permalink_structure() {
		// Switch to the site's default locale, bypassing the active user's locale.
		if ( function_exists( 'switch_to_locale' ) && did_action( 'admin_init' ) ) {
			switch_to_locale( get_locale() );
		}

		$permalink_settings = self::get_raw_permalink_settings();

		// First-time activations will get this cleared on activation.
		if ( ! array_key_exists( 'cariera_addons_resume_archive', $permalink_settings ) ) {
			// Create entry to prevent future checks.
			$permalink_settings['cariera_addons_resume_archive'] = '';

			// This isn't the first activation and the theme supports it. Set the default to legacy value.
			$permalink_settings['cariera_addons_resume_archive'] = _x( 'resumes', 'Post type archive slug - resave permalinks after changing this', 'cariera-addons' );
			update_option( self::PERMALINK_OPTION_NAME, $permalink_settings );
		}

		$permalinks = wp_parse_args(
			$permalink_settings,
			[
				'cariera_addons_resume_base'     => '',
				'cariera_addons_resume_archive'  => '',
				'cariera_addons_resume_category' => '',
				'cariera_addons_resume_skill'    => '',
			]
		);

		// Ensure rewrite slugs are set. Use legacy translation options if not.
		$permalinks['resume_rewrite_slug']          = untrailingslashit( empty( $permalinks['cariera_addons_resume_base'] ) ? _x( 'resume', 'Resume permalink - resave permalinks after changing this', 'cariera-addons' ) : $permalinks['cariera_addons_resume_base'] );
		$permalinks['resume_archive_rewrite_slug']  = untrailingslashit( empty( $permalinks['cariera_addons_resume_archive'] ) ? 'resumes' : $permalinks['cariera_addons_resume_archive'] );
		$permalinks['resume_category_rewrite_slug'] = untrailingslashit( empty( $permalinks['cariera_addons_resume_category'] ) ? _x( 'resume-category', 'Resume category permalink - resave permalinks after changing this', 'cariera-addons' ) : $permalinks['cariera_addons_resume_category'] );
		$permalinks['resume_skill_rewrite_slug']    = untrailingslashit( empty( $permalinks['cariera_addons_resume_skill'] ) ? _x( 'resume-skill', 'Resume skill slug - resave permalinks after changing this', 'cariera-addons' ) : $permalinks['cariera_addons_resume_skill'] );

		// Restore the original locale.
		if ( function_exists( 'restore_current_locale' ) && did_action( 'admin_init' ) ) {
			restore_current_locale();
		}

		return $permalinks;
	}
}
