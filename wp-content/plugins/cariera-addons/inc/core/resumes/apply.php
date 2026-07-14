<?php

namespace Cariera_Addons\Core\Resumes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Apply {

	use \Cariera_Addons\Src\Traits\Singleton;

	/**
	 * Error
	 *
	 * @var string
	 */
	private $error = '';

	/**
	 * Message
	 *
	 * @var string
	 */
	private $message = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'init' ], 20 );
		add_action( 'wp', [ $this, 'apply_with_resume_handler' ] );
		add_action( 'submit_resume_form_start', [ $this, 'resume_form_intro' ] );
	}

	/**
	 * Ensure application areas show the correct content.
	 *
	 * @since   0.9.5
	 * @version 1.0.6
	 */
	public function init() {
		if ( ! get_option( 'resume_manager_enable_application' ) ) {
			return;
		}

		$user_resumes = $this->get_user_resumes();

		/**
		 * What content is shown is based on settings and whether or not the user has resumes.
		 */
		if ( empty( $user_resumes ) && get_option( 'resume_manager_force_resume' ) ) {
			remove_all_actions( 'job_manager_application_details_email' );
			remove_all_actions( 'job_manager_application_details_url' );
			add_action( 'job_manager_application_details_email', [ $this, 'force_apply_with_resume' ], 20 );
			add_action( 'job_manager_application_details_url', [ $this, 'force_apply_with_resume' ], 20 );
		} else {
			if ( get_option( 'resume_manager_enable_application', 1 ) ) {
				// If we're forcing application through resume manager, we should disable other forms and content.
				if ( get_option( 'resume_manager_force_application' ) ) {
					remove_all_actions( 'job_manager_application_details_email' );
				}
				add_action( 'job_manager_application_details_email', [ $this, 'apply_with_resume' ], 20 );
			}

			if ( self::application_is_activated() && get_option( 'resume_manager_enable_application_for_url_method', true ) ) {
				// If we're forcing application through resume manager, we should disable other forms and content.
				if ( get_option( 'resume_manager_force_application' ) ) {
					remove_all_actions( 'job_manager_application_details_url' );
				}
				add_action( 'job_manager_application_details_url', [ $this, 'apply_with_resume' ], 20 );
			}
		}
	}

	/**
	 * Resume form intro
	 *
	 * @since   0.9.5
	 * @version 1.1.0
	 */
	public function resume_form_intro() {
		// phpcs:ignore
		if ( empty( $_REQUEST['job_id'] ) ) {
			return;
		}

		$job_id = absint( $_REQUEST['job_id'] ); // phpcs:ignore

		if ( get_post_type( $job_id ) !== \WP_Job_Manager_Post_Types::PT_LISTING ) {
			return;
		}

		// translators: Placeholder is the job title.
		echo '<p class="applying_for">' . sprintf( __( 'Submit your resume below to apply for the job "%s".', 'cariera-addons' ), '<a href="' . esc_url( get_permalink( $job_id ) ) . '">' . esc_html( get_the_title( $job_id ) ) . '</a>' ) . '</p>';
	}

	/**
	 * Get a user's resumes which they can apply with
	 *
	 * @since 0.9.5
	 *
	 * TODO: This gets included in every page.
	 */
	private function get_user_resumes() {
		if ( is_user_logged_in() ) {
			$args = apply_filters(
				'resume_manager_get_application_form_resumes_args',
				[
					'post_type'           => \Cariera_Addons\Core\Resumes\Post_Types::CPT_RESUME,
					'post_status'         => [ 'publish', 'hidden' ],
					'ignore_sticky_posts' => 1,
					'posts_per_page'      => -1,
					'orderby'             => 'date',
					'order'               => 'desc',
					'author'              => get_current_user_id(),
				]
			);

			$resumes = get_posts( $args );
		} else {
			$resumes = [];
		}

		return $resumes;
	}

	/**
	 * Allow users to apply to a job with a resume
	 *
	 * @since 0.9.5
	 */
	public function apply_with_resume() {
		get_job_manager_template( 'resumes/apply-with-resume.php', [ 'resumes' => $this->get_user_resumes() ], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Allow users to apply to a job with a resume
	 *
	 * @since 0.9.5
	 */
	public function force_apply_with_resume() {
		get_job_manager_template( 'resumes/force-apply-with-resume.php', [], 'cariera-addons', CARIERA_ADDONS_PATH . '/templates/' );
	}

	/**
	 * Send the application email if posted
	 *
	 * @since   0.9.5
	 * @version 1.1.0
	 *
	 * @throws \Exception On validation error.
	 */
	public function apply_with_resume_handler() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This is being handled in the template and we want to allow for other templates to handle it as well.
		if ( empty( $_POST['cariera_addons_resumes_apply_with_resume'] ) ) {
			return;
		}

		// Sanitize and prepare variables.
		$resume_id           = isset( $_POST['resume_id'] ) ? absint( $_POST['resume_id'] ) : 0; // phpcs:ignore
		$job_id              = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0; // phpcs:ignore
		$application_message = str_replace( '[nl]', "\n", sanitize_text_field( str_replace( "\n", '[nl]', strip_tags( stripslashes( $_POST['application_message'] ) ) ) ) ); // phpcs:ignore

		// Hook results display.
		add_action( 'job_content_start', [ $this, 'apply_with_resume_result' ] );
		add_action( 'job_manager_before_job_apply_' . $job_id, [ $this, 'apply_with_resume_result' ] );

		try {
			if ( ! $resume_id ) {
				throw new \Exception( esc_html__( 'Please choose a resume to apply with.', 'cariera-addons' ) );
			}

			if ( ! $job_id ) {
				throw new \Exception( esc_html__( 'This job cannot be applied for using a resume.', 'cariera-addons' ) );
			}

			if ( empty( $application_message ) ) {
				throw new \Exception( esc_html__( 'Please enter a message to include with your application.', 'cariera-addons' ) );
			}

			$method = get_the_job_application_method( $job_id );

			if ( 'email' !== $method->type && ! ( self::application_is_activated() && get_option( 'resume_manager_enable_application_for_url_method', 1 ) ) ) {
				throw new \Exception( esc_html__( 'This job cannot be applied for using a resume', 'cariera-addons' ) );
			}

			if ( $this->send_application( $job_id, $resume_id, $application_message ) ) {
				$this->message = esc_html__( 'Your application has been sent successfully.', 'cariera-addons' );
				add_filter( 'job_manager_show_job_apply_' . $job_id, '__return_false' );
			} else {
				throw new \Exception( esc_html__( 'Error sending application.', 'cariera-addons' ) );
			}
		} catch ( \Exception $e ) {
			$this->error = $e->getMessage();
		}
	}

	/**
	 * Send the application email or tell the Applications plugin to handle it.
	 *
	 * @since 0.9.5
	 *
	 * @param int    $job_id              Job post ID.
	 * @param int    $resume_id           Resume post ID.
	 * @param string $application_message Message that was sent along with resume application.
	 */
	public static function send_application( $job_id, $resume_id, $application_message ) {
		$method = get_the_job_application_method( $job_id );

		/**
		 * Fire action to notify employer if possible.
		 *
		 * @since 0.9.5
		 *
		 * @param int    $user_id             User ID of the person who submitted the application.
		 * @param int    $job_id              Job post ID.
		 * @param int    $resume_id           Resume post ID.
		 * @param string $application_message Message that was sent along with resume application.
		 */
		do_action( 'resume_manager_apply_with_resume', get_current_user_id(), $job_id, $resume_id, $application_message );

		// Generally, it should be sent if the application method includes an email.
		$sent = is_object( $method ) && ! empty( $method->raw_email );

		// If we're using WP Job Manager 1.34.1 and up we can check to see if the notification was actually sent.
		if ( method_exists( 'Cariera_Addons\Core\Resumes\Email_Notifications', 'send_notifications' ) ) {
			$sent = Email_Notifications::send_notifications( 'apply_with_resume' );
		}

		/**
		 * Fire action after email notification is attempted.
		 *
		 * @since 0.9.5
		 *
		 * @param int    $user_id             User ID of the person who submitted the application.
		 * @param int    $job_id              Job post ID.
		 * @param int    $resume_id           Resume post ID.
		 * @param string $application_message Message that was sent along with resume application.
		 * @param bool   $sent                If an email notification was sent.
		 */
		do_action( 'applied_with_resume', get_current_user_id(), $job_id, $resume_id, $application_message, $sent );

		// If we're using Applications for a URL application method, assume the application was filed.
		// We do this after the action is fired above because Applications will attempt to send its own
		// notification if the notification wasn't successful above.
		if (
			'email' !== $method->type
			&& self::application_is_activated()
			&& get_option( 'resume_manager_enable_application_for_url_method', 1 )
		) {
			$sent = true;
		}

		/**
		 * Filter if the email was actually sent.
		 *
		 * @since 0.9.5
		 *
		 * @param bool   $sent                If an email notification was sent.
		 * @param int    $user_id             User ID of the person who submitted the application.
		 * @param int    $job_id              Job post ID.
		 * @param int    $resume_id           Resume post ID.
		 * @param string $application_message Message that was sent along with resume application.
		 */
		return apply_filters( 'resume_manager_apply_with_resume_email_sent', $sent, get_current_user_id(), $job_id, $resume_id, $application_message );
	}

	/**
	 * Show results - errors and messages
	 */
	public function apply_with_resume_result() {
		if ( $this->message ) {
			echo '<p class="job-manager-message">' . esc_html( $this->message ) . '</p>';
		} elseif ( $this->error ) {
			echo '<p class="job-manager-error">' . esc_html( $this->error ) . '</p>';
		}
	}

	/**
	 * Check if applications are enabled and classes exist.
	 *
	 * @since 0.9.5
	 */
	private static function application_is_activated() {
		return ( class_exists( 'WP_Job_Manager_Applications' ) || class_exists( 'Cariera_Addons\Core\Applications\Applications' ) );
	}
}
