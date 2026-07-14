<?php

namespace Cariera_Packages\Integration\Resume;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class View {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Post type
	 *
	 * @var string
	 */
	private $post_type = 'resume';

	/**
	 * Package type
	 *
	 * @var string
	 */
	private $package_type = 'resume_view_package';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Single template handling.
		add_filter( 'single_template', [ $this, 'single_template' ], 10, 3 );

		// AJAX Split View Restriction.
		add_action( 'cariera_listing_split_view_before', [ $this, 'enqueue_assets_split_view' ] );
		add_filter( 'cariera_before_split_view_template_loading', [ $this, 'handle_split_view_restriction' ], 10, 3 );

		// Handling the submission form.
		add_action( 'wp', [ $this, 'form_handler' ] );

		// Template form output action.
		add_action( 'cariera_packages_single_resume', [ $this, 'form_output' ] );

		// Packages descriptions.
		add_action( 'cariera_packages_user_package_description', [ $this, 'user_package_description' ], 10, 2 );
		add_action( 'cariera_packages_user_package_footer', [ $this, 'user_package_footer' ], 10, 2 );
		add_action( 'cariera_packages_package_description', [ $this, 'package_description' ], 10, 2 );
		add_action( 'cariera_packages_package_footer', [ $this, 'package_footer' ], 10, 2 );

		// Cariera Active & User Packages.
		add_filter( 'cariera_user_packages', [ $this, 'user_packages_count' ] );
		add_action( 'cariera_user_packages_content', [ $this, 'user_packages_content' ] );
		add_filter( 'cariera_dashboard_active_packages', [ $this, 'active_packages_count' ] );
		add_filter( 'cariera_dashboard_active_packages_content', [ $this, 'active_packages_content' ], 10, 2 );

