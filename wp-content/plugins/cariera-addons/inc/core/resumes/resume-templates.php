<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Echo the location for a resume/candidate
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param boolean     $map_link
 * @param WP_Post|int $post
 */
function the_candidate_location( $map_link = true, $post = null ) {
	$location = get_the_candidate_location( $post );

	if ( $location ) {
		if ( $map_link ) {
			echo apply_filters( 'the_candidate_location_map_link', '<a class="google_map_link candidate-location" href="http://maps.google.com/maps?q=' . urlencode( $location ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false">' . esc_html( $location ) . '</a>', $location, $post ); // phpcs:ignore
		} else {
			echo '<span class="candidate-location">' . esc_html( $location ) . '</span>';
		}
	}
}

/**
 * Get the location for a resume/candidate
 *
 * @since 0.9.5
 *
 * @param WP_Post|int $post
 */
function get_the_candidate_location( $post = null ) {
	$post = get_post( $post );

	if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
		return;
	}

	return apply_filters( 'the_candidate_location', $post->_candidate_location, $post );
}

/**
 * Display a candidates given job title
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param  string      $before
 * @param  string      $after
 * @param  boolean     $echo
 * @param WP_Post|int $post
 */
function the_candidate_title( $before = '', $after = '', $echo = true, $post = null ) {
	$title = get_the_candidate_title( $post );

	if ( empty( $title ) ) {
		return;
	}

	$title = esc_html( wp_strip_all_tags( $title ) );

	$title = $before . $title . $after;

	if ( $echo ) {
		echo $title; // phpcs:ignore
	} else {
		return $title;
	}
}

/**
 * Get a candidates given job title
 *
 * @since 0.9.5
 *
 * @param WP_Post|int $post
 */
function get_the_candidate_title( $post = null ) {
	$post = get_post( $post );

	if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
		return '';
	}

	return apply_filters( 'the_candidate_title', $post->_candidate_title, $post );
}

/**
 * Output the resume ID.
 *
 * @since 0.9.5
 *
 * @param WP_Post|int $post
 */
function the_resume_id( $post = null ) {
	$post = get_post( $post );
	return $post->ID;
}

/**
 * Display friendly drop-down select descriptor for resume.
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param  string      $before
 * @param  string      $after
 * @param  boolean     $echo
 * @param WP_Post|int $post
 */
function the_resume_select_label( $before = '', $after = '', $echo = true, $post = null ) {
	$title = get_resume_select_label( $post );

	if ( empty( $title ) ) {
		return;
	}

	$title = esc_attr( wp_strip_all_tags( $title ) );
	$title = $before . $title . $after;

	if ( $echo ) {
		echo $title; // phpcs:ignore
	} else {
		return $title;
	}
}

/**
 * Get the friendly drop-down select descriptor for resume.
 *
 * @since 0.9.5
 *
 * @param WP_Post|int $post
 */
function get_resume_select_label( $post = null ) {
	$post = get_post( $post );

	if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
		return '';
	}

	$title = get_the_candidate_title( $post );
	$label = $post->post_title;
	if ( ! empty( $title ) ) {
		$label .= ' (' . $title . ')';
	}

	/**
	 * Filters the label used for resumes in drop-down select lists.
	 *
	 * @since 0.9.5
	 *
	 * @param string  $label  Label to be filtered.
	 * @param WP_Post $post Resume to get label for.
	 */
	return apply_filters( 'resume_manager_resume_select_label', $label, $post );
}

/**
 * Output the photo for the resume/candidate
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param string      $size
 * @param mixed       $default
 * @param WP_Post|int $post
 */
