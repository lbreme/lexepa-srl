<?php

namespace Breme\Lexepa\srl;

use Breme\Lexepa\srl\Lexepa_Srl_Interface;

//require_once( 'class-lexepa-srl-interface.php' );

/**
 * Lexepa Abstract Class.
 *
 * Abstract class that implements functions defined by Lexepa_Srl_Interface interface.
 *
 */
abstract class Lexepa_Srl_Abstract implements Lexepa_Srl_Interface
{
	/**
	 * Begin of parsing
	 *
	 * @param string $string Original string to unserialize.
	 * @param int    $offset Initial offset.
	 */
	public function begin_parsing( $string, $offset ) {}

	/**
	 * Null value found
	 *
	 */
	public function null_value() {}

	/**
	 * Boolean value found
	 *
	 * @param string $boolean_value It can be '0' or '1'.
	 * @param int    $offset Offset of the boolean value found.
	 */
	public function boolean_value( $boolean_value, $offset ) {}

	/**
	 * Integer value found
	 *
	 * @param string $integer_value Integer value.
	 * @param int    $offset Offset of the integer value found.
	 */
	public function integer_value( $integer_value, $offset ) {}

	/**
	 * Array reference found
	 *
	 * @param string $index Index of the array found.
	 * @param int    $offset Offset of the index found.
	 */
	public function array_reference( $index, $offset ) {}

	/**
	 * Object reference found
	 *
	 * @param string $index Index of the object found.
	 * @param int    $offset Offset of the index found.
	 */
	public function object_reference( $index, $offset ) {}

	/**
	 * Decimal value found
	 *
	 * @param string $decimal_value Decimal value.
	 * @param int    $offset Offset of the decimal value found.
	 */
	public function decimal_value( $decimal_value, $offset ) {}

	/**
	 * String length value found
	 *
	 * @param string $string_length String length value.
	 * @param int    $offset Offset of the string length value found.
	 */
	public function string_length( $string_length, $offset ) {}

	/**
	 * String value found
	 *
	 * @param string $string_value String value.
	 * @param int    $offset Offset of the string value found.
	 */
	public function string_value( $string_value, $offset ) {}

	/**
	 * Number of items of the array found
	 *
	 * @param string $items_num Number of items.
	 * @param int    $offset Offset of the number of items found.
	 */
	public function array_items_num( $items_num, $offset ) {}

	/**
	 * End of the array
	 *
	 */
	public function end_array() {}

	/**
	 * Object name length value found
	 *
	 * @param string $object_name_length Object name length value.
	 * @param int    $offset Offset of the object name length value found.
	 */
	public function object_name_length( $object_name_length, $offset ) {}

	/**
	 * Object name value found
	 *
	 * @param string $object_name Object name value.
	 * @param int    $offset Offset of the object name value found.
	 */
	public function object_name( $object_name, $offset ) {}

	/**
	 * Number of properties of the object found
	 *
	 * @param string $object_props_num Number of properties of the object.
	 * @param int    $offset Offset of the number of properties of the object found.
	 */
	public function object_props_num( $object_props_num, $offset ) {}

	/**
	 * End of the object
	 *
	 */
	public function end_object() {}

	/**
	 * Custom object name length value found
	 *
	 * @param string $custom_object_name_length Custom object name length value.
	 * @param int    $offset Offset of the custom object name length value found.
	 */
	public function custom_object_name_length( $custom_object_name_length, $offset ) {}

	/**
	 * Custom object name value found
	 *
	 * @param string $custom_object_name Custom object name value.
	 * @param int    $offset Offset of the custom object name value found.
	 */
	public function custom_object_name( $custom_object_name, $offset ) {}

	/**
	 * Number of properties of the custom object found
	 *
	 * @param string $custom_object_props_num Number of properties of the custom object.
	 * @param int    $offset Offset of the number of properties of the custom object found.
	 */
	public function custom_object_props_num( $custom_object_props_num, $offset ) {}

	/**
	 * End of the custom object
	 *
	 */
	public function end_custom_object() {}

	/**
	 * End of parsing
	 *
	 * @param bool $parsing_result True if the string is unserializable.
	 */
	public function end_parsing( $parsing_result ) {}

	/**
	 * Set error parsing the string.
	 *
	 * @param string $error Error parsing the string.
	 */
	public function set_error( $error ) {}
}

?>