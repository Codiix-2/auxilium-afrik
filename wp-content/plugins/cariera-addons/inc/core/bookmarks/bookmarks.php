<?php

namespace Cariera_Addons\Core\Bookmarks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bookmarks {

	use \Cariera_Addons\Src\Traits\Singleton;

	const TABLE = 'job_manager_bookmarks';

	/**
	 * Supported post types for bookmarks.
	 *
	 * @var array
	 */
	private $supported_post_types = [ 'job_listing', 'resume', 'company', 'cariera_event' ];

	/**
	 * Constructor
	 */
	public function __construct() {
		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		if ( class_exists( 'WP_Job_Manager_Bookmarks' ) ) {
			return;
		}

		$this->init_plugin();

		// Set up startup actions.
		add_action( 'job_manager_shortcodes', [ $this, 'maybe_add_bookmark_shortcodes' ] );

		// User deletion.
		add_action( 'delete_user', [ $this, 'remove_user_bookmarks' ], 10, 2 );

		// Cariera before listing split view.
		add_action( 'cariera_listing_split_view_before', [ $this, 'enqueue_bookmark_script' ] );
	}

	/**
	 * Required notices when WPJM Bookmarks is installed and activated.
	 *
	 * @since 0.9.4
	 */
	public function required_notices() {
		if ( class_exists( 'WP_Job_Manager_Bookmarks' ) ) {
			echo '<div class="error">';
			echo '<p>' . wp_kses_post( __( 'Please deactivate <strong>WP Job Manager Bookmarks</strong> to enable the <strong>Cariera Addons Bookmarks</strong> feature.', 'cariera-addons' ) ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Init plugin
	 *
	 * @since 0.9.0
	 */
	public function init_plugin() {
		add_action( 'wp', [ $this, 'bookmark_handler' ] );

		// Add Bookmark templates.
		add_action( 'cariera_bookmark_hook', [ $this, 'bookmark_trigger' ], 10 );
		add_action( 'cariera_bookmark_hook', [ $this, 'bookmark_popup' ], 11 );
		add_action( 'cariera_company_bookmarks', [ $this, 'bookmark_trigger' ], 10 );
		add_action( 'cariera_company_bookmarks', [ $this, 'bookmark_popup' ], 11 );
		add_action( 'cariera_events_single_event_info_end', [ $this, 'bookmark_trigger' ], 10 );
		add_action( 'cariera_events_single_event_info_end', [ $this, 'bookmark_popup' ], 11 );
		add_action( 'cariera_addons_bookmark_popup_form', [ $this, 'bookmark_form' ] );

		// Shortcode.
		add_shortcode( 'my_bookmarks', [ $this, 'my_bookmarks' ] );

		// Filters.
		add_filter( 'post_class', [ $this, 'already_bookmarked_post_class' ], 20, 2 );
		add_filter( 'job_manager_job_stats_summary', [ $this, 'job_stats_summary' ], 10, 2 );
	}

	/**
	 * Handle the bookmark form
	 *
	 * @since   0.9.0
	 * @version 1.0.2
	 */
	public function bookmark_handler() {
		global $wpdb;

		if ( ! is_user_logged_in() ) {
			return;
		}

		$response      = null;
		$table         = $wpdb->prefix . self::TABLE;
		$current_user  = get_current_user_id();
		$allowed_types = apply_filters( 'cariera_addons_bookmark_post_types', [ 'job_listing', 'resume', 'company', 'cariera_event' ] );

		// Add or update bookmark.
		if ( isset( $_POST['submit_bookmark'], $_POST['bookmark_post_id'], $_POST['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'update_bookmark' ) ) { // phpcs:ignore
			$post_id = absint( $_POST['bookmark_post_id'] );

			if ( ! $this->can_bookmark( $post_id ) ) {
				$response = [
					'error_code' => 400,
					'error'      => esc_html__( 'Bad request', 'cariera-addons' ),
				];
			} else {
				$note = wp_kses_post( wp_unslash( $_POST['bookmark_notes'] ?? '' ) );

				if ( in_array( get_post_type( $post_id ), $allowed_types, true ) ) {
					if ( ! $this->is_bookmarked( $post_id ) ) {
						$wpdb->insert(
							$table,
							[
								'user_id'       => $current_user,
								'post_id'       => $post_id,
								'bookmark_note' => $note,
								'date_created'  => current_time( 'mysql' ),
							]
						);
						do_action( 'cariera_addons_bookmark_added', $post_id, $current_user, $note );
					} else {
						$wpdb->update(
							$table,
							[
								'bookmark_note' => $note,
							],
							[
								'post_id' => $post_id,
								'user_id' => $current_user,
							]
						);
						do_action( 'cariera_addons_bookmark_updated', $post_id, $current_user, $note );
					}

					delete_transient( 'bookmark_count_' . $post_id );

					$response = [
						'success' => true,
						'note'    => $note,
					];
				}
			}
		}

		// Remove bookmark.
		if ( isset( $_GET['remove_bookmark'], $_GET['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'remove_bookmark' ) ) { // phpcs:ignore
			$post_id = absint( $_GET['remove_bookmark'] );

			// phpcs:ignore
			$wpdb->delete(
				$table,
				[
					'post_id' => $post_id,
					'user_id' => $current_user,
				]
			);

			delete_transient( 'bookmark_count_' . $post_id );

			do_action( 'cariera_addons_bookmark_removed', $post_id, $current_user );

			$response = [ 'success' => true ];
		} elseif ( isset( $_GET['remove_bookmark'] ) ) {
			$response = [
				'error_code' => 400,
				'error'      => esc_html__( 'Bad request', 'cariera-addons' ),
			];
		}

		// Bail early if no action taken.
		if ( null === $response ) {
			return;
		}

		// Handle AJAX and redirect fallback.
		if ( ! defined( 'DOING_AJAX' ) && ! empty( $_REQUEST['wpjm-ajax'] ) ) {
			define( 'DOING_AJAX', true );
		}

		if ( wp_doing_ajax() ) {
			wp_send_json( $response, ! empty( $response['error_code'] ) ? $response['error_code'] : 200 );
		} else {
			wp_safe_redirect( remove_query_arg( [ 'submit_bookmark', 'remove_bookmark', '_wpnonce', 'wpjm-ajax' ] ) );
			exit;
		}
	}

	/**
	 * Bookmark button trigger
	 *
	 * @since   0.9.0
	 * @version 0.9.9
	 */
	public function bookmark_trigger() {
		global $job_preview, $resume_preview, $company_preview, $event_preview;

		if ( $job_preview || $resume_preview || $company_preview || $event_preview ) {
			return;
		}

		get_job_manager_template(
			'bookmarks/bookmark-trigger.php',
			[
				'is_bookmarked' => $this->is_bookmarked( get_the_ID() ),
				'post_type'     => get_post_type( get_the_ID() ),
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}

	/**
	 * Bookmark Popup
	 *
	 * @since   0.9.0
	 * @version 0.9.9
	 */
	public function bookmark_popup() {
		global $job_preview, $resume_preview, $company_preview, $event_preview;

		if ( $job_preview || $resume_preview || $company_preview || $event_preview ) {
			return;
		}

		get_job_manager_template( 'bookmarks/bookmark-popup.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Show the bookmark form
	 *
	 * @since   0.9.0
	 * @version 1.0.2
	 */
	public function bookmark_form() {
		global $post, $job_preview, $resume_preview, $company_preview, $event_preview;

		if ( $job_preview || $resume_preview || $company_preview || $event_preview ) {
			return;
		}

		ob_start();

		$post_type = get_post_type_object( $post->post_type );

		if ( ! is_user_logged_in() ) {
			get_job_manager_template(
				'bookmarks/logged-out-bookmark-form.php',
				[
					'post_type' => $post_type,
					'post'      => $post,
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);
		} else {
			$is_bookmarked = $this->is_bookmarked( $post->ID );

			if ( $is_bookmarked ) {
				$note = $this->get_note( $post->ID );
			} else {
				$note = '';
			}

			wp_enqueue_script( 'cariera-addons-bookmarks' );
			wp_enqueue_style( 'cariera-addons-bookmarks' );

			get_job_manager_template(
				'bookmarks/bookmark-form.php',
				[
					'post_type'     => $post_type,
					'post'          => $post,
					'is_bookmarked' => $is_bookmarked,
					'note'          => $note,
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);
		}

		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get a user's bookmarks
	 *
	 * @since   0.9.0
	 * @version 0.9.10
	 *
	 * @param  integer $user_id
	 * @param  integer $limit
	 * @param  integer $offset
	 * @param  string  $orderby_key
	 * @param  string  $order_dir
	 * @return array|object
	 */
	public function get_user_bookmarks( $user_id = 0, $limit = 0, $offset = 0, $orderby_key = 'date', $order_dir = 'ASC' ) {
		global $wpdb;

		if ( ! $user_id ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			} else {
				return false;
			}
		}

		// Define allowed ordering options.
		$order_options = [
			'date'       => '`bm`.`date_created`',
			'post_title' => '`p`.`post_title`',
			'post_date'  => '`p`.`post_date`',
		];

		// Validate order key and direction.
		$order_by  = $order_options[ $orderby_key ] ?? $order_options['date'];
		$order_dir = in_array( strtoupper( $order_dir ), [ 'ASC', 'DESC' ], true ) ? strtoupper( $order_dir ) : 'ASC';

		// Construct table names with proper escaping.
		$bookmark_table = esc_sql( $wpdb->prefix . self::TABLE );

		// Base query.
		$sql = "
			SELECT %s `bm`.* 
			FROM `{$bookmark_table}` `bm`
			LEFT JOIN `{$wpdb->posts}` `p` ON `bm`.`post_id` = `p`.`ID`
			WHERE `bm`.`user_id` = %%d AND `p`.`post_status` = 'publish'
			ORDER BY {$order_by} {$order_dir}
		";

		// Paginated.
		if ( $limit > 0 ) {
			$sql_query = sprintf( $sql, 'SQL_CALC_FOUND_ROWS' ) . ' LIMIT %d, %d';
			$prepared  = $wpdb->prepare( $sql_query, $user_id, $offset, $limit ); // phpcs:ignore
			$results   = $wpdb->get_results( $prepared ); // phpcs:ignore
			$total     = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // phpcs:ignore

			return (object) [
				'max_found_rows' => $total,
				'max_num_pages'  => ( $limit > 0 ) ? (int) ceil( $total / $limit ) : 1,
				'results'        => $results,
			];
		}

		// Non-paginated.
		$sql_query = sprintf( $sql, '' );
		return $wpdb->get_results( $wpdb->prepare( $sql_query, $user_id ) ); // phpcs:ignore
	}

	/**
	 * User bookmarks shortcode
	 *
	 * @since   0.9.0
	 * @version 1.0.5
	 *
	 * @param array $atts
	 */
	public function my_bookmarks( $atts ) {
		if ( ! is_user_logged_in() ) {
			ob_start();
			get_job_manager_template( 'bookmarks/my-bookmarks-login.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
			return ob_get_clean();
		}

		$atts = shortcode_atts(
			[
				'posts_per_page' => '25',
				'orderby'        => 'date', // Options: date, post_date, post_title.
				'order'          => 'DESC',
			],
			$atts
		);

		ob_start();

		wp_enqueue_script( 'cariera-addons-bookmarks-dashboard' );
		wp_enqueue_style( 'cariera-addons-bookmarks-dashboard' );

		if ( $atts['posts_per_page'] >= 0 ) {
			$bookmarks = $this->get_user_bookmarks( get_current_user_id(), $atts['posts_per_page'], ( max( 1, get_query_var( 'paged' ) ) - 1 ) * $atts['posts_per_page'], $atts['orderby'], $atts['order'] );

			get_job_manager_template(
				'bookmarks/my-bookmarks.php',
				[
					'bookmarks'     => $bookmarks->results,
					'max_num_pages' => $bookmarks->max_num_pages,
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);
		} else {
			$bookmarks = $this->get_user_bookmarks( get_current_user_id(), 0, 0, $atts['orderby'], $atts['order'] );

			get_job_manager_template(
				'bookmarks/my-bookmarks.php',
				[
					'bookmarks'     => $bookmarks,
					'max_num_pages' => 1,
				],
				'cariera-addons',
				CARIERA_ADDONS_PATH . '/templates/'
			);
		}

		return ob_get_clean();
	}

	/**
	 * Add note that the listing is bookmarked
	 *
	 * @since   0.9.0
	 * @version 1.0.6
	 *
	 * @param array $classes
	 */
	public function already_bookmarked_post_class( $classes ) {
		global $post;

		if ( is_admin() || ! $post ) {
			return $classes;
		}

		if ( in_array( get_post_type( $post ), $this->supported_post_types, true ) && is_user_logged_in() && $this->is_bookmarked( $post->ID ) ) {
			$classes[] = 'listing-bookmarked';
		}

		return $classes;
	}

	/**
	 * Add bookmark count to job listing overlay stats section.
	 *
	 * @since 0.9.0
	 *
	 * @param array    $stats
	 * @param \WP_Post $job
	 *
	 * @return array
	 */
	public function job_stats_summary( $stats, $job ) {

		if ( ! empty( $stats['interest']['stats'] ) && ! empty( $job->ID ) ) {
			$stats['interest']['stats'][] =
				[
					'icon'  => 'url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' fill=\'none\' viewBox=\'0 0 24 24\'%3e%3cpath stroke=\'black\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M16 3H8a2 2 0 0 0-2 2v16l6-3 6 3V5a2 2 0 0 0-2-2Z\'/%3e%3c/svg%3e")',
					'label' => esc_html__( 'Bookmarks', 'cariera-addons' ),
					'value' => $this->get_job_bookmark_count( $job->ID ),
				];
		}

		return $stats;
	}

	/**
	 * Count the number of bookmarks for a job listing.
	 *
	 * @since   0.9.0
	 * @version 0.9.10
	 *
	 * @param int $job_id
	 *
	 * @return int
	 */
	public function get_job_bookmark_count( $job_id ) {
		global $wpdb;

		$table       = esc_sql( $wpdb->prefix . self::TABLE );
		$cache_key   = 'wpjm_stats_bookmark_count_' . $job_id;
		$cache_group = 'wpjm_bookmarks';

		$bookmark_count = wp_cache_get( $cache_key, $cache_group );

		if ( empty( $bookmark_count ) ) {

			//phpcs:ignore
			$bookmark_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( id ) FROM `{$table}` WHERE post_id = %d", $job_id ) );

			wp_cache_set( $cache_key, $bookmark_count, $cache_group, HOUR_IN_SECONDS );
		}

		return absint( $bookmark_count );
	}

	/**
	 * Adds the shortcode to the job manager shortcodes list if not there.
	 *
	 * @since 0.9.0
	 *
	 * @param array $shortcode_list
	 */
	public function maybe_add_bookmark_shortcodes( $shortcode_list = [] ) {
		if ( ! in_array( 'my_bookmarks', $shortcode_list, true ) ) {
			$shortcode_list[] = 'my_bookmarks';
		}

		return $shortcode_list;
	}

	/**
	 * Remove user bookmarks on user deletion.
	 * Hooked into `delete_user`.
	 *
	 * @since   0.9.0
	 * @version 0.9.10
	 *
	 * @param int $user_id  User ID to remove bookmarks.
	 * @param int $reassign User ID to remove bookmarks.
	 */
	public function remove_user_bookmarks( $user_id, $reassign = null ) {
		global $wpdb;

		$table = esc_sql( $wpdb->prefix . self::TABLE );

		if ( null !== $reassign ) {
			// Reassign bookmarks.
			$wpdb->update(
				$table,
				[
					'user_id' => $reassign,
				],
				[
					'user_id' => $user_id,
				]
			);

			return;
		}

		// Get post_ids to be removed from user.
		$sql_query = $wpdb->prepare(
			"SELECT post_id FROM `{$table}` " .
			'WHERE `user_id` = %d',
			$user_id
		);

		$results = $wpdb->get_results( $sql_query );

		// Delete user bookmarks.
		$wpdb->delete(
			$table,
			[
				'user_id' => $user_id,
			]
		);

		// Reset bookmark counters.
		foreach ( $results as $result ) {
			delete_transient( 'bookmark_count_' . $result->post_id );
		}
	}

	/**
	 * See if a post is bookmarked by ID
	 *
	 * @since   0.9.0
	 * @version 0.9.11
	 *
	 * @param int $post_id
	 */
	public function is_bookmarked( $post_id ) {
		global $wpdb;

		$user_id = get_current_user_id();

		if ( ! $user_id || ! is_numeric( $post_id ) ) {
			return false;
		}

		// Cache key: user + post.
		static $cache = [];

		$cache_key = $user_id . '_' . (int) $post_id;

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		// Build the SQL manually, fully, with escaped table name.
		$table_name = esc_sql( $wpdb->prefix . self::TABLE );

		// Write the SQL before prepare().
		$sql = "SELECT 1 FROM `{$table_name}` WHERE post_id = %d AND user_id = %d LIMIT 1";

		// Only values go into prepare().
		$prepared_sql = $wpdb->prepare( $sql, $post_id, $user_id ); // phpcs:ignore

		$result = (bool) $wpdb->get_var( $prepared_sql ); // phpcs:ignore

		// Store in static cache for this request.
		$cache[ $cache_key ] = $result;

		return $result;
	}

	/**
	 * See if a user can bookmark a post.
	 *
	 * @since   0.9.0
	 * @version 0.9.10
	 *
	 * @param int $post_id
	 */
	public function can_bookmark( $post_id ) {
		if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== get_post_type( $post_id ) ) {
			return true;
		}

		if ( function_exists( 'resume_manager_user_can_view_resume' ) ) {
			return resume_manager_user_can_view_resume( $post_id );
		}

		return false;
	}

	/**
	 * Get the total number of bookmarks for a post by ID
	 *
	 * @since   0.9.0
	 * @version 0.9.10
	 *
	 * @param int $post_id
	 */
	public function bookmark_count( $post_id ) {
		global $wpdb;

		$table         = esc_sql( $wpdb->prefix . self::TABLE );
		$transient_key = 'bookmark_count_' . $post_id;

		// Try to get the cached bookmark count.
		$bookmark_count = get_transient( $transient_key );

		if ( false === $bookmark_count ) {
			// If no cache found, query the database for the count of bookmarks for this post.
			$bookmark_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(id) FROM `{$table}` WHERE post_id = %d",
					$post_id
				)
			);

			// Cache the result for one year.
			set_transient( $transient_key, $bookmark_count, YEAR_IN_SECONDS );
		}

		return absint( $bookmark_count );
	}

	/**
	 * Get a bookmark's note
	 *
	 * @since   0.9.0
	 * @version 0.9.10
	 *
	 * @param int $post_id
	 */
	public function get_note( $post_id ) {
		global $wpdb;

		$table = esc_sql( $wpdb->prefix . self::TABLE );

		// phpcs:ignore
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT bookmark_note FROM `{$table}` WHERE post_id = %d AND user_id = %d",
				$post_id,
				get_current_user_id()
			)
		);
	}

	/**
	 * Enqueue bookmark script for Cariera before listing split view.
	 *
	 * @since 0.9.11
	 */
	public function enqueue_bookmark_script() {
		wp_enqueue_script( 'cariera-addons-bookmarks' );
		wp_enqueue_style( 'cariera-addons-bookmarks' );
	}
}
