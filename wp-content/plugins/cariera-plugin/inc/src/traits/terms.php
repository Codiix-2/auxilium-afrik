<?php

namespace Cariera_Core\Src\Traits;

trait Terms {

	/**
	 * Background image field
	 *
	 * @since 1.7.3
	 *
	 * @param string $img
	 */
	protected function background_img( $img = '' ) {
		?>
		<div class="bg-image-wrapper">
			<div class="uploaded-image">
				<?php if ( ! empty( $img ) ) { ?>
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php esc_attr_e( 'Term background image', 'cariera-core' ); ?>" />
				<?php } ?>
			</div>

			<input type="hidden" name="cariera_background_image" class="image-input" value="<?php echo esc_attr( $img ); ?>" style="margin-bottom: 10px;">
			<div class="upload-image-action">
				<a type="button" class="button cariera-upload-btn"><?php esc_html_e( 'upload image', 'cariera-core' ); ?></a>
				<a href="#" class="button cariera_remove_image_button"><?php esc_html_e( 'Remove image', 'cariera-core' ); ?></a>
			</div>
			<p class="description"><?php esc_html_e( 'Upload or select a background image.', 'cariera-core' ); ?></p>
		</div>

		<?php
	}

	/**
	 * Image icon field
	 *
	 * @since 1.7.3
	 *
	 * @param string $img
	 */
	protected function image_icon( $img = '' ) {
		?>
		<div class="image-icon-wrapper">
			<div class="uploaded-image">
				<?php if ( ! empty( $img ) ) { ?>
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php esc_attr_e( 'Term background image', 'cariera-core' ); ?>" />
				<?php } ?>
			</div>

			<input type="hidden" name="cariera_image_icon" class="image-input" value="<?php echo esc_attr( $img ); ?>" style="margin-bottom: 10px;">
			<div class="upload-image-action">
				<a type="button" class="button cariera-upload-btn"><?php esc_html_e( 'upload image', 'cariera-core' ); ?></a>
				<a href="#" class="button cariera_remove_image_button"><?php esc_html_e( 'Remove image', 'cariera-core' ); ?></a>
			</div>
			<p class="description"><?php esc_html_e( 'Upload or select a custom image icon.', 'cariera-core' ); ?></p>
		</div>

		<?php
	}

	/**
	 * Font icon field
	 *
	 * @since   1.7.3
	 * @version 1.8.5
	 *
	 * @param string $icon
	 */
	protected function font_icon( $icon = '' ) {
		?>
		<button class="button load-icons"><?php esc_html_e( 'Select Icons', 'cariera-core' ); ?></button>
		<select class="cariera-icon-select" name="cariera_font_icon" data-selected-icon="<?php echo esc_attr( $icon ); ?>" style="display: none"></select>
		<p class="description"><?php esc_html_e( 'Icon will be displayed in categories grid view', 'cariera-core' ); ?></p>
		<?php
	}
}
