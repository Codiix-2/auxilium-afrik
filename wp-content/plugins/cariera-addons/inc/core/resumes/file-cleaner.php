<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class File_Cleaner {

	/**
	 * Initialize the hooks for file cleaning.
	 *
	 * @since 0.9.5
	 */
	public static function init() {
		add_action( 'before_delete_post', [ __CLASS__, 'handle_resume_deletion' ] );
	}

	/**
	 * Handle the deletion of a Resume post and delete its files if needed. This
	 * function is used in the `before_delete_post` hook and only works with
	 * posts of type `resume`.
	 *
	 * @since 0.9.5
	 *
	 * @param int $resume_id
	 */
	public static function handle_resume_deletion( $resume_id ) {
		if ( \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME !== get_post_type( $resume_id ) || ! self::should_delete_files() ) {
			return;
		}

		if ( resume_manager_attach_uploaded_files() ) {
			self::delete_attachments( $resume_id );
		} else {
			self::delete_files_from_fields( $resume_id );
		}
	}

	/**
	 * Whether we should be deleting files along with Resumes.
	 *
	 * @since 0.9.5
	 */
	private static function should_delete_files() {
		return get_option( 'resume_manager_delete_files_on_resume_deletion' );
	}

	/**
	 * Delete all attachments from the given Resume.
	 *
	 * @since 0.9.5
	 *
	 * @param int $resume_id
	 */
	private static function delete_attachments( $resume_id ) {
		$attachments = get_attached_media( '', $resume_id );
		foreach ( $attachments as $attachment ) {
			wp_delete_attachment( $attachment->ID, true );
		}
	}

	/**
	 * Delete files based on the form fields of type "file".
	 *
	 * @since   0.9.5
	 * @version 0.9.8
	 *
	 * @param int $resume_id
	 */
	private static function delete_files_from_fields( $resume_id ) {
		$file_fields = self::get_file_fields( $resume_id );

		foreach ( $file_fields as $key => $field_config ) {
			$meta_key  = "_$key";
			$file_urls = get_post_meta( $resume_id, $meta_key, true );

			if ( empty( $file_urls ) ) {
				continue;
			}

			// Normalize to array.
			$file_urls = is_array( $file_urls ) ? $file_urls : [ $file_urls ];

			foreach ( $file_urls as $file_url ) {
				$file_path = self::get_filepath_for_upload( $file_url );

				if ( $file_path ) {
					wp_delete_file( $file_path );

					// Delete resized image variants, if any.
					$path_parts     = pathinfo( $file_path );
					$file_path_glob = str_replace(
						'.' . $path_parts['extension'],
						'-[0-9]*x[0-9]*.' . $path_parts['extension'],
						$file_path
					);

					$resized_files = glob( $file_path_glob );
					if ( $resized_files ) {
						foreach ( $resized_files as $resized_file ) {
							wp_delete_file( $resized_file );
						}
					}
				}
			}
		}
	}

	/**
	 * Gets the file fields.
	 *
	 * @since   0.9.5
	 * @version 0.9.11
	 *
	 * @param int $resume_id
	 */
	private static function get_file_fields( $resume_id ) {
		require_once JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php';

		$fields_raw = Forms\Submit_Resume::get_resume_fields();
		$fields     = [];
		foreach ( $fields_raw as $key => $field_config ) {
			if ( 'file' !== $field_config['type'] ) {
				continue;
			}

			$fields[ $key ] = $field_config;
		}

		/**
		 * Allows filtering on what fields should be considered "file" fields
		 * for cleanup on delete.
		 *
		 * @since 0.9.5
		 *
		 * @param $fields    array List of fields (key => config).
		 * @param $resume_id int   Resume ID.
		 */
		return apply_filters( 'resume_manager_file_fields_to_cleanup', $fields, $resume_id );
	}

	/**
	 * Given a URL for an uploaded file, try to determine the file path.
	 *
	 * @since 0.9.5
	 *
	 * @param string $upload_url The URL of the uploaded file.
	 */
	private static function get_filepath_for_upload( $upload_url ) {
		$wp_upload_dir = wp_upload_dir();

		$file_path = str_replace(
			[ $wp_upload_dir['baseurl'], $wp_upload_dir['url'] ],
			[ $wp_upload_dir['basedir'], $wp_upload_dir['path'] ],
			$upload_url
		);

		$found_path    = $file_path !== $upload_url;
		$in_upload_dir = 0 === strpos( realpath( $file_path ), realpath( $wp_upload_dir['basedir'] ) );

		if ( ! $found_path || ! $in_upload_dir ) {
			// We were unable to determine a valid path for the URL.
			return null;
		}

		return $file_path;
	}
}
