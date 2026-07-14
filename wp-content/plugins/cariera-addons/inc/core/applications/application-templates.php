<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'job_application_meta' ) ) {

	/**
	 * Output job_application_meta
	 *
	 * @since   0.9.3
	 * @version 0.9.8
	 *
	 * @param object $application
	 */
	function job_application_meta( $application ) {
		if ( ! ( $application instanceof \WP_Post ) ) {
			return;
		}

		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION !== $application->post_type ) {
			return;
		}

		$meta = get_post_custom( $application->ID );

		/**
		 * Filter the job application meta fields.
		 *
		 * @param array   $meta        Multidimensional array of meta values.
		 * @param WP_Post $application The application post object.
		 */
		$meta     = apply_filters( 'job_application_meta', $meta, $application );
		$has_meta = false;

		if ( ! empty( $meta ) && is_array( $meta ) ) {
			foreach ( $meta as $key => $values ) {
				if ( str_starts_with( $key, '_' ) || empty( $values ) ) {
					continue;
				}

				if ( ! $has_meta ) {
					echo '<dl class="job-application-meta">';
					$has_meta = true;
				}

				$value = is_array( $values ) ? $values[0] : $values;

				echo '<dt>' . esc_html( $key ) . '</dt>';
				echo '<dd>' . wp_kses_post( make_clickable( wpautop( esc_html( wp_strip_all_tags( $value ) ) ) ) ) . '</dd>';
			}

			if ( $has_meta ) {
				echo '</dl>';
			}
		}
	}
}

if ( ! function_exists( 'job_application_content' ) ) {

	/**
	 * Output job_application_content
	 *
	 * @since 0.9.3
	 *
	 * @param object $application
	 */
	function job_application_content( $application ) {
		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION_FORM === $application->post_type ) {
			echo apply_filters( 'job_application_content', wpautop( wptexturize( $application->post_content ) ), $application );
		}
	}
}

