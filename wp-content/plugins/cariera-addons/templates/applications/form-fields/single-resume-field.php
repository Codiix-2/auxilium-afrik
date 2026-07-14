<?php
/**
 * Hidden field that is used when a user only has one resume to apply with.
 *
 * This template can be overridden by copying it to yourtheme/cariera-addons/applications/form-fields/single-resume-field.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Gnodesign
 * @package     cariera-addons
 * @category    Template
 * @since       0.9.3
 * @version     0.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="hidden" class="input-text" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo isset( $field['value'] ) ? esc_attr( $field['value'] ) : ''; ?>" />
<?php
if ( ! empty( $field['description'] ) ) {
	echo $field['description'];
}
