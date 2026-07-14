<?php

namespace Cariera_Addons\Core\Applications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Application_Form {
	/**
	 * The post ID.
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * The post title.
	 *
	 * @var string
	 */
	private $post_title;

	/**
	 * The form fields.
	 *
	 * @var array
	 */
	private $form_fields;

	/**
	 * The candidate email template.
	 *
	 * @var array {
	 *      @type string $content The content of the email.
	 *      @type string $subject The subject of the email.
	 * }
	 */
	private $candidate_email_template;

	/**
	 * The employer email template.
	 *
	 * @var array {
	 *      @type string $content The content of the email.
	 *      @type string $subject The subject of the email.
	 * }
	 */
	private $employer_email_template;

	/**
	 * The meta keys for 'job_application_form' post type.
	 *
	 * @var string
	 */
	private const FORM_FIELDS             = '_form_fields';
	private const CANDIDATE_EMAIL_CONTENT = '_candidate_email_content';
	private const CANDIDATE_EMAIL_SUBJECT = '_candidate_email_subject';
	private const EMPLOYER_EMAIL_CONTENT  = '_employer_email_content';
	private const EMPLOYER_EMAIL_SUBJECT  = '_employer_email_subject';
	public const FORM_POST_META_KEY       = '_application_form';

	/**
	 * Constructor.
	 * Sets the post ID and post title.
	 *
	 * @param int $post_id The post ID. If null, a new post will be created on save.
	 */
	public function __construct( $post_id = null ) {
		$this->post_id = $post_id ?? null;

		if ( $this->post_id ) {
			$this->load_post_data();
		} else {
			$this->load_default_data();
		}
	}

	/**
	 * Load the form fields. Only called when the post ID is set.
	 *
	 * @since 0.9.3
	 */
	private function load_post_data() {
		$this->post_title  = get_the_title( $this->post_id );
		$this->form_fields = get_post_meta( $this->post_id, self::FORM_FIELDS, true );

		if ( ! is_array( $this->form_fields ) ) {
			$this->load_default_data();
			return;
		}

		$this->candidate_email_template = [
			'content' => get_post_meta( $this->post_id, self::CANDIDATE_EMAIL_CONTENT, true ),
			'subject' => get_post_meta( $this->post_id, self::CANDIDATE_EMAIL_SUBJECT, true ),
		];
		$this->employer_email_template  = [
			'content' => get_post_meta( $this->post_id, self::EMPLOYER_EMAIL_CONTENT, true ),
			'subject' => get_post_meta( $this->post_id, self::EMPLOYER_EMAIL_SUBJECT, true ),
		];
	}

	/**
	 * Load the default data for the form.
	 *
	 * @since 0.9.3
	 */
	private function load_default_data() {
		$defaults          = self::get_default_data();
		$this->form_fields = $defaults['form_fields'];

		$this->candidate_email_template = $defaults['candidate_email_template'];
		$this->employer_email_template  = $defaults['employer_email_template'];
	}

	/**
	 * Save/persist the form fields.
	 * For new forms, this assumes fields and post title would have been set using the `set` method.
	 *
	 * @since 0.9.3
	 *
	 * @param array $args More arguments to pass to wp_insert_post eg 'post_status' and 'post_title'.
	 */
	public function save( $args = [] ) {
		$post_data = [
			'ID'         => $this->post_id,
			'post_title' => $this->post_title ?? '',
			'post_type'  => \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION_FORM,
			'meta_input' => [
				self::FORM_FIELDS             => $this->form_fields,
				self::CANDIDATE_EMAIL_CONTENT => $this->candidate_email_template['content'] ?? '',
				self::CANDIDATE_EMAIL_SUBJECT => $this->candidate_email_template['subject'] ?? '',
				self::EMPLOYER_EMAIL_CONTENT  => $this->employer_email_template['content'] ?? '',
				self::EMPLOYER_EMAIL_SUBJECT  => $this->employer_email_template['subject'] ?? '',
			],
		];

		$post_data        = array_merge( $post_data, $args );
		$this->post_id    = $this->post_id ? wp_update_post( $post_data ) : wp_insert_post( $post_data );
		$this->post_title = get_the_title( $this->post_id );
	}

	/**
	 * Set a form field.
	 *
	 * @since 0.9.3
	 *
	 * @param array $args
	 */
	public function set( $args ) {
		if ( isset( $args['form_fields'] ) ) {
			$this->form_fields = $args['form_fields'];
		}
		if ( isset( $args['candidate_email_template'] ) ) {
			$this->candidate_email_template = $args['candidate_email_template'];
		}
		if ( isset( $args['employer_email_template'] ) ) {
			$this->employer_email_template = $args['employer_email_template'];
		}
		if ( isset( $args['post_title'] ) ) {
			$this->post_title = $args['post_title'];
		}
	}

	/**
	 * Get a form field.
	 *
	 * @since   0.9.3
	 * @version 0.9.4
	 *
	 * @param string $key The key of the field.
	 */
	public function get( $key = null ) {
		$properties = [
			'form_fields'              => $this->form_fields,
			'candidate_email_template' => $this->candidate_email_template,
			'employer_email_template'  => $this->employer_email_template,
			'post_id'                  => $this->post_id,
			'post_title'               => $this->post_title,
		];

		if ( $key !== null && array_key_exists( $key, $properties ) ) {
			return $properties[ $key ];
		}

		return $properties;
	}

	/**
	 * Get all form data for a form.
	 *
	 * @since 0.9.3
	 *
	 * @param int|null $post_id
	 */
	public static function get_form_data( $post_id = null ) {
		return ( new self( $post_id ) )->get();
	}

	/**
	 * Get the default data for creating a new form.
	 *
	 * @since 0.9.3
	 */
	public static function get_default_data() {
		return [
			'form_fields'              => get_job_application_default_form_fields(),
			'employer_email_template'  => [
				'content' => get_job_application_default_email_content(),
				'subject' => get_job_application_default_email_subject(),
			],

			'candidate_email_template' => [
				'content' => '',
				'subject' => get_job_application_default_candidate_email_subject(),
			],
		];
	}
}
