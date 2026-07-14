<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Sections
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Sections {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;
	/**
	 * @var array Object cache of sections
	 */
	private $sections;

	/**
	 * Sections constructor.
	 *
	 * @param $type \WPJMSF\Job|\WPJMSF\Resume
	 */
	public function __construct( $type ) {
		$this->type = $type;
	}

	/**
	 * Add section_id to fields (for use with ORM), and process for other handling (HTML, Translation, etc)
	 *
	 *
	 * @param       $fields_array
	 * @param       $section_id
	 * @param array $nested_keys
	 *
	 * @return mixed
	 * @since 0.1.1
	 *
	 */
	public function process_fields( $fields_array, $section_id, $nested_keys = array() ){

		$default_nested_keys = array( 'spacing', 'styles' );
		$nested_keys = array_merge( $default_nested_keys, $nested_keys );

		$html_allowed_fields = apply_filters( 'search_and_filtering_sanitize_fields_html_allowed_fields', array( 'html', 'caption', 'label', 'separator' ), $this, $fields_array, $section_id );
		$translate_fields = apply_filters( 'search_and_filtering_fields_translate_fields', array( 'label', 'placeholder', 'caption' ), $this, $fields_array, $section_id );

		foreach( (array) $fields_array as $index => $field_option ){
			$a = $field_option;

			foreach( (array) $field_option as $fo_key => $fo_val ){

				/**
				 * Convert back any fields saved with HTML entities encoded
				 */
				if( in_array( $fo_key, $html_allowed_fields ) ){

					$fields_array[ $index ][ $fo_key ] = stripslashes( html_entity_decode( $fo_val ) );

				} elseif( $fo_key === 'type_config' && is_array( $fo_val ) ){

					foreach( $html_allowed_fields as $html_allowed_field ){
						if( array_key_exists( $html_allowed_field, $fo_val ) ){
							$fields_array[ $index ][ 'type_config' ][ $html_allowed_field ] = stripslashes( html_entity_decode( $fo_val[ $html_allowed_field ] ) );
						}
					}

					if( isset( $fo_val['show_select_all'] ) && ! empty( $fo_val['show_select_all'] ) ){
						/**
						 * Pull translation value if one exists
						 */
						$l10n = __( $fo_val['show_select_all'], 'wp-job-manager-search-and-filtering' );
						/**
						 * For some reason TranslatePress adds tags around returned value, making it impossible to compare without stripping
						 */
						if ( class_exists( 'TRP_Translation_Manager' ) && method_exists( 'TRP_Translation_Manager', 'strip_gettext_tags' ) ) {
							$l10n = \TRP_Translation_Manager::strip_gettext_tags( $l10n );
						}
						if( $l10n !== $fo_val['show_select_all'] ){
							$fields_array[ $index ]['type_config']['show_select_all'] = $l10n;
						}
					}

				} elseif( $fo_key === 'data_source_options' && ! empty( $fo_val ) ){

					foreach( $fo_val as $fo_val_cdo_index => $fo_val_cdo_val ){
						if( isset( $fo_val_cdo_val['label'] ) ){
							/**
							 * Pull translation value if one exists
							 */
							$l10n = __( $fo_val_cdo_val['label'], 'wp-job-manager-search-and-filtering' );
							/**
							 * For some reason TranslatePress adds tags around returned value, making it impossible to compare without stripping
							 */
							if( class_exists( 'TRP_Translation_Manager' ) && method_exists( 'TRP_Translation_Manager', 'strip_gettext_tags' ) ){
								$l10n = \TRP_Translation_Manager::strip_gettext_tags( $l10n );
							}
							if( $l10n !== $fo_val_cdo_val['label'] ){
								$fields_array[ $index ]['data_source_options'][ $fo_val_cdo_index ]['label_l10n'] = $l10n;
							}
						}
					}

				} elseif( in_array( $fo_key, $translate_fields ) ){

					$l10n = __( $fo_val, 'wp-job-manager-search-and-filtering' );
					/**
					 * For some reason TranslatePress adds tags around returned value, making it impossible to compare without stripping
					 */
					if ( class_exists( 'TRP_Translation_Manager' ) && method_exists( 'TRP_Translation_Manager', 'strip_gettext_tags' ) ) {
						$l10n = \TRP_Translation_Manager::strip_gettext_tags( $l10n );
					}
					/**
					 * Pass through __() to translate
					 */
					if( $fo_val !== $l10n ){
						$fields_array[ $index ]['l10n'][ $fo_key ] = $l10n;
					}
				}

				if( ! in_array( $fo_key, $nested_keys ) ){
					continue;
				}

				if ( ! isset( $fields_array[ $index ][ $fo_key ] ) || empty( $fields_array[ $index ][ $fo_key ] ) ) {
					$fields_array[ $index ][ $fo_key ] = array();
				}

				$fields_array[ $index ][ $fo_key ]['field_slug'] = $fields_array[ $index ]['slug'];
				$fields_array[ $index ][ $fo_key ]['section_id'] = $section_id;
			}
		}

		return $fields_array;
	}

	/**
	 * Check if Section Exists and Enabled based on Auto Output
	 *
	 *
	 * @param $output
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	function has_enabled_section_by_output( $output ){
		// Should be cached in CPT class Object after first call (which is why we don't pass custom args for disabled)
		$sections = $this->get_sections();
		$has_one = false;

		foreach( (array) $sections as $section ){
			if( $section['output'] === $output && in_array( $section['post_status'], array( 'publish', 'enabled' ) ) ){
				$has_one = true;
				break;
			}
		}

		return $has_one;
	}

	/**
	 * Check if Section Exists and Enabled based on Array of Outputs
	 *
	 *
	 * @param array $outputs
	 *
	 * @return bool
	 * @since 0.1.1
	 *
	 */
	function has_enabled_section_by_outputs( $outputs ) {

		// Should be cached in CPT class Object after first call (which is why we don't pass custom args for disabled)
		$sections = $this->get_sections();
		$has_one  = false;

		foreach ( (array) $sections as $section ) {
			if ( in_array( $section['output'], $outputs ) && in_array( $section['post_status'], array( 'publish', 'enabled' ) ) ) {
				$has_one = true;
				break;
			}
		}

		return $has_one;
	}

	/**
	 * Enable Section
	 *
	 *
	 * @param $section_id
	 *
	 * @return int|\WP_Error
	 * @since 0.1.1
	 *
	 */
	function enable_section( $section_id ) {
		return wp_update_post( array( 'ID' => $section_id, 'post_status' => 'publish' ) );
	}

	/**
	 * Disable Section
	 *
	 *
	 * @param $section_id
	 *
	 * @return int|\WP_Error
	 * @since 0.1.1
	 *
	 */
	function disable_section( $section_id ) {
		return wp_update_post( array( 'ID' => $section_id, 'post_status' => 'disabled' ) );
	}

	/**
	 * Get Section Meta or Default (if empty)
	 *
	 * @param        $section_id
	 * @param        $meta_key
	 * @param string $default
	 *
	 * @since 1.1.0
	 *
	 */
	public function get_meta_or_default( $section_id, $meta_key, $default = '' ) {

		$value = get_post_meta( $section_id, $meta_key, true );
		if( empty( $value ) ){
			return $default;
		}

		return $value;
	}

	/**
	 * Get Single Section
	 *
	 *
	 * @param $section_id
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	public function get_section( $section_id ){

		$section = get_post( $section_id );

		if ( ! empty( $section ) && ! is_wp_error( $section ) && $section->post_type === $this->type->post_type ) {

			$spacing_meta          = get_post_meta( $section_id, 'spacing', true );
			$styles_meta           = get_post_meta( $section_id, 'styles', true );
			$grids_meta            = get_post_meta( $section_id, 'grids', true );
			$fields_meta           = get_post_meta( $section_id, 'fields', true );
			$output                = get_post_meta( $section_id, 'output', true );
			$is_form               = get_post_meta( $section_id, 'is_form', true );
			$disable_form_on_listings_page = get_post_meta( $section_id, 'disable_form_on_listings_page', true );
			$enable_mobile_mode    = get_post_meta( $section_id, 'enable_mobile_mode', true );
			$show_label            = get_post_meta( $section_id, 'show_label', true );
			$logic                 = get_post_meta( $section_id, 'logic', true );
			$checkbox              = get_post_meta( $section_id, 'checkbox', true );
			$wrapper_classes       = get_post_meta( $section_id, 'wrapper_classes', true );
			$breakpoints           = get_post_meta( $section_id, 'breakpoints', true );

			$section_data = array(
				'label'              => $section->post_title,
				'is_form'            => $is_form,
				'disable_form_on_listings_page' => empty( $disable_form_on_listings_page ) ? 0 : 1,
				'enable_mobile_mode' => empty( $enable_mobile_mode ) ? 0 : 1,
				'show_label'         => $show_label,
				'post_status'        => $section->post_status,
				'spacing'            => ! empty( $spacing_meta ) ? $spacing_meta : array(),
				'styles'             => ! empty( $styles_meta ) ? $styles_meta : array(),
				'fields'             => $this->process_fields( ! empty( $fields_meta ) ? $fields_meta : array(), $section_id ),
				'grids'              => ! empty( $grids_meta ) ? $grids_meta : array(),
				'ID'                 => $section_id,
				'output'             => $output,
				'priority'           => $section->priority,
				'type_slug'          => $this->type->slug,
				'logic'              => $logic,
				'checkbox'           => $checkbox,
				'wrapper_classes'    => $wrapper_classes,
			);

			if( ! empty( $breakpoints ) ){
				$section_data['breakpoints'] = $breakpoints;
			} else {
				$section_data['breakpoints'] = array( 'lg' => 1200, 'md' => 992, 'sm' => 768, 'xs' => 576, 'xxs' => 0 );
			}

		} else {
			$section_data = $section;
		}

		return apply_filters( 'search_and_filtering_sections_get_section', $section_data, $section_id, $section, $this );
	}

	/**
	 * Get All Sections
	 *
	 *
	 * @param bool  $force
	 * @param array $args
	 *
	 * @return int[]|\WP_Post[]
	 * @since 0.1.1
	 */
	function get_sections( $force = false, $args = array() ) {

		if ( ! $this->sections || $force ) {
			$posts_array = $this->type->cpt->get_posts( $args );

			$this->sections = array();
			foreach ( (array) $posts_array as $post ) {

				$add_section = array(
					'ID'          => $post->ID,
					'post_title'  => $post->post_title,
					'post_status' => $post->post_status,
					'label'       => $post->post_title,
					'output'      => $post->output,
					'is_form'     => $post->is_form,
					'disable_form_on_listings_page' => $post->disable_form_on_listings_page,
					'priority'    => floatval( $post->priority )
				);

				$this->sections[] = $add_section;
			}
		}

		return apply_filters( 'wpjmsf_get_sections', $this->sections, $args, $force, $this );
	}

	/**
	 * Get Sections by Auto Output Value
	 *
	 * This method returns all sections that match a specific output value, sorting them
	 * based on the priority configured for the section.  This is used by the Auto Output
	 * handling when it's not just a specific section being output.
	 *
	 *
	 * @param       $output
	 * @param array $args
	 * @param bool  $force
	 *
	 * @return mixed|void
	 * @since 0.1.1
	 *
	 */
	function get_sections_by_output( $output, $args = array(), $force = false ) {
		$sections = $this->get_sections( $force );
		$sections_with_output = array();

		foreach( (array) $sections as $section ){
			if( $section['output'] === $output ){
				$sections_with_output[] = $section;
			}
		}

		if( ! empty( $sections_with_output ) ){
			// Sort based on priority
			$priority = array_column( $sections_with_output, 'priority' );
			array_multisort( $priority, SORT_ASC, $sections_with_output );
		}

		return apply_filters( 'wpjmsf_get_sections_by_output', $sections_with_output, $output, $args, $force, $this );
	}
}