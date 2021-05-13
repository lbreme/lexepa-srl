<?php

namespace Breme\Lexepa\srl;

define('T_SRL_NULL',          0);
define('T_SRL_BOOL',          1);
define('T_SRL_INTEGER',       2);
define('T_SRL_DECIMAL',       3);
define('T_SRL_STRING',        4);
define('T_SRL_ARRAY',         5);
define('T_SRL_OBJECT',        6);
define('T_SRL_CUSTOM',        7);
define('T_SRL_REF_ARRAY',     8);
define('T_SRL_REF_OBJ',       9);
define('T_SRL_OPEN_BRACKET',  10);
define('T_SRL_CLOSE_BRACKET', 11);
define('T_SRL_COLON',         12);
define('T_SRL_SEMICOLON',     13);
define('T_SRL_DOUBLE_APEX',   14);
define('T_SRL_INTEGER_VALUE', 15);
define('T_SRL_DECIMAL_VALUE', 16);
define('T_SRL_STRING_VALUE',  17);

/**
 * Lexepa_Srl Class.
 *
 * Class for lexing and parsing a serialized string in PHP.
 *
 */
class Lexepa_Srl
{
	/**
	 * Index of the tokens array.
	 *
	 * @var int
	 */
	private $idx = 0;

	/**
	 * Offset.
	 *
	 * @var int
	 */
	private $offset = 0;

	/**
	 * String to parse.
	 *
	 * @var string
	 */
	private $value = '';

	/**
	 * Pieces of the string to be parsed.
	 *
	 * @var string
	 */
	private $chunk = '';

	/**
	 * Current character.
	 *
	 * @var string
	 */
	private $curr_char = '';

	/**
	 * Length of the string.
	 *
	 * @var int
	 */
	private $string_length = 0;

	/**
	 * If we are in the "get all characters" state.
	 *
	 * @var bool
	 */
	private $catch_all_state = false;

	/**
	 * If we are in the "escape" state.
	 *
	 * @var bool
	 */
	private $escape_state = false;

	/**
	 * Reference to the object that implements the Lexepa_Srl_Interface interface.
	 *
	 * @var Lexepa_Srl_Interface
	 */
	private $lexepa_srl = null;

	/**
	 * Tokens of the string to parse.
	 *
	 * @var array
	 */
	private $tokens = array();

	/**
	 * Characters reserved for parsing.
	 *
	 * @var array
	 */
	private $reserved = array(
		"N"  => T_SRL_NULL,
		"b"  => T_SRL_BOOL,
		"i"  => T_SRL_INTEGER,
		"d"  => T_SRL_DECIMAL,
		"s"  => T_SRL_STRING,
		"a"  => T_SRL_ARRAY,
		"O"  => T_SRL_OBJECT,
		"C"  => T_SRL_CUSTOM,
		"R"  => T_SRL_REF_ARRAY,
		"r"  => T_SRL_REF_OBJ,
		"{"  => T_SRL_OPEN_BRACKET,
		"}"  => T_SRL_CLOSE_BRACKET,
		":"  => T_SRL_COLON,
		";"  => T_SRL_SEMICOLON,
		"\"" => T_SRL_DOUBLE_APEX
	);

	/**
	 * Load the object that implements Lexepa_Srl_Interface interface, the string to parse and an initial offset.
	 *
	 * @param Lexepa_Srl_Interface $lexepa_srl     Reference to the object that implements the Lexepa_Srl_Interface interface.
	 * @param string               $string         String to parse. Optional.
	 * @param int                  $initial_offset Initial offset. Optional.
	 */
	public function __construct( Lexepa_Srl_Interface $lexepa_srl, $string = '', $initial_offset = 0 ) {

		$this->lexepa_srl = $lexepa_srl;
		$this->value      = $string;
		$this->offset     = $initial_offset;
	}

	/**
	 * Set the string to parse.
	 *
	 * @param string $string String to parse.
	 */
	public function set_string( $string )
	{
		$this->value = $string;
	}

	/**
	 * Get the string to parse.
	 *
	 * @return string String to parse.
	 */
	public function get_string()
	{
		return $this->value;
	}

	/**
	 * Set the string to parse.
	 *
	 * @param int $initial_offset Initial offset.
	 */
	public function set_initial_offset( $initial_offset )
	{
		$this->offset = $initial_offset;
	}

	/**
	 * Get the initial offset.
	 *
	 * @return int Initial offset.
	 */
	public function get_initial_offset()
	{
		return $this->offset;
	}

