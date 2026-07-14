<?php
/**
 * Form field that is repeated multiple times.
 *
 * @package     cariera-addons
 * @since       0.9.5
 * @version     1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sub_keys       = array_keys( $field['fields'] );
$title_key      = ! empty( $sub_keys ) ? $sub_keys[0] : '';
$subtitle_key   = isset( $sub_keys[1] ) ? $sub_keys[1] : '';
$title_label    = $title_key ? ( $field['fields'][ $title_key ]['label'] ?? '' ) : '';
$subtitle_label = $subtitle_key ? ( $field['fields'][ $subtitle_key ]['label'] ?? '' ) : '';
$field_id       = 'repeater-field-' . uniqid();
?>

<div class="cariera-addons-resumes-field"
	id="<?php echo esc_attr( $field_id ); ?>"
	data-field-key="<?php echo esc_attr( $key ); ?>"
	data-title-key="<?php echo esc_attr( $title_key ); ?>"
	data-subtitle-key="<?php echo esc_attr( $subtitle_key ); ?>"
	data-title-label="<?php echo esc_attr( $title_label ); ?>"
	data-subtitle-label="<?php echo esc_attr( $subtitle_label ); ?>">

	<?php if ( ! empty( $field['value'] ) && is_array( $field['value'] ) ) : ?>
		<?php
		foreach ( $field['value'] as $index => $value ) :
			$title_value      = $value[ $title_key ] ?? '';
			$subtitle_value   = $subtitle_key ? ( $value[ $subtitle_key ] ?? '' ) : '';
			$title_display    = $title_value ? $title_value : $title_label;
			$subtitle_display = $subtitle_value ? $subtitle_value : $subtitle_label;
			?>
			<div class="cariera-addons-resumes-data-row">
				<input type="hidden" class="repeated-row-index" name="repeated-row-<?php echo esc_attr( $key ); ?>[]" value="<?php echo absint( $index ); ?>" />
				<div class="row-header" role="button" tabindex="0">
					<span class="reorder-handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'cariera-addons' ); ?>">
						<i class="las la-ellipsis-v" aria-hidden="true"></i>
					</span>
					<button type="button" class="collapse-toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle details', 'cariera-addons' ); ?>">
						<i class="las la-angle-down" aria-hidden="true"></i>
					</button>
					<div class="row-preview">
						<div class="preview-title"><?php echo esc_html( $title_display ); ?></div>
						<?php if ( $subtitle_key ) : ?>
							<div class="preview-subtitle"><?php echo esc_html( $subtitle_display ); ?></div>
						<?php endif; ?>
					</div>
					<a href="#" class="remove-row" aria-label="<?php esc_attr_e( 'Remove item', 'cariera-addons' ); ?>">
						<i class="las la-trash-alt" aria-hidden="true"></i>
					</a>
				</div>
				<div class="row-body collapsed">
					<?php
					foreach ( $field['fields'] as $subkey => $subfield ) :
						$subfield_name = $key . '_' . $subkey . '_' . $index;
						?>
						<fieldset class="fieldset-<?php echo esc_attr( $subkey ); ?>">
							<label for="<?php echo esc_attr( $subfield_name ); ?>">
								<?php echo esc_html( $subfield['label'] ); ?>
								<?php if ( ! $subfield['required'] ) : ?>
									<small><?php esc_html_e( '(optional)', 'cariera-addons' ); ?></small>
								<?php endif; ?>
							</label>
							<div class="field">
								<?php
								$subfield['name']  = $subfield_name;
								$subfield['value'] = $value[ $subkey ] ?? $subfield['default'] ?? '';
								$class->get_field_template( $subkey, $subfield );
								?>
							</div>
						</fieldset>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<!-- Template for new rows -->
	<template id="<?php echo esc_attr( $field_id ); ?>-template">
		<div class="cariera-addons-resumes-data-row">
			<input type="hidden" class="repeated-row-index" name="repeated-row-<?php echo esc_attr( $key ); ?>[]" value="{{INDEX}}" />
			<div class="row-header" role="button" tabindex="0">
				<span class="reorder-handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'cariera-addons' ); ?>">
					<i class="las la-ellipsis-v" aria-hidden="true"></i>
				</span>
				<button type="button" class="collapse-toggle" aria-expanded="true" aria-label="<?php esc_attr_e( 'Toggle details', 'cariera-addons' ); ?>">
					<i class="las la-angle-down" aria-hidden="true"></i>
				</button>
				<div class="row-preview">
					<div class="preview-title"><?php echo esc_html( $title_label ); ?></div>
					<?php if ( $subtitle_key ) : ?>
						<div class="preview-subtitle"><?php echo esc_html( $subtitle_label ); ?></div>
					<?php endif; ?>
				</div>
				<a href="#" class="remove-row" aria-label="<?php esc_attr_e( 'Remove item', 'cariera-addons' ); ?>">
					<i class="las la-trash-alt" aria-hidden="true"></i>
				</a>
			</div>
			<div class="row-body">
				<?php
				foreach ( $field['fields'] as $subkey => $subfield ) :
					$subfield_name = $key . '_' . $subkey . '_{{INDEX}}';
					?>
					<fieldset class="fieldset-<?php echo esc_attr( $subkey ); ?>">
						<label for="<?php echo esc_attr( $subfield_name ); ?>">
							<?php echo esc_html( $subfield['label'] ); ?>
							<?php if ( ! $subfield['required'] ) : ?>
								<small><?php esc_html_e( '(optional)', 'cariera-addons' ); ?></small>
							<?php endif; ?>
						</label>
						<div class="field">
							<?php
							$subfield['name']  = $subfield_name;
							$subfield['value'] = $subfield['default'] ?? '';
							$class->get_field_template( $subkey, $subfield );
							?>
						</div>
					</fieldset>
				<?php endforeach; ?>
			</div>
		</div>
	</template>

	<!-- Add Row Button -->
	<button type="button" class="cariera-addons-resumes-add-row btn btn-main btn-icon btn-effect">
		<i class="las la-plus-circle" aria-hidden="true"></i>
		<?php echo esc_html( ! empty( $field['add_row'] ) ? $field['add_row'] : esc_html__( 'Add URL', 'cariera-addons' ) ); ?>
	</button>

	<?php if ( ! empty( $field['description'] ) ) : ?>
		<small class="description"><?php echo esc_html( $field['description'] ); ?></small>
	<?php endif; ?>
</div>
