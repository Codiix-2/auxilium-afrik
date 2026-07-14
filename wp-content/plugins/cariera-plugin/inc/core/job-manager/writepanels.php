<?php

namespace Cariera_Core\Core\Job_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Writepanels' ) ) {
	include JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-writepanels.php';
}

class Writepanels extends \WP_Job_Manager_Writepanels {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Displays Company select fields
	 *
	 * @since   1.4.4
	 * @version 1.8.3
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public static function input_company_select( $key, $field ) {
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>

		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>">
				<?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
				<?php if ( ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
				<?php endif; ?>
			</label>
			<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>">
				<?php
				// Check if there is a selected company.
				if ( ! empty( $field['value'] ) ) {
					$selected_company_id = $field['value'];
					$selected_company    = get_post( $selected_company_id );

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
		</p>

		<?php
	}
}
