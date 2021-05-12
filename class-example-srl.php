<?php

require_once __DIR__ . '/vendor/autoload.php';

use Breme\Lexepa\srl\Lexepa_Srl_Abstract;
use Breme\Lexepa\srl\Lexepa_Srl;

class MyClass
{
	private   $my_private   = 'This is my private attribute';

	protected $my_protected = 'This is my protected attribute';

	protected $my_array     = array(
		'key1' => 'This is my first value',
		'key2' => 'This is my second value',
		'key3' => 20
	);
}

$myObject = new MyClass();

$myObjectSerialized = serialize( $myObject );

class Example_Srl extends Lexepa_Srl_Abstract
{
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
$lexepa_srl  = new Lexepa_Srl( $example_srl, $myObjectSerialized );

$lexepa_srl->parse_srl();

?>