function the_candidate_photo( $size = 'thumbnail', $default = null, $post = null ) {
	$logo = get_the_candidate_photo( $post );

	// Determine the final logo URL with proper fallback logic.
	if ( empty( $logo ) ) {
		if ( $default ) {
			$logo = $default;
		} else {
			$logo = apply_filters( 'resume_manager_default_candidate_photo', CARIERA_ADDONS_URL . '/assets/images/candidate.png' );
		}
	}

	// Early return if no logo.
	if ( empty( $logo ) ) {
		return;
	}

	// Only resize uploaded images, not theme defaults.
	$is_uploaded_image = wp_attachment_is_image( $logo );
	if ( 'full' !== $size && $is_uploaded_image ) {
		$logo = job_manager_get_resized_image( $logo, $size );
	}

	// Build image attributes.
	$attributes = apply_filters(
		'cariera_addons_candidate_photo_attributes',
		[
			'class'    => 'candidate_photo',
			'alt'      => esc_attr__( 'Candidate Photo', 'cariera-addons' ),
			'loading'  => 'lazy',
			'decoding' => 'async',
		],
		$logo,
		$size,
		$post
	);

	// Build HTML attributes string efficiently.
	$attributes_html = '';
	foreach ( $attributes as $name => $value ) {
		if ( ! empty( $value ) && is_scalar( $value ) ) {
			$attributes_html .= sprintf(
				' %s="%s"',
				sanitize_key( $name ),
				esc_attr( $value )
			);
		}
	}

	// Output the image with security.
	printf(
		'<img src="%s"%s />',
		esc_url( $logo ),
		wp_kses(
			$attributes_html,
			[
				'class'    => [],
				'alt'      => [],
				'loading'  => [],
				'decoding' => [],
				'srcset'   => [],
				'sizes'    => [],
				'width'    => [],
				'height'   => [],
				'id'       => [],
				'title'    => [],
			]
		)
	);
}

/**
 * Get the photo for the resume/candidate
 *
 * @since 0.9.5
 *
 * @param WP_Post|int $post
 */
function get_the_candidate_photo( $post = null ) {
	$post = get_post( $post );

	if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
		return;
	}

	return apply_filters( 'the_candidate_photo', $post->_candidate_photo, $post );
}

/**
 * Output the category
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param WP_Post|int $post
 */
function the_resume_category( $post = null ) {
	echo get_the_resume_category( $post ); // phpcs:ignore
}

/**
 * Get the category
 *
 * @since 0.9.5
 *
 * @param WP_Post|int $post
 */
function get_the_resume_category( $post = null ) {
	$post = get_post( $post );

	if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
		return '';
	}

	if ( ! get_option( 'resume_manager_enable_categories' ) ) {
		return '';
	}

	$categories = wp_get_object_terms( $post->ID, \Cariera_Addons\Core\Resumes\Post_Types::TAX_CATEGORY, [ 'fields' => 'names' ] );

	if ( is_wp_error( $categories ) ) {
		return '';
	}

	return implode( ', ', $categories );
}

/**
 * Outputs the jobs status
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param WP_Post|int $post
 */
function the_resume_status( $post = null ) {
	echo get_the_resume_status( $post ); // phpcs:ignore
}

/**
 * Gets the jobs status
 *
 * @since   0.9.5
 * @version 0.9.6
 *
 * @param WP_Post|int $post
 */
function get_the_resume_status( $post = null ) {
	$post = get_post( $post );

	$status = $post->post_status;

	if ( 'publish' === $status ) {
		$status = esc_html__( 'Published', 'cariera-addons' );
	} elseif ( 'expired' === $status ) {
		$status = esc_html__( 'Expired', 'cariera-addons' );
	} elseif ( 'pending' === $status ) {
		$status = esc_html__( 'Pending Review', 'cariera-addons' );
	} elseif ( 'hidden' === $status ) {
		$status = esc_html__( 'Hidden', 'cariera-addons' );
	} else {
		$status = esc_html__( 'Inactive', 'cariera-addons' );
	}

	return apply_filters( 'the_resume_status', $status, $post );
}

/**
 * Returns the registration fields used when an account is required.
 *
 * @since   0.9.5
 * @version 1.0.7
 */