	/**
	 * Begin the process of lexing and parsing.
	 *
	 */
	public function parse_srl()
	{
		if ( empty( $this->value ) ) return;

		$offset = $this->offset;

		$this->reset();

		$values = str_split( $this->value );

		foreach ( $values as $this->curr_char ) {

			if ( $this->string_length > 0 ) {
				switch ( $this->curr_char ) {
					case "\\":
						if ( ! $this->escape_state ) {
							$this->escape_state = true;
						} else {
							$this->escape_state = false;
							$this->string_length--;
						}
						$this->chunk .= $this->curr_char;
						break;
					default:
						$this->escape_state = false;
						$this->chunk .= $this->curr_char;
						$this->string_length--;
						break;
				}

				if ( 0 === $this->string_length ) {
					$this->tokens[] = array( 'T' => T_SRL_STRING_VALUE, 'V' => $this->chunk, 'O' => ( $this->offset - strlen( $this->chunk ) + 1 ) );
					$this->chunk = '';
				}
			} else {
				switch ( $this->curr_char ) {
					case "\"":
						$this->add_token( $this->curr_char );
						if ( ! $this->catch_all_state ) {
							if ( isset( $this->tokens[ count( $this->tokens ) - 3 ] ) && T_SRL_INTEGER_VALUE === $this->tokens[ count( $this->tokens ) - 3 ]['T'] ) {
								$this->string_length = (int) $this->tokens[ count( $this->tokens ) - 3 ]['V'];
								if ( 0 === $this->string_length ) { // case of length string = 0
									$this->tokens[] = array( 'T' => T_SRL_STRING_VALUE, 'V' => '', 'O' => $this->offset + 1 );
								}
								$this->catch_all_state = true;
							}
						} else {
							$this->catch_all_state = false;
						}
						break;
					case "{":
						$this->add_token( $this->curr_char );
						if ( isset( $this->tokens[ count( $this->tokens ) - 11 ] ) && T_SRL_CUSTOM === $this->tokens[ count( $this->tokens ) - 11 ]['T'] ) {
							if ( isset( $this->tokens[ count( $this->tokens ) - 3 ] ) && T_SRL_INTEGER_VALUE === $this->tokens[ count( $this->tokens ) - 3 ]['T'] ) {
								$this->string_length = (int) $this->tokens[ count( $this->tokens ) - 3 ]['V'];
								if ( 0 === $this->string_length ) { // case of length string = 0
									$this->tokens[] = array( 'T' => T_SRL_STRING_VALUE, 'V' => '', 'O' => $this->offset + 1 );
								}
							}
						}
						break;
					case "}":
					case ":":
					case ";":
						$this->add_token( $this->curr_char );
						break;
					case "\\":
						break;
					default:
						$this->chunk .= $this->curr_char;
						break;
				}
			}

			$this->offset++;
		}

		$this->lexepa_srl->begin_parsing( $this->value, $offset);
		$parsing_result = $this->parse_srl_string();
		$this->lexepa_srl->end_parsing( $parsing_result );
	}

	/**
	 * Reset tokens, chunk and index
	 *
	 */
	private function reset()
	{
		$this->tokens       = array();
		$this->chunk        = '';
		$this->idx          = 0;
	}

	/**
	 * Add a token.
	 *
	 * @param string $char Character index for reserved characters. Optional.
	 */
	private function add_token( $char = null ) {
		if ( '' !== $this->chunk ) {
			if ( isset( $this->reserved[ $this->chunk ] ) ) {
				$this->tokens[] = array( 'T' => $this->reserved[ $this->chunk ], 'V' => $this->chunk, 'O' => ( $this->offset - strlen( $this->chunk ) ) );
			} else if ( is_numeric( $this->chunk ) ) {
				if ( false === strpos( $this->chunk, '.' ) ) {
					$this->tokens[] = array( 'T' => T_SRL_INTEGER_VALUE, 'V' => $this->chunk, 'O' => ( $this->offset - strlen( $this->chunk ) ) );
				} else {
					$this->tokens[] = array( 'T' => T_SRL_DECIMAL_VALUE, 'V' => $this->chunk, 'O' => ( $this->offset - strlen( $this->chunk ) ) );
				}
			}
			$this->chunk = '';
		}
		if ( ! is_null( $char ) ) {
			$this->tokens[] = array( 'T' => $this->reserved[ $char ], 'V' => $char, 'O' => $this->offset );
		}
	}

