<?php
/**
 * Choose Promotion Modal Template
 *
 * This template can be overridden by copying it to yourtheme/cariera-packages/dashboard/choose-promotion.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera Packages
 * @category    Template
 * @since       0.9.9
 * @version     0.9.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-packages-promotions' );
wp_enqueue_script( 'cariera-packages-promotions' );
?>

<div id="cariera-packages-promotions" class="small-dialog zoom-anim-dialog mfp-hide">
	<div class="small-dialog-headline">
		<h3 class="title"><?php echo esc_html( $title ); ?></h3>
	</div>

	<div class="small-dialog-content">
		<div class="cariera-packages promo-packages-wrapper">
			<span class="loader"><span></span></span>

			<?php
			// Existing Promotional Packages.
			if ( ! empty( $products ) ) {
				?>
				<h2 class="title"><?php esc_html_e( 'Promotional Packages', 'cariera-packages' ); ?></h2>
				<ul class="promo-packages buy-packages" data-package-type="<?php echo esc_attr( $type ); ?>">
					<?php
					foreach ( (array) $products as $product ) {
						$product  = wc_get_product( $product );
						$duration = absint( get_post_meta( $product->get_id(), $duration_meta, true ) );

						if ( ! $product->is_type( [ 'cariera_package' ] ) || ! $product->is_purchasable() || $duration <= 0 ) {
							continue;
						}
						?>

						<li class="promo-package" data-package-id="<?php echo esc_attr( $product->get_id() ); ?>" data-process="buy-package">
							<div class="package-icon">
								<i class="las la-bolt"></i>
							</div>

							<div class="package-details">
								<h5><?php echo esc_html( $product->get_name() ); ?></h5>
								<?php if ( ! empty( $product->get_short_description() ) ) { ?>
									<p class="promo-desc"><?php echo wp_kses_post( $product->get_short_description() ); ?></p>
								<?php } ?>
								<p>
									<span><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
									<?php
									// translators: %s is the duration number.
									printf( esc_html__( 'Promotion lasts for %s days', 'cariera-packages' ), number_format_i18n( $duration ) );
									?>
								</p>
							</div>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>

			<?php if ( ! empty( $packages ) ) { ?>
				<h2 class="title"><?php esc_html_e( 'Owned Packages', 'cariera-packages' ); ?></h2>
				<ul class="promo-packages use-package" data-package-type="<?php echo esc_attr( $type ); ?>">
					<?php
					foreach ( (array) $packages as $package ) {
						$duration   = absint( get_post_meta( $package, 'cariera_packages_listing_promotion_duration', true ) );
						$product_id = absint( get_post_meta( $package, 'cariera_packages_product_id', true ) );

						if ( $duration <= 0 ) {
							continue;
						}
						?>

						<li class="promo-package" data-package-id="<?php echo esc_attr( $package ); ?>" data-process="use-package">
							<div class="package-icon">
								<i class="las la-bolt"></i>
							</div>

							<div class="package-details">
								<h5><?php echo esc_html( get_the_title( $package ) ); ?></h5>
								<p>
									<?php
									// translators: %s is the duration number.
									printf(
										esc_html__( 'Promotion lasts for %s days', 'cariera-packages' ),
										number_format_i18n( $duration )
									);
									?>
								</p>
							</div>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>

			<?php if ( empty( $products ) && empty( $packages ) ) { ?>
				<span><?php esc_html_e( 'There are no promotional packages available at the moment.', 'cariera-packages' ); ?></span>
			<?php } ?>
		</div>
	</div>
</div>
