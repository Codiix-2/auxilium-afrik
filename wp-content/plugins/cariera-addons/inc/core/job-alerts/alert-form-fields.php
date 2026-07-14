<?php

namespace Cariera_Addons\Core\Job_Alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Alert_Form_Fields {

	/**
	 * The active alert form fields.
	 *
	 * @var array
	 */
	private array $active_fields;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->active_fields = $this->get_active_fields();
	}

	/**
	 * Get all default alert form fields.
	 *
	 * @since 0.9.2
	 *
	 * TODO: This for captcha support.
	 */
	public static function get_default_fields() {
		return apply_filters(
			'job_manager_alerts_form_fields',
			[
				'keywords'            => esc_html__( 'Keywords', 'cariera-addons' ),
				'location'            => esc_html__( 'Location', 'cariera-addons' ),
				'categories'          => esc_html__( 'Categories', 'cariera-addons' ),
				'tags'                => esc_html__( 'Tags', 'cariera-addons' ),
				'job_type'            => esc_html__( 'Job Type', 'cariera-addons' ),
				'permission_checkbox' => esc_html__( 'Permission Checkbox', 'cariera-addons' ),
			]
		);
	}

	/**
	 * Check if a field is enabled.
	 *
	 * @since 0.9.2
	 *
	 * @param string $field Field name.
	 */
	public function is_active( $field ) {
		return ! empty( $this->active_fields[ $field ] );
	}

	/**
	 * Get the active alert form fields.
	 *
	 * @since   0.9.2
	 * @version 1.1.0
	 *
	 * TODO: This for captcha support.
	 */
	public function get_active_fields() {
		$fields         = self::get_default_fields();
		$field_settings = get_option( 'job_manager_alerts_form_fields', false );

		if ( false !== $field_settings && empty( $field_settings['fields'] ) ) {
			return [];
		}

		if ( ! empty( $field_settings ) ) {
			$fields_enabled = array_fill_keys( array_keys( $field_settings['fields'] ), true );
			$fields         = array_intersect_key( $fields, $fields_enabled );
		} elseif ( '0' === get_option( 'job_manager_permission_checkbox' ) ) {
			$fields['permission_checkbox'] = false;
		}

		if ( ! taxonomy_exists( 'job_listing_region' ) || wp_count_terms( 'job_listing_region' ) <= 0 ) {
			unset( $fields['region'] );
		}

		if ( ! taxonomy_exists( \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG ) || wp_count_terms( \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG ) <= 0 ) {
			unset( $fields['tags'] );
		}

		if ( ! get_option( 'job_manager_enable_categories' ) || wp_count_terms( 'job_listing_category' ) <= 0 ) {
			unset( $fields['categories'] );
		}

		if ( ! get_option( 'job_manager_enable_types' ) || wp_count_terms( 'job_listing_types' ) <= 0 ) {
			unset( $fields['job_type'] );
		}

		return $fields;
	}

	/**
	 * Render the alert opt-in checkbox or consent message field.
	 *
	 * @since   0.9.2
	 * @version 1.1.0
	 *
	 * @param bool $selected
	 */
	public function alert_permission( $selected = false ) {
		$consent_message = Settings::get_alert_consent_message( true );

		if ( $this->is_active( 'permission_checkbox' ) ) {
			return sprintf(
				'<input type="checkbox" class="input-checkbox" name="alert_permission" id="alert_permission" value="1" required %1$s />
				<label for="alert_permission">%2$s</label>',
				checked( $selected, true, false ),
				wp_kses_post( $consent_message )
			);
		}

		return sprintf( '<div class="alert_consent_message">%s</div>', wp_kses_post( Settings::get_alert_consent_message() ) );
	}

	/**
	 * Render the job categories dropdown.
	 *
	 * @since 0.9.2
	 *
	 * @param bool $selected
	 */
	public function alert_cats( $selected = null ) {
		return job_manager_dropdown_categories(
			[
				'taxonomy'     => 'job_listing_category',
				'hierarchical' => 1,
				'echo'         => 0,
				'name'         => 'alert_cats',
				'class'        => 'alert_cats cariera-select2',
				'orderby'      => 'name',
				'selected'     => $selected,
				'hide_empty'   => false,
				'placeholder'  => esc_html__( 'Any job category', 'cariera-addons' ),
			]
		);
	}

	/**
	 * Render the job tags dropdown.
	 *
	 * @since   0.9.2
	 * @version 0.9.10
	 *
	 * @param bool $selected
	 */
	public function alert_tags( $selected = null ) {
		return job_manager_dropdown_categories(
			[
				'taxonomy'     => \Cariera_Addons\Core\Tags\Tags::TAX_JOB_TAG,
				'hierarchical' => 0,
				'echo'         => 0,
				'name'         => 'alert_tags',
				'class'        => 'alert_tags cariera-select2',
				'orderby'      => 'name',
				'selected'     => $selected,
				'hide_empty'   => false,
				'placeholder'  => esc_html__( 'Any job tag', 'cariera-addons' ),
			]
		);
	}

	/**
	 * Render the regions dropdown.
	 *
	 * @since 0.9.2
	 *
	 * @param bool $selected
	 */
	public function alert_regions( $selected = null ) {
		return job_manager_dropdown_categories(
			[
				'taxonomy'     => 'job_listing_region',
				'hierarchical' => 0,
				'echo'         => 0,
				'name'         => 'alert_regions',
				'class'        => 'alert_regions cariera-select2',
				'orderby'      => 'name',
				'selected'     => $selected,
				'hide_empty'   => false,
				'placeholder'  => esc_html__( 'Any job region', 'cariera-addons' ),
			]
		);
	}

	/**
	 * Render the job type dropdown.
	 *
	 * @since 0.9.2
	 *
	 * @param bool $selected
	 */
	public function alert_job_type( $selected = null ) {
		return job_manager_dropdown_categories(
			[
				'taxonomy'     => 'job_listing_type',
				'hierarchical' => 0,
				'echo'         => 0,
				'name'         => 'alert_job_type',
				'class'        => 'alert_job_type cariera-select2',
				'orderby'      => 'name',
				'selected'     => $selected,
				'hide_empty'   => false,
				'placeholder'  => esc_html__( 'Any job type', 'cariera-addons' ),
			]
		);
	}

	/**
	 * Render the alert frequency dropdown.
	 *
	 * @since 0.9.2
	 *
	 * @param array $args {
	 *   Arguments for the alert frequency dropdown.
	 *   @type string $selected The selected option.
	 *   @type string $class    Class for the <select> element.
	 * }
	 */
	public function alert_frequency( $args = [] ) {
		$selected = $args['selected'] ?? null;
		$class    = $args['class'] ?? '';

		$schedules = Notifier::get_alert_schedules();

		$html = '<select name="alert_frequency" id="alert_frequency" class="' . esc_attr( $class ) . '">';
		foreach ( $schedules as $key => $schedule ) {
			$html .= '<option value="' . esc_attr( $key ) . '" ' . selected( $selected, $key, false ) . '>' . esc_html( $schedule['display'] ) . '</option>';
		}
		$html .= '</select>';

		return $html;
	}
}
