<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'create_job_application' ) ) {
	/**
	 * Create a new job application
	 *
	 * @since   0.9.3
	 * @version 1.0.9
	 *
	 * @param int    $job_id
	 * @param string $candidate_name
	 * @param string $candidate_email
	 * @param string $application_message
	 * @param array  $meta
	 * @param bool   $notification
	 * @param bool   $source
	 */
	function create_job_application( $job_id, $candidate_name, $candidate_email, $application_message, $meta = [], $notification = true, $source = '' ) {
		$job = get_post( $job_id );

		if ( ! $job || \WP_Job_Manager_Post_Types::PT_LISTING !== $job->post_type ) {
			return false;
		}

		$application_data = [
			'post_title'     => wp_kses_post( $candidate_name ),
			'post_content'   => wp_kses_post( $application_message ),
			'post_status'    => current( array_keys( get_job_application_statuses() ) ),
			'post_type'      => \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION,
			'comment_status' => 'closed',
			'post_author'    => $job->post_author,
			'post_parent'    => $job_id,
		];

		// Apply a filter to allow modification of just the post data.
		$application_data = apply_filters( 'cariera_addons_create_job_application_data', $application_data );
		$application_id   = wp_insert_post( $application_data );

		if ( $application_id ) {
			update_post_meta( $application_id, '_job_applied_for', $job->post_title );
			update_post_meta( $application_id, '_candidate_email', $candidate_email );
			update_post_meta( $application_id, '_candidate_user_id', get_current_user_id() );
			update_post_meta( $application_id, '_rating', 0 );
			update_post_meta( $application_id, '_application_source', $source );

			if ( $meta ) {
				foreach ( $meta as $key => $value ) {
					update_post_meta( $application_id, $key, $value );
				}
			}

			if ( $notification ) {
				$method = get_the_job_application_method( $job_id );

				if ( 'email' === $method->type ) {
					$send_to = $method->raw_email;
				} elseif ( $job->post_author ) {
					$user    = get_user_by( 'id', $job->post_author );
					$send_to = $user->user_email;
				} else {
					$send_to = '';
				}

				if ( $send_to ) {
					$attachments = [];

					if ( function_exists( 'get_resume_attachments' ) ) {
						$resume_id = get_job_application_resume_id( $application_id );
						if ( $resume_id && 'publish' === get_post_status( $resume_id ) ) {
							$resume_files = get_resume_attachments( $resume_id );
							if ( ! empty( $resume_files['attachments'] ) ) {
								$attachments = $resume_files['attachments'];
							}
						}
					}

					if ( ! empty( $meta['_attachment_file'] ) ) {
						if ( is_array( $meta['_attachment_file'] ) ) {
							foreach ( $meta['_attachment_file'] as $file ) {
								$attachments[] = $file;
							}
						} else {
							$attachments[] = $meta['_attachment_file'];
						}
					}

					$existing_shortcode_tags = $GLOBALS['shortcode_tags'];
					remove_all_shortcodes();
					job_application_email_add_shortcodes(
						[
							'application_id'      => $application_id,
							'job_id'              => $job_id,
							'user_id'             => get_current_user_id(),
							'candidate_name'      => $candidate_name,
							'candidate_email'     => $candidate_email,
							'application_message' => $application_message,
							'meta'                => $meta,
						]
					);
					$subject = do_shortcode( get_job_application_email_subject() );
					$form_id = $meta['_form_id'] ?? null;
					$message = do_shortcode( get_job_application_email_content( $form_id ) );
					$message = str_replace( "\n\n\n\n", "\n\n", implode( "\n", array_map( 'trim', explode( "\n", $message ) ) ) );
					$is_html = ( $message !== wp_strip_all_tags( $message ) );

					// Does this message contain formatting already?
					if ( $is_html && ! strstr( $message, '<p' ) && ! strstr( $message, '<br' ) ) {
						$message = nl2br( $message );
					}

					$GLOBALS['shortcode_tags'] = $existing_shortcode_tags;

					$headers   = [];
					$headers[] = 'Reply-To: ' . $candidate_email;
					$headers[] = $is_html ? 'Content-Type: text/html' : 'Content-Type: text/plain';
					$headers[] = 'charset=utf-8';

					wp_mail(
						apply_filters( 'create_job_application_notification_recipient', $send_to, $job_id, $application_id ),
						apply_filters( 'create_job_application_notification_subject', $subject, $job_id, $application_id ),
						apply_filters( 'create_job_application_notification_message', $message ),
						apply_filters( 'create_job_application_notification_headers', $headers, $job_id, $application_id ),
						apply_filters( 'create_job_application_notification_attachments', $attachments, $job_id, $application_id )
					);
				}
			}

			return $application_id;
		}

		return false;
	}
}

