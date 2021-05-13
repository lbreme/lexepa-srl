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
	public function begin_parsing( $string, $offset ) {
		echo 'String serialized: ' . $string . '<br />';
	}

	public function string_length( $string_length, $offset )
	{
		echo 'String length: ' . $string_length . '<br />';
	}

	public function string_value( $string_value, $offset )
	{
		echo 'String value: ' . $string_value . '<br />';
	}

	public function integer_value( $integer_value, $offset )
	{
		echo 'Integer value: ' . $integer_value . '<br />';
	}

	public function decimal_value( $decimal_value, $offset ) {
		echo 'Decimal value: ' . $decimal_value . '<br />';
	}

	public function boolean_value( $boolean_value, $offset ) {
		echo 'Boolean value: ' . $boolean_value . '<br />';
	}

	public function null_value() {
		echo 'Null value found' . '<br />';
	}

	public function array_items_num( $items_num, $offset ) {
		echo 'Number of items of the array: ' . $items_num . '<br />';
	}

	public function end_parsing( $parse_result )
	{
		if ( $parse_result ) {
			echo 'Good job!' . '<br />';
		} else {
			echo 'There was an error' . '<br />';
		}
	}

	public function set_error( $error )
	{
		echo $error . '<br />';
	}
}

$example_srl = new Example_Srl();
$lexepa_srl  = new Lexepa_Srl( $example_srl, $myArraySerialized );

$lexepa_srl->parse_srl();

?>