		// Single resume Listing Notice.
		add_action( 'single_resume_start', [ $this, 'view_resume_notice' ] );
	}

	/**
	 * Filter on Single Template
	 *
	 * Listing authors will be able to view their own listings without a package.
	 *
	 * @since   0.9.5
	 * @version 0.9.24
	 *
	 * @param mixed  $template
	 * @param string $type
	 * @param mixed  $templates
	 */
	public function single_template( $template, $type, $templates ) {
		global $post;

		// Check post type.
		if ( $this->post_type !== $post->post_type ) {
			return $template;
		}

		// Return if resume view package is required.
		$required_package = get_option( 'cariera_packages_require_view_package' );
		if ( ! isset( $required_package[ $this->post_type ] ) || ! $required_package[ $this->post_type ] ) {
			return $template;
		}

		// Admins are restricted if the option requires packages.
		$admin_required = get_option( 'cariera_packages_admin_require_view_package' );
		if ( ! $admin_required[ $this->post_type ] && current_user_can( 'manage_options' ) ) {
			return $template;
		}

		// View feature listings without a package if option is enabled.
		$featured_required = get_option( 'cariera_packages_view_featured_listing' );
		if ( $featured_required[ $this->post_type ] && ! empty( $post->_featured ) ) {
			return $template;
		}

		// If there are no active packages, return the original template.
		$packages = \Cariera_Packages\Helpers::get_package_products( $this->package_type );
		if ( count( $packages ) === 0 ) {
			return $template;
		}

		// Check if user can fix view the resume listing.
		if ( $this->user_can_view( $post->ID ) ) {
			return $template;
		}

		$single_template = CARIERA_PACKAGES_PATH . 'templates/single-resume.php';

		if ( ! file_exists( $single_template ) ) {
			return $template;
		}

		return $single_template;
	}

	/**
	 * Enqueue assets for split view template.
	 *
	 * @since 0.9.22
	 */
	public function enqueue_assets_split_view() {
		wp_enqueue_style( 'cariera-packages' );
		wp_enqueue_script( 'cariera-packages' );
	}

	/**
	 * Handle split view restriction for resume listings.
	 *
	 * @since 0.9.7
	 *
	 * @param string  $output
	 * @param WP_Post $listing
	 * @param string  $post_type
	 */
	public function handle_split_view_restriction( $output, $listing, $post_type ) {
		if ( ! $listing instanceof \WP_Post || $post_type !== $this->post_type ) {
			return $output;
		}

		$required_package  = get_option( 'cariera_packages_require_view_package' );
		$admin_required    = get_option( 'cariera_packages_admin_require_view_package' );
		$featured_required = get_option( 'cariera_packages_view_featured_listing' );
		$packages          = \Cariera_Packages\Helpers::get_package_products( $this->package_type );

		// View restriction not enabled.
		if ( ! isset( $required_package[ $this->post_type ] ) || ! $required_package[ $this->post_type ] ) {
			return $output;
		}

		// Allow admin access if enabled.
		if ( ! $admin_required[ $this->post_type ] && current_user_can( 'manage_options' ) ) {
			return $output;
		}

		// Allow viewing featured listings if permitted.
		if ( $featured_required[ $this->post_type ] && ! empty( $listing->_featured ) ) {
			return $output;
		}

		// No package products available.
		if ( count( $packages ) === 0 ) {
			return $output;
		}

		// Check if user has a valid package.
		if ( $this->user_can_view( $listing->ID ) ) {
			return $output;
		}

		// Load restricted template content.
		ob_start();

		$template = CARIERA_PACKAGES_PATH . 'templates/content-single-resume.php';

		if ( file_exists( $template ) ) {
			include $template;
		}

		return ob_get_clean();
	}

	/**
	 * Check if user can view the listing
	 *
	 * @since   0.9.5
	 * @version 0.9.21
	 *
	 * @param int $listing_id
	 */
	public function user_can_view( $listing_id = false ) {
		// Here I need a check that will handle if the user has already checked the resume.
		$user_id        = get_current_user_id();
		$user_packages  = \Cariera_Packages\Helpers::get_user_packages( $user_id, $this->package_type );
		$listing_author = get_post_field( 'post_author', $listing_id );

		// Check if user is the author of the listing.
		if ( absint( $listing_author ) === $user_id || $this->is_share_link( $listing_id ) ) {
			return true;
		}

		// Check if the user has a valid package for viewing this resume.
		if ( $user_packages ) {
			foreach ( $user_packages as $package ) {
				if ( $this->can_view_listing( $user_id, $package, $listing_id ) ) {
					// Valid package found, the user can view the resume.
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if listing is shared via share link
	 *
	 * @since 0.9.21
	 *
	 * @param int $listing_id
	 */
	public function is_share_link( $listing_id ) {
		if ( is_object( $listing_id ) && $listing_id->ID ) {
			$listing_id = $listing_id->ID;
		}

		$is_share_link = false;

		$key = get_post_meta( $listing_id, 'share_link_key', true );

		if ( $key && ! empty( $_GET['key'] ) && $key === $_GET['key'] ) { // phpcs:ignore
			$is_share_link = true;
		}

		return apply_filters( 'cariera_packages_resume_is_share_link', $is_share_link, $listing_id, $key );
	}

	/**
	 * Validates the user package and listing based on given parameters.
	 *
	 * @since 0.9.5
	 *
	 * @param int $user_id          The ID of the user to validate against.
	 * @param int $user_package_id  The ID of the user package to validate.
	 * @param int $listing_id       The ID of the listing to check in viewed resumes.
	 */
	public function can_view_listing( $user_id, $user_package_id, $listing_id ) {
		$prefix         = 'cariera_packages_';
		$user_id_meta   = get_post_meta( $user_package_id, $prefix . 'user_id', true );
		$viewed_resumes = get_post_meta( $user_package_id, $prefix . 'viewed_resumes', true );

		// Check if the user ID matches.
		if ( absint( $user_id_meta ) !== $user_id ) {
			return false;
		}

		// Check if viewed_resumes is not empty and contains the listing ID.
		if ( empty( $viewed_resumes ) ) {
			return false;
		}

		// Convert viewed_resumes into an array of integers.
		$viewed_resumes_array = array_map( 'intval', array_filter( explode( ',', $viewed_resumes ), 'is_numeric' ) );

		// Check if the listing ID exists in the viewed_resumes array.
		if ( ! in_array( $listing_id, $viewed_resumes_array, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Outputting the main form to the page.
	 *
	 * @since   0.9.5
	 * @version 0.9.10
	 */
	public function form_output() {
		$user_id       = get_current_user_id();
		$listing_id    = get_the_ID();
		$packages      = \Cariera_Packages\Helpers::get_package_products( $this->package_type );
		$user_packages = \Cariera_Packages\Helpers::get_user_packages( $user_id, 'resume_view_package', true );

		wp_enqueue_style( 'cariera-packages' );
		wp_enqueue_script( 'cariera-packages' );
		?>

		<form method="post" id="<?php echo esc_attr( $this->package_type ); ?>_selection" class="cariera-package-selection">
			<div class="cariera_packages_title">
				<input type="submit" name="continue" class="button" value="<?php esc_attr_e( 'Select a View Package', 'cariera-packages' ); ?>" />
				<input type="hidden" name="cariera_packages_form" value="<?php echo esc_attr( 'cariera_packages_form' ); ?>" />
				<input type="hidden" name="package_post_type" value="<?php echo esc_attr( $this->post_type ); ?>" />
				<input type="hidden" name="package_type" value="<?php echo esc_attr( $this->package_type ); ?>" />
				<input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing_id ); ?>" /> 
				<h2><?php esc_html__( 'Please select a package to view this listing\'s details.', 'cariera-packages' ); ?></h2>
			</div>
			<div class="cariera-packages resume_packages">
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
	 * User Package description
	 *
	 * @since   0.9.5
	 * @version 0.9.21
	 *
	 * @param int    $package_id
	 * @param string $package_type
	 */
	public function user_package_description( $package_id, $package_type ) {
		if ( $this->package_type !== $package_type ) {
			return;
		}

		$view_limit = get_post_meta( $package_id, 'cariera_packages_view_resume_limit', true );
		$used_views = $this->used_views_count( $package_id );

		if ( empty( $view_limit ) ) {
			printf(
				// Translators: %1$s. Number of used views.
				esc_html__( '%1$s out of unlimited views have been used.', 'cariera-packages' ),
				esc_html( $used_views )
			);
		} else {
			printf(
				// Translators: %1$s. Number of used views, %2$d. Total number of available views.
				esc_html__( '%1$s out of %2$d available views used.', 'cariera-packages' ),
				esc_html( $used_views ),
				esc_html( $view_limit )
			);
		}
	}

	/**
	 * User Package footer text
	 *
	 * @since 0.9.5
	 *
	 * @param int    $package_id
	 * @param string $package_type
	 */
	public function user_package_footer( $package_id, $package_type ) {
		if ( $this->package_type !== $package_type ) {
			return;
		}

		// Get package data.
		$view_limit      = get_post_meta( $package_id, 'cariera_packages_view_resume_limit', true );
		$used_views      = $this->used_views_count( $package_id );
		$remaining_views = esc_html__( 'Unlimited', 'cariera-packages' );

		if ( ! empty( $view_limit ) ) {
			// Remaining views calculation.
			$remaining_views = max( 0, $view_limit - $used_views );
		}

		?>
		<span class="price">
			<?php echo esc_html( $remaining_views ); ?>
		</span>
		<span class="caption">
			<?php esc_html_e( 'Remaining Views', 'cariera-packages' ); ?>
		</span>
		<?php
	}

	/**
	 * Return the number of used resume views.
	 *
	 * @param int $package_id
	 */
	private function used_views_count( $package_id ) {
		// Define the meta key prefix.
		$prefix = 'cariera_packages_';

		// Retrieve the viewed resumes meta value.
		$resume_ids = get_post_meta( $package_id, $prefix . 'viewed_resumes', true );

		// Return the count of viewed resumes, handling empty or invalid values gracefully.
		return ! empty( $resume_ids ) ? count( array_filter( explode( ',', $resume_ids ), 'is_numeric' ) ) : 0;
	}

	/**
	 * Package description
	 *
	 * @since 0.9.5
	 *
	 * @param int    $package_id
	 * @param string $package_type
	 */
	public function package_description( $package_id, $package_type ) {
		if ( $this->package_type !== $package_type ) {
			return;
		}

		$view_limit = $package_id->_view_resume_limit;
		$short_desc = $package_id->_package_use_sd;

		// Handle missing or invalid view limit data.
		if ( empty( $view_limit ) ) {
			$view_limit = esc_html__( 'Unlimited', 'cariera-packages' );
		}

		if ( 'yes' === $short_desc ) {
			echo '<div class="short-description">';
			echo wp_kses_post( $package_id->post_excerpt );
			echo '</div>';
		}

		// Output the description with correct pluralization.
		printf(
			esc_html(
				_n(
					'View %s single resume listing.',
					'View %s single resume listings.',
					is_numeric( $view_limit ) ? (int) $view_limit : 2, // Default to plural if 'Unlimited'.
					'cariera-packages'
				)
			),
			esc_html( $view_limit )
		);
	}

	/**
	 * Package footer text
	 *
	 * @since 0.9.5
	 *
	 * @param WP_POST|int $package_id
	 * @param string      $package_type
	 */
	public function package_footer( $package_id, $package_type ) {
		if ( $this->package_type !== $package_type ) {
			return;
		}

		$product = wc_get_product( $package_id );
		?>
		<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		<?php
	}

	/**
	 * Creates a Resume View Package for a User.
	 *
	 * @since   0.9.5
	 * @version 0.9.9
	 *
	 * @param int $user_id    The ID of the user who will own the package.
	 * @param int $product_id The ID of the WooCommerce product representing the package.
	 * @param int $order_id   The ID of the WooCommerce order associated with the package.
	 * @param int $listing_id   The ID of the listing associated with the package.
	 *
	 * @return int|false The ID of the created package post on success, or false on failure.
	 */
	public static function create_user_view_package( $user_id, $product_id, $order_id, $listing_id = null ) {
		// Validate the user ID.
		if ( empty( $user_id ) || ! is_numeric( $user_id ) || ! get_user_by( 'id', $user_id ) ) {
			return false; // Invalid user ID, abort the process.
		}

		// Fetch the WooCommerce product.
		$package = wc_get_product( $product_id );

		// Validate product type; only proceed for valid package types.
		if ( ! $package || ! $package->is_type( [ 'cariera_package', 'cariera_package_subscription' ] ) ) {
			return false;
		}

		// Get the package type for further validation or use.
		$package_type = \Cariera_Packages\Helpers::get_product_package_type( $product_id );

		// Prepare the post arguments.
		$args = apply_filters(
			'cariera_packages_resume_view_package_data',
			[
				'post_title'  => $package->get_title(),
				'post_status' => 'publish',
				'post_type'   => \Cariera_Packages\Post_Types\Cariera_Package::CPT_PACKAGE,
			],
			$user_id,
			$product_id,
			$order_id
		);

		// Insert the new package post.
		$user_package_id = wp_insert_post( $args );

		// If the post creation fails, return false.
		if ( ! $user_package_id || is_wp_error( $user_package_id ) ) {
			return false;
		}

		// General meta data prefix.
		$prefix = 'cariera_packages_';

		// Update general meta data for the package.
		update_post_meta( $user_package_id, "{$prefix}product_id", $product_id );
		update_post_meta( $user_package_id, "{$prefix}order_id", $order_id );
		update_post_meta( $user_package_id, "{$prefix}user_id", $user_id );
		update_post_meta( $user_package_id, "{$prefix}package_type", $package_type );

		// Retrieve and update package-specific meta data.
		$view_limit = get_post_meta( $product_id, '_view_resume_limit', true );

		// Update package-specific meta data.
		update_post_meta( $user_package_id, "{$prefix}view_resume_limit", $view_limit );

		// Assign listing to the package if listing_id exists.
		if ( ! empty( $listing_id ) ) {
			update_post_meta( $user_package_id, "{$prefix}viewed_resumes", $listing_id );
		} else {
			update_post_meta( $user_package_id, "{$prefix}viewed_resumes", '' );
		}

		// Trigger a custom action hook for additional processing or integrations.
		do_action( 'cariera_packages_resume_view_package_meta', $user_package_id, $user_id, $product_id, $order_id );

		// Return the ID of the newly created package post.
		return $user_package_id;
	}

	/**
	 * Form submission handling
	 *
	 * @since   0.9.5
	 * @version 0.9.16
	 */
	public function form_handler() {
		if ( empty( $_POST['cariera_packages_form'] ) ) {
			return;
		}

		if ( $this->post_type !== $_POST['package_post_type'] || $this->package_type !== $_POST['package_type'] ) {
			return;
		}

		$package_id    = isset( $_POST['cariera_package'] ) ? sanitize_text_field( wp_unslash( $_POST['cariera_package'] ) ) : '';
		$user_id       = get_current_user_id();
		$invalid_chars = 'INVALIDCHARACTER';
		$listing_id    = isset( $_REQUEST['listing_id'] ) ? absint( $_REQUEST['listing_id'] ) : get_the_ID();
		$package_type  = isset( $_REQUEST['package_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['package_type'] ) ) : $this->package_type;

		// User Package Selected.
		if ( ! is_numeric( $package_id ) ) {

			// Strip out `user-` as all user packages start with `user-`.
			$user_package_id = absint( substr( $package_id, 5 ) );

			// Verify valid package with available uses.
			if ( $this->is_valid( $user_id, $user_package_id, $listing_id ) ) {

				do_action( 'cariera_packages_form_user_package_before_add_post', $listing_id, $user_package_id, $package_type );

				// Add passed listing ID to used posts.
				$result = $this->add_viewed_listing( $user_id, $user_package_id, $listing_id );

				do_action( 'cariera_packages_form_user_package_after_add_post', $result, $listing_id, $user_package_id, $package_type );

				// Set redirect to permalink of listing id.
				$listing_url = get_permalink( $listing_id );

				// Add a notice query parameter to the URL.
				$redirect = add_query_arg(
					[
						'notice' => 'listing_viewed',
					],
					$listing_url
				);

				// Redirect back to origin listing.
				wp_redirect( $redirect );
				exit;
			}
		} else {
			// Purchase Package Selected.
			$meta = [
				'listing_id'   => $listing_id,
				'package_type' => $package_type,
			];

			do_action( 'cariera_packages_form_process_form_before', $package_id, $meta );

			\Cariera_Packages\WooCommerce\Cart::process_form( $package_id, $meta );
		}
	}

	/**
	 * Check if package is valid in order to view a listing
	 *
	 * @since 0.9.5
	 *
	 * @param int $user_id          The ID of the user to validate against.
	 * @param int $user_package_id  The ID of the user package to validate.
	 * @param int $listing_id       The ID of the listing to check in viewed resumes.
	 */
	private function is_valid( $user_id, $user_package_id, $listing_id ) {
		$prefix = 'cariera_packages_';

		// Retrieve meta data for the package.
		$user_id_meta   = get_post_meta( $user_package_id, $prefix . 'user_id', true );
		$view_limit     = get_post_meta( $user_package_id, $prefix . 'view_resume_limit', true );
		$viewed_resumes = get_post_meta( $user_package_id, $prefix . 'viewed_resumes', true );

		// Default: assume the package is invalid.
		$is_valid = false;

		// Validate the user ID.
		if ( absint( $user_id_meta ) !== absint( $user_id ) ) {
			return false;
		}

		if ( empty( $view_limit ) ) {
			return true;
		}

		// Convert viewed_resumes into an array of integers.
		$viewed_resumes_array = array_map( 'intval', array_filter( explode( ',', $viewed_resumes ), 'is_numeric' ) );

		// Validate the package: is it still within the view limit?
		if ( count( $viewed_resumes_array ) < absint( $view_limit ) ) {
			$is_valid = true;
		}

		return $is_valid;
	}

	/**
	 * Adds a viewed listing to a user's package.
	 *
	 * @since 0.9.5
	 *
	 * @param int $user_id         The ID of the user viewing the listing.
	 * @param int $user_package_id The ID of the user's package being updated.
	 * @param int $listing_id      The ID of the listing being viewed.
	 */
	private function add_viewed_listing( $user_id, $user_package_id, $listing_id ) {
		// Ensure required parameters are provided.
		if ( empty( $user_id ) || empty( $user_package_id ) || empty( $listing_id ) ) {
			return false;
		}

		$prefix = 'cariera_packages_';

		// Get user package and validate it.
		$user_package = get_post( $user_package_id );
		if ( empty( $user_package ) ) {
			return false;
		}

		// Verify the package belongs to the user.
		$package_user_id = get_post_meta( $user_package_id, $prefix . 'user_id', true );
		if ( $package_user_id != $user_id ) {
			return false;
		}

		// Fetch the current list of viewed resumes.
		$viewed_resumes = get_post_meta( $user_package_id, $prefix . 'viewed_resumes', true );

		// Process the viewed resumes list.
		if ( ! empty( $viewed_resumes ) ) {
			$viewed_resumes_array = array_map( 'trim', explode( ',', $viewed_resumes ) );

			// Add the new listing ID if it's not already in the list
			if ( ! in_array( $listing_id, $viewed_resumes_array, true ) ) {
				$viewed_resumes_array[] = $listing_id;
			}
		} else {
			$viewed_resumes_array = [ $listing_id ];
		}

		// Save the updated list back to the database.
		$updated = update_post_meta( $user_package_id, $prefix . 'viewed_resumes', implode( ',', $viewed_resumes_array ) );

		// Return whether the update was successful.
		return $updated !== false;
	}

	/**
	 * Update the user packages count for Cariera User Packages compatibility
	 *
	 * @since 0.9.5
	 *
	 * @param int $packages number of user packages
	 */
	public function user_packages_count( $packages ) {
		$user_id       = get_current_user_id();
		$user_packages = \Cariera_Packages\Helpers::get_user_packages( $user_id, 'resume_view_package' );

		return $packages + count( $user_packages );
	}

	/**
	 * Cariera User Packages compatibility - add packages content
	 *
	 * @since   0.9.5
	 * @version 0.9.12
	 */
	public function user_packages_content() {
		$user_id       = get_current_user_id();
		$view_packages = \Cariera_Packages\Helpers::get_user_packages( $user_id, 'resume_view_package' );

		// Loop through the custom packages and output rows.
		if ( empty( $view_packages ) ) {
			return;
		}

		foreach ( $view_packages as $package_id ) {

			$package = get_post( $package_id );

			$view_limit = get_post_meta( $package_id, 'cariera_packages_view_resume_limit', true );
			$used_views = $this->used_views_count( $package_id );

			// Early exit for invalid package.
			if ( ! $package ) {
				continue;
			}
			?>
			<tr>
				<td class="package-order-id">
					<?php echo ! empty( $package->cariera_packages_order_id ) ? esc_html( $package->cariera_packages_order_id ) : esc_html__( 'No order ID', 'cariera-packages' ); ?>
				</td>
				<td class="package-title">
					<h6><?php echo esc_html( $package->post_title ); ?></h6>

					<p><?php wp_kses_post( printf( __( 'You have <span>%s</span> resume views left.', 'cariera-packages' ), $view_limit ? absint( $view_limit - $used_views ) : esc_html__( 'Unlimited', 'cariera-packages' ) ) ); ?></p>
				</td>

				<td class="package-type"><?php esc_html_e( 'Resume View', 'cariera-packages' ); ?></td>
				<td class="package-status">
					<?php
					$package_used = $used_views >= $view_limit && $view_limit !== 0;

					if ( $package_used ) {
						echo '<span class="status used">' . esc_html__( 'Used', 'cariera-packages' ) . '</span>';
					} else {
						echo '<span class="status active">' . esc_html__( 'Active', 'cariera-packages' ) . '</span>';
					}
					?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Update the active packages count for Cariera Dashboard Active Packages compatibility
	 *
	 * @since 0.9.5
	 *
	 * @param int $packages number of user packages.
	 */
	public function active_packages_count( $packages ) {
		$user_id       = get_current_user_id();
		$user_packages = \Cariera_Packages\Helpers::get_user_packages( $user_id, 'resume_view_package', true );

		return $packages + count( $user_packages );
	}

	/**
	 * Cariera dashboard active packages compatibility - packages content
	 *
	 * @since 0.9.2
	 *
	 * @param int $displayed_packages
	 * @param int $max_packages
	 */
	public function active_packages_content( $displayed_packages, $max_packages ) {
		$user_id       = get_current_user_id();
		$view_packages = \Cariera_Packages\Helpers::get_user_packages( $user_id, 'resume_view_package', true );

		// Loop through the custom packages and output rows.
		if ( empty( $view_packages ) ) {
			return;
		}

		foreach ( $view_packages as $package_id ) {
			if ( $displayed_packages >= $max_packages ) {
				break;
			}
			$package = get_post( $package_id );

			$view_limit = get_post_meta( $package_id, 'cariera_packages_view_resume_limit', true );
			$used_views = $this->used_views_count( $package_id );

			// Early exit for invalid package.
			if ( ! $package ) {
				continue;
			}
			?>

			<li class="package">
				<i class="lar la-eye"></i>

				<div class="content">
					<h6 class="package-title"><?php echo esc_html( $package->post_title ); ?></h6>
					
					<p><?php printf( esc_html__( 'You can view %s more resume listings.', 'cariera-packages' ), $view_limit ? absint( $view_limit - $used_views ) : esc_html__( 'Unlimited', 'cariera-packages' ) ); ?></p>
				</div>
			</li>
			<?php
		}
	}

	/**
	 * Add notice when resume can be viewed.
	 *
	 * @since 0.9.5
	 */
	public function view_resume_notice() {
		if ( ! isset( $_GET['notice'] ) || 'listing_viewed' !== $_GET['notice'] ) {
			return;
		}

		echo '<div class="job-manager-message success">' . esc_html__( 'You can now view this resume listing!', 'cariera-packages' ) . '</div>';
	}
}