if ( ! function_exists( 'get_job_application_count' ) ) {
	/**
	 * Get number of applications for a job
	 *
	 * @since   0.9.3
	 * @version 1.0.8
	 *
	 * @param  int    $job_id
	 * @param  string $status Application status.
	 */
	function get_job_application_count( $job_id, $status = '' ) {
		global $wpdb;

		$post_type = \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION;
		$statuses  = $status ? (array) $status : array_merge( array_keys( get_job_application_statuses() ), [ 'publish' ] );

		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

		$sql = "
			SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_parent = %d
			AND post_type = %s
			AND post_status IN ($placeholders)
		";

		$args = array_merge( [ $job_id, $post_type ], $statuses );

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore
	}
}

if ( ! function_exists( 'user_has_applied_for_job' ) ) {
	/**
	 * See if a user has already appled for a job
	 *
	 * @since   0.9.3
	 * @version 1.0.8
	 *
	 * @param  int         $user_id
	 * @param  int|WP_Post $job_id
	 * @return bool
	 */
	function user_has_applied_for_job( $user_id, $job_id ) {
		global $wpdb;

		if ( ! $user_id ) {
			return false;
		}

		if ( $job_id instanceof WP_Post ) {
			$job_id = $job_id->ID;
		}

		$post_type = \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION;
		$statuses  = array_merge( array_keys( get_job_application_statuses() ), [ 'publish' ] );

		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

		$sql = "
			SELECT 1
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm
				ON pm.post_id = p.ID
				AND pm.meta_key = '_candidate_user_id'
				AND pm.meta_value = %d
			WHERE p.post_parent = %d
			AND p.post_type = %s
			AND p.post_status IN ($placeholders)
			LIMIT 1
		";

		$args = array_merge(
			[ absint( $user_id ), absint( $job_id ), $post_type ],
			$statuses
		);

		return (bool) $wpdb->get_var( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore
	}
}

/**
 * Job Application Statuses
 *
 * @since   0.9.3
 * @version 0.9.10
 */
function get_job_application_statuses() {
	return apply_filters(
		'job_application_statuses',
		[
			'new'         => _x( 'New', 'Job application status', 'cariera-addons' ),
			'interviewed' => _x( 'Interviewed', 'Job application status', 'cariera-addons' ),
			'offer'       => _x( 'Offer extended', 'Job application status', 'cariera-addons' ),
			'hired'       => _x( 'Hired', 'Job application status', 'cariera-addons' ),
			'rejected'    => _x( 'Rejected', 'Job application status', 'cariera-addons' ),
			'archived'    => _x( 'Archived', 'Job application status', 'cariera-addons' ),
		]
	);
}

/**
 * Get default form fields
 *
 * @return array
 */
function get_job_application_default_form_fields() {
	$default_fields = [
		'candidate_name'         => [
			'label'       => esc_html__( 'Full name', 'cariera-addons' ),
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 1,
			'rules'       => [ 'from_name' ],
		],
		'candidate_email'        => [
			'label'       => esc_html__( 'Email address', 'cariera-addons' ),
			'description' => '',
			'type'        => 'text',
			'required'    => true,
			'placeholder' => '',
			'priority'    => 2,
			'rules'       => [ 'from_email' ],
		],
		'application_message'    => [
			'label'       => esc_html__( 'Message', 'cariera-addons' ),
			'type'        => 'textarea',
			'required'    => true,
			'placeholder' => esc_html__( 'Your cover letter/message sent to the employer', 'cariera-addons' ),
			'priority'    => 3,
			'rules'       => [ 'message' ],
		],
		'resume_id'              => [
			'label'       => esc_html__( 'Online Resume', 'cariera-addons' ),
			'description' => '',
			'type'        => 'resumes',
			'required'    => false,
			'priority'    => 4,
			'rules'       => [],
		],
		'application_attachment' => [
			'label'       => esc_html__( 'Upload CV', 'cariera-addons' ),
			'type'        => 'file',
			'required'    => true,
			'priority'    => 5,
			'placeholder' => '',
			'multiple'    => true,
			'rules'       => [ 'attachment' ],
			// translators: %s is the maximum file size allowed.
			'description' => sprintf( __( 'Upload your CV/resume or any other relevant file. Max. file size: %s.', 'cariera-addons' ), size_format( wp_max_upload_size() ) ),
		],
	];

	if ( ! function_exists( 'get_resume_share_link' ) ) {
		unset( $default_fields['resume_id'] );
		$default_fields['application_attachment']['required'] = true;
	} else {
		$default_fields['application_attachment']['required'] = false;
	}

	return $default_fields;
}

/**
 * Get the form fields for the application form
 *
 * @since 0.9.3
 *
 * @param bool     $suppress_filters Whether to apply filters to the returned result.
 * @param int|null $form_id Optional. The form ID.
 */
function get_job_application_form_fields( $suppress_filters = false, $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form   = new \Cariera_Addons\Core\Applications\Application_Form( $form_id );
		$fields = $form->get( 'form_fields' );
	} else {
		$fields = \Cariera_Addons\Core\Applications\Default_Form::get_default_form()['form_fields'];
	}

	return $suppress_filters ? $fields : apply_filters( 'job_application_form_fields', $fields, $form_id );
}

