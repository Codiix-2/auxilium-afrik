<?php

namespace Cariera_Addons\Core\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tags {

	use \Cariera_Addons\Src\Traits\Singleton;


	const TAX_JOB_TAG = 'job_listing_tag';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		if ( class_exists( 'WP_Job_Manager_Job_Tags' ) ) {
			return;
		}

		// Init main functions when plugin loads.
		$this->init_plugin();
	}

	/**
	 * Required notices when WPJM Job Tags is installed and activated.
	 *
	 * @since   0.9.4
	 * @version 0.9.9
	 */
	public function required_notices() {
		if ( class_exists( 'WP_Job_Manager_Job_Tags' ) ) {
			echo '<div class="error">';
			echo '<p>' . wp_kses_post( __( 'Please deactivate <strong>WP Job Manager Job Tags</strong> to enable the <strong>Cariera Addons Job Tags</strong> feature.', 'cariera-addons' ) ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Init plugin
	 *
	 * @since 0.9.1
	 */
	public function init_plugin() {
		add_action( 'init', [ $this, 'register_taxonomy' ] );

		// Settings.
		add_filter( 'job_manager_settings', [ $this, 'settings' ] );

		// Add column to admin.
		add_filter( 'manage_edit-job_listing_columns', [ $this, 'columns' ], 20 );
		add_action( 'manage_job_listing_posts_custom_column', [ $this, 'custom_columns' ], 2 );

		// Job Submission.
		add_filter( 'submit_job_form_fields', [ $this, 'job_tag_field' ] );
		add_filter( 'submit_job_form_validate_fields', [ $this, 'validate_job_tag_field' ], 10, 3 );
		add_action( 'job_manager_update_job_data', [ $this, 'save_job_tag_field' ], 10, 2 );
		add_action( 'submit_job_form_fields_get_job_data', [ $this, 'get_job_tag_field_data' ], 10, 2 );

		// Output.
		add_filter( 'the_job_description', [ $this, 'display_tags' ] );

		// Format job tag.
		add_filter( 'format_job_tag', [ $this, 'format_job_tag' ] );

		// Feeds.
		add_filter( 'job_feed_args', [ $this, 'job_feed_args' ] );

		// Shortcodes.
		new \Cariera_Addons\Core\Tags\Shortcodes();
	}

	/**
	 * Register Taxonomy
	 *
	 * @since   0.9.1
	 * @version 0.9.9
	 */
	public function register_taxonomy() {
		if ( taxonomy_exists( self::TAX_JOB_TAG ) ) {
			return;
		}

		$singular         = esc_html__( 'Job Tag', 'cariera-addons' );
		$plural           = esc_html__( 'Job Tags', 'cariera-addons' );
		$admin_capability = 'manage_job_listings';

		register_taxonomy(
			self::TAX_JOB_TAG,
			[ \WP_Job_Manager_Post_Types::PT_LISTING ],
			[
				'hierarchical'          => false,
				'update_count_callback' => '_update_post_term_count',
				'label'                 => $plural,
				'labels'                => \Cariera_Addons\Helpers::create_taxonomy_labels( $singular, $plural ),
				'show_ui'               => true,
				'query_var'             => apply_filters( 'enable_job_tag_archives', get_option( 'job_manager_enable_tag_archive' ) ),
				'capabilities'          => [
					'manage_terms' => $admin_capability,
					'edit_terms'   => $admin_capability,
					'delete_terms' => $admin_capability,
					'assign_terms' => $admin_capability,
				],
				'show_in_rest'          => true,
				'rewrite'               => [
					'slug'       => _x( 'job-tag', 'permalink', 'cariera-addons' ),
					'with_front' => false,
				],
			]
		);
	}

	/**
	 * Add Job Tags Settings
	 *
	 * @since   0.9.1
	 * @version 0.9.10
	 *
	 * @param array $settings
	 */
	public function settings( $settings = [] ) {
		$settings['job_listings'][1][]   = [
			'name'     => 'job_manager_enable_tag_archive',
			'std'      => '',
			'label'    => esc_html__( 'Tag Archives', 'cariera-addons' ),
			'cb_label' => esc_html__( 'Enable Tag Archives', 'cariera-addons' ),
			'desc'     => wp_kses_post( __( 'Enabling tag archives will make job tags (inside jobs and tag clouds) link through to an archive of all jobs with said tag. Please note, tag archives will look like your post archives unless you create a special template to handle the display of job listings called <code>taxonomy-job_listing_tag.php</code> inside your theme. See <a href="http://codex.wordpress.org/Template_Hierarchy#Custom_Taxonomies_display">Template Hierarchy</a> for more information.', 'cariera-addons' ) ),
			'type'     => 'checkbox',
			'track'    => 'bool',
		];
		$settings['job_listings'][1][]   = [
			'name'    => 'job_manager_tags_filter_type',
			'std'     => 'any',
			'label'   => esc_html__( 'Tags Filter Type', 'cariera-addons' ),
			'desc'    => esc_html__( 'Determines how jobs are queried when selecting tags.', 'cariera-addons' ),
			'type'    => 'select',
			'options' => [
				'any' => esc_html__( 'Jobs will be shown if within ANY chosen tag', 'cariera-addons' ),
				'all' => esc_html__( 'Jobs will be shown if within ALL chosen tags', 'cariera-addons' ),
			],
			'track'   => 'value',
		];
		$settings['job_submission'][1][] = [
			'name'  => 'job_manager_max_tags',
			'std'   => '',
			'label' => esc_html__( 'Maximum Job Tags', 'cariera-addons' ),
			'desc'  => esc_html__( 'Enter the number of tags per job submission you wish to allow, or leave blank for unlimited tags.', 'cariera-addons' ),
			'type'  => 'input',
			'track' => 'is-default',
		];
		$settings['job_submission'][1][] = [
			'name'    => 'job_manager_tag_input',
			'std'     => '',
			'label'   => esc_html__( 'Tag Input', 'cariera-addons' ),
			'options' => [
				''            => 'Text box (comma select tags)',
				'multiselect' => 'Multiselect (list of pre-defined tags)',
				'checkboxes'  => 'Checkboxes (list of pre-defined tags)',
			],
			'desc'    => '',
			'type'    => 'select',
			'track'   => 'value',
		];
		return $settings;
	}

	/**
	 * Add a job tag column to admin
	 *
	 * @since   0.9.1
	 * @version 0.9.4
	 *
	 * @param array $columns
	 */
	public function columns( $columns ) {
		$new_columns = [];

		foreach ( $columns as $key => $value ) {
			if ( 'job_listing_category' === $key ) {
				$new_columns['job_tags'] = esc_html__( 'Tags', 'cariera-addons' );
			}

			$new_columns[ $key ] = $value;
		}

		return $new_columns;
	}

	/**
	 * Handle display of new column
	 *
	 * @since   0.9.1
	 * @version 0.9.4
	 *
	 * @param string $column
	 */
	public function custom_columns( $column ) {
		global $post;

		if ( 'job_tags' !== $column ) {
			return;
		}

		$terms = $this->get_job_tag_list( $post->ID );

		if ( empty( $terms ) ) {
			echo '<span class="na">&ndash;</span>';
		} else {
			echo wp_kses_post( $terms );
		}
	}

	/**
	 * Gets a formatted list of job tags for a post ID
	 *
	 * @since   0.9.1
	 * @version 0.9.10
	 *
	 * @param int $job_id
	 */
	public function get_job_tag_list( $job_id ) {
		$separator = apply_filters( 'job_manager_tag_list_sep', ', ' );
		$terms     = get_the_term_list( $job_id, self::TAX_JOB_TAG, '', $separator, '' );

		$tag_archives_enabled = apply_filters( 'enable_job_tag_archives', get_option( 'job_manager_enable_tag_archive' ) );

		return $tag_archives_enabled ? $terms : wp_strip_all_tags( $terms );
	}

	/**
	 * Add the job tag field to the submission form
	 *
	 * @since   0.9.1
	 * @version 0.9.10
	 *
	 * @param array $fields
	 */
	public function job_tag_field( $fields ) {
		$max_tags = get_option( 'job_manager_max_tags' );
		$note     = '';

		if ( ! empty( $max_tags ) ) {
			$note = ' ' . sprintf(
				/* translators: %d: max number of tags allowed */
				__( 'Maximum of %d.', 'cariera-addons' ),
				intval( $max_tags )
			);
			// You can now append this $note to the tag field's description elsewhere.
		}

		switch ( get_option( 'job_manager_tag_input' ) ) {
			case 'multiselect':
				$fields['job']['job_tags'] = [
					'label'       => esc_html__( 'Job tags', 'cariera-addons' ),
					'description' => esc_html__( 'Choose some tags, such as required skills or technologies, for this job.', 'cariera-addons' ) . $note,
					'placeholder' => esc_html__( 'Choose some tags&hellip;', 'cariera-addons' ),
					'type'        => 'term-multiselect',
					'taxonomy'    => self::TAX_JOB_TAG,
					'required'    => false,
					'priority'    => '4.5',
				];
				break;
			case 'checkboxes':
				$fields['job']['job_tags'] = [
					'label'       => esc_html__( 'Job tags', 'cariera-addons' ),
					'description' => esc_html__( 'Choose some tags, such as required skills or technologies, for this job.', 'cariera-addons' ) . $note,
					'type'        => 'term-checklist',
					'taxonomy'    => self::TAX_JOB_TAG,
					'required'    => false,
					'priority'    => '4.5',
				];
				break;
			default:
				$fields['job']['job_tags'] = [
					'label'       => esc_html__( 'Job tags', 'cariera-addons' ),
					'description' => esc_html__( 'Comma separate tags, such as required skills or technologies, for this job.', 'cariera-addons' ) . $note,
					'type'        => 'text',
					'required'    => false,
					'placeholder' => esc_html__( 'e.g. PHP, Social Media, Management', 'cariera-addons' ),
					'priority'    => '4.5',
				];
				break;
		}

		return $fields;
	}

	/**
	 * Validate fields
	 *
	 * @since   0.9.1
	 * @version 0.9.10
	 *
	 * @param  bool  $passed
	 * @param  array $fields
	 * @param  array $values
	 * @return bool on success, wp_error on failure
	 */
	public function validate_job_tag_field( $passed, $fields, $values ) {
		$max  = get_option( 'job_manager_max_tags' );
		$tags = is_array( $values['job']['job_tags'] ) ? $values['job']['job_tags'] : array_filter( explode( ',', $values['job']['job_tags'] ) );

		if ( $max && count( $tags ) > $max ) {
			// translators: %d is max number of tags allowed.
			return new \WP_Error( 'validation-error', sprintf( __( 'Please enter no more than %d tags.', 'cariera-addons' ), $max ) );
		}

		return $passed;
	}

	/**
	 * Save posted tags to the job
	 *
	 * @since   0.9.1
	 * @version 0.9.10
	 *
	 * @param int   $job_id
	 * @param array $values
	 */
	public function save_job_tag_field( $job_id, $values ) {
		switch ( get_option( 'job_manager_tag_input' ) ) {
			case 'multiselect':
			case 'checkboxes':
				$tags = array_map( 'absint', $values['job']['job_tags'] );
				break;
			default:
				if ( is_array( $values['job']['job_tags'] ) ) {
					$tags = array_map( 'absint', $values['job']['job_tags'] );
				} else {
					$raw_tags = array_filter( array_map( 'sanitize_text_field', explode( ',', $values['job']['job_tags'] ) ) );

					// Loop tags we want to set and put them into an array.
					$tags = [];

					foreach ( $raw_tags as $tag ) {
						$tags[] = apply_filters( 'format_job_tag', $tag );
					}
				}
				break;
		}

		if ( ! empty( $tags ) ) {
			wp_set_object_terms( $job_id, $tags, self::TAX_JOB_TAG, false );
		}
	}

	/**
	 * Get Job Tags for the field when editing
	 *
	 * @since   0.9.1
	 * @version 0.9.10
	 *
	 * @param array    $data
	 * @param \WP_Post $job
	 */
	public function get_job_tag_field_data( $data, $job ) {
		switch ( get_option( 'job_manager_tag_input' ) ) {
			case 'multiselect':
			case 'checkboxes':
				$data['job']['job_tags']['value'] = wp_get_object_terms( $job->ID, self::TAX_JOB_TAG, [ 'fields' => 'ids' ] );
				break;
			default:
				$data['job']['job_tags']['value'] = implode( ', ', wp_get_object_terms( $job->ID, self::TAX_JOB_TAG, [ 'fields' => 'names' ] ) );
				break;
		}
		return $data;
	}

	/**
	 * Show tags on job pages
	 *
	 * @since   0.9.1
	 * @version 0.9.4
	 *
	 * @param string $content
	 */
	public function display_tags( $content ) {
		global $post;

		$terms = $this->get_job_tag_list( $post->ID );

		if ( ! empty( $terms ) ) {
			$content .= sprintf(
				'<p class="job_tags"><strong>%s</strong> %s</p>',
				esc_html__( 'Tagged as:', 'cariera-addons' ),
				wp_kses_post( $terms )
			);
		}

		return $content;
	}

	/**
	 * Format a tag
	 *
	 * @since 0.9.1
	 *
	 * @param string $tag
	 */
	public static function format_job_tag( $tag ) {
		// We'll assume that small tags less than or equal to 3 chars are abbreviated. Uppercase them.
		if ( strlen( $tag ) <= 3 ) {
			$tag = strtoupper( $tag );
		} else {
			$tag = strtolower( $tag );
		}
		return $tag;
	}

	/**
	 * Tag support for feeds
	 *
	 * @since   0.9.1
	 * @version 0.9.10
	 *
	 * @param array $args
	 */
	public function job_feed_args( $args ) {
		// phpcs:ignore
		if ( ! empty( $_GET['job_tags'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => self::TAX_JOB_TAG,
				'field'    => 'slug',
				'terms'    => explode( ',', sanitize_text_field( wp_unslash( $_GET['job_tags'] ) ) ), // phpcs:ignore
			];
		}

		return $args;
	}
}