if ( ! function_exists( 'job_application_edit' ) ) {

	/**
	 * Output job_application_edit
	 *
	 * @since 0.9.3
	 *
	 * @param object $application
	 */
	function job_application_edit( $application ) {
		get_job_manager_template(
			'applications/job-application-edit.php',
			[
				'application' => $application,
				'job_id'      => $application->post_parent,
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}
}

if ( ! function_exists( 'job_application_notes' ) ) {

	/**
	 * Output job_application_notes
	 *
	 * @since   0.9.3
	 * @version 1.0.4
	 *
	 * @param object $application
	 */
	function job_application_notes( $application ) {
		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION !== $application->post_type ) {
			return;
		}

		$args = [
			'post_id' => $application->ID,
			'approve' => 'approve',
			'type'    => 'job_application_note',
			'order'   => 'asc',
		];

		// Temporarily remove filter to get all comments.
		remove_filter( 'comments_clauses', [ \Cariera_Addons\Core\Applications\Dashboard::class, 'exclude_application_comments' ], 10 );
		$notes = get_comments( $args );
		add_filter( 'comments_clauses', [ \Cariera_Addons\Core\Applications\Dashboard::class, 'exclude_application_comments' ], 10 );

		echo '<ul class="job-application-notes-list">';

		if ( $notes ) {
			foreach ( $notes as $note ) {
				$comment_id      = absint( $note->comment_ID );
				$comment_author  = esc_html( $note->comment_author );
				$comment_date    = esc_attr( $note->comment_date_gmt );
				$comment_diff    = human_time_diff( strtotime( $note->comment_date_gmt ), time() );
				$comment_content = wp_kses_post( $note->comment_content );
				?>

				<li rel="<?php echo esc_attr( $comment_id ); ?>" class="job-application-note">
					<div class="job-application-note-content">
						<?php echo wpautop( wptexturize( $comment_content ) ); ?>
					</div>
					<p class="job-application-note-meta">
						<abbr class="exact-date" title="<?php echo esc_attr( $comment_date ); ?> GMT">
							<?php printf( esc_html__( 'added %s ago', 'cariera-addons' ), $comment_diff ); ?>
						</abbr>
						<?php printf( ' ' . esc_html__( 'by %s', 'cariera-addons' ), $comment_author ); ?>
						<a href="#" class="delete_note"><?php esc_html_e( 'Delete note', 'cariera-addons' ); ?></a>
					</p>
				</li>
				<?php
			}
		}

		echo '</ul>';
		?>

		<div class="job-application-note-add">
			<p><textarea name="job_application_note" class="input-text" cols="20" rows="5" placeholder="<?php esc_attr_e( 'Private note regarding this application', 'cariera-addons' ); ?>"></textarea></p>
			<p><input type="button" data-application_id="<?php echo esc_attr( $application->ID ); ?>" class="button" value="<?php esc_attr_e( 'Add note', 'cariera-addons' ); ?>" /></p>
		</div>
		<?php
	}

}

if ( ! function_exists( 'job_application_header' ) ) {

	/**
	 * Output job_application_header
	 *
	 * @since 0.9.3
	 *
	 * @param object $application
	 */
	function job_application_header( $application ) {
		get_job_manager_template(
			'applications/job-application-header.php',
			[
				'application' => $application,
				'job_id'      => $application->post_parent,
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}
}

if ( ! function_exists( 'job_application_footer' ) ) {

	/**
	 * Output job_application_footer
	 *
	 * @since 0.9.3
	 *
	 * @param object $application
	 */
	function job_application_footer( $application ) {
		get_job_manager_template(
			'applications/job-application-footer.php',
			[
				'application' => $application,
				'job_id'      => $application->post_parent,
			],
			'cariera-addons',
			CARIERA_ADDONS_PATH . '/templates/'
		);
	}
}

if ( ! function_exists( 'get_job_application_email' ) ) {

	/**
	 * Output get_job_application_email
	 *
	 * @since 0.9.3
	 *
	 * @param int $application_id
	 */
	function get_job_application_email( $application_id ) {
		return get_post_meta( $application_id, '_candidate_email', true );
	}
}

if ( ! function_exists( 'get_job_application_attachments' ) ) {

	/**
	 * Output get_job_application_attachments
	 *
	 * @since   0.9.3
	 * @version 0.9.4
	 *
	 * @param int $application_id
	 */
	function get_job_application_attachments( $application_id ) {
		$attachments = get_post_meta( $application_id, '_attachment', true );

		if ( ! is_array( $attachments ) ) {
			$attachments = $attachments ? [ $attachments ] : [];
		}

		return array_filter( $attachments );
	}
}

if ( ! function_exists( 'get_job_application_attachment_name' ) ) {

	/**
	 * Output get_job_application_attachment_name
	 *
	 * @since 0.9.3
	 *
	 * @param string $attachment URL of attachment.
	 * @param int    $limit
	 */
	function get_job_application_attachment_name( $attachment, $limit = 0 ) {
		$attachment_name = basename( $attachment );
		if ( $limit && strlen( $attachment_name ) > $limit ) {
			$attachment_name = substr( $attachment_name, 0, $limit ) . '..' . substr( $attachment_name, -4 );
		}
		return $attachment_name;
	}
}

if ( ! function_exists( 'get_job_application_resume_id' ) ) {

	/**
	 * Output get_job_application_resume_id
	 *
	 * @since 0.9.3
	 *
	 * @param object $application_id
	 */
	function get_job_application_resume_id( $application_id ) {
		return get_post_meta( $application_id, '_resume_id', true );
	}
}

if ( ! function_exists( 'get_job_application_avatar' ) ) {

	/**
	 * Output get_job_application_avatar
	 *
	 * @since 0.9.3
	 *
	 * @param object $application_id
	 * @param int    $size
	 */
	function get_job_application_avatar( $application_id, $size = 42 ) {
		$email     = get_job_application_email( $application_id );
		$resume_id = get_job_application_resume_id( $application_id );

		if ( $resume_id && 'publish' === get_post_status( $resume_id ) && function_exists( 'get_the_candidate_photo' ) ) {
			$photo_url = get_the_candidate_photo( $resume_id );
			if ( ! empty( $photo_url ) ) {
				return '<img src="' . esc_url( $photo_url ) . '" height="' . esc_attr( $size ) . '" />';
			}
		}

		return $email ? get_avatar( $email, $size ) : '';
	}
}

if ( ! function_exists( 'get_job_application_rating' ) ) {

	/**
	 * Output get_job_application_avatar
	 *
	 * @since 0.9.3
	 *
	 * @param object $application_id
	 */
	function get_job_application_rating( $application_id ) {
		$rating = get_post_meta( $application_id, '_rating', true );
		return is_numeric( $rating ) && $rating > 0 ? $rating : 0;
	}
}