/**
 * Get all Application Forms
 *
 * @since   0.9.3
 * @version 1.0.6
 */
function get_application_forms() {
	$posts = new WP_Query(
		[
			'post_type'        => \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION_FORM,
			'posts_per_page'   => - 1,
			'suppress_filters' => true,
			'orderby'          => 'title',
			'order'            => 'ASC',
		]
	);

	$default_form = \Cariera_Addons\Core\Applications\Default_Form::get_default_form();

	if ( $posts->post_count <= 1 ) {
		return null;
	}

	$application_forms = [];

	if ( array_key_exists( 'ID', $default_form ) ) {
		$application_forms[ $default_form['ID'] ] = $default_form['post_title'];
	}

	foreach ( $posts->posts as $post ) {
		$application_forms[ $post->ID ] = $post->post_title;
	}
	return apply_filters( 'job_application_forms', $application_forms );
}

/**
 * Get Application Form Fields
 *
 * @since 0.9.3
 */
function get_submit_job_application_form_field() {
	global $post;

	$application_forms = get_application_forms();

	if ( empty( $application_forms ) ) {
		return null;
	}

	$field_array = [
		'label'       => esc_html__( 'Application Form', 'cariera-addons' ),
		'placeholder' => '',
		'type'        => 'select',
		'classes'     => [ 'select2' ],
		'options'     => array_map( 'esc_attr', $application_forms ),
	];

	return $field_array;
}

/**
 * Get the default email content
 *
 * @since 0.9.3
 */
function get_job_application_default_email_content() {
	$message = <<<'EOF'
Hello

A candidate ([from_name]) has submitted their application for the position "[job_title]".

=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

[message]

=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

[meta_data]

[job_dashboard_url prefix="You can view this and any other applications here: "]

You can contact them directly at: [from_email]
EOF;
	return $message;
}

/**
 * Get employer email content.
 *
 * @since 0.9.3
 *
 * @param int|null $form_id Optional. The form ID.
 */
function get_job_application_email_content( $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form     = new \Cariera_Addons\Core\Applications\Application_Form( $form_id );
		$template = $form->get( 'employer_email_template' );
		$content  = $template['content'];
	} else {
		$content = \Cariera_Addons\Core\Applications\Default_Form::get_default_form()['employer_email_template']['content'];
	}
	return apply_filters( 'job_application_email_content', $content, $form_id );
}

/**
 * Get the default email subject
 *
 * @since 0.9.3
 */
function get_job_application_default_email_subject() {
	return esc_html__( 'New job application for [job_title]', 'cariera-addons' );
}

/**
 * Get employer email subject
 *
 * @since 0.9.3
 *
 * @param int|null $form_id Optional. The form ID.
 */
function get_job_application_email_subject( $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form     = new \Cariera_Addons\Core\Applications\Application_Form( $form_id );
		$template = $form->get( 'employer_email_template' );
		$subject  = $template['subject'];
	} else {
		$subject = \Cariera_Addons\Core\Applications\Default_Form::get_default_form()['employer_email_template']['subject'];
	}
	return apply_filters( 'job_application_email_subject', $subject, $form_id );
}

/**
 * Get candidate email content.
 *
 * @since 0.9.3
 *
 * @param int|null $form_id Optional. The form ID.
 */
function get_job_application_candidate_email_content( $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form     = new \Cariera_Addons\Core\Applications\Application_Form( $form_id );
		$template = $form->get( 'candidate_email_template' );
		$content  = $template['content'];
	} else {
		$content = \Cariera_Addons\Core\Applications\Default_Form::get_default_form()['candidate_email_template']['content'];
	}
	return apply_filters( 'job_application_candidate_email_content', $content, $form_id );
}

/**
 * Get the default email subject.
 *
 * @since 0.9.3
 */
function get_job_application_default_candidate_email_subject() {
	return esc_html__( 'Your job application for [job_title]', 'cariera-addons' );
}

