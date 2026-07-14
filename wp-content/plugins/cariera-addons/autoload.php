<?php
namespace Cariera_Addons;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload Core classes and files
 *
 * @since   1.0.0
 * @version 1.0.1
 *
 * @param string $class
 */
function autoload( $class ) {

	// Namespace prefix.
	$prefix = 'Cariera_Addons\\';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// No, move to the next registered autoloader.
		return;
	}

	// If the plugin path constant isn't defined, stop.
	if ( ! defined( 'CARIERA_ADDONS_PATH' ) ) {
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Replace '_' with '-'.
	$classname = strtolower( str_replace( '_', '-', $relative_class ) );

	// Replace the namespace prefix with the base directory, replace namespace
	// Separators with directory separators in the relative class name, append with .php.
	$file = strtolower( str_replace( '\\', '/', $classname ) ) . '.php';

	$file_location = untrailingslashit( CARIERA_ADDONS_PATH ) . "/inc/{$file}";
	if ( file_exists( $file_location ) ) {
		require_once $file_location;
	}
}

spl_autoload_register( 'Cariera_Addons\autoload' );
