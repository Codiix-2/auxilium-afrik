<?php
/**
 * Onboarding Importer - Uninstaller Popup: Confirmation step
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/popup-uninstall-confirmation.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.9.8
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = [
	'posts'   => [
		// translators: %d is the number of content items.
		'label' => __( '%d Content Items', 'cariera-core' ),
		'count' => ! empty( $import_data['posts'] ) ? count( $import_data['posts'] ) : 0,
	],
	'terms'   => [
		// translators: %d is the number of taxonomies.
		'label' => __( '%d Taxonomies', 'cariera-core' ),
		'count' => ! empty( $import_data['terms'] ) ? count( $import_data['terms'] ) : 0,
	],
	'media'   => [
		// translators: %d is the number of media files.
		'label' => __( '%d Media Files', 'cariera-core' ),
		'count' => ! empty( $import_data['media'] ) ? count( $import_data['media'] ) : 0,
	],
	'widgets' => [
		'label' => __( 'Widget Settings', 'cariera-core' ),
		'count' => ! empty( $import_data['widgets'] ) ? 1 : 0,
	],
	'menus'   => [
		'label' => __( 'Menu Locations', 'cariera-core' ),
		'count' => ! empty( $import_data['menus'] ) ? 1 : 0,
	],
	'options' => [
		'label' => __( 'Theme Options', 'cariera-core' ),
		'count' => ! empty( $import_data['options'] ) ? 1 : 0,
	],
];
?>

<form action="#" method="POST" id="uninstall-confirmation-form" class="uninstall-confirmation-wrapper">
	<div class="popup-icon-container">
		<div class="icon-wrapper">
			<i class="fas fa-exclamation"></i>
		</div>
	</div>

	<h4 class="popup-title"><?php esc_html_e( 'Uninstall Demo Content', 'cariera-core' ); ?></h4>

	<div class="notice-warning">
		<p class="headline"><?php esc_html_e( 'This action is permanent!', 'cariera-core' ); ?></p>
		<p class="subtitle"><?php esc_html_e( 'The following imported data will be removed from your database:', 'cariera-core' ); ?></p>
	</div>

	<div class="data-summary">
		<?php
		foreach ( $items as $item ) {
			if ( $item['count'] > 0 ) {
				$output = ( strpos( $item['label'], '%d' ) !== false ) ? sprintf( $item['label'], $item['count'] ) : $item['label'];
				?>
				<div class="item">
					<?php echo esc_html( $output ); ?>
				</div>
				<?php
			}
		}
		?>
	</div>

	<div class="popup-footer">
		<input type="hidden" name="demo_slug" id="demo_slug" value="<?php echo esc_attr( $demo_slug ); ?>" />
		<?php wp_nonce_field( 'start_uninstall' ); ?>
		
		<div class="buttons">
			<a href="#" class="close-button"><?php esc_html_e( 'Cancel', 'cariera-core' ); ?></a>
			<button type="submit" class="next-button"><?php esc_html_e( 'Delete Demo Data', 'cariera-core' ); ?></button>
		</div>
	</div>
</form>
