# Lexepa-Srl
Library for lexing and parsing a serialized string in PHP.

<h1>Installing Lexepa-Srl</h1>
<p>First, get <a href="https://getcomposer.org/download/">Composer</a>, if you don't already use it.</p>
<p>Next, run the following command inside the directory of your project:</p>
<pre>composer require lbreme/lexepa-srl</pre>

<h1>How does it work?</h1>
<p>The Lexepa-Srl library analyzes any text string coming from the result of a serialization in PHP. During the analysis a series of callback functions are called to which are passed as arguments the elements that constitute the serialized string.</p>

<p>We clarify with an example, which is contained in the file <a href="https://github.com/lbreme/lexepa-srl/blob/main/class-example-srl.php">class-example-srl.php</a>, which to make it work is to copy in the root of your project:</p>

<pre>
/*
We create a class derived from the Lexepa_Srl_Abstract class, which implements all the
callback functions that will be called by the analysis of the serialized string
*/
class Example_Srl extends Lexepa_Srl_Abstract
{
	/**
	 * Begin of parsing
	 *
	 * @param string $string Original string to unserialize.
	 * @param int    $offset Initial offset.
	 */
	public function begin_parsing( $string, $offset ) {
		echo 'String serialized: ' . $string . '&lt;br /&gt;';
	}

	/**
	 * String length value found
	 *
	 * @param string $string_length String length value.
	 * @param int    $offset Offset of the string length value found.
	 */
	public function string_length( $string_length, $offset )
	{
		echo 'String length: ' . $string_length . '&lt;br /&gt;';
	}

	/**
	 * String value found
	 *
	 * @param string $string_value String value.
	 * @param int    $offset Offset of the string value found.
	 */
	public function string_value( $string_value, $offset )
	{
		echo 'String value: ' . $string_value . '&lt;br /&gt;';
	}

	/**
	 * Integer value found
	 *
	 * @param string $integer_value Integer value.
	 * @param int    $offset Offset of the integer value found.
	 */
	public function integer_value( $integer_value, $offset )
	{
		echo 'Integer value: ' . $integer_value . '&lt;br /&gt;';
	}

	/**
	 * Decimal value found
	 *
	 * @param string $decimal_value Decimal value.
	 * @param int    $offset Offset of the decimal value found.
	 */
	public function decimal_value( $decimal_value, $offset ) {
		echo 'Decimal value: ' . $decimal_value . '&lt;br /&gt;';
	}

	/**
	 * Boolean value found
	 *
	 * @param string $boolean_value It can be '0' or '1'.
	 * @param int    $offset Offset of the boolean value found.
	 */
	public function boolean_value( $boolean_value, $offset ) {
		echo 'Boolean value: ' . $boolean_value . '&lt;br /&gt;';
	}

	/**
	 * Null value found
	 *
	 */
	public function null_value() {
		echo 'Null value found' . '&lt;br /&gt;';
	}

	/**
	 * Number of items of the array found
	 *
	 * @param string $items_num Number of items.
	 * @param int    $offset Offset of the number of items found.
	 */
	public function array_items_num( $items_num, $offset ) {
		echo 'Number of items of the array: ' . $items_num . '&lt;br /&gt;';
	}

	/**
	 * End of parsing
	 *
	 * @param bool $parsing_result True if the string is unserializable.
	 */
	public function end_parsing( $parse_result )
	{
		if ( $parse_result ) {
			echo 'Good job!' . '&lt;br /&gt;';
		} else {
			echo 'There was an error' . '&lt;br /&gt;';
		}
	}

	/**
	 * Set error parsing the string.
	 *
	 * @param string $error Error parsing the string.
	 */
	public function set_error( $error )
	{
		echo $error . '&lt;br /&gt;';
	}
}

$example_srl = new Example_Srl();

/*
We instantiate the Lexepa-Srl library class, passing as arguments the $example_srl object
containing the callback functions and the serialized string
*/
$lexepa_srl  = new Lexepa_Srl( $example_srl, $myArraySerialized );

// Let's start the analysis
$lexepa_srl->parse_srl();

</pre>

<p>The result of this example is as follows:</p>

<pre>
String serialized: a:5:{s:4:"key1";s:22:"This is my first value";s:4:"key2";s:23:"This is my second value";s:4:"key3";i:20;s:4:"key4";b:1;s:4:"key5";N;}
Number of items of the array: 5
String length: 4
String value: key1
String length: 22
String value: This is my first value
String length: 4
String value: key2
String length: 23
String value: This is my second value
String length: 4
String value: key3
Integer value: 20
String length: 4
String value: key4
Boolean value: 1
String length: 4
String value: key5
Null value found
Good job!
</pre>

<p>The callback functions implemented by the Lexepa_Srl_Abstract class are contained and documented in the interface file <a href="https://github.com/lbreme/lexepa-srl/blob/main/src/class-lexepa-srl-interface.php">class-lexepa-srl-interface.php</a></p>

<p>The Lexepa_Srl library was created by taking as reference the following document that specifies how an object is serialized in PHP:</p>
<p><a href="https://www.phpinternalsbook.com/php5/classes_objects/serialization.html">https://www.phpinternalsbook.com/php5/classes_objects/serialization.html</a></p>