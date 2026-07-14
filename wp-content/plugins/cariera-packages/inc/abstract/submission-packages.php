<?php

namespace Cariera_Packages\Abstract;

use Cariera_Packages\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract base class for package-based submission flows.
 */
abstract class Submission_Packages {

	/**
	 * The CPT slug (e.g. 'job_listing', 'resume', 'company').
	 *
	 * @var string
	 */
	protected $post_type = '';

	/**
	 * The package type slug (e.g. 'job_submission_package').
	 *
	 * @var string
	 */
	protected $package_type = '';

	/**
	 * The submission type key used in meta (e.g. 'job_submission, resume_submission').
	 *
	 * @var string
	 */
	protected $submission_type = '';

	/**
	 * Product meta key for the listing submission limit.
	 *
	 * @var string
	 */
	protected $product_limit_meta_key = '';

	/**
	 * Product meta key for the listing submission duration.
	 *
	 * @var string
	 */
	protected $product_duration_meta_key = '';

	/**
	 * Product meta key for the listing submission featured status.
	 *
	 * @var string
	 */
	protected $product_featured_meta_key = '';

	/**
	 * Posted / cookie-stored package ID.
	 *
	 * @var int
	 */
	protected static $package_id = 0;

	/**
	 * Whether the stored package ID refers to a user package (vs a product ID).
	 *
	 * @var bool
	 */
	protected static $is_user_package = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Derive submission_type from package_type when not set explicitly.
		if ( empty( $this->submission_type ) && ! empty( $this->package_type ) ) {
			$this->submission_type = str_replace( '_package', '', $this->package_type );
		}

		// Derive product meta keys from post_type when not set explicitly.
		if ( empty( $this->product_limit_meta_key ) ) {
			$this->product_limit_meta_key = "_{$this->post_type}_limit";
		}

		if ( empty( $this->product_duration_meta_key ) ) {
			$this->product_duration_meta_key = "_{$this->post_type}_duration";
		}

