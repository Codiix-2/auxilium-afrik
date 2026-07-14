<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Kirki {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Customizer config
	 *
	 * @var array
	 */
	protected static $config = [];

	/**
	 * Customizer fields
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Consturctor
	 */
	public function __construct() {
		// Add our CSS.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

		// Add google fonts.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_fonts' ] );

		// Modify customizer fields.
		add_action( 'customize_register', [ $this, 'customize_modify' ] );

		// Delay customizer initialization until after init.
		add_action( 'init', [ $this, 'customizer_init' ] );
	}

	/**
	 * Get the value of an option from the db.
	 *
	 * @since 1.7.3
	 *
	 * @param string $config_id The ID of the configuration corresponding to this field.
	 * @param string $field_id  The field_id (defined as 'settings' in the field arguments).
	 */
	public static function get_option( $config_id = '', $field_id = '' ) {
		// if Kirki exists, use it.
		if ( class_exists( 'Kirki' ) ) {
			return \Kirki::get_option( $config_id, $field_id );
		}
		// Kirki does not exist, continue with our custom implementation.
		// Get the default value of the field.
		$default = '';
		if (
			isset( self::$fields[ $field_id ] ) &&
			isset( self::$fields[ $field_id ]['default'] )
		) {
			$default = self::$fields[ $field_id ]['default'];
		}
		// Make sure the config is defined.
		if ( isset( self::$config[ $config_id ] ) ) {
			if ( 'option' == self::$config[ $config_id ]['option_type'] ) {
				// check if we're using serialized options.
				if (
					isset( self::$config[ $config_id ]['option_name'] ) &&
					! empty( self::$config[ $config_id ]['option_name'] )
				) {
					// Get all our options.
					$all_options = get_option(
						self::$config[ $config_id ]['option_name'],
						[]
					);
					// If our option is not saved, return the default value.
					if ( ! isset( $all_options[ $field_id ] ) ) {
						return $default;
					}

					// Option was set, return its value unserialized.
					return maybe_unserialize( $all_options[ $field_id ] );
				}
				// If we're not using serialized options, get the value and return it.
				// We'll be using a dummy default here to check if the option has been set or not.
				// We'll be using md5 to make sure it's randomish and impossible to be actually set by a user.
				$dummy = md5( $config_id . '_UNDEFINED_VALUE' );
				$value = get_option( $field_id, $dummy );
				// setting has not been set, return default.
				if ( $dummy == $value ) {
					return $default;
				}

				return $value;
			}

			// We're not using options so fallback to theme_mod.
			return get_theme_mod( $field_id, $default );
		}
	}

	/**
	 * Create a new panel
	 *
	 * @since 1.7.3
	 *
	 * @param string $id    the ID for this panel.
	 * @param array  $args   the panel arguments.
	 */
	public static function add_panel( $id = '', $args = [] ) {
		if ( class_exists( 'Kirki' ) ) {
			\Kirki::add_panel( $id, $args );
		}
		// If Kirki does not exist then there's no reason to add any panels.
	}

	/**
	 * Create a new section
	 *
	 * @since 1.7.3
	 *
	 * @param string $id  the ID for this section.
	 * @param array  $args the section arguments.
	 */
	public static function add_section( $id, $args ) {
		if ( class_exists( 'Kirki' ) ) {
			\Kirki::add_section( $id, $args );
		}
	}

	/**
	 * Sets the configuration options.
	 *
	 * @since 1.7.3
	 *
	 * @param string $config_id    The configuration ID.
	 * @param array  $args         The configuration arguments.
	 */
	public static function add_config( $config_id, $args = [] ) {
		if ( class_exists( 'Kirki' ) ) {
			\Kirki::add_config( $config_id, $args );
			return;
		}
		// Kirki does not exist, set the config arguments.
		$config[ $config_id ] = $args;
		// Make sure an option_type is defined.
		if ( ! isset( self::$config[ $config_id ]['option_type'] ) ) {
			self::$config[ $config_id ]['option_type'] = 'theme_mod';
		}
	}

	/**
	 * Create a new field
	 *
	 * @since 1.7.3
	 *
	 * @param string $config_id    The configuration ID.
	 * @param array  $args         The field's arguments.
	 */
	public static function add_field( $config_id, $args ) {
		if ( class_exists( 'Kirki' ) ) {
			\Kirki::add_field( $config_id, $args );
			return;
		}

		// Kirki was not located, so we'll need to add our fields here.
		// check that the "settings" & "type" arguments have been defined.
		if ( isset( $args['settings'] ) && isset( $args['type'] ) ) {
			// Make sure we add the config_id to the field itself.
			// This will make it easier to get the value when generating the CSS later.
			if ( ! isset( $args['kirki_config'] ) ) {
				$args['kirki_config'] = $config_id;
			}
			// Background fields need to be built separately.
			if ( 'background' == $args['type'] && isset( $args['output'] ) ) {
				if ( isset( $args['default'] ) && is_array( $args['default'] ) ) {
					foreach ( $args['default'] as $default_property => $default_value ) {
						$subfield = $args;
						// No need to process the opacity, it is factored in the color control.
						if ( 'opacity' == $key ) {
							continue;
						}
						$key             = esc_attr( $key );
						$setting         = $key;
						$output_property = 'background-' . $key;
						if ( 'attach' == $key ) {
							$output_property = 'background-attachment';
						}
						if ( is_string( $subfield['output'] ) ) {
							$subfield['output'] = [
								[
									'element'  => $args['output'],
									'property' => $output_property,
								],
							];
						} else {
							foreach ( $subfield['output'] as $key => $output ) {
								$subfield['output'][ $key ]['property'] = $output_property;
							}
						}
						$type = 'select';
						if ( in_array( $key, [ 'color', 'image' ], true ) ) {
							$type = $key;
						}
						$property_setting                  = esc_attr( $args['settings'] ) . '_' . $setting;
						self::$fields[ $property_setting ] = $subfield;
					}
				}
			}
			self::$fields[ $args['settings'] ] = $args;
		}
	}

	/**
	 * Enqueues the stylesheet
	 *
	 * @since 1.7.3
	 */
	public function enqueue_styles() {
		if ( class_exists( 'Kirki' ) ) {
			return;
		}
		// Get our inline styles.
		$styles = $this->get_styles();
		// If we have some styles to add, add them now.
		if ( ! empty( $styles ) ) {
			// enqueue the theme's style.css file.
			$current_theme = ( wp_get_theme() );

		}
	}

	/**
	 * Gets all our styles and returns them as a string.
	 *
	 * @since 1.7.3
	 */
	public function get_styles() {
		// Get an array of all our fields.
		$fields = self::$fields;
		// Check if we need to exit early.
		if ( empty( self::$fields ) || ! is_array( $fields ) ) {
			return;
		}
		// initially we're going to format our styles as an array.
		// This is going to make processing them a lot easier
		// and make sure there are no duplicate styles etc.
		$css = [];
		// start parsing our fields.
		foreach ( $fields as $field ) {
			// No need to process fields without an output, or an improperly-formatted output.
			if ( ! isset( $field['output'] ) || empty( $field['output'] ) || ! is_array( $field['output'] ) ) {
				continue;
			}
			// Get the value of this field.
			$value = self::get_option( $field['kirki_config'], $field['settings'] );
			// start parsing the output arguments of the field.
			foreach ( $field['output'] as $output ) {
				// Make sure all output properties are properly set before proceeding.
				$element     = ( isset( $output['element'] ) ) ? $output['element'] : '';
				$property    = ( isset( $output['property'] ) ) ? $output['property'] : '';
				$media_query = ( isset( $output['media_query'] ) ) ? $output['media_query'] : 'global';
				$prefix      = ( isset( $output['prefix'] ) ) ? $output['prefix'] : '';
				$units       = ( isset( $output['units'] ) ) ? $output['units'] : '';
				$suffix      = ( isset( $output['suffix'] ) ) ? $output['suffix'] : '';

				// If element is an array, convert it to a string.
				if ( is_array( $element ) ) {
					$element = array_unique( $element );
					sort( $element );
					$element = implode( ',', $element );
				}
				// Simple fields.
				if ( ! is_array( $value ) ) {
					if ( ! empty( $element ) && ! empty( $property ) ) {
						$css[ $media_query ][ $element ][ $property ] = $prefix . $value . $units . $suffix;
					}
				} elseif ( 'typography' == $field['type'] ) {
					foreach ( $value as $key => $subvalue ) {
						if ( 'font-family' == $key ) {
							// add double quotes if needed.
							if ( false !== strpos( $subvalue, ' ' ) && false === strpos( $subvalue, '"' ) ) {
								$css[ $media_query ][ $element ][ $key ] = '"' . $subvalue . '"';
							}
						}
						if ( 'variant' == $key ) {
							$font_weight = str_replace( 'italic', '', $subvalue );
							$font_weight = ( in_array( $font_weight, [ '', 'regular' ], true ) ) ? '400' : $font_weight;
							// Is this italic?
							$is_italic = ( false !== strpos( $subvalue, 'italic' ) );
							$styles[ $media_query ][ $element ]['font-weight'] = $font_weight;
							if ( $is_italic ) {
								$styles[ $media_query ][ $element ]['font-style'] = 'italic';
							}
						} else {
							$css[ $media_query ][ $element ][ $key ] = $subvalue;
						}
					}
				} elseif ( 'spacing' == $field['type'] ) {
					foreach ( $value as $key => $subvalue ) {
						if ( empty( $property ) ) {
							$property = $key;
						} elseif ( false !== strpos( $output['property'], '%%' ) ) {
							$property = str_replace( '%%', $key, $property );
						} else {
							$property = $property . '-' . $key;
						}
						$css[ $media_query ][ $element ][ $property ] = $subvalue;
					}
				}
			}
		}
		// Process the array of CSS properties and produce the final CSS.
		$final_css = '';
		if ( ! is_array( $css ) || empty( $css ) ) {
			return '';
		}
		// Parse the generated CSS array and create the CSS string for the output.
		foreach ( $css as $media_query => $styles ) {
			// Handle the media queries.
			$final_css .= ( 'global' != $media_query ) ? $media_query . '{' : '';
			foreach ( $styles as $style => $style_array ) {
				$final_css .= $style . '{';
				foreach ( $style_array as $property => $value ) {
					$value = ( is_string( $value ) ) ? $value : '';
					// Make sure background-images are properly formatted.
					if ( 'background-image' == $property ) {
						if ( false === strrpos( $this->value, 'url(' ) ) {
							$this->value = 'url("' . esc_url_raw( $value ) . '")';
						}
					} else {
						$value = esc_textarea( $value );
					}
					$final_css .= $property . ':' . $value . ';';
				}
				$final_css .= '}';
			}
			$final_css .= ( 'global' != $media_query ) ? '}' : '';
		}
		return $final_css;
	}

	/**
	 * Enqueue Fonts.
	 *
	 * @since 1.7.3
	 */
	public function enqueue_fonts() {
		// Check if we need to exit early.
		if ( empty( self::$fields ) || ! is_array( self::$fields ) ) {
			return;
		}

		foreach ( self::$fields as $field ) {
			// Process typography fields.
			if ( isset( $field['type'] ) && 'typography' == $field['type'] ) {
				// Check if we've got everything we need.
				if ( ! isset( $field['kirki_config'] ) || ! isset( $field['settings'] ) ) {
					continue;
				}
				$value = self::get_option( $field['kirki_config'], $field['settings'] );
				if ( isset( $value['font-family'] ) ) {
					$url = '//fonts.googleapis.com/css?family=' . str_replace( ' ', '+', $value['font-family'] );
					if ( ! isset( $value['variant'] ) ) {
						$value['variant'] = '';
					}
					if ( ! empty( $value['variant'] ) ) {
						$url .= ':' . $value['variant'];
					}
					if ( ! isset( $value['subset'] ) ) {
						$value['subset'] = '';
					}
					if ( ! empty( $value['subset'] ) ) {
						if ( is_array( $value['subset'] ) ) {
							$value['subset'] = implode( ',', $value['subsets'] );
						}
						$url .= '&subset=' . $value['subset'];
					}
					$key = md5( $value['font-family'] . $value['variant'] . $value['subset'] );
					// check that the URL is valid. we're going to use transients to make this faster.
					$url_is_valid = get_transient( $key );
					if ( false === $url_is_valid ) { // transient does not exist.
						$response = wp_remote_get( 'https:' . $url );
						if ( ! is_array( $response ) ) {
							// the url was not properly formatted,
							// cache for 12 hours and continue to the next field.
							set_transient( $key, null, 12 * HOUR_IN_SECONDS );
							continue;
						}
						// check the response headers.
						if ( isset( $response['response'] ) && isset( $response['response']['code'] ) ) {
							if ( 200 == $response['response']['code'] ) {
								// URL was ok.
								// set transient to true and cache for a week.
								set_transient( $key, true, 7 * 24 * HOUR_IN_SECONDS );
								$url_is_valid = true;
							}
						}
					}
					// If the font-link is valid, enqueue it.
					if ( $url_is_valid ) {
						wp_enqueue_style( $key, $url, null, null );
					}
				}
			}
		}
	}

	/**
	 * Move some default sections to 'general' panel
	 *
	 * @since 1.7.3
	 *
	 * @param [type] $wp_customize
	 */
	public function customize_modify( $wp_customize ) {
		$wp_customize->get_section( 'title_tagline' )->panel     = 'general';
		$wp_customize->get_section( 'static_front_page' )->panel = 'general';
	}

	/**
	 * Initialize customizer settings
	 *
	 * @since 1.7.3
	 */
	public function customizer_init() {

		self::add_config(
			'cariera',
			[
				'option_type' => 'option',
				'capability'  => 'edit_theme_options',
			]
		);

		require get_template_directory() . '/inc/customizer/blog.php';
		require get_template_directory() . '/inc/customizer/companies.php';
		require get_template_directory() . '/inc/customizer/dashboard.php';
		require get_template_directory() . '/inc/customizer/extra.php';
		require get_template_directory() . '/inc/customizer/footer.php';
		require get_template_directory() . '/inc/customizer/general.php';
		require get_template_directory() . '/inc/customizer/header.php';
		require get_template_directory() . '/inc/customizer/jobs.php';
		require get_template_directory() . '/inc/customizer/pages.php';
		require get_template_directory() . '/inc/customizer/resumes.php';
		require get_template_directory() . '/inc/customizer/typography.php';
		require get_template_directory() . '/inc/customizer/woocommerce.php';
	}
}