function resume_manager_get_registration_fields() {
	$generate_username_from_email      = resume_manager_generate_username_from_email();
	$use_standard_password_setup_email = resume_manager_use_standard_password_setup_email();
	$account_required                  = resume_manager_user_requires_account();

	$registration_fields = [];
	if ( resume_manager_enable_registration() ) {
		if ( ! $generate_username_from_email ) {
			$registration_fields['create_account_username'] = [
				'type'     => 'text',
				'label'    => esc_html__( 'Username', 'cariera-addons' ),
				'required' => $account_required,
				'value'    => isset( $_POST['create_account_username'] ) ? sanitize_text_field( wp_unslash( $_POST['create_account_username'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
			];
		}

		if ( ! $use_standard_password_setup_email ) {
			$registration_fields['create_account_password'] = [
				'type'         => 'password',
				'label'        => esc_html__( 'Password', 'cariera-addons' ),
				'autocomplete' => false,
				'required'     => $account_required,
			];

			$password_hint = wpjm_get_password_rules_hint();
			if ( $password_hint ) {
				$registration_fields['create_account_password']['description'] = $password_hint;
			}

			$registration_fields['create_account_password_verify'] = [
				'type'         => 'password',
				'label'        => esc_html__( 'Verify Password', 'cariera-addons' ),
				'autocomplete' => false,
				'required'     => $account_required,
			];
		}
	}

	/**
	 * Filters the fields used at registration.
	 *
	 * @since 0.9.5
	 *
	 * @param array $registration_fields
	 */
	return apply_filters( 'resume_manager_get_registration_fields', $registration_fields );
}

/**
 * True if an the user can post a resume. By default, you must be logged in.
 *
 * @since 0.9.5
 */
function resume_manager_user_can_post_resume() {
	$can_post = true;

	if ( ! is_user_logged_in() ) {
		if ( resume_manager_user_requires_account() && ! resume_manager_enable_registration() ) {
			$can_post = false;
		}
	}

	return apply_filters( 'resume_manager_user_can_post_resume', $can_post );
}

/**
 * True if registration is enabled.
 *
 * @since   0.9.5
 * @version 0.9.6
 */
function resume_manager_enable_registration() {
	return apply_filters( 'resume_manager_enable_registration', 1 === absint( get_option( 'resume_manager_enable_registration' ) ) );
}

/**
 * True if an account is required to post.
 *
 * @since   0.9.5
 * @version 0.9.6
 */
function resume_manager_user_requires_account() {
	return apply_filters( 'resume_manager_user_requires_account', 1 === absint( get_option( 'resume_manager_user_requires_account' ) ) );
}

/**
 * True if usernames are generated from email addresses.
 *
 * @since   0.9.5
 * @version 0.9.6
 */
function resume_manager_generate_username_from_email() {
	return apply_filters( 'resume_manager_generate_username_from_email', 1 === absint( get_option( 'resume_manager_generate_username_from_email' ) ) );
}

/**
 * Output the class
 *
 * @since   0.9.5
 * @version 0.9.6
 *
 * @param string $class
 * @param mixed  $post_id
 */
function resume_class( $class = '', $post_id = null ) {
	echo 'class="' . esc_attr( join( ' ', get_resume_class( $class, $post_id ) ) ) . '"';
}

/**
 * Get the class
 *
 * @since 0.9.5
 *
 * @param string $class
 * @param int    $post_id
 */
function get_resume_class( $class = '', $post_id = null ) {
	$post = get_post( $post_id );

	if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
		return [];
	}

	$classes = [];

	if ( empty( $post ) ) {
		return $classes;
	}

	$classes[] = 'resume';

	if ( is_resume_featured( $post ) ) {
		$classes[] = 'resume_featured';
	}

	return get_post_class( $classes, $post->ID );
}

/**
 * Output the resume permalinks
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param WP_Post|int $post
 */
function the_resume_permalink( $post = null ) {
	$post = get_post( $post );
	echo get_the_resume_permalink( $post ); // phpcs:ignore
}

/**
 * Output the resume links
 *
 * @since 0.9.5
 *
 * @param WP_Post|int $post
 */
function the_resume_links( $post = null ) {
	$post = get_post( $post );
	get_job_manager_template( 'resumes/resume-links.php', [ 'post' => $post ], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
}

/**
 * Get the resume permalinks
 *
 * @since 0.9.5
 *
 * @param WP_Post|int $post
 */
function get_the_resume_permalink( $post = null ) {
	$post = get_post( $post );
	$link = get_permalink( $post );

	return apply_filters( 'the_resume_permalink', $link, $post );
}

/**
 * Returns true or false based on whether the resume has any website links to display.
 *
 * @since 0.9.5
 *
 * @param object $post
 */
function resume_has_links( $post = null ) {
	return count( get_resume_links( $post ) ) ? true : false;
}

/**
 * Returns true or false based on whether the resume has a file uploaded.
 *
 * @since 0.9.5
 *
 * @param object $post
 */
function resume_has_file( $post = null ) {
	return get_resume_file() ? true : false;
}

/**
 * Returns an array of links defined for a resume
 *
 * @since 0.9.5
 *
 * @param object $post
 */
function get_resume_links( $post = null ) {
	$post = get_post( $post );

	return array_filter( (array) get_post_meta( $post->ID, '_links', true ) );
}

/**
 * If multiple files have been attached to the resume_file field, return the in array format.
 *
 * @since 0.9.5
 *
 * @param object $post
 */
function get_resume_files( $post = null ) {
	$post  = get_post( $post );
	$files = get_post_meta( $post->ID, '_resume_file', true );
	if ( empty( $files ) ) {
		return [];
	}
	$files = is_array( $files ) ? $files : [ $files ];
	return $files;
}

/**
 * Return resume attachment URLs and file paths
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param object $post
 */
function get_resume_attachments( $post = null ) {
	$post  = get_post( $post );
	$files = get_post_meta( $post->ID, '_resume_file', true );
	$files = is_array( $files ) ? $files : [ $files ];

	foreach ( $files as $id => $file_path ) {
		if ( ! is_multisite() ) {

			/*
			 * Download file may be either http or https.
			 * site_url() depends on whether the page containing the download (ie; My Account) is served via SSL because WC
			 * modifies site_url() via a filter to force_ssl.
			 * So blindly doing a str_replace is incorrect because it will fail when schemes are mismatched. This code
			 * handles the various permutations.
			 */
			$scheme = parse_url( $file_path, PHP_URL_SCHEME ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

			if ( $scheme ) {
				$content_url = set_url_scheme( WP_CONTENT_URL, $scheme );
			} else {
				$content_url = is_ssl() ? str_replace( 'https:', 'http:', WP_CONTENT_URL ) : WP_CONTENT_URL;
			}

			$file_path = str_replace( $content_url, WP_CONTENT_DIR, $file_path );
		} else {
			$network_url = is_ssl() ? str_replace( 'https:', 'http:', network_admin_url() ) : network_admin_url();
			$upload_dir  = wp_upload_dir();

			// Try to replace network url.
			$file_path = str_replace( trailingslashit( $network_url ), ABSPATH, $file_path );

			// Now try to replace upload URL.
			$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_path );
		}

		if ( ! empty( $file_path ) ) {
			$file_path = realpath( $file_path );
		}

		if ( $file_path ) {
			$attachments[ $id ] = $file_path;
		} else {
			unset( $files[ $id ] );
		}
	}
	if ( ! isset( $attachments ) ) {
		$attachments = false;
	}
	return [
		'files'       => $files,
		'attachments' => $attachments,
	];
}

/**
 * Returns the resume file attached to a resume.
 *
 * @since 0.9.5
 *
 * @param object $post
 */
function get_resume_file( $post = null ) {
	$post = get_post( $post );
	$file = get_post_meta( $post->ID, '_resume_file', true );
	return is_array( $file ) ? current( $file ) : $file;
}

/**
 * Returns a download link for a resume file.
 *
 * @since 0.9.5
 *
 * @param  object $post
 * @param  int    $key
 * @param  string $url
 */
function get_resume_file_download_url( $post = null, $key = 0, $url = false ) {
	$post = get_post( $post );
	return add_query_arg(
		[
			'download-resume' => $post->ID,
			'file-id'         => $key,
		],
		$url
	);
}

/**
 * Return whether or not the resume has been featured
 *
 * @since 0.9.5
 *
 * @param object $post
 */
function is_resume_featured( $post = null ) {
	$post = get_post( $post );

	return $post->_featured ? true : false;
}

/**
 * Output the candidate video
 *
 * @since   0.9.5
 * @version 1.0.7
 *
 * @param WP_Post|int $post
 */
function the_candidate_video( $post = null ) {
	$video    = get_the_candidate_video( $post );
	$video    = is_ssl() ? str_replace( 'http:', 'https:', $video ) : $video;
	$filetype = wp_check_filetype( $video );

	if ( ! empty( $filetype['ext'] ) ) {
		$video_embed = wp_video_shortcode( [ 'src' => $video ] );
	} else {
		$video_embed = ! empty( $video ) ? wp_oembed_get( $video ) : false;
	}

	$video_embed = apply_filters( 'the_candidate_video_embed', $video_embed, $post );

	if ( $video_embed ) {
		echo '<div class="candidate-video">' . $video_embed . '</div>'; // phpcs:ignore
	}
}

/**
 * Get the candidate video URL
 *
 * @since 0.9.5
 *
 * @param mixed $post
 */
function get_the_candidate_video( $post = null ) {
	$post = get_post( $post );

	if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== $post->post_type ) {
		return;
	}
	return apply_filters( 'the_candidate_video', $post->_candidate_video, $post );
}
