<?php

namespace Cariera_Packages\Integration\Resume;

use Cariera_Packages\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Submission extends \Cariera_Packages\Abstract\Submission_Packages {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Post type for this submission type.
	 *
	 * @var string
	 */
	protected $post_type = 'resume';

	/**
	 * Package type for this submission type.
	 *
	 * @var string
	 */
	protected $package_type = 'resume_submission_package';

	/**
	 * Submission type for this submission type.
	 *
	 * @var string
	 */
	protected $submission_type = 'resume_submission';

	/**
	 * Product meta key for the resume listing limit.
	 *
	 * @var string
	 */
	protected $product_limit_meta_key = '_resume_limit';

	/**
	 * Product meta key for the resume listing duration.
	 *
	 * @var string
	 */
	protected $product_duration_meta_key = '_resume_duration';

	/**
	 * Product meta key for the resume listing featured status.
	 *
	 * @var string
	 */
	protected $product_featured_meta_key = '_resume_featured';

	/**
	 * Register all hooks for this submission type.
	 *
	 * @since 0.9.15
	 */
	protected function register_hooks() {
		// Settings.
		add_filter( 'resume_manager_settings', [ $this, 'resume_manager_settings' ] );

		// Submission & renewal flow.
		add_filter( 'submit_resume_steps', [ $this, 'submit_resume_steps' ], 10 );

		// Package card display.
		add_action( 'cariera_packages_user_package_description', [ $this, 'user_package_description' ], 10, 3 );
		add_action( 'cariera_packages_user_package_footer', [ $this, 'user_package_footer' ], 10, 2 );
		add_action( 'cariera_packages_package_description', [ $this, 'buy_package_description' ], 10, 2 );
		add_action( 'cariera_packages_package_footer', [ $this, 'buy_package_footer' ], 10, 2 );

		// User packages widget.
		add_filter( 'cariera_user_packages', [ $this, 'user_packages_count' ] );
		add_action( 'cariera_user_packages_content', [ $this, 'user_packages_content' ] );

		// Dashboard active packages widget.
		add_filter( 'cariera_dashboard_active_packages', [ $this, 'active_packages_count' ] );
		add_filter( 'cariera_dashboard_active_packages_content', [ $this, 'active_packages_content' ], 10, 2 );
	}

	/**
	 * Inject the "Paid Listings Flow" option into WPJM Resume settings.
	 *
	 * @since 0.9.15
	 *
	 * @param array $settings
	 */
	public function resume_manager_settings( $settings = [] ) {
		$settings['resume_submission'][1][] = [
			'name'    => 'resume_manager_paid_listings_flow',
			'std'     => '',
			'label'   => esc_html__( 'Paid Listings Flow', 'cariera-packages' ),
			'desc'    => esc_html__( 'Select when users should choose a package during listing submission.', 'cariera-packages' ),
			'type'    => 'select',
			'options' => [
				''       => esc_html__( 'After entering job details', 'cariera-packages' ),
				'before' => esc_html__( 'Before entering job details', 'cariera-packages' ),
			],
			'track'   => 'value',
		];

		return $settings;
	}

	/**
	 * Add the "Choose Package" step to the resume submission flow.
	 *
	 * @since   0.9.15
	 * @version 0.9.16
	 *
	 * @param array $steps
	 */
	public function submit_resume_steps( $steps ) {
		$packages   = Helpers::get_package_products( $this->package_type );
		$submission = get_option( 'cariera_packages_submission_package' );

		if ( empty( $packages ) || empty( $submission[ $this->post_type ] ) ) {
			return $steps;
		}

		$paid_flow = get_option( 'resume_manager_paid_listings_flow' );

		// Add "Choose Package" step.
		$steps['cariera-choose-package'] = [
			'name'     => esc_html__( 'Choose a package', 'cariera-packages' ),
			'view'     => [ $this, 'choose_package_form' ],
			'handler'  => [ $this, 'choose_package_handler' ],
			'priority' => 25,
		];

		if ( 'before' === $paid_flow ) {
			$steps['cariera-choose-package']['priority'] = 5;
			$steps['cariera-process-package']            = [
				'name'     => '',
				'view'     => false,
				'handler'  => [ $this, 'choose_package_handler' ],
				'priority' => 25,
			];
		} else {
			// Change the submit button text if the flow is "after".
			add_filter( 'submit_resume_step_preview_submit_text', [ $this, 'submit_button_text' ], 10 );
		}

		add_filter( 'submit_resume_post_status', [ $this, 'submit_listing_post_status' ], 10, 2 );

		return $steps;
	}

	/**
	 * Choose package form
	 *
	 * @since 0.9.15
	 */
	public function choose_package_form() {
		$form          = \WP_Resume_Manager_Form_Submit_Resume::instance();
		$resume_id     = $form->get_resume_id();
		$job_id        = $form->get_job_id();
		$step          = $form->get_step();
		$form_name     = $form->form_name;
		$packages      = Helpers::get_package_products( $this->package_type );
		$user_packages = Helpers::get_user_packages( get_current_user_id(), $this->package_type, true );
		$button_text   = 'before' !== get_option( 'resume_manager_paid_listings_flow' ) ? __( 'Submit &rarr;', 'cariera-packages' ) : __( 'Listing Details &rarr;', 'cariera-packages' );

		wp_enqueue_style( 'cariera-wpjm-submissions' );
		wp_enqueue_style( 'cariera-packages' );
		wp_enqueue_script( 'cariera-packages' );
		?>

		<form method="post" id="<?php echo esc_attr( $this->package_type ); ?>_selection" class="cariera-package-selection">
			<div class="cariera_packages_title cariera-listing-submission">
				<div class="submission-progress"></div>

				<?php do_action( 'cariera_resume_submission_steps' ); ?>

				<input type="submit" name="continue" class="button" value="<?php echo esc_attr( apply_filters( 'submit_job_step_choose_package_submit_text', $button_text ) ); ?>" />
				<input type="hidden" name="resume_id" value="<?php echo esc_attr( $resume_id ); ?>" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
				<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
				<input type="hidden" name="resume_manager_form" value="<?php echo esc_attr( $form_name ); ?>" />
			</div>

			<div class="cariera-packages <?php echo esc_attr( $this->post_type ); ?>_packages">
				<?php
				get_job_manager_template(
					'package-form.php',
					[
						'package_type'  => $this->package_type,
						'packages'      => $packages,
						'user_packages' => $user_packages,
					],
					'cariera-packages',
					CARIERA_PACKAGES_PATH . '/templates/'
				);
				?>
			</div>
		</form>
		<?php
	}

	/**
	 * Choose package handler
	 *
	 * @since 0.9.15
	 */
	public function choose_package_handler() {
		$form = \WP_Resume_Manager_Form_Submit_Resume::instance();

		// Validate Selected Package.
		$validation = self::validate_package( self::$package_id, self::$is_user_package );

		// Error? Go back to choose package step.
		if ( is_wp_error( $validation ) ) {
			$form->add_error( $validation->get_error_message() );
			$form->set_step( array_search( 'cariera-choose-package', array_keys( $form->get_steps() ), true ) );
			return false;
		}

		// Store selection in cookie.
		wc_setcookie( 'chosen_package_id', self::$package_id );
		wc_setcookie( 'chosen_package_is_user_package', self::$is_user_package ? 1 : 0 );

		// Process the package unless we're doing this before a resume is submitted.
		if ( 'before' !== get_option( 'resume_manager_paid_listings_flow' ) || 'cariera-process-package' === $form->get_step_key() ) {
			// Product the package.
			if ( self::assign_package_to_listing( self::$package_id, self::$is_user_package, $form->get_resume_id() ) ) {
				$form->next_step();
			}
		} else {
			$form->next_step();
		}
	}

	/**
	 * Create a submission package for user after a successful order.
	 *
	 * @since 0.9.15
	 *
	 * @param int      $user_id
	 * @param int      $product_id
	 * @param int      $order_id
	 * @param int|null $listing_id
	 */
	public static function create_user_submission_package( $user_id, $product_id, $order_id, $listing_id = null ) {
		// Validate the user ID.
		if ( empty( $user_id ) || ! is_numeric( $user_id ) || ! get_user_by( 'id', $user_id ) ) {
			return false;
		}

		// Get the WooCommerce product.
		$package = wc_get_product( $product_id );

		// Validate product type; only proceed for valid package types.
		if ( ! $package || ! $package->is_type( [ 'cariera_package' ] ) ) {
			return false;
		}

		// Get package type.
		$package_type = \Cariera_Packages\Helpers::get_product_package_type( $product_id );

		// Prepare the post arguments.
		$args = apply_filters(
			'cariera_packages_resume_submission_package_data',
			[
				'post_title'  => $package->get_title(),
				'post_status' => 'publish',
				'post_type'   => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
			],
			$user_id,
			$product_id,
			$order_id
		);

		// Create the new submission package post.
		$user_package = wp_insert_post( $args );

		// If the post creation fails, return false.
		if ( ! $user_package || is_wp_error( $user_package ) ) {
			return false;
		}

		// General meta data prefix.
		$prefix = 'cariera_packages_';
		update_post_meta( $user_package, "{$prefix}product_id", $product_id );
		update_post_meta( $user_package, "{$prefix}order_id", $order_id );
		update_post_meta( $user_package, "{$prefix}user_id", $user_id );
		update_post_meta( $user_package, "{$prefix}package_type", $package_type );

		// Retrieve and update package-specific meta data.
		$limit    = get_post_meta( $product_id, '_resume_limit', true );
		$duration = get_post_meta( $product_id, '_resume_duration', true );
		$featured = get_post_meta( $product_id, '_resume_featured', true ) === 'yes' ? 1 : 0;

		// Update package-specific meta data.
		update_post_meta( $user_package, "{$prefix}resume_submission_limit", $limit );
		update_post_meta( $user_package, "{$prefix}resume_submission_duration", $duration );
		update_post_meta( $user_package, "{$prefix}resume_submission_count", '0' );
		update_post_meta( $user_package, "{$prefix}resume_submission_featured", $featured );

		// Trigger a custom action hook for additional processing or integrations.
		do_action( 'cariera_packages_resume_submission_package_meta', $user_package, $user_id, $product_id, $order_id );

		return $user_package;
	}

	/**
	 * Assigns a pre-purchased "user package" to listing.
	 *
	 * @since 0.9.15
	 *
	 * @param int  $user_package_id     The user package id.
	 * @param int  $resume_id           The resume id.
	 * @param bool $is_renewal          Whether this is a renewal.
	 *
	 * TODO: Subscriptions not supported yet, will need to add handling for that.
	 */
	protected static function assign_user_package_to_listing( $user_package_id, $resume_id, $is_renewal ) {
		if ( empty( $user_package_id ) || empty( $resume_id ) ) {
			return false;
		}

		// Get user package meta data.
		$duration   = get_post_meta( $user_package_id, 'cariera_packages_resume_submission_duration', true );
		$featured   = get_post_meta( $user_package_id, 'cariera_packages_resume_submission_featured', true );
		$product_id = get_post_meta( $user_package_id, 'cariera_packages_product_id', true );

		// Give resume the package attributes.
		update_post_meta( $resume_id, '_resume_duration', $duration );
		update_post_meta( $resume_id, '_featured', $featured ? 1 : 0 );
		update_post_meta( $resume_id, '_package_id', $product_id );
		update_post_meta( $resume_id, '_user_package_id', $user_package_id );

		// Todo: Subscriptions support to be added.
		// if ( $package instanceof \WC_Product_Job_Package_Subscription && 'listing' === $package->get_package_subscription_type() ) {
		// update_post_meta( $resume_id, '_resume_expires', '' ); // Never expire automatically
		// }

		// Approve the job.
		if ( in_array( get_post_status( $resume_id ), [ 'pending_payment', 'expired' ], true ) ) {
			Helpers::approve_listing_with_package( $resume_id, get_current_user_id(), $user_package_id );
		}

		// This is documented in assign_product_to_listing.
		do_action( 'cariera_packages_process_package_for_resume', $user_package_id, true, $resume_id );

		return true;
	}

	/**
	 * Set resume-specific meta, add product to cart, redirect to checkout.
	 *
	 * @since 0.9.15
	 *
	 * @param int  $product_id
	 * @param int  $resume_id
	 * @param bool $is_renewal
	 *
	 * TODO: Add support for WC Subscriptions.
	 */
	protected static function assign_product_to_listing( int $product_id, int $resume_id, bool $is_renewal ): void {
		$package = wc_get_product( $product_id );

		$is_featured = false;

		// Featured listing handling.
		if ( $package instanceof \WC_Product && $package->is_type( 'cariera_package' ) ) {
			$is_featured = get_post_meta( $product_id, '_resume_featured', true ) === 'yes' ? 1 : 0;
		}

		// Retrieve and update package-specific meta data.
		$duration = get_post_meta( $product_id, '_resume_duration', true );

		// Give job the package attributes.
		update_post_meta( $resume_id, '_resume_duration', $duration );
		update_post_meta( $resume_id, '_featured', $is_featured );
		update_post_meta( $resume_id, '_package_id', $product_id );

		// TODO: Subscriptions not supported yet, will need to add handling for that.
		// if ( $package instanceof WC_Product_Job_Package_Subscription && 'listing' === $package->get_package_subscription_type() && 'publish' !== get_post_status( $job_id ) ) {
		// update_post_meta( $resume_id, '_resume_expires', '' ); // Never expire automatically
		// }

		// Add package to the cart.
		WC()->cart->add_to_cart(
			$product_id,
			1,
			'',
			'',
			[
				'resume_id' => $resume_id,
			]
		);

		wc_add_to_cart_message( $product_id );

		// Clear cookie.
		wc_setcookie( 'chosen_package_id', '', time() - HOUR_IN_SECONDS );
		wc_setcookie( 'chosen_package_is_user_package', '', time() - HOUR_IN_SECONDS );

		/**
		 * Triggered when either a package or a product is linked to the listing.
		 *
		 * @param int  $product_id      Contains either the user package id or the product id.
		 * @param bool $is_user_package True if the previous argument has the user package.
		 * @param int  $resume_id       The resume id.
		 */
		do_action( 'cariera_packages_process_package_for_resume', $product_id, false, $resume_id );

		// Redirect to checkout page.
		wp_safe_redirect( get_permalink( wc_get_page_id( 'checkout' ) ) );
		exit;
	}

	/**
	 * Remaining listings text content.
	 * This is used in dashboard active packages & [cariera_user_packages].
	 *
	 * @since   0.9.15
	 * @version 0.9.16
	 *
	 * @param bool $with_span
	 */
	protected function get_remaining_listings_text( $with_span = true ) {
		// translators: %s: number of remaining resumes the user can post.
		return $with_span ? __( 'You have <span>%s</span> resumes left that you can post.', 'cariera-packages' ) : __( 'You have %s resumes left that you can post.', 'cariera-packages' );
	}

	/**
	 * Listing duration text.
	 * This is used in dashboard active packages & [cariera_user_packages].
	 *
	 * @since   0.9.15
	 * @version 0.9.16
	 *
	 * @param bool $with_span
	 */
	protected function get_duration_text( $with_span = true ) {
		// translators: %s: resume duration value (e.g. 30 days).
		return $with_span ? __( 'Resume duration: <span>%s</span>', 'cariera-packages' ) : __( 'Resume duration: %s', 'cariera-packages' );
	}

	/**
	 * Package type label used in [cariera_user_packages] shortcode.
	 *
	 * @since 0.9.15
	 */
	protected function package_type_label() {
		return esc_html__( 'Resume Submission', 'cariera-packages' );
	}

	/**
	 * Get the remaining-listings label.
	 *
	 * @since 0.9.15
	 */
	protected function remaining_label() {
		return esc_html__( 'Resumes Remaining', 'cariera-packages' );
	}
}