		$this->handle_submitted_package();
		$this->register_hooks();
	}

	/**
	 * Abstract method to register hooks for this submission type.
	 *
	 * @since 0.9.14
	 */
	abstract protected function register_hooks();

	/**
	 * Abstract method to render the "choose package" form.
	 *
	 * @since 0.9.14
	 */
	abstract public function choose_package_form();

	/**
	 * Abstract method to handle the "choose package" form submission.
	 *
	 * @since 0.9.14
	 */
	abstract public function choose_package_handler();

	/**
	 * Abstract method to create a user submission package post after a successful order.
	 *
	 * @since 0.9.14
	 *
	 * @param int      $user_id
	 * @param int      $product_id
	 * @param int      $order_id
	 * @param int|null $listing_id
	 */
	abstract public static function create_user_submission_package( $user_id, $product_id, $order_id, $listing_id = null );

	/**
	 * Abstract method to assign a pre-purchased "user package" to a listing.
	 *
	 * @since 0.9.14
	 *
	 * @param int  $user_package_id
	 * @param int  $listing_id
	 * @param bool $is_renewal
	 */
	abstract protected static function assign_user_package_to_listing( $user_package_id, $listing_id, $is_renewal );

	/**
	 * Abstract method to link products to a listing.
	 *
	 * @param int  $product_id
	 * @param int  $listing_id
	 * @param bool $is_renewal
	 */
	abstract protected static function assign_product_to_listing( int $product_id, int $listing_id, bool $is_renewal );

	/**
	 * Handle posted package data from form submission or cookie. This is needed to ensure the package information is available early in the process, for example when rendering the title on the submit job page.
	 *
	 * @since   0.9.12
	 * @version 0.9.15
	 */
	private function handle_submitted_package() {
		if ( ! empty( $_POST['cariera_package'] ) ) { // phpcs:ignore
			// A numeric value refers to a product ID, while the 'user-{id}' format refers to an already-purchased user package.
			if ( is_numeric( $_POST['cariera_package'] ) ) { // phpcs:ignore
				self::$package_id      = absint( $_POST['cariera_package'] ); // phpcs:ignore
				self::$is_user_package = false;
			} else {
				self::$package_id      = absint( substr( sanitize_text_field( wp_unslash( $_POST['cariera_package'] ) ), 5 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				self::$is_user_package = true;
			}
		} elseif ( ! empty( $_COOKIE['chosen_package_id'] ) ) {
			self::$package_id      = absint( sanitize_text_field( wp_unslash( $_COOKIE['chosen_package_id'] ) ) ); // phpcs:ignore
			self::$is_user_package = absint( sanitize_text_field( wp_unslash( $_COOKIE['chosen_package_is_user_package'] ) ) ) === 1; // phpcs:ignore
		}
	}

	/**
	 * Return the WooCommerce product ID for the selected package, resolving user-package references to their underlying product.
	 *
	 * @since 0.9.14
	 */
	public static function get_package_id() {
		if ( static::$is_user_package ) {
			return (int) get_post_meta( static::$package_id, 'cariera_packages_product_id', true );
		}

		return static::$package_id;
	}

	/**
	 * Validate the selected package.
	 *
	 * @since 0.9.14
	 *
	 * @param int  $package_id
	 * @param bool $is_user_package
	 *
	 * TODO: Add support for WC Subscriptions.
	 */
	protected static function validate_package( $package_id, $is_user_package ) {
		if ( empty( $package_id ) ) {
			return new \WP_Error( 'error', __( 'Invalid Package', 'cariera-packages' ) );
		} elseif ( $is_user_package ) {
			if ( ! \Cariera_Packages\Helpers::user_package_is_valid( get_current_user_id(), $package_id ) ) {
				return new \WP_Error( 'error', __( 'Invalid Package', 'cariera-packages' ) );
			}
		} else {
			$package = wc_get_product( $package_id );

			if ( ! $package->is_type( 'cariera_package' ) && ! $package->is_type( 'cariera_package_subscription' ) ) {
				return new \WP_Error( 'error', __( 'Invalid Package', 'cariera-packages' ) );
			}

			// TODO: WC Subscriptions support to be added.
			// Don't let them buy the same subscription twice if the subscription is for the package
			// if ( class_exists( 'WC_Subscriptions' ) && is_user_logged_in() && $package->is_type( 'job_package_subscription' ) && $package instanceof WC_Product_Job_Package_Subscription && 'package' === $package->get_package_subscription_type() ) {
			// if ( wcs_user_has_subscription( get_current_user_id(), $package_id, 'active' ) ) {
			// return new \WP_Error( 'error', __( 'You already have this subscription.', 'wp-job-manager-wc-paid-listings' ) );
			// }
			// }
		}

		return true;
	}

	/**
	 * Assign package to listing
	 *
	 * @since   0.9.12
	 * @version 0.9.15
	 *
	 * @param  int|string $package_id
	 * @param  bool       $is_user_package
	 * @param  int        $listing_id
	 */
	protected static function assign_package_to_listing( $package_id, $is_user_package, $listing_id ) {
		// Make sure the job has the correct status.
		if ( 'preview' === get_post_status( $listing_id ) ) {
			$update_listing                  = [];
			$update_listing['ID']            = $listing_id;
			$update_listing['post_status']   = 'pending_payment';
			$update_listing['post_date']     = current_time( 'mysql' );
			$update_listing['post_date_gmt'] = current_time( 'mysql', 1 );
			$update_listing['post_author']   = get_current_user_id();
			wp_update_post( $update_listing );
		}

		$is_renewal = isset( $_GET['action'] ) && 'renew' === $_GET['action']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used for comparison.

		if ( $is_user_package ) {
			return static::assign_user_package_to_listing( $package_id, $listing_id, $is_renewal );
		}

		if ( $package_id ) {
			static::assign_product_to_listing( $package_id, $listing_id, $is_renewal );
		}

		return false;
	}

	/**
	 * Ensure new listings start as 'pending_payment' instead of 'publish' / 'pending'.
	 * Hooked to submit_{post_type}_post_status by each subclass.
	 *
	 * @since 0.9.15
	 *
	 * @param string   $status
	 * @param \WP_Post $listing
	 */
	public static function submit_listing_post_status( $status, $listing ) {
		if ( 'preview' === $listing->post_status ) {
			return 'pending_payment';
		}

		if ( 'expired' === $listing->post_status ) {
			return 'expired';
		}

		return $status;
	}

	/**
	 * Package type label used in [cariera_user_packages] shortcode.
	 *
	 * @since 0.9.14
	 */
	protected function package_type_label() {
		return esc_html__( 'Submission', 'cariera-packages' );
	}

	/**
	 * Get the remaining-listings label.
	 *
	 * @since 0.9.14
	 */
	protected function remaining_label() {
		return esc_html__( 'Listings Remaining', 'cariera-packages' );
	}

	/**
	 * Return the "Choose a package →" button label for the preview step.
	 *
	 * @since 0.9.15
	 */
	public static function submit_button_text() {
		return __( 'Choose a package &rarr;', 'cariera-packages' );
	}

	/**
	 * Remaining listings text content.
	 * This is used in dashboard active packages & [cariera_user_packages].
	 *
	 * @since   0.9.14
	 * @version 0.9.16
	 *
	 * @param bool $with_span
	 */
	protected function get_remaining_listings_text( $with_span = true ) {
		// translators: %s: number of remaining job listings the user can post.
		return $with_span ? __( 'You have <span>%s</span> listings left that you can post.', 'cariera-packages' ) : __( 'You have %s listings left that you can post.', 'cariera-packages' );
	}

	/**
	 * Listing duration text.
	 * This is used in dashboard active packages & [cariera_user_packages].
	 *
	 * @since   0.9.14
	 * @version 0.9.16
	 *
	 * @param bool $with_span
	 */
	protected function get_duration_text( $with_span = true ) {
		// translators: %s: duration of the listing (e.g., 30 days).
		return $with_span ? __( 'Listing duration: <span>%s</span>', 'cariera-packages' ) : __( 'Listing duration: %s', 'cariera-packages' );
	}

	/**
	 * Increment the active-packages count for the dashboard widget.
	 *
	 * @since 0.9.14
	 *
	 * @param int $packages
	 */
	public function active_packages_count( $packages ) {
		return $packages + count( Helpers::get_user_packages( get_current_user_id(), $this->package_type, true ) );
	}

	/**
	 * Increment the total user-package count.
	 *
	 * @since 0.9.14
	 *
	 * @param int $packages
	 */
	public function user_packages_count( $packages ) {
		return $packages + count( Helpers::get_user_packages( get_current_user_id(), $this->package_type ) );
	}

	/**
	 * Output table rows for the [cariera_user_packages] widget.
	 *
	 * @since   0.9.14
	 * @version 0.9.20
	 */
	public function user_packages_content() {
		$submission_packages = Helpers::get_user_packages( get_current_user_id(), $this->package_type );

		if ( empty( $submission_packages ) ) {
			return;
		}

		foreach ( $submission_packages as $package_id ) {
			$package = get_post( $package_id );

			// Early exit for invalid package.
			if ( ! $package ) {
				continue;
			}

			$type      = $this->submission_type;
			$limit     = (int) get_post_meta( $package_id, "cariera_packages_{$type}_limit", true );
			$used      = (int) get_post_meta( $package_id, "cariera_packages_{$type}_count", true );
			$duration  = (int) get_post_meta( $package_id, "cariera_packages_{$type}_duration", true );
			$order_id  = (int) get_post_meta( $package_id, 'cariera_packages_order_id', true );
			$exhausted = $limit > 0 && $used >= $limit;
			?>
			<tr>
				<td class="package-order-id">
					<?php echo ! empty( $order_id ) ? esc_html( $order_id ) : esc_html__( 'No order ID', 'cariera-packages' ); ?>
				</td>
				<td class="package-title">
					<h6><?php echo esc_html( $package->post_title ); ?></h6>
					<p>
						<?php
						printf(
							wp_kses_post( $this->get_remaining_listings_text() ),
							$limit ? absint( $limit - $used ) : esc_html__( 'Unlimited', 'cariera-packages' )
						);
						?>
					</p>
					<p>
						<?php
						printf(
							wp_kses_post( $this->get_duration_text() ),
							// Translators: %s: listing duration.
							$duration ? sprintf( _n( '%d day', '%d days', $duration, 'cariera-packages' ), $duration ) : '-', // phpcs:ignore
						);
						?>
					</p>
				</td>
				<td class="package-type"><?php echo esc_html( $this->package_type_label() ); ?></td>
				<td class="package-status">
					<?php if ( $exhausted ) { ?>
						<span class="status used"><?php esc_html_e( 'Used', 'cariera-packages' ); ?></span>
					<?php } else { ?>
						<span class="status active"><?php esc_html_e( 'Active', 'cariera-packages' ); ?></span>
					<?php } ?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Render the description for a user-package card.
	 *
	 * @since   0.9.14
	 * @version 0.9.20
	 *
	 * @param int    $package_id
	 * @param string $package_type
	 */
	public function user_package_description( $package_id, $package_type ) {
		if ( $this->package_type !== $package_type ) {
			return;
		}

		$package_type = Helpers::get_package_type( $package_id );
		$limit        = (int) get_post_meta( $package_id, "cariera_packages_{$this->submission_type}_limit", true );
		$count        = (int) get_post_meta( $package_id, "cariera_packages_{$this->submission_type}_count", true );
		$duration     = (int) get_post_meta( $package_id, "cariera_packages_{$this->submission_type}_duration", true );
		$featured     = (int) get_post_meta( $package_id, "cariera_packages_{$this->submission_type}_featured", true );

		echo esc_html( $this->get_user_package_description( $package_type, $limit, $count, $duration, $featured ) );
	}

	/**
	 * Get user package description in listing submission.
	 *
	 * @since   0.9.14
	 * @version 0.9.20
	 *
	 * @param string $package_type
	 * @param int    $limit
	 * @param int    $count
	 * @param int    $duration
	 * @param int    $featured
	 */
	protected function get_user_package_description( $package_type, $limit, $count, $duration, $featured ) {
		$label = $this->get_listing_label( $package_type, $count );

		// Listing submission limit.
		if ( $limit ) {
			// translators: 1: posted count, 2: label, 3: limit.
			$description = sprintf( esc_html__( '%1$d %2$s posted out of %3$d', 'cariera-packages' ), $count, $label, $limit );
		} else {
			// translators: 1: posted count, 2: label.
			$description = sprintf( esc_html__( '%1$d %2$s posted', 'cariera-packages' ), $count, $label );
		}

		// Listing submission duration.
		if ( $duration ) {
			// translators: %s: number of days.
			$description .= sprintf( ', ' . _n( 'listed for %s day', 'listed for %s days', $duration, 'cariera-packages' ), $duration );
		} else {
			$description .= esc_html__( ', listed for unlimited time', 'cariera-packages' );
		}

		// Featured listing.
		if ( $featured ) {
			$description .= '. ' . esc_html__( 'Submitted listings will be featured.', 'cariera-packages' );
		}

		return $description;
	}

	/**
	 * Render the footer for a user-package card.
	 *
	 * @since   0.9.14
	 * @version 0.9.17
	 *
	 * @param int    $package_id
	 * @param string $package_type
	 */
	public function user_package_footer( $package_id, $package_type ) {
		if ( $this->package_type !== $package_type ) {
			return;
		}

		$limit = (int) get_post_meta( $package_id, "cariera_packages_{$this->submission_type}_limit", true );
		$count = (int) get_post_meta( $package_id, "cariera_packages_{$this->submission_type}_count", true );

		if ( $limit <= 0 ) {
			$remaining = esc_html__( 'Unlimited', 'cariera-packages' );
		} else {
			$remaining = max( 0, $limit - $count );
		}
		?>
		<span class="price"><?php echo esc_html( $remaining ); ?></span>
		<span class="caption"><?php echo esc_html( $this->remaining_label() ); ?></span>
		<?php
	}

	/**
	 * Output list items for the dashboard active-packages widget.
	 *
	 * @since   0.9.14
	 * @version 0.9.20
	 *
	 * @param int $displayed_packages
	 * @param int $max_packages
	 */
	public function active_packages_content( &$displayed_packages, $max_packages ) {
		$submission_packages = Helpers::get_user_packages( get_current_user_id(), $this->package_type );

		if ( empty( $submission_packages ) ) {
			return;
		}

		foreach ( $submission_packages as $package_id ) {
			if ( $displayed_packages >= $max_packages ) {
				break;
			}

			$package = get_post( $package_id );
			if ( ! $package ) {
				continue;
			}

			$type      = $this->submission_type;
			$limit     = (int) get_post_meta( $package_id, "cariera_packages_{$type}_limit", true );
			$used      = (int) get_post_meta( $package_id, "cariera_packages_{$type}_count", true );
			$duration  = (int) get_post_meta( $package_id, "cariera_packages_{$type}_duration", true );
			$remaining = $limit > 0 ? max( 0, $limit - $used ) : false;

			// Skip fully-used packages (unlimited packages are never skipped).
			if ( $limit > 0 && 0 === $remaining ) {
				continue;
			}
			?>
			<li class="package">
				<i class="las la-rocket"></i>
				<div class="content">
					<h6 class="package-title"><?php echo esc_html( $package->post_title ); ?></h6>
					<p>
						<?php
						printf(
							esc_html( $this->get_remaining_listings_text( false ) ),
							false !== $remaining ? absint( $remaining ) : esc_html__( 'Unlimited', 'cariera-packages' )
						);
						?>
					</p>
					<p>
						<?php
						printf(
							esc_html( $this->get_duration_text( false ) ),
							// Translators: %s: number of days.
							$duration ? sprintf( _n( '%d day', '%d days', $duration, 'cariera-packages' ), $duration ) : '-', // phpcs:ignore
						);
						?>
					</p>
				</div>
			</li>
			<?php
			++$displayed_packages;
		}
	}

	/**
	 * Render the description for a product-package card.
	 *
	 * @since   0.9.14
	 * @version 0.9.20
	 *
	 * @param int|object $package_id
	 * @param string     $package_type
	 */
	public function buy_package_description( $package_id, $package_type ) {
		if ( $this->package_type !== $package_type ) {
			return;
		}

		$product = wc_get_product( is_object( $package_id ) ? $package_id->ID : $package_id );
		if ( ! $product ) {
			return;
		}

		$post       = get_post( $product->get_id() );
		$limit      = (int) get_post_meta( $product->get_id(), $this->product_limit_meta_key, true );
		$duration   = (int) get_post_meta( $product->get_id(), $this->product_duration_meta_key, true );
		$featured   = get_post_meta( $product->get_id(), $this->product_featured_meta_key, true );
		$short_desc = get_post_meta( $product->get_id(), '_package_use_sd', true );

		if ( 'yes' === $short_desc && ! empty( $post->post_excerpt ) ) {
			echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		$this->get_buy_package_description( $product, $limit, $duration, $featured );
	}

	/**
	 * Get buy package description in listing submission.
	 *
	 * @since   0.9.14
	 * @version 0.9.20
	 *
	 * @param \WC_Product $product
	 * @param int         $limit
	 * @param int         $duration
	 * @param int         $featured
	 */
	protected function get_buy_package_description( $product, $limit, $duration, $featured ) {
		$package_type = Helpers::get_product_package_type( $product );
		$price_html   = wp_kses_post( $product->get_price_html() );
		$count        = $limit ? $limit : esc_html__( 'Unlimited', 'cariera-packages' );
		$label        = $this->get_listing_label( $package_type, $count );

		// Output price and listing count.
		// translators: 1: item count, 2: label (CPT label).
		printf( esc_html__( '%1$s %2$s', 'cariera-packages' ), $count, $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $duration ) {
			// translators: %s: number of days.
			printf( ' - ' . _n( 'listed for %s day', 'listed for %s days', $duration, 'cariera-packages' ), $duration ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			esc_html_e( '- listed for unlimited time', 'cariera-packages' );
		}

		if ( 'yes' === $featured ) {
			echo '<br>';
			esc_html_e( ' Submitted listings will be featured.', 'cariera-packages' );
		}
	}

	/**
	 * Render the footer for a product-package card.
	 *
	 * @since   0.9.14
	 * @version 0.9.17
	 *
	 * @param int|object $package_id
	 * @param string     $package_type
	 */
	public function buy_package_footer( $package_id, $package_type ) {
		if ( $this->package_type !== $package_type ) {
			return;
		}

		$product = wc_get_product( is_object( $package_id ) ? $package_id->ID : $package_id );
		if ( ! $product ) {
			return;
		}

		$duration   = (int) get_post_meta( $product->get_id(), $this->product_duration_meta_key, true );
		$price_html = $product->get_price_html();

		if ( empty( $price_html ) ) {
			$price_html = esc_html__( 'Free', 'cariera-packages' );
		}
		?>
		<span class="price"><?php echo wp_kses_post( $price_html ); ?></span>
		<span class="caption">
			<?php
			if ( $duration ) {
				// translators: %s: number of days.
				printf( _n( '%s day published', '%s days published', $duration, 'cariera-packages' ), $duration ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				esc_html_e( 'Published for unlimited time', 'cariera-packages' );
			}
			?>
		</span>
		<?php
	}

	/**
	 * Get correct singular or plural label based on package type.
	 *
	 * @since 0.9.17
	 *
	 * @param string $package_type Package type string.
	 * @param int    $count        Number of items.
	 */
	private function get_listing_label( $package_type, $count ) {
		switch ( $package_type ) {
			case 'job_submission_package':
				$label_single = esc_html__( 'job', 'cariera-packages' );
				$label_plural = esc_html__( 'jobs', 'cariera-packages' );
				break;

			case 'resume_submission_package':
				$label_single = esc_html__( 'resume', 'cariera-packages' );
				$label_plural = esc_html__( 'resumes', 'cariera-packages' );
				break;

			default:
				$label_single = esc_html__( 'listing', 'cariera-packages' );
				$label_plural = esc_html__( 'listings', 'cariera-packages' );
		}

		return ( 1 === (int) $count ) ? $label_single : $label_plural;
	}
}