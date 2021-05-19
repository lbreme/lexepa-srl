<?php

require_once __DIR__ . '/vendor/autoload.php';

use Breme\Lexepa\Srl\Lexepa_Srl_Abstract;
use Breme\Lexepa\Srl\Lexepa_Srl;

$my_array = array(
	'key1' => 'This is my first value',
	'key2' => 'This is my second value',
	'key3' => 20,
	'key4' => 45.98,
	'key4' => true,
	'key5' => null
);

$myArraySerialized = serialize( $my_array );

class Example_Srl extends Lexepa_Srl_Abstract
{
	/**
	 * Begin of parsing
	 *
	 * @param string $string Original string to unserialize.
	 * @param int    $offset Initial offset.
	 */
	public function begin_parsing( $string, $offset ) {
		echo 'String serialized: ' . $string . '<br />';
	}

	/**
	 * String length value found
	 *
	 * @param string $string_length String length value.
	 * @param int    $offset Offset of the string length value found.
	 */
	public function string_length( $string_length, $offset )
	{
		echo 'String length: ' . $string_length . '<br />';
	}

	/**
	 * String value found
	 *
	 * @param string $string_value String value.
	 * @param int    $offset Offset of the string value found.
	 */
	public function string_value( $string_value, $offset )
	{
		echo 'String value: ' . $string_value . '<br />';
	}

	/**
	 * Integer value found
	 *
	 * @param string $integer_value Integer value.
	 * @param int    $offset Offset of the integer value found.
	 */
	public function integer_value( $integer_value, $offset )
	{
		echo 'Integer value: ' . $integer_value . '<br />';
	}

	/**
	 * Decimal value found
	 *
	 * @param string $decimal_value Decimal value.
	 * @param int    $offset Offset of the decimal value found.
	 */
	public function decimal_value( $decimal_value, $offset ) {
		echo 'Decimal value: ' . $decimal_value . '<br />';
	}

	/**
	 * Boolean value found
	 *
	 * @param string $boolean_value It can be '0' or '1'.
	 * @param int    $offset Offset of the boolean value found.
	 */
	public function boolean_value( $boolean_value, $offset ) {
		echo 'Boolean value: ' . $boolean_value . '<br />';
	}

	/**
	 * Null value found
	 *
	 */
	public function null_value() {
		echo 'Null value found' . '<br />';
	}

	/**
	 * Number of items of the array found
	 *
	 * @param string $items_num Number of items.
	 * @param int    $offset Offset of the number of items found.
	 */
	public function array_items_num( $items_num, $offset ) {
		echo 'Number of items of the array: ' . $items_num . '<br />';
	}

	/**
	 * End of parsing
	 *
	 * @param bool $parsing_result True if the string is unserializable.
	 */
	public function end_parsing( $parse_result )
	{
		if ( $parse_result ) {
			echo 'Good job!' . '<br />';
		} else {
			echo 'There was an error' . '<br />';
		}
	}

	/**
	 * Set error parsing the string.
	 *
	 * @param string $error Error parsing the string.
	 */
	public function set_error( $error )
	{
		echo $error . '<br />';
	}
}

$example_srl = new Example_Srl();
$lexepa_srl  = new Lexepa_Srl( $example_srl, $myArraySerialized );

$lexepa_srl->parse_srl();

?>