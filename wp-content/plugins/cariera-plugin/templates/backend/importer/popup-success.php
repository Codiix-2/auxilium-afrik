<?php
/**
 * Onboarding Importer Popup: Success
\*
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/popup-download-images-form.php.
\*
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.9.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$quick_links = [
	[
		'title'       => __( 'Core Settings', 'cariera-core' ),
		'description' => __( 'Manage global theme settings.', 'cariera-core' ),
		'url'         => admin_url( 'admin.php?page=cariera_settings' ),
		'icon'        => 'fas fa-cog',
	],
	[
		'title'       => __( 'Customizer', 'cariera-core' ),
		'description' => __( 'Preview and adjust your site\'s design.', 'cariera-core' ),
		'url'         => admin_url( 'customize.php' ),
		'icon'        => 'fas fa-paint-brush',
	],
	[
		'title'       => __( 'Documentation', 'cariera-core' ),
		'description' => __( 'Documentation articles for the theme.', 'cariera-core' ),
		'url'         => 'https://docs.cariera.cc/',
		'icon'        => 'fas fa-book',
	],
	[
		'title'       => __( 'Get Support', 'cariera-core' ),
		'description' => __( 'Open a support ticket.', 'cariera-core' ),
		'url'         => 'https://support.gnodesign.com',
		'icon'        => 'fas fa-life-ring',
	],
];
?>

<div id="import-success" class="cariera-success-wrapper">
	<div class="success-header">
		<div class="success-trigger">
			<div class="success-circle"></div>
			<i class="fas fa-check check-icon"></i>
		</div>
		<h4 class="popup-title"><?php esc_html_e( 'Import Complete', 'cariera-core' ); ?></h4>
		<p class="popup-subtitle"><?php esc_html_e( 'The demo content was successfully imported.', 'cariera-core' ); ?></p>
	</div>

	<div class="success-options">
		<?php foreach ( $quick_links as $quick_link ) : ?>
			<a href="<?php echo esc_url( $quick_link['url'] ); ?>" class="action-item" target="_blank" rel="noopener noreferrer">
				<div class="action-content">
					<div class="icon-box" aria-hidden="true">
						<i class="<?php echo esc_attr( $quick_link['icon'] ); ?>"></i>
					</div>
					<div class="action-text">
						<span class="title"><?php echo esc_html( $quick_link['title'] ); ?></span>
						<span class="description"><?php echo esc_html( $quick_link['description'] ); ?></span>
					</div>
				</div>
			</a>
		<?php endforeach; ?>
	</div>

	<a href="https://1.envato.market/rate" target="_blank" rel="noopener noreferrer" class="rating-card">
		<div class="rating-info">
			<span class="rating-label"><?php esc_html_e( 'Loving the theme?', 'cariera-core' ); ?></span>
			<div class="stars">
				<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
			</div>
		</div>
		<div class="rating-cta">
			<?php esc_html_e( 'Rate on ThemeForest', 'cariera-core' ); ?>
			<i class="fas fa-external-link-alt"></i>
		</div>
	</a>

	<div class="popup-footer">
		<div class="buttons">
			<a href="#" class="close-button"><?php esc_html_e( 'Close', 'cariera-core' ); ?></a>
			<a href="<?php echo esc_url( site_url( '/' ) ); ?>" target="_blank" class="next-button">
				<?php esc_html_e( 'View your website', 'cariera-core' ); ?>
				<i class="fas fa-arrow-right"></i>
			</a>
		</div>
	</div>
</div>
