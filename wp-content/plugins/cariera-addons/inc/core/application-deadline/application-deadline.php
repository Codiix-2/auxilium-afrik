<?php

namespace Cariera_Addons\Core\Application_Deadline;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Job_Manager\Job_Dashboard_Shortcode;
use WP_Job_Manager\UI\UI_Elements;

class Application_Deadline {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Required plugins check.
		add_action( 'admin_notices', [ $this, 'required_notices' ] );

		if ( class_exists( 'WP_Job_Manager_Application_Deadline' ) ) {
			return;
		}

		// Init main functions when plugin loads.
		$this->init_plugin();
		$this->maybe_schedule_cron();
	}

	/**
	 * Required notices when WPJM Application Deadline is enabled.
	 *
	 * @since 0.9.4
	 */
	public function required_notices() {
		// If WP Job Manager Application Deadline is installed and activated.
		if ( class_exists( 'WP_Job_Manager_Application_Deadline' ) ) {
			echo '<div class="error">';
			echo '<p>' . wp_kses_post( __( 'Please deactivate <strong>WP Job Manager Application Deadline</strong> to enable the <strong>Cariera Addons Application Deadline</strong> feature.', 'cariera-addons' ) ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Init plugin
	 *
	 * @since   0.9.0
	 * @version 0.9.7
	 */
	public function init_plugin() {
		add_filter( 'job_manager_settings', [ $this, 'settings' ] );
		add_filter( 'submit_job_form_fields', [ $this, 'deadline_field' ] );
		add_filter( 'submit_job_form_validate_fields', [ $this, 'validate_deadline_field' ], 10, 3 );
		add_action( 'job_manager_update_job_data', [ $this, 'save_deadline_field' ], 10, 2 );
		add_action( 'submit_job_form_fields_get_job_data', [ $this, 'get_deadline_field_data' ], 10, 2 );
		add_filter( 'job_listing_meta_end', [ $this, 'display_the_deadline' ] );
		add_filter( 'job_manager_candidates_can_apply', [ $this, 'job_manager_candidates_can_apply' ] );
		add_filter( 'job_manager_job_listing_data_fields', [ $this, 'admin_fields' ] );

		// Cron.
		add_action( 'check_application_deadlines', [ $this, 'check_application_deadlines' ] );

		// Add column to admin.
		add_filter( 'manage_edit-job_listing_columns', [ $this, 'columns' ], 20 );
		add_action( 'manage_job_listing_posts_custom_column', [ $this, 'custom_columns' ], 1 );
		add_filter( 'manage_edit-job_listing_sortable_columns', [ $this, 'job_admin_closing_date_sort' ] );
		add_filter( 'pre_get_posts', [ $this, 'job_admin_orderby_deadline' ] );

		// Dashboard.
		if ( class_exists( Job_Dashboard_Shortcode::class ) ) {
			remove_action( 'job_manager_job_dashboard_column_date', [ Job_Dashboard_Shortcode::class, 'the_expiration_date' ] );
			add_action( 'job_manager_job_dashboard_column_date', [ $this, 'job_dashboard_column_expires_or_closing_date' ] );
		}

		// Order by.
		add_filter( 'get_job_listings_query_args', [ $this, 'get_job_listings_query_args' ] );

		// Renewals.
		add_filter( 'job_manager_renewal_expiry_date', [ $this, 'update_application_deadline' ], 10, 2 );
		add_filter( 'job_manager_job_can_be_renewed', [ $this, 'job_can_be_renewed' ], 10, 2 );
	}

	/**
	 * Add Settings
	 *
	 * @since 0.9.0
	 *
	 * @param  array $settings
	 * @return array
	 */
	public function settings( $settings = [] ) {
		$settings['job_listings'][1][] = [
			'name'     => 'job_manager_expire_when_deadline_passed',
			'std'      => '0',
			'label'    => esc_html__( 'Automatic deadline expiry', 'cariera-addons' ),
			'cb_label' => esc_html__( 'Enable automatic expiration', 'cariera-addons' ),
			'desc'     => esc_html__( 'Enable this option to automatically expire jobs when application closing dates pass.', 'cariera-addons' ),
			'type'     => 'checkbox',
			'track'    => 'bool',
		];

		return $settings;
	}

	/**
	 * Add the job deadline field to the submission form
	 *
	 * @since 0.9.0
	 *
	 * @param array $fields
	 */
	public function deadline_field( $fields ) {
		if ( ! get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			$desc = esc_html__( 'Deadline for new applicants.', 'cariera-addons' );
		} else {
			$desc = esc_html__( 'Deadline for new applicants. The listing will end automatically after this date.', 'cariera-addons' );
		}

		$field_type = version_compare( JOB_MANAGER_VERSION, '1.30.0', '>=' ) ? 'date' : 'text';

		$fields['job']['job_deadline'] = [
			'label'       => esc_html__( 'Closing date', 'cariera-addons' ),
			'description' => $desc,
			'type'        => $field_type,
			'required'    => false,
			'placeholder' => '',
			'priority'    => '6.5',
		];

		return $fields;
	}

	/**
	 * Validate fields
	 *
	 * @since 0.9.0
	 *
	 * @param  bool  $passed
	 * @param  array $fields
	 * @param  array $values
	 * @return bool on success, wp_error on failure
	 */
	public function validate_deadline_field( $passed, $fields, $values ) {
		$value = $values['job']['job_deadline'];

		if ( ! empty( $value ) && ( ! strtotime( $value ) || strtotime( $value ) == -1 ) ) {
			return new WP_Error( 'validation-error', __( 'Please enter a valid closing date.', 'cariera-addons' ) );
		}

		return $passed;
	}

	/**
	 * Save posted deadline to the job
	 *
	 * @since 0.9.0
	 *
	 * @param int   $job_id
	 * @param array $values
	 */
	public function save_deadline_field( $job_id, $values ) {
		$value = $values['job']['job_deadline'];

		update_post_meta( $job_id, '_application_deadline', $value );
	}

	/**
	 * Get Job Tags for the field when editing
	 *
	 * @since 0.9.0
	 *
	 * @param array    $data
	 * @param \WP_Post $job
	 */
	public function get_deadline_field_data( $data, $job ) {
		$data['job']['job_deadline']['value'] = get_post_meta( $job->ID, '_application_deadline', true );
		return $data;
	}

	/**
	 * Show deadline on job pages
	 *
	 * @since   0.9.0
	 * @version 1.0.4
	 */
	public function display_the_deadline() {
		global $post;

		$deadline = get_post_meta( $post->ID, '_application_deadline', true );

		if ( ! $deadline ) {
			return;
		}

		$timestamp    = strtotime( $deadline );
		$current_time = current_time( 'timestamp' );
		$days_diff    = floor( ( $current_time - $timestamp ) / DAY_IN_SECONDS );

		$expiring_days = apply_filters( 'job_manager_application_deadline_expiring_days', 2 );
		$expiring      = ( $days_diff >= -$expiring_days );
		$expired       = ( $days_diff > 0 );

		// Format deadline date.
		$date_str = date_i18n( $this->get_date_format(), $timestamp );

		// Do not display anything if listing is already expired on single listing page.
		if ( is_singular( \WP_Job_Manager_Post_Types::PT_LISTING ) && $expired ) {
			return;
		}

		/**
		 * Filters the display string for the application closing date.
		 *
		 * @since 0.9.0
		 *
		 * @param string $date_str  The default date string to be displayed.
		 * @param int    $timestamp The timestamp of the closing date.
		 */
		$date_str = apply_filters( 'job_manager_application_deadline_closing_date_display', $date_str, $timestamp );

		if ( ! $date_str ) {
			return;
		}

		// Build CSS classes.
		$classes = [ 'application-deadline' ];
		if ( $expiring ) {
			$classes[] = 'expiring';
		}
		if ( $expired ) {
			$classes[] = 'expired';
		}

		$label = $expired ? esc_html__( 'Closed', 'cariera-addons' ) : esc_html__( 'Closes', 'cariera-addons' );

		printf(
			'<li class="%1$s"><label>%2$s:</label> %3$s</li>',
			esc_attr( implode( ' ', $classes ) ),
			esc_html( $label ),
			wp_kses_post( $date_str )
		);
	}

	/**
	 * Can candidates apply?
	 *
	 * @since 0.9.0
	 *
	 * @param  bool $can_apply
	 * @return bool
	 */
	public function job_manager_candidates_can_apply( $can_apply ) {
		global $post;

		$deadline = get_post_meta( $post->ID, '_application_deadline', true );
		if ( $deadline ) {
			$days_expired = floor( ( current_time( 'timestamp' ) - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) );
			$expired      = $days_expired > 0;

			if ( $expired ) {
				$can_apply = false;
			}
		}
		return $can_apply;
	}

	/**
	 * Fields in admin
	 *
	 * @since 0.9.0
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function admin_fields( $fields = [] ) {
		$fields['_application_deadline'] = [
			'label'       => esc_html__( 'Application closing date', 'cariera-addons' ),
			'placeholder' => '',
			'classes'     => [ 'job-manager-datepicker' ],
		];
		return $fields;
	}

	/**
	 * Expire jobs
	 *
	 * @since   0.9.0
	 * @version 0.9.7
	 */
	public function check_application_deadlines() {
		global $wpdb;

		if ( ! get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			return;
		}

		// Change status to expired.
		$job_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
					LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID
					WHERE postmeta.meta_key = '_application_deadline'
					AND postmeta.meta_value > 0
					AND postmeta.meta_value < %s
					AND posts.post_status = 'publish'
					AND posts.post_type = %s
				",
				date( 'Y-m-d', current_time( 'timestamp' ) ),
				\WP_Job_Manager_Post_Types::PT_LISTING
			)
		);

		if ( $job_ids ) {
			foreach ( $job_ids as $job_id ) {
				$job_data                = [];
				$job_data['ID']          = $job_id;
				$job_data['post_status'] = 'expired';
				wp_update_post( $job_data );
			}
		}
	}

	/**
	 * Add a job tag column to admin
	 *
	 * @since 0.9.0
	 *
	 * @param array $columns
	 */
	public function columns( $columns ) {
		$new_columns = [];

		foreach ( $columns as $key => $value ) {
			if ( 'job_expires' === $key ) {
				$new_columns['job_deadline'] = esc_html__( 'Closing', 'cariera-addons' );

				if ( get_option( 'job_manager_expire_when_deadline_passed' ) ) {
					$new_columns['job_expires_or_closing_date'] = esc_html__( 'Expires', 'cariera-addons' );
				}
			}
			$new_columns[ $key ] = $value;
		}

		if ( get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			unset( $new_columns['job_expires'] );
		}

		return $new_columns;
	}

	/**
	 * Handle display of new column
	 *
	 * @since   0.9.0
	 * @version 1.0.4
	 *
	 * @param  string $column
	 */
	public function custom_columns( $column ) {
		global $post;

		if ( 'job_deadline' === $column ) {
			$deadline = get_post_meta( $post->ID, '_application_deadline', true );
			if ( empty( $deadline ) ) {
				echo '<span class="na">&ndash;</span>';
			} else {
				echo esc_html( date_i18n( $this->get_date_format(), strtotime( $deadline ) ) );
			}
		} elseif ( 'job_expires_or_closing_date' === $column ) {
			$timestamps = [];
			$deadline   = get_post_meta( $post->ID, '_application_deadline', true );

			if ( $deadline ) {
				$timestamps[] = strtotime( $deadline );
			}

			if ( $post->_job_expires ) {
				$timestamps[] = strtotime( $post->_job_expires );
			}

			sort( $timestamps );

			echo count( $timestamps ) > 0 ? esc_html( date_i18n( get_option( 'date_format' ), $timestamps[0] ) ) : '&ndash;';
		}
	}

	/**
	 * Make Closing date column sortable.
	 *
	 * @since 0.9.0
	 *
	 * @param array $columns Sortable columns.
	 *
	 * @return array Sortable columns
	 */
	public function job_admin_closing_date_sort( $columns ) {
		$columns['job_deadline'] = 'deadline';
		return $columns;
	}

	/**
	 * Add support to order by deadline
	 *
	 * @since   0.9.0
	 * @version 0.9.7
	 *
	 * @param WP_Query $query The query.
	 */
	public function job_admin_orderby_deadline( $query ) {
		if ( isset( $query->query['post_type'] ) ) {
			if ( is_admin() && \WP_Job_Manager_Post_Types::PT_LISTING === $query->query['post_type'] && 'deadline' === $query->get( 'orderby' ) ) {
				$query->set( 'meta_key', '_application_deadline' );
				$query->set( 'orderby', 'meta_value' );
			}
		}
	}

	/**
	 * Output closing date if set, and expiration date if relevant.
	 *
	 * @since 0.9.0
	 *
	 * @param \WP_Post $job
	 */
	public function job_dashboard_column_expires_or_closing_date( $job ) {
		$deadline = get_post_meta( $job->ID, '_application_deadline', true );

		if ( $deadline ) {
			// translators: Placeholder is the closing date of the job listing.
			echo '<small>' . UI_Elements::rel_time( $deadline, __( 'Closes in %s', 'cariera-addons' ) ) . '</small>';
		}

		if ( ! $deadline || ! get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			Job_Dashboard_Shortcode::the_expiration_date( $job );
		}
	}

	/**
	 * Handle sorting
	 *
	 * @since 0.9.0
	 *
	 * @param  array $args
	 * @return array
	 */
	public function get_job_listings_query_args( $args ) {
		if ( 'deadline' === $args['orderby'] ) {
			$args['meta_key']     = '_application_deadline';
			$args['meta_value']   = '';
			$args['meta_compare'] = '';
			$args['orderby']      = [
				'meta_value' => $args['order'],
				'post_date'  => $args['order'],
			];
		}

		return $args;
	}

	/**
	 * Update the application deadline in a similar way with expiry during renewals.
	 *
	 * @since 0.9.0
	 *
	 * @param string  $new_expiry The new expiry date.
	 * @param WP_Post $job        The job that is being renewed.
	 */
	public function update_application_deadline( $new_expiry, $job ) {
		$application_deadline = date_create_immutable_from_format( 'Y-m-d', get_post_meta( $job->ID, '_application_deadline', true ) );

		if ( empty( $application_deadline ) ) {
			return $new_expiry;
		}

		update_post_meta( $job->ID, '_application_deadline', calculate_job_expiry( $job->ID, false, $application_deadline ) );

		return $new_expiry;
	}

	/**
	 * Update the application deadline in a similar way with expiry during renewals.
	 *
	 * @since 0.9.0
	 *
	 * @param string  $can_be_renewed Whether the job can be renewed.
	 * @param WP_Post $job            The job.
	 */
	public function job_can_be_renewed( $can_be_renewed, $job ) {
		if ( ! get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			return $can_be_renewed;
		}

		$application_deadline = date_create_immutable_from_format( 'Y-m-d', get_post_meta( $job->ID, '_application_deadline', true ) );

		if ( empty( $application_deadline ) ) {
			return $can_be_renewed;
		}

		$expiring_soon_days = get_option( 'job_manager_renewal_days', 5 );
		$current_time_stamp = current_datetime()->getTimestamp();
		$status             = get_post_status( $job );

		if ( 'publish' === $status && $application_deadline->getTimestamp() - $current_time_stamp < $expiring_soon_days * DAY_IN_SECONDS ) {
			return true;
		}

		return $can_be_renewed;
	}

	/**
	 * Get the date format string to use for displaying dates. Uses the
	 * WordPress date_format option if it is set.
	 *
	 * @since 0.9.0
	 *
	 * @return string the date format string.
	 */
	private function get_date_format() {
		$date_format = get_option( 'date_format' );
		if ( ! $date_format ) {
			$date_format = 'M j, Y';
		}
		return $date_format;
	}

	/**
	 * Create cron jobs
	 *
	 * @since 0.9.0
	 */
	private function maybe_schedule_cron() {
		if ( ! wp_next_scheduled( 'check_application_deadlines' ) ) {
			$timestamp  = strtotime( 'midnight' );
			$timestamp -= get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;

			wp_schedule_event( $timestamp, 'daily', 'check_application_deadlines' );
		}
	}
}
