<?php
/**
 * Form used when creating a new resume listing alert.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resume-alerts/alert-form.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Alerts
 * @category    Template
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-resume-alerts' );
?>
<form method="post" class="job-manager-form job-manager-resume-alerts-form">
	<fieldset>
		<label for="alert_name"><?php esc_html_e( 'Alert Name', 'cariera' ); ?></label>
		<div class="field">
			<input type="text" name="alert_name" value="<?php echo esc_attr( $alert_name ); ?>" id="alert_name" class="input-text" placeholder="<?php esc_attr_e( 'Enter a name for your alert', 'cariera' ); ?>" />
		</div>
	</fieldset>
	<fieldset>
		<label for="alert_keyword"><?php esc_html_e( 'Keyword', 'cariera' ); ?></label>
		<div class="field">
			<input type="text" name="alert_keyword" value="<?php echo esc_attr( $alert_keyword ); ?>" id="alert_keyword" class="input-text" placeholder="<?php esc_attr_e( 'Optionally add a keyword to match resumes against', 'cariera' ); ?>" />
		</div>
	</fieldset>
	<?php if ( taxonomy_exists( 'resume_region' ) && wp_count_terms( 'resume_region' ) > 0 ) : ?>
		<fieldset>
			<label for="alert_regions"><?php esc_html_e( 'Resume Region', 'cariera' ); ?></label>
			<div class="field">
				<?php
					job_manager_dropdown_categories(
						[
							'show_option_all' => false,
							'hierarchical'    => true,
							'orderby'         => 'name',
							'taxonomy'        => 'resume_region',
							'name'            => 'alert_regions',
							'class'           => 'alert_regions job-manager-enhanced-select',
							'hide_empty'      => 0,
							'selected'        => $alert_regions,
							'placeholder'     => esc_html__( 'Any region', 'cariera' ),
						]
					);
				?>
			</div>
		</fieldset>
	<?php else : ?>
		<fieldset>
			<label for="alert_location"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
			<div class="field">
				<input type="text" name="alert_location" value="<?php echo esc_attr( $alert_location ); ?>" id="alert_location" class="input-text" placeholder="<?php esc_attr_e( 'Optionally define a location to search against', 'cariera' ); ?>" />
			</div>
		</fieldset>
	<?php endif; ?>
	<?php if ( get_option( 'resume_manager_enable_categories' ) && wp_count_terms( 'resume_category' ) > 0 ) : ?>
		<fieldset>
			<label for="alert_cats"><?php esc_html_e( 'Categories', 'cariera' ); ?></label>
			<div class="field">
				<?php
					wp_enqueue_script( 'wp-job-manager-term-multiselect' );

					job_manager_dropdown_categories(
						[
							'taxonomy'     => 'resume_category',
							'hierarchical' => 1,
							'name'         => 'alert_cats',
							'orderby'      => 'name',
							'selected'     => $alert_cats,
							'class'        => 'alert_categories job-manager-enhanced-select',
							'hide_empty'   => false,
							'placeholder'  => esc_html__( 'Any category', 'cariera' ),
						]
					);
				?>
			</div>
		</fieldset>
	<?php endif; ?>
	<?php if ( get_option( 'resume_manager_enable_skills' ) && wp_count_terms( 'resume_skill' ) > 0 ) : ?>
		<fieldset>
			<label for="alert_resume_skill"><?php esc_html_e( 'Resume Skills', 'cariera' ); ?></label>
			<div class="field">
				<select name="alert_resume_skill[]" data-placeholder="<?php esc_attr_e( 'Any resume skills', 'cariera' ); ?>" id="alert_resume_skill" multiple="multiple" class="job-manager-enhanced-select">
					<?php
						$terms = get_resume_skills();
					foreach ( $terms as $term ) {
						echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( in_array( $term->term_id, $alert_resume_skill, true ), true, false ) . '>' . esc_html( $term->name ) . '</option>';
					}
					?>
				</select>
			</div>
		</fieldset>
	<?php endif; ?>
	<fieldset>
		<label for="alert_frequency"><?php esc_html_e( 'Email Frequency', 'cariera' ); ?></label>
		<div class="field">
			<select name="alert_frequency" id="alert_frequency" class="job-manager-enhanced-select">
				<?php foreach ( WP_Job_Manager_Resume_Alerts_Notifier::get_alert_schedules() as $key => $schedule ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $alert_frequency, $key ); ?>><?php echo esc_html( $schedule['display'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</fieldset>
	<p>
		<?php wp_nonce_field( 'job_manager_alert_actions' ); ?>
		<input type="hidden" name="alert_id" value="<?php echo absint( $alert_id ); ?>" />
		<input type="submit" id="submit-resume-alert" name="submit-resume-alert" value="<?php esc_attr_e( 'Save alert', 'cariera' ); ?>" />
	</p>
</form>
