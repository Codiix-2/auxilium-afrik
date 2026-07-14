<?php
/**
 * Debug Log
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/migrations.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.8.3
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$migration_items = Cariera_Core\Core\Migrations::get_migration_items();
?>

<div class="cariera-migrations">
	<div class="migrations-item-wrapper">
		<?php do_action( 'cariera_migrations_before_content' ); ?>

		<?php
		if ( ! empty( $migration_items ) ) {
			foreach ( $migration_items as $item ) {
				if ( empty( $item['name'] ) || empty( $item['action'] ) ) {
					continue;
				}

				$name        = $item['name'];
				$action      = $item['action']; // phpcs:ignore
				$description = isset( $item['description'] ) ? $item['description'] : '';
				$btn_title   = isset( $item['btn_title'] ) ? $item['btn_title'] : '';
				$type        = isset( $item['type'] ) ? $item['type'] : ''; // phpcs:ignore
				$link        = isset( $item['link'] ) ? $item['link'] : ''; // phpcs:ignore
				$addon       = isset( $item['addon'] ) ? $item['addon'] : '';
				?>

				<div class="migrations-item migrations-item-<?php echo esc_attr( sanitize_title( $name ) ); ?>">
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" class="migration-form">
						<div class="process"><span><?php esc_html_e( 'Processing...', 'cariera-core' ); ?></span></div>

						<p class="item-name">
							<?php if ( ! empty( $addon ) ) { ?>
								<span class="item-addon"><?php echo esc_html( $addon ); ?></span>
							<?php } ?>
							<?php echo esc_html( $name ); ?>
						</p>

						<p class="item-description"><?php echo esc_html( $description ); ?></p>

						<div class="item-footer">
							<?php if ( ! empty( $type ) && ! empty( $link ) ) { ?>
								<a href="<?php echo esc_url( $link ); ?>" class="button item-button" target="_blank"><?php echo esc_html( $btn_title ); ?></a>
							<?php } else { ?>
								<button name="migrations" data-action="<?php echo esc_attr( $action ); ?>" class="cariera-btn"><?php echo esc_html( $btn_title ); ?></button>
							<?php } ?>

							<?php wp_nonce_field( $action ); ?>
							<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
						</div>
					</form>
				</div>
			<?php } ?>
		<?php } ?>

		<?php do_action( 'cariera_migrations_after_content' ); ?>
	</div>
</div>
