<?php
require_once('TransactionField.php');

class Transaction {
	/**
	 * Set the path to the transactions file.
	 *
	 * @var string
	 * @access private
	 */
	private $log_file = null;

	/**
	 * The integer transaction type code.
	 *
	 * @var integer
	 * @access private
	 */
	protected $type = null;

	/**
	 * Holds an array of integer transaction types, indexed by their common name. 
	 *
	 * @var array
	 * @access private
	 */
	private static $types = array(
		'waitlist' => 8,
		'recurring_ach' => 21,
		'recurring_cc' => 20,
		'cash_or_check' => 22
		);

	/**
	 * Indicates whether or not the given transaction has been successfully logged to the log file. 
	 *
	 * @var boolean
	 * @access private
	 */
	private $logged = false;

	protected $log_entry_field_groups = array(
			array(
				'timestamp',
				'_blank',
				'ip_address'
				)
		);


	private $required_field_group_level = 0;

	/**
	 * Holds an array of TransactionField objects, indexed by their field name. 
	 *
	 * @var array
	 * @access private
	 */
	private $fields = array();

	/**
	 * Holds an array of log fiels. 
	 *
	 * @var array
	 * @access private
	 */
	private $log_fields = array();

	private $log_separator = "^^";


	/**
	 * Checks that a string contains something other than whitespace
	 *
	 * Returns true if string contains something other than whitespace
	 *
	 * $check can be passed as an array:
	 * array('check' => 'valueToCheck');
	 *
	 * @param string $type Transaction type by common name
	 * @return boolean Success
	 * @access public
	 */
	public function __construct( $type=null ){
		if( is_null( $this->type ) ) {
			//Because extensions will automatically set the type
			$this->_set_type( $type );
		}

		$this->_set_ip_address();
		$this->_set_timestamp();
		
	}

	/**
	 * Sets the Transaction type by common name or integer type value
	 *
	 * Returns true if the Transaction type was set successfully
	 *
	 * $type can be passed as an integer or a common type name
	 *
	 * @param string $type Transaction type by common name
	 * @return boolean Success
	 * @access public
	 */
	private function _set_type( $type ){
		if( is_int( $type ) ){

		} else {
			if( array_key_exists( $type, self::$types ) ){
				$this->type = self::$types[$type];
				return true;
			}
		}
	}

	/**
	 * Sets the Transaction type by common name or integer type value
	 *
	 * Returns true if the Transaction type was set successfully
	 *
	 * $type can be passed as an integer or a common type name
	 *
	 * @param string $field_name The name of the TransactionField
	 * @param mixed $field_value If setting the value of a field, the desired new value
	 * @return boolean Success if trying to set a field value
	 * @return mixed if getting a value
	 * @access public
	 */
	public function field( $field_name, $field_value = null ){
		$field = $this->_get_field_by_name( $field_name );

		if( $field && !is_null( $field_value ) ){
			// Edit the field value
			return $this->_set_field_value( $field, $field_value );

		} elseif( !$field && !is_null( $field_value ) ) {
			// Add a new field with the given value
			return $this->_add_field( $field_name, $field_value );

		} elseif( $field )  {
			// Get the field value
			return $this->_get_field_value( $field );

		} else {
			//Trying to pass a value to a field that doesn't exists
			return false;
			/**
			 Should we throw an exception here instead?
			 */
		}
	}

	public function commit(){
		$entry = $this->get_log_entry();
		return $entry ? $this->_write_to_file( $entry ) : false;
	}

	private function  _write_to_file( $entry ){

	}

	public function get_log_entry(){
		$string_type = strval($this->type);
		$string_type = ( strlen($string_type) < 2 ) ? "0" . $string_type : $string_type;
		
		$entry = $string_type;

		$level = $this->_set_required_field_group_level();

		for($i=0;$i<=$level;$i++){
			$group = $this->log_entry_field_groups[$i];
			if(!empty($group)){
				foreach($group as $field_name => $required){
					$entry .= $this->log_separator;
					try {
						
						$field = $this->_get_field_by_name( $field_name );
						if( !$field && $required ){
							throw new Exception;
						} elseif( $field ) {
							$entry .= $this->_get_field_value( $field );
						}	
						
					} catch( Exception $e ) {
						//trigger_error('The required field "' . $field_name . '" was left empty.', E_WARNING);
						die('The required field "' . $field_name . '" was left empty.');
						return false;
					}
				}
			}
		}
		return $entry;
	}

	private function _set_field_value( $field, $field_value ){
		return ( $field->set_value( $field_value ) ) ? $this->_get_field_value( $field ) : false;
	}

	private function _get_field_value( $field ){
		return $field->value();
	}

	private function _add_field( $field_name, $field_value ){
		$field = new TransactionField( $field_name, $field_value );

		$desired_number_of_fields = count( $this->fields ) + 1;
		$this->fields[$field_name] = $field;
		$actual_number_of_fields = count( $this->fields );
		
		if( ( $desired_number_of_fields == $actual_number_of_fields )
			&& ( get_class( end( $this->fields ) ) == 'TransactionField' ) ) {
			return $this->_get_field_value( $field );
		} else {
			return false;
		}
	}

	/**
	 * Gets a TransactionField object by name
	 *
	 * Returns a TransactionField object if one exists
	 * within the $fields array at the given index, or false if no
	 * object exists at the given index
	 *
	 * $field_name can be passed as an integer or a common type name
	 *
	 * @param string $field_name The name of the TransactionField
	 * @return TransactionField Success
	 * @return boolean Failure
	 * @access public
	 */
	private function _get_field_by_name( $field_name ){
		if( $this->_field_exists( $field_name ) ){
			return $this->fields[$field_name];
		} else {
			return false;
		}
	}

	/**
	 * Checks to see if a given field exists
	 *
	 * Returns true if field exists, false if not
	 *
	 * @param string $field_name The name of a potential TransactionField
	 * @return boolean Does field exist
	 * @access public
	 */
	private function _field_exists( $field_name ){
		return array_key_exists( $field_name, $this->fields );
	}

	private function _set_ip_address(){
		return $this->_add_field( 'ip_address', $_SERVER['REMOTE_ADDR'] );
	}

	private function _set_timestamp(){
		return $this->_add_field( 'timestamp', strftime( "%D %R" ) );
	}

	private function _set_required_field_group_level(){
		$field_group_count = count( $this->log_entry_field_groups );
		$field_groups = array_reverse( $this->log_entry_field_groups );

		foreach( $field_groups as $field_group_key => $field_group ){
			if( !empty( $field_group ) ){
				foreach( $field_group as $field_name => $required ){
					$field = $this->_get_field_by_name( $field_name );
					if( $field && $field->value() ){
						$level = ( $field_group_count - $field_group_key - 1 );
						if( $level > $this->required_field_group_level ){
							$this->required_field_group_level = $level;
						}
					}
				}
			}
		}
		return $this->required_field_group_level;
	}


}
