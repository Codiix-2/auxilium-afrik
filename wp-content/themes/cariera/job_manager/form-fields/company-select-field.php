<?php
/**
 * Custom: Field to select all companies
 *
 * This template can be overridden by copying it to yourtheme/job_manager/form-fields/company-select-field.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.0
 * @version     1.9.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get current user ID.
$user_id = get_current_user_id();

// Get selected value.
$job_id = ! empty( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Input is used safely.
$job    = $job_id ? get_post( $job_id ) : null;

// Initialize selected value.
$maybe_value = '';

// Editing an existing job.
if ( $job_id && $job ) {
	$maybe_value = get_post_meta( $job_id, '_company_manager_id', true );
}

// New job submission: preselect the user's latest company.
if ( empty( $maybe_value ) && ! $job_id && $user_id ) {
	$maybe_value = cariera_get_user_latest_company_id( $user_id );
}

$maybe_required = apply_filters( 'cariera_job_submit_company_manager_required', false ) ? 'required' : '';
?>

<select name="<?php echo isset( $field['name'] ) ? esc_attr( $field['name'] ) : esc_attr( $key ); ?>" class="cariera-company-select" id="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $maybe_required ); ?>>
	<?php
	if ( ! empty( $maybe_value ) ) {
		$selected_company = get_post( $maybe_value );
		if ( $selected_company ) {
			$company_title = $selected_company->post_title;
			if ( 'pending' === $selected_company->post_status ) {
				$company_title .= ' (' . $selected_company->post_status . ')';
			}
			?>
			<option value="<?php echo esc_attr( $selected_company->ID ); ?>" selected="selected">
				<?php echo esc_html( $company_title ); ?>
			</option>
			<?php
		}
	}
	?>
</select>

<?php if ( ! empty( $field['description'] ) ) { ?>
	<small class="description"><?php echo wp_kses_post( $field['description'] ); ?></small>
	<?php
}