/**
 * Get candidate email subject
 *
 * @since 0.9.3
 *
 * @param int|null $form_id Optional. The form ID.
 */
function get_job_application_candidate_email_subject( $form_id = null ) {
	if ( $form_id && ! is_null( get_post( $form_id ) ) ) {
		$form     = new \Cariera_Addons\Core\Applications\Application_Form( $form_id );
		$template = $form->get( 'candidate_email_template' );
		$subject  = $template['subject'];
	} else {
		$subject = \Cariera_Addons\Core\Applications\Default_Form::get_default_form()['candidate_email_template']['subject'];
	}
	return apply_filters( 'job_application_candidate_email_subject', $subject, $form_id );
}

/**
 * Get tags to dynamically replace in the notification email
 *
 * @since 0.9.3
 *
 * @param int|null $form_id Optional. The form ID.
 */
function get_job_application_email_tags( $form_id = null ) {
	$tags = [
		'from_name'         => esc_html__( 'Candidate Name', 'cariera-addons' ),
		'from_email'        => esc_html__( 'Candidate Email', 'cariera-addons' ),
		'message'           => esc_html__( 'Message from candidate', 'cariera-addons' ),
		'meta_data'         => esc_html__( 'All custom form fields in list format', 'cariera-addons' ),
		'application_id'    => esc_html__( 'Application ID', 'cariera-addons' ),
		'user_id'           => esc_html__( 'User ID of applicant', 'cariera-addons' ),
		'job_id'            => esc_html__( 'Job ID', 'cariera-addons' ),
		'job_title'         => esc_html__( 'Job Title', 'cariera-addons' ),
		'job_url'           => esc_html__( 'URL of the job listing', 'cariera-addons' ),
		'job_dashboard_url' => esc_html__( 'URL to the frontend job dashboard page', 'cariera-addons' ),
		'company_name'      => esc_html__( 'Name of the company which submitted the job listing', 'cariera-addons' ),
		'job_post_meta'     => esc_html__( 'Some meta data from the job. e.g. <code>[job_post_meta key="_job_location"]</code>', 'cariera-addons' ),
	];

	foreach ( get_job_application_form_fields( false, $form_id ) as $key => $field ) {
		if ( isset( $tags[ $key ] ) ) {
			continue;
		}
		if ( in_array( 'message', $field['rules'], true ) || in_array( 'from_name', $field['rules'], true ) || in_array( 'from_email', $field['rules'], true ) || in_array( 'attachment', $field['rules'], true ) ) {
			continue;
		}

		// translators: %s is the field label.
		$tags[ $key ] = sprintf( __( 'Custom field named "%s"', 'cariera-addons' ), $field['label'] );
	}

	return $tags;
}

/**
 * Get the default form data for the application form.
 *
 * @since 0.9.3
 */
function get_default_job_application_form_data() {
	return \Cariera_Addons\Core\Applications\Default_Form::get_default_form();
}

/**
 * Shortcode handler
 *
 * @since 0.9.3
 *
 * @param array  $atts
 * @param string $content
 * @param string $value
 */
function job_application_email_shortcode_handler( $atts, $content, $value ) {
	$atts = shortcode_atts(
		[
			'prefix' => '',
			'suffix' => '',
		],
		$atts
	);

	if ( ! empty( $value ) ) {
		return wp_kses_post( $atts['prefix'] ) . $value . wp_kses_post( $atts['suffix'] );
	}
}

/**
 * Add shortcodes for email content
 *
 * @since   0.9.3
 * @version 1.0.1
 *
 * @param array $data
 */