	/**
	 * Begin parsing the serialized string. See https://www.phpinternalsbook.com/php5/classes_objects/serialization.html
	 *
	 * @return bool True if the string is unserializable.
	 */
	private function parse_srl_string()
	{
		$parse_srl_string = false;

		if ( isset( $this->tokens[ $this->idx ] ) ) {

			// Null pattern: N;
			if ( T_SRL_NULL === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_SEMICOLON === $this->tokens[ $this->idx ]['T'] ) {
						$this->lexepa_srl->null_value();
						$parse_srl_string = true;
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			// Boolean pattern: b:1; or b:0;
			} else if ( T_SRL_BOOL === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] && in_array( $this->tokens[ $this->idx ]['V'], array( '0', '1' ), true ) ) {
								$this->lexepa_srl->boolean_value( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_SEMICOLON === $this->tokens[ $this->idx ]['T'] ) {
										$parse_srl_string = true;
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected the characters "0" or "1" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected the characters "0" or "1" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			// Integer pattern: i:42;
			} else if ( T_SRL_INTEGER === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
								$this->lexepa_srl->integer_value( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_SEMICOLON === $this->tokens[ $this->idx ]['T'] ) {
										$parse_srl_string = true;
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			// Reference in an Array: R:2;
			} else if ( T_SRL_REF_ARRAY === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
								$this->lexepa_srl->array_reference( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_SEMICOLON === $this->tokens[ $this->idx ]['T'] ) {
										$parse_srl_string = true;
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			// Reference in an Object: r:1;
			} else if ( T_SRL_REF_OBJ === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
								$this->lexepa_srl->object_reference( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_SEMICOLON === $this->tokens[ $this->idx ]['T'] ) {
										$parse_srl_string = true;
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {

					}
				} else {
					$this->lexepa_srl->set_error( _( 'We expected the character ":" at the position ' ) . $this->tokens[ $this->idx ]['O'] );
				}
			// Decimal pattern: d:42.378900000000002;
			} else if ( T_SRL_DECIMAL === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_DECIMAL_VALUE === $this->tokens[ $this->idx ]['T'] ) {
								$this->lexepa_srl->decimal_value( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_SEMICOLON === $this->tokens[ $this->idx ]['T'] ) {
										$parse_srl_string = true;
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected a decimal value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected a decimal value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			// String pattern: s:6:"foobar";
			} else if ( T_SRL_STRING === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
								$this->lexepa_srl->string_length( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
										if ( isset( $this->tokens[ ++$this->idx ] ) ) {
											if ( T_SRL_DOUBLE_APEX === $this->tokens[ $this->idx ]['T'] ) {
												if ( isset( $this->tokens[ ++$this->idx ] ) ) {
													if ( T_SRL_STRING_VALUE === $this->tokens[ $this->idx ]['T'] ) {
														$this->lexepa_srl->string_value( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
														if ( isset( $this->tokens[ ++$this->idx ] ) ) {
															if ( T_SRL_DOUBLE_APEX === $this->tokens[ $this->idx ]['T'] ) {
																if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																	if ( T_SRL_SEMICOLON === $this->tokens[ $this->idx ]['T'] ) {
																		$parse_srl_string = true;
																	} else {
																		$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																	}
																} else {
																	$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ";" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																}
															} else {
																$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
															}
														} else {
															$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
														}
													} else {
														$this->lexepa_srl->set_error( sprintf( _( 'It is expected a sring value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
													}
												} else {
													$this->lexepa_srl->set_error( sprintf( _( 'It is expected a sring value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
												}
											} else {
												$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
											}
										} else {
											$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
										}
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			// Array pattern: a:2:{...}
			} else if ( T_SRL_ARRAY === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
								$this->lexepa_srl->array_items_num( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
										if ( isset( $this->tokens[ ++$this->idx ] ) ) {
											if ( T_SRL_OPEN_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
												if ( isset( $this->tokens[ ++$this->idx ] ) ) {
													if ( T_SRL_CLOSE_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
														$this->lexepa_srl->end_array();
														$parse_srl_string = true;
													} else {
														while ( $this->parse_srl_string() ) {
															if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																if ( T_SRL_CLOSE_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
																	$this->lexepa_srl->end_array();
																	$parse_srl_string = true;
																	break;
																}
															}
														}
													}
												} else {
													$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character "}" or another string to unserialize at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
												}
											} else {
												$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character "(" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
											}
										} else {
											$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character "(" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
										}
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			// Object pattern: O:4:"Test":3:{...}
			} else if ( T_SRL_OBJECT === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
								$this->lexepa_srl->object_name_length( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
										if ( isset( $this->tokens[ ++$this->idx ] ) ) {
											if ( T_SRL_DOUBLE_APEX === $this->tokens[ $this->idx ]['T'] ) {
												if ( isset( $this->tokens[ ++$this->idx ] ) ) {
													if ( T_SRL_STRING_VALUE === $this->tokens[ $this->idx ]['T'] ) {
														$this->lexepa_srl->object_name( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
														if ( isset( $this->tokens[ ++$this->idx ] ) ) {
															if ( T_SRL_DOUBLE_APEX === $this->tokens[ $this->idx ]['T'] ) {
																if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																	if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
																		if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																			if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
																				$this->lexepa_srl->object_props_num( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
																				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
																						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																							if ( T_SRL_OPEN_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
																								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																									if ( T_SRL_CLOSE_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
																										$this->lexepa_srl->end_object();
																										$parse_srl_string = true;
																									} else {
																										while ( $this->parse_srl_string() ) {
																											if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																												if ( T_SRL_CLOSE_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
																													$this->lexepa_srl->end_object();
																													$parse_srl_string = true;
																													break;
																												}
																											}
																										}
																									}
																								} else {
																									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character "}" or another string to unserialize at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																								}
																							} else {
																								$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character "(" or another string to unserialize at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																							}
																						} else {
																							$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character "(" or another string to unserialize at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																						}
																					} else {
																						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																					}
																				} else {
																					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																				}
																			} else {
																				$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																			}
																		} else {
																			$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																		}
																	} else {
																		$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																	}
																} else {
																	$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																}
															} else {
																$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
															}
														} else {
															$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
														}
													} else {
														$this->lexepa_srl->set_error( sprintf( _( 'It is expected a string value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
													}
												} else {
													$this->lexepa_srl->set_error( sprintf( _( 'It is expected a string value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
												}
											} else {
												$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
											}
										} else {
											$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
										}
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			// Custom Object pattern: C:5:"Test":6:{...}
			} else if ( T_SRL_CUSTOM === $this->tokens[ $this->idx ]['T'] ) {
				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
							if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
								$this->lexepa_srl->custom_object_name_length( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
									if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
										if ( isset( $this->tokens[ ++$this->idx ] ) ) {
											if ( T_SRL_DOUBLE_APEX === $this->tokens[ $this->idx ]['T'] ) {
												if ( isset( $this->tokens[ ++$this->idx ] ) ) {
													if ( T_SRL_STRING_VALUE === $this->tokens[ $this->idx ]['T'] ) {
														$this->lexepa_srl->custom_object_name( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
														if ( isset( $this->tokens[ ++$this->idx ] ) ) {
															if ( T_SRL_DOUBLE_APEX === $this->tokens[ $this->idx ]['T'] ) {
																if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																	if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
																		if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																			if ( T_SRL_INTEGER_VALUE === $this->tokens[ $this->idx ]['T'] ) {
																				$this->lexepa_srl->custom_object_props_num( $this->tokens[ $this->idx ]['V'], $this->tokens[ $this->idx ]['O'] );
																				if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																					if ( T_SRL_COLON === $this->tokens[ $this->idx ]['T'] ) {
																						if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																							if ( T_SRL_OPEN_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
																								if ( isset( $this->tokens[ ++$this->idx ] ) ) {
																									if ( T_SRL_CLOSE_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
																										$this->lexepa_srl->end_custom_object();
																										$parse_srl_string = true;
																									} else if ( T_SRL_STRING_VALUE === $this->tokens[ $this->idx ]['T'] ) {
																										if ( isset( $this->tokens[ ++$this->idx ] ) && T_SRL_CLOSE_BRACKET === $this->tokens[ $this->idx ]['T'] ) {
																											$parse_srl_string = true;
																										} else {
																											$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ")" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																										}
																									} else {
																										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ")" or a string value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																									}
																								} else {
																									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ")" or a string value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																								}
																							} else {
																								$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character "(" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																							}
																						} else {
																							$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character "(" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																						}
																					} else {
																						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																					}
																				} else {
																					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																				}
																			} else {
																				$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																			}
																		} else {
																			$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																		}
																	} else {
																		$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
																	}
																} else {
																	$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
																}
															} else {
																$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
															}
														} else {
															$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
														}
													} else {
														$this->lexepa_srl->set_error( sprintf( _( 'It is expected a string value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
													}
												} else {
													$this->lexepa_srl->set_error( sprintf( _( 'It is expected a string value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
												}
											} else {
												$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
											}
										} else {
											$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character " (double apex) at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
										}
									} else {
										$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
									}
								} else {
									$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
								}
							} else {
								$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
							}
						} else {
							$this->lexepa_srl->set_error( sprintf( _( 'It is expected a integer value at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
						}
					} else {
						$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
					}
				} else {
					$this->lexepa_srl->set_error( sprintf( _( 'It is expected the character ":" at offset %d' ), $this->tokens[ $this->idx ]['O'] + 1 ) );
				}
			} else {
				$this->lexepa_srl->set_error( sprintf( _( 'Invalid character at offset %d' ), $this->tokens[ $this->idx ]['O'] ) );
			}
		} else {
			$this->lexepa_srl->set_error( _( 'One of the following characters "N", "b", "i", "R", "r", "d", "s", "a", "O", "C" was expected at the beginning of the string' ) );
		}

		return $parse_srl_string;
	}
}

?>