<?php

// Introduced in PHP >= 5.3.0
if ( ! function_exists( 'array_replace_recursive' ) ) {
	function array_replace_recursive( $array, $array1 ) {

		if ( ! function_exists( 'compat_array_recurse' ) ) {

			function compat_array_recurse( $array, $array1 ) {

				foreach ( $array1 as $key => $value ) {
					// create new key in $array, if it is empty or not an array
					if ( ! isset( $array[ $key ] ) || ( isset( $array[ $key ] ) && ! is_array( $array[ $key ] ) ) ) {
						$array[ $key ] = array();
					}

					// overwrite the value in the base array
					if ( is_array( $value ) ) {
						$value = compat_array_recurse( $array[ $key ], $value );
					}
					$array[ $key ] = $value;
				}

				return $array;
			}

		}

		// handle the arguments, merge one by one
		$args  = func_get_args();
		$array = $args[0];
		if ( ! is_array( $array ) ) {
			return $array;
		}
		for ( $i = 1; $i < count( $args ); $i ++ ) {
			if ( is_array( $args[ $i ] ) ) {
				$array = compat_array_recurse( $array, $args[ $i ] );
			}
		}

		return $array;
	}
}

/**
 * This file is part of the array_column library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey (http://benramsey.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
// Introduced in PHP >= 5.5.0
if ( ! function_exists( 'array_column' ) ) {
	/**
	 * Returns the values from a single column of the input array, identified by
	 * the $columnKey.
	 *
	 * Optionally, you may provide an $indexKey to index the values in the returned
	 * array by the values from the $indexKey column in the input array.
	 *
	 * @param array $input     A multi-dimensional array (record set) from which to pull
	 *                         a column of values.
	 * @param mixed $columnKey The column of values to return. This value may be the
	 *                         integer key of the column you wish to retrieve, or it
	 *                         may be the string key name for an associative array.
	 * @param mixed $indexKey  (Optional.) The column to use as the index/keys for
	 *                         the returned array. This value may be the integer key
	 *                         of the column, or it may be the string key name.
	 *
	 * @return array
	 */
	function array_column( $input = null, $columnKey = null, $indexKey = null ) {

		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc   = func_num_args();
		$params = func_get_args();

		if ( $argc < 2 ) {
			trigger_error( "array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING );

			return null;
		}

		if ( ! is_array( $params[0] ) ) {
			trigger_error(
				'array_column() expects parameter 1 to be array, ' . gettype( $params[0] ) . ' given',
				E_USER_WARNING
			);

			return null;
		}

		if ( ! is_int( $params[1] )
		     && ! is_float( $params[1] )
		     && ! is_string( $params[1] )
		     && $params[1] !== null
		     && ! ( is_object( $params[1] ) && method_exists( $params[1], '__toString' ) )
		) {
			trigger_error( 'array_column(): The column key should be either a string or an integer', E_USER_WARNING );

			return false;
		}

		if ( isset( $params[2] )
		     && ! is_int( $params[2] )
		     && ! is_float( $params[2] )
		     && ! is_string( $params[2] )
		     && ! ( is_object( $params[2] ) && method_exists( $params[2], '__toString' ) )
		) {
			trigger_error( 'array_column(): The index key should be either a string or an integer', E_USER_WARNING );

			return false;
		}

		$paramsInput     = $params[0];
		$paramsColumnKey = ( $params[1] !== null ) ? (string) $params[1] : null;

		$paramsIndexKey = null;
		if ( isset( $params[2] ) ) {
			if ( is_float( $params[2] ) || is_int( $params[2] ) ) {
				$paramsIndexKey = (int) $params[2];
			} else {
				$paramsIndexKey = (string) $params[2];
			}
		}

		$resultArray = array();

		foreach ( $paramsInput as $row ) {
			$key    = $value = null;
			$keySet = $valueSet = false;

			if ( $paramsIndexKey !== null && array_key_exists( $paramsIndexKey, $row ) ) {
				$keySet = true;
				$key    = (string) $row[ $paramsIndexKey ];
			}

			if ( $paramsColumnKey === null ) {
				$valueSet = true;
				$value    = $row;
			} elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
				$valueSet = true;
				$value    = $row[ $paramsColumnKey ];
			}

			if ( $valueSet ) {
				if ( $keySet ) {
					$resultArray[ $key ] = $value;
				} else {
					$resultArray[] = $value;
				}
			}

		}

		return $resultArray;
	}
}

// Introduced in PHP >= 5.4.0
if ( ! function_exists( 'hex2bin' ) ) {
	function hex2bin( $str ) {

		$sbin = "";
		$len  = strlen( $str );
		for ( $i = 0; $i < $len; $i += 2 ) {
			$sbin .= pack( "H*", substr( $str, $i, 2 ) );
		}

		return $sbin;
	}
}

