<?php

namespace Cariera_Addons\Core\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_shortcode( 'jobs_by_tag', [ $this, 'jobs_by_tag' ] );
		add_shortcode( 'job_tag_cloud', [ $this, 'job_tag_cloud' ] );

		// Change core output jobs shortcode.
		add_filter( 'job_manager_output_jobs_defaults', [ $this, 'output_jobs_defaults' ] );
		add_action( 'job_manager_job_filters_search_jobs_end', [ $this, 'show_tag_filter' ] );

		// Clear transients.
		add_action( 'set_object_terms', [ $this, 'clear_transient' ] );
		add_action( 'pending_to_expired', [ $this, 'clear_transient' ] );
		add_action( 'publish_post', [ $this, 'clear_transient' ] );

		// Job filters output & search results.
		add_action( 'job_manager_job_filters_end', [ $this, 'job_manager_job_filters_end' ] );
		add_filter( 'job_manager_get_listings_result', [ $this, 'job_manager_get_listings_result' ] );
		add_filter( 'job_manager_get_listings', [ $this, 'apply_tag_filter' ], 10, 2 );
	}

	/**
	 * Jobs by tag shortcode
	 *
	 * @since   0.9.1
	 * @version 0.9.4
	 *
	 * @param array $atts
	 */
	public function jobs_by_tag( $atts ) {
		global $job_manager;

		ob_start();

		$atts = shortcode_atts(
			[
				'per_page' => -1,
				'orderby'  => 'date',
				'order'    => 'desc',
				'tag'      => '',
				'tags'     => '',
			],
			$atts
		);

		$tags = array_filter( array_map( 'sanitize_title', explode( ',', $atts['tags'] ) ) );

		if ( $tag ) {
			$tags[] = sanitize_title( $atts['tag'] );
		}

		if ( ! $tags ) {
			return;
		}

		$args = [
			'post_type'           => \WP_Job_Manager_Post_Types::PT_LISTING,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => intval( $atts['per_page'] ),
			'orderby'             => sanitize_text_field( $atts['orderby'] ),
			'order'               => strtoupper( sanitize_text_field( $atts['order'] ) ),
			'tax_query'           => [
				[
					'taxonomy' => \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG,
					'field'    => 'slug',
					'terms'    => $tags,
				],
			],
		];

		if ( absint( get_option( 'job_manager_hide_filled_positions' ) ) === 1 ) {
			$args['meta_query'] = [
				[
					'key'     => '_filled',
					'value'   => '1',
					'compare' => '!=',
				],
			];
		}

		$jobs = new \WP_Query( apply_filters( 'job_manager_output_jobs_args', $args ) );

		if ( $jobs->have_posts() ) { ?>
			<ul class="job_listings">
				<?php
				while ( $jobs->have_posts() ) :
					$jobs->the_post();
					get_job_manager_template_part( 'content', 'job_listing' );
				endwhile;
				?>
			</ul>
			<?php
		} else {
			echo '<p>' . sprintf(
				/* translators: %s: tag list */
				__( 'No jobs found tagged with %s.', 'cariera-addons' ),
				esc_html( implode( ', ', $tags ) )
			) . '</p>';
		}

		wp_reset_postdata();

		return '<div class="job_listings">' . ob_get_clean() . '</div>';
	}

	/**
	 * Job Tag cloud shortcode
	 *
	 * @since 0.9.1
	 *
	 * @param array $atts
	 */
	public function job_tag_cloud( $atts ) {
		ob_start();

		$atts = shortcode_atts(
			[
				'smallest'                  => 8,
				'largest'                   => 22,
				'unit'                      => 'pt',
				'number'                    => 45,
				'format'                    => 'flat',
				'separator'                 => "\n",
				'orderby'                   => 'count',
				'order'                     => 'DESC',
				'exclude'                   => null,
				'include'                   => null,
				'link'                      => 'view',
				'taxonomy'                  => \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG,
				'echo'                      => false,
				'topic_count_text_callback' => [ $this, 'tag_cloud_text_callback' ],
			],
			$atts
		);

		$html = wp_tag_cloud( apply_filters( 'job_tag_cloud', $atts ) );

		if ( ! apply_filters( 'enable_job_tag_archives', get_option( 'job_manager_enable_tag_archive' ) ) ) {
			$html = str_replace( '</a>', '</span>', preg_replace( "/<a(.*)href='([^'']*)'(.*)>/", '<span$1$3>', $html ) );
		}

		return $html;
	}

	/**
	 * Change default args
	 *
	 * @since 0.9.1
	 *
	 * @param array $atts
	 */
	public function output_jobs_defaults( $atts ) {
		$atts['show_tags'] = true;
		return $atts;
	}

	/**
	 * Show the tag cloud
	 *
	 * @since   0.9.1
	 * @version 0.9.10
	 *
	 * @param array $shortcode_atts
	 */
	public function show_tag_filter( $shortcode_atts ) {
		if ( isset( $shortcode_atts['show_tags'] ) && ( false === $shortcode_atts['show_tags'] || 'false' == (string) $shortcode_atts['show_tags'] ) ) {
			return;
		}

		if ( 0 === intval( wp_count_terms( \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG ) ) ) {
			return;
		}

		wp_enqueue_script( 'cariera-addons-tag-filters' );

		echo '<div class="filter_wide filter_by_tag">' . esc_html__( 'Filter by tag:', 'cariera-addons' ) . ' <span class="filter_by_tag_cloud"></span></div>';
	}

	/**
	 * Clear transients
	 *
	 * @since 0.9.1
	 */
	public function clear_transient() {
		delete_transient( 'job_tag_q' );
	}

	/**
	 * Job Filters output
	 *
	 * @since 0.9.1
	 */
	public function job_manager_job_filters_end() {
		if ( is_tax( \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG ) ) {
			$queried_object = get_queried_object();
			echo '<input type="hidden" name="is_job_listing_tag" value="1" />';
			echo '<input type="hidden" name="job_tag[]" value="' . esc_attr( $queried_object->name ) . '" />';
		}
	}

	/**
	 * When updating jobs via ajax, get tag cloud
	 *
	 * @since   0.9.1
	 * @version 1.0.2
	 *
	 * @param array $results
	 */
	public function job_manager_get_listings_result( $results ) {
		// phpcs:ignore
		if ( isset( $_REQUEST['form_data'] ) ) {
			parse_str( wp_unslash( $_REQUEST['form_data'] ), $params ); // phpcs:ignore
			if ( isset( $params['is_job_listing_tag'] ) ) {
				return $results;
			}
		}

		$html              = '';
		$search_categories = isset( $_REQUEST['search_categories'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search_categories'] ) ) : ''; // phpcs:ignore

		if ( is_array( $search_categories ) ) {
			$search_categories = array_filter(
				array_map( 'sanitize_text_field', wp_unslash( $search_categories ) )
			);
		} else {
			$search_categories = array_filter(
				[ sanitize_text_field( stripslashes( $search_categories ) ) ]
			);
		}

		if ( $search_categories ) {
			// Get IDS.
			foreach ( $search_categories as $key => $search_category ) {
				if ( ! is_numeric( $search_category ) ) {
					$category_object           = get_term_by( 'slug', $search_category, 'job_listing_category' );
					$search_categories[ $key ] = $category_object->term_id;
				}
			}

			$transient_key = md5( implode( ',', $search_categories ) );
			$transient     = array_filter( (array) get_transient( 'job_tag_q' ) );

			if ( empty( $transient[ $transient_key ] ) ) {
				foreach ( $search_categories as $search_category ) {
					$search_categories = array_merge( $search_categories, get_term_children( $search_category, 'job_listing_category' ) );
				}
				$jobs_in_category = get_objects_in_term( array_unique( $search_categories ), 'job_listing_category' );
				$include_tags     = [];

				foreach ( $jobs_in_category as $job_id ) {
					$terms = wp_get_post_terms( $job_id, \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG, [ 'fields' => 'ids' ] );

					if ( is_array( $terms ) ) {
						$include_tags = array_merge( $include_tags, $terms );
					}

					$include_tags = array_unique( $include_tags );
				}

				$transient[ $transient_key ] = $include_tags;
				set_transient( 'job_tag_q', $transient, DAY_IN_SECONDS * 30 );
			} else {
				$include_tags = $transient[ $transient_key ];
			}
		} else {
			$include_tags = true;
		}

		if ( ! empty( $include_tags ) ) {
			$atts = [
				'smallest'                  => 1,
				'largest'                   => 2,
				'unit'                      => 'em',
				'number'                    => 25,
				'format'                    => 'flat',
				'separator'                 => "\n",
				'orderby'                   => 'count',
				'order'                     => 'DESC',
				'exclude'                   => null,
				'link'                      => 'view',
				'taxonomy'                  => \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG,
				'echo'                      => false,
				'topic_count_text_callback' => [ $this, 'tag_cloud_text_callback' ],
				'include'                   => is_array( $include_tags ) ? implode( ',', $include_tags ) : null,
			];
			$html = wp_tag_cloud( apply_filters( 'job_filter_tag_cloud', $atts ) );
			$html = $html ?? '';
			$html = preg_replace( "/<a(.*)href='([^'']*)'(.*)>/", '<a href="#"$1$3>', $html );
		}

		$results['tag_filter'] = $html;

		return $results;
	}

	/**
	 * Filter by tag
	 *
	 * @since   0.9.1
	 * @version 1.0.2
	 *
	 * @param array $query_args
	 * @param array $args
	 */
	public function apply_tag_filter( $query_args, $args ) {
		// phpcs:ignore
		if ( isset( $_REQUEST['form_data'] ) ) {
			$params = [];

			// phpcs:ignore
			parse_str( $_REQUEST['form_data'], $params );

			if ( isset( $params['job_tag'] ) ) {
				$tags      = array_filter( $params['job_tag'] );
				$tag_array = [];

				foreach ( $tags as $tag ) {
					$tag         = get_term_by( 'name', $tag, \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG );
					$tag_array[] = $tag->slug;
				}

				$query_args['tax_query'][] = [
					'taxonomy' => \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG,
					'field'    => 'slug',
					'terms'    => $tag_array,
					'operator' => 'any' === get_option( 'job_manager_tags_filter_type', 'any' ) ? 'IN' : 'AND',
				];

				add_filter( 'job_manager_get_listings_custom_filter', '__return_true' );
				add_filter( 'job_manager_get_listings_custom_filter_text', [ $this, 'apply_tag_filter_text' ] );
				add_filter( 'job_manager_get_listings_custom_filter_rss_args', [ $this, 'apply_tag_filter_rss' ] );
			}
		} elseif ( ! empty( $args['search_tags'] ) ) {
			$query_args['tax_query'][] = [
				'taxonomy' => \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG,
				'field'    => 'slug',
				'terms'    => $args['search_tags'],
				'operator' => 'any' === get_option( 'job_manager_tags_filter_type', 'any' ) ? 'IN' : 'AND',
			];
		}

		return $query_args;
	}

	/**
	 * Append 'showing' text
	 *
	 * @since   0.9.1
	 * @version 1.0.2
	 *
	 * @param string $text
	 */
	public function apply_tag_filter_text( $text ) {
		$params = [];
		parse_str( $_REQUEST['form_data'], $params ); // phpcs:ignore

		$text .= ' ' . esc_html__( 'tagged', 'cariera-addons' ) . ' &quot;' . implode( '&quot;, &quot;', array_filter( $params['job_tag'] ) ) . '&quot;';

		return $text;
	}

	/**
	 * Apply tag filter to RSS feed
	 *
	 * @since   0.9.1
	 * @version 1.0.2
	 *
	 * @param array $args
	 */
	public function apply_tag_filter_rss( $args ) {
		$params = [];
		parse_str( $_REQUEST['form_data'], $params ); // phpcs:ignore

		$args['job_tags'] = implode( ',', array_filter( $params['job_tag'] ) );

		return $args;
	}

	/**
	 * Tag cloud text callback
	 *
	 * @since   0.9.1
	 * @version 0.9.4
	 *
	 * @param int $count
	 */
	public function tag_cloud_text_callback( $count ) {
		$count_i18n = number_format_i18n( $count );

		return sprintf(
			// Translators: %s is the number of jobs in the tag cloud.
			_n( '%s job', '%s jobs', $count, 'cariera-addons' ),
			$count_i18n
		);
	}
}
