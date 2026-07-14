<?php

namespace Cariera_Packages\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Metaboxes {

	use \Cariera_Packages\Src\Traits\Singleton;

	/**
	 * Maps post_type → package_type slug.
	 *
	 * @var array
	 */
	private const PACKAGE_TYPE_MAP = [
		'job_listing' => 'job_submission_package',
		'resume'      => 'resume_submission_package',
	];

	/**
	 * Maps package_type → submission_type.
	 *
	 * @var array
	 */
	private const SUBMISSION_TYPE_MAP = [
		'job_submission_package'    => 'job_submission',
		'resume_submission_package' => 'resume_submission',
	];

	/**
	 * Maps post_type → listing duration meta key.
	 *
	 * @var array
	 */
	private const DURATION_META_MAP = [
		'job_listing' => '_job_duration',
		'resume'      => '_resume_duration',
	];

	/**
	 * Maps post_type → listing expiry meta key.
	 *
	 * @var array
	 */
	private const EXPIRY_META_MAP = [
		'job_listing' => '_job_expires',
		'resume'      => '_resume_expires',
	];

	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );

		// Switching packages via the metabox AJAX trigger.
		add_action( 'wp_ajax_cariera_packages_switch_submission_package', [ $this, 'switch_submission_package' ] );
	}

	/**
	 * Register the metabox on every supported CPT.
	 *
	 * @since 0.9.21
	 */
	public function register_meta_boxes(): void {
		foreach ( self::get_supported_submission_post_types() as $post_type ) {
			add_meta_box(
				'cariera_packages_assigned_package',
				esc_html__( 'Switch Package', 'cariera-packages' ),
				[ $this, 'render_listing_user_packages' ],
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the metabox content.
	 *
	 * @since 0.9.21
	 *
	 * @param \WP_Post $post
	 */
	public function render_listing_user_packages( \WP_Post $post ): void {
		$post_type             = get_post_type( $post->ID );
		$current_package_id    = (int) get_post_meta( $post->ID, '_user_package_id', true );
		$current_package_label = $current_package_id > 0 ? (string) $current_package_id : esc_html__( 'None', 'cariera-packages' );
		$package_type          = self::PACKAGE_TYPE_MAP[ $post_type ] ?? '';

		if ( empty( $package_type ) ) {
			return;
		}

		$packages = \Cariera_Packages\Helpers::get_user_packages( $post->post_author, $package_type, true );
		?>
		<p>
			<label for="cariera_user_package_id">
				<strong><?php esc_html_e( 'User Packages', 'cariera-packages' ); ?></strong>
			</label>
		</p>
		<select id="cariera_user_package_id" name="cariera_user_package_id" style="width:100%">
			<option value=""><?php esc_html_e( 'No package assigned', 'cariera-packages' ); ?></option>
			<?php
			foreach ( $packages as $package_id ) {
				$package_title = get_the_title( $package_id );
				?>
				<option value="<?php echo esc_attr( $package_id ); ?>" <?php selected( $current_package_id, $package_id ); ?>>
					<?php echo esc_html( $package_title ); ?>
				</option>
			<?php } ?>
		</select>

		<p style="margin-top:8px">
			<?php
			printf(
				/* translators: %d: current package post ID */
				esc_html__( 'Current Package ID: %s', 'cariera-packages' ),
				esc_html( $current_package_label )
			);
			?>
		</p>

		<button type="button" class="cariera-btn" id="cariera-switch-package">
			<?php esc_html_e( 'Switch Package', 'cariera-packages' ); ?>
		</button>

		<script>
		jQuery(function($){
			$('#cariera-switch-package').on('click', function(){
				const postId = <?php echo (int) $post->ID; ?>;
				const packageId = $('#cariera_user_package_id').val();

				$.post(ajaxurl, {
					action: 'cariera_packages_switch_submission_package',
					post_id: postId,
					package_id: packageId,
					nonce: '<?php echo esc_attr( wp_create_nonce( 'cariera_packages_assigned_package_nonce' ) ); ?>'
				}, function(response){
					alert(response.data);
					location.reload();
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for switching a listing's assigned package from the metabox.
	 *
	 * @since 0.9.21
	 */
	public function switch_submission_package() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'No permission' );
		}

		check_ajax_referer( 'cariera_packages_assigned_package_nonce', 'nonce' );

		$post_id    = absint( $_POST['post_id'] ?? 0 );
		$package_id = absint( $_POST['package_id'] ?? 0 );

		if ( ! $post_id ) {
			wp_send_json_error( 'Invalid post' );
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			wp_send_json_error( 'Post not found' );
		}

		$post_type       = $post->post_type;
		$submission_type = self::SUBMISSION_TYPE_MAP[ self::PACKAGE_TYPE_MAP[ $post_type ] ?? '' ] ?? '';
		$duration_meta   = self::DURATION_META_MAP[ $post_type ] ?? '';
		$expiry_meta     = self::EXPIRY_META_MAP[ $post_type ] ?? '';

		if ( empty( $submission_type ) ) {
			wp_send_json_error( esc_html__( 'Invalid type', 'cariera-packages' ) );
		}

		$old_package_id = (int) get_post_meta( $post_id, '_user_package_id', true );

		// Adjust counts.
		if ( $old_package_id ) {
			$this->adjust_submission_count( $old_package_id, $submission_type, -1 );
		}

		if ( $package_id ) {
			$this->apply_package_to_listing( $post_id, $package_id, $submission_type, $duration_meta, $expiry_meta );
			$this->adjust_submission_count( $package_id, $submission_type, +1 );
		} else {
			delete_post_meta( $post_id, '_package_id' );
			delete_post_meta( $post_id, '_user_package_id' );
			delete_post_meta( $post_id, '_featured' );
			delete_post_meta( $post_id, $duration_meta );
			delete_post_meta( $post_id, $expiry_meta );

			// Calculate expiry manually.
			$expires = $this->calculate_expiry( $post_id );
			if ( $expires ) {
				update_post_meta( $post_id, $expiry_meta, $expires );
			}
		}

		wp_send_json_success( esc_html__( 'Package switched successfully', 'cariera-packages' ) );
	}

	/**
	 * Applies user package to a listing and updates related meta data.
	 *
	 * @since 0.9.21
	 *
	 * @param int    $listing_id
	 * @param int    $package_id
	 * @param string $submission_type
	 * @param string $duration_meta
	 * @param string $expiry_meta
	 */
	private function apply_package_to_listing( int $listing_id, int $package_id, string $submission_type, string $duration_meta, string $expiry_meta ): void {
		$product_id = (int) get_post_meta( $package_id, 'cariera_packages_product_id', true );
		$duration   = (int) get_post_meta( $package_id, "cariera_packages_{$submission_type}_duration", true );
		$featured   = get_post_meta( $package_id, "cariera_packages_{$submission_type}_featured", true );

		update_post_meta( $listing_id, '_user_package_id', $package_id );
		update_post_meta( $listing_id, '_package_id', $product_id );
		update_post_meta( $listing_id, '_featured', $featured ? 1 : 0 );

		// Update duration so it can be used in the expiry calculation hooked to wp_update_post.
		update_post_meta( $listing_id, $duration_meta, $duration );

		// Delete listing expiry so it can be recalculated based on the new duration.
		delete_post_meta( $listing_id, $expiry_meta );

		// Calculate expiry manually.
		$expires = $this->calculate_expiry( $listing_id );
		if ( $expires ) {
			update_post_meta( $listing_id, $expiry_meta, $expires );
		}

		/**
		 * Fires after a package is manually reassigned to a listing via the admin metabox.
		 *
		 * @param int    $package_id
		 * @param int    $listing_id
		 * @param string $submission_type
		 */
		do_action( 'cariera_packages_admin_submission_package_switch', $package_id, $listing_id, $submission_type );
	}

	/**
	 * Calculate and update the expiry meta for a listing based on CPT.
	 *
	 * @since 0.9.21
	 *
	 * @param int $listing_id
	 *
	 * TODO: Add more CPT support here.
	 */
	private function calculate_expiry( $listing_id ) {
		$post_type = get_post_type( $listing_id );

		switch ( $post_type ) {
			case 'job_listing':
				return calculate_job_expiry( $listing_id );

			case 'resume':
				return calculate_resume_expiry( $listing_id );

			default:
				return '';
		}
	}

	/**
	 * Safely increment or decrement a package's submission count, floored at 0.
	 *
	 * @since 0.9.21
	 *
	 * @param int    $package_id
	 * @param string $submission_type
	 * @param int    $delta  +1 or -1.
	 */
	private function adjust_submission_count( int $package_id, string $submission_type, int $delta ): void {
		$meta_key = "cariera_packages_{$submission_type}_count";
		$current  = (int) get_post_meta( $package_id, $meta_key, true );
		update_post_meta( $package_id, $meta_key, max( 0, $current + $delta ) );
	}

	/**
	 * Get the CPTs that have submission packages enabled in Settings.
	 *
	 * @since 0.9.21
	 *
	 * @return string[]
	 */
	private static function get_supported_submission_post_types(): array {
		$setting = get_option( 'cariera_packages_submission_package', [] );

		if ( empty( $setting ) || ! is_array( $setting ) ) {
			return [];
		}

		// The multi_switch setting stores enabled post types as [ 'job_listing' => 1, 'resume' => 0 ].
		return array_keys( array_filter( $setting ) );
	}
}