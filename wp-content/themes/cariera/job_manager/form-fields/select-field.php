<?php
/**
 * Shows the `select` form field on job listing forms.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/form-fields/select-field.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.31.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$field_name  = isset( $field['name'] ) ? $field['name'] : $key;
$is_required = ! empty( $field['required'] ) ? 'required' : '';
$field_value = isset( $field['value'] ) ? $field['value'] : ( isset( $field['default'] ) ? $field['default'] : '' );
?>

<select
	name="<?php echo esc_attr( $field_name ); ?>"
	id="<?php echo esc_attr( $key ); ?>"
	<?php echo esc_attr( $is_required ); ?>
	class="cariera-select2"
	autocomplete="off"
>
	<?php foreach ( $field['options'] as $option_key => $option_value ) : ?>
		<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $field_value, $option_key ); ?>>
			<?php echo esc_html( $option_value ); ?>
		</option>
	<?php endforeach; ?>
</select>


<?php if ( ! empty( $field['description'] ) ) : ?>
	<small class="description">
		<?php echo wp_kses_post( $field['description'] ); ?>
	</small>
<?php endif; ?>