if ( ! function_exists( 'remove_class_filter' ) ) {
	/**
	 * Remove Class Filter Without Access to Class Object
	 *
	 * In order to use the core WordPress remove_filter() on a filter added with the callback
	 * to a class, you either have to have access to that class object, or it has to be a call
	 * to a static method.  This method allows you to remove filters with a callback to a class
	 * you don't have access to.
	 *
	 * Works with WordPress 1.2+ (4.7+ support added 9-19-2016)
	 * Updated 2-27-2017 to use internal WordPress removal for 4.7+ (to prevent PHP warnings output)
	 *
	 * @param string $tag         Filter to remove
	 * @param string $class_name  Class name for the filter's callback
	 * @param string $method_name Method name for the filter's callback
	 * @param int    $priority    Priority of the filter (default 10)
	 *
	 * @return bool Whether the function is removed.
	 */
	function remove_class_filter( $tag, $class_name = '', $method_name = '', $priority = 10 ) {

		global $wp_filter;

		// Check that filter actually exists first
		if ( ! isset( $wp_filter[ $tag ] ) ) {
			return false;
		}

		/**
		 * If filter config is an object, means we're using WordPress 4.7+ and the config is no longer
		 * a simple array, rather it is an object that implements the ArrayAccess interface.
		 *
		 * To be backwards compatible, we set $callbacks equal to the correct array as a reference (so $wp_filter is updated)
		 *
		 * @see https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/
		 */
		if ( is_object( $wp_filter[ $tag ] ) && isset( $wp_filter[ $tag ]->callbacks ) ) {
			// Create $fob object from filter tag, to use below
			$fob       = $wp_filter[ $tag ];
			$callbacks = &$wp_filter[ $tag ]->callbacks;
		} else {
			$callbacks = &$wp_filter[ $tag ];
		}

		// Exit if there aren't any callbacks for specified priority
		if ( ! isset( $callbacks[ $priority ] ) || empty( $callbacks[ $priority ] ) ) {
			return false;
		}

		// Loop through each filter for the specified priority, looking for our class & method
		foreach ( (array) $callbacks[ $priority ] as $filter_id => $filter ) {

			// Filter should always be an array - array( $this, 'method' ), if not goto next
			if ( ! isset( $filter['function'] ) || ! is_array( $filter['function'] ) ) {
				continue;
			}

			// If first value in array is not an object, it can't be a class
			if ( ! is_object( $filter['function'][0] ) ) {
				continue;
			}

			// Method doesn't match the one we're looking for, goto next
			if ( $filter['function'][1] !== $method_name ) {
				continue;
			}

			// Method matched, now let's check the Class
			if ( get_class( $filter['function'][0] ) === $class_name ) {

				// WordPress 4.7+ use core remove_filter() since we found the class object
				if ( isset( $fob ) ) {
					// Handles removing filter, reseting callback priority keys mid-iteration, etc.
					$fob->remove_filter( $tag, $filter['function'], $priority );

				} else {
					// Use legacy removal process (pre 4.7)
					unset( $callbacks[ $priority ][ $filter_id ] );
					// and if it was the only filter in that priority, unset that priority
					if ( empty( $callbacks[ $priority ] ) ) {
						unset( $callbacks[ $priority ] );
					}
					// and if the only filter for that tag, set the tag to an empty array
					if ( empty( $callbacks ) ) {
						$callbacks = array();
					}
					// Remove this filter from merged_filters, which specifies if filters have been sorted
					unset( $GLOBALS['merged_filters'][ $tag ] );
				}

				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'remove_class_action' ) ) {
	/**
	 * Remove Class Action Without Access to Class Object
	 *
	 * In order to use the core WordPress remove_action() on an action added with the callback
	 * to a class, you either have to have access to that class object, or it has to be a call
	 * to a static method.  This method allows you to remove actions with a callback to a class
	 * you don't have access to.
	 *
	 * Works with WordPress 1.2+ (4.7+ support added 9-19-2016)
	 *
	 * @param string $tag         Action to remove
	 * @param string $class_name  Class name for the action's callback
	 * @param string $method_name Method name for the action's callback
	 * @param int    $priority    Priority of the action (default 10)
	 *
	 * @return bool               Whether the function is removed.
	 */
	function remove_class_action( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
		return remove_class_filter( $tag, $class_name, $method_name, $priority );
	}
}

if ( ! function_exists( 'wpjmsf_trim_whitespace' ) ) {
	function wpjmsf_trim_whitespace( $value ) {

		if ( is_array( $value ) ) {
			$value = array_map( 'wpjmsf_trim_whitespace', $value );
		} else {
			$value = trim( $value );
		}

		return $value;
	}
}