function job_application_email_add_shortcodes( $data ) {
	$candidate_name      = isset( $data['candidate_name'] ) ? $data['candidate_name'] : '';
	$candidate_email     = isset( $data['candidate_email'] ) ? $data['candidate_email'] : '';
	$application_message = isset( $data['application_message'] ) ? $data['application_message'] : '';
	$job_id              = isset( $data['job_id'] ) ? absint( $data['job_id'] ) : 0;
	$application_id      = isset( $data['application_id'] ) ? absint( $data['application_id'] ) : 0;
	$user_id             = isset( $data['user_id'] ) ? absint( $data['user_id'] ) : 0;
	$meta                = isset( $data['meta'] ) ? (array) $data['meta'] : [];

	$job_title         = html_entity_decode( wp_strip_all_tags( get_the_title( $job_id ) ) );
	$dashboard_id      = get_option( 'job_manager_job_dashboard_page_id' );
	$job_dashboard_url = $dashboard_id ? htmlspecialchars_decode(
		add_query_arg(
			[
				'action' => 'show_applications',
				'job_id' => $job_id,
			],
			get_permalink( $dashboard_id )
		)
	) : '';
	$meta_data         = [];
	$company_name      = get_the_company_name( $job_id );

	add_shortcode(
		'from_name',
		function ( $atts, $content = '' ) use ( $candidate_name ) {
			return job_application_email_shortcode_handler( $atts, $content, $candidate_name );
		}
	);
	add_shortcode(
		'from_email',
		function ( $atts, $content = '' ) use ( $candidate_email ) {
			return job_application_email_shortcode_handler( $atts, $content, $candidate_email );
		}
	);
	add_shortcode(
		'message',
		function ( $atts, $content = '' ) use ( $application_message ) {
			return job_application_email_shortcode_handler( $atts, $content, $application_message );
		}
	);
	add_shortcode(
		'job_id',
		function ( $atts, $content = '' ) use ( $job_id ) {
			return job_application_email_shortcode_handler( $atts, $content, $job_id );
		}
	);
	add_shortcode(
		'job_title',
		function ( $atts, $content = '' ) use ( $job_title ) {
			return job_application_email_shortcode_handler( $atts, $content, $job_title );
		}
	);
	add_shortcode(
		'job_url',
		function ( $atts, $content = '' ) use ( $job_id ) {
			return job_application_email_shortcode_handler( $atts, $content, get_permalink( $job_id ) );
		}
	);
	add_shortcode(
		'job_dashboard_url',
		function ( $atts, $content = '' ) use ( $job_dashboard_url ) {
			return job_application_email_shortcode_handler( $atts, $content, $job_dashboard_url );
		}
	);
	add_shortcode(
		'company_name',
		function ( $atts, $content = '' ) use ( $company_name ) {
			return job_application_email_shortcode_handler( $atts, $content, $company_name );
		}
	);
	add_shortcode(
		'application_id',
		function ( $atts, $content = '' ) use ( $application_id ) {
			return job_application_email_shortcode_handler( $atts, $content, $application_id );
		}
	);
	add_shortcode(
		'user_id',
		function ( $atts, $content = '' ) use ( $user_id ) {
			return job_application_email_shortcode_handler( $atts, $content, $user_id );
		}
	);
	add_shortcode(
		'job_post_meta',
		function ( $atts, $content = '' ) use ( $job_id ) {
			$atts  = shortcode_atts( [ 'key' => '' ], $atts );
			$value = get_post_meta( $job_id, sanitize_text_field( $atts['key'] ), true );
			return job_application_email_shortcode_handler( $atts, $content, $value );
		}
	);
	$form_id = get_post_meta( get_the_ID(), \Cariera_Addons\Core\Applications\Application_Form::FORM_POST_META_KEY, true );

	foreach ( get_job_application_form_fields( false, $form_id ) as $key => $field ) {
		if ( in_array( 'message', $field['rules'], true ) || in_array( 'from_name', $field['rules'], true ) || in_array( 'from_email', $field['rules'], true ) || in_array( 'attachment', $field['rules'], true ) ) {
			continue;
		}

		$value = '';
		if ( 'file' === $field['type'] && true === $field['multiple'] ) {
			$multiple_files = '';
			foreach ( $meta as $index => $filename ) {
				if ( false !== strpos( $index, $field['label'] ) ) {
					$multiple_files .= $meta[ $index ] . ' ';
				}
			}
			$value = $multiple_files;
		} else {
			$value = isset( $meta[ $field['label'] ] ) ? $meta[ $field['label'] ] : '';
		}

		if ( 'resumes' === $field['type'] && function_exists( 'get_resume_share_link' ) && isset( $meta['_resume_id'] ) ) {
			$value = get_resume_share_link( $meta['_resume_id'] );
		}

		$meta_data[] = [
			'label' => $field['label'],
			'value' => $value,
		];

		add_shortcode(
			$key,
			function ( $atts, $content = '' ) use ( $value ) {
				return job_application_email_shortcode_handler( $atts, $content, $value );
			}
		);
	}

	$meta_data         = array_filter( $meta_data );
	$meta_data_strings = [];

	foreach ( $meta_data as $attributes ) {
		$meta_data_strings[] = $attributes['label'] . ': ' . $attributes['value'];
	}
	$meta_data_strings = implode( "\n", $meta_data_strings );

	add_shortcode(
		'meta_data',
		function ( $atts, $content = '' ) use ( $meta_data_strings ) {
			return job_application_email_shortcode_handler( $atts, $content, $meta_data_strings );
		}
	);

	do_action( 'job_application_email_add_shortcodes', $data );
}
