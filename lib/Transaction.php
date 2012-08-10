<?php
require_once('TransactionField.php');
require_once('PHPMailer.php');

class Transaction {
	/**
	 * Set the path to the transactions file.
	 *
	 * @var string
	 * @access private
	 */
	private $log_file = '/../transactions.txt';

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

	private $log_field_delimiter = "^^";
	private $log_field_delimiter_replacement = "**"; //If the delimiter is found in a field value, replace it with this unless the field is wrapped
	private $log_field_wrapper = null; //Set to null if fields are not to be wrapped by a string (like a " or ').

	private $errors = array();

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
	
	public function get_errors(){
		return $this->errors;
	}
	public function commit(){
		$entry = $this->get_log_entry();
		if( is_a( $entry, "Exception" ) ){
			$error_msg = 'There was a problem with the transaction data.';
			$this->_add_error( $error_msg );
			return false;
		} else {
			return $this->_write_to_log( $entry );
		}
	}

	public function get_log_entry(){
		$string_type = strval($this->type);
		$string_type = ( strlen($string_type) < 2 ) ? "0" . $string_type : $string_type;
		
		$entry = $string_type;

		$level = $this->_set_required_field_group_level();
		
		$errors = array();

		for($i=0;$i<=$level;$i++){
			$group = $this->log_entry_field_groups[$i];
			$group_errors = array();

			if(!empty($group)){
				if( $this->_is_group_empty( $group ) && $i != 0 ){
					foreach($group as $field){
						//Add the blank field
						$entry .= $this->log_field_delimiter . $this->log_field_delimiter;
					}
				} else {
					foreach($group as $field_name => $required){
						$entry .= $this->log_field_delimiter;
	
						$field = $this->_get_field_by_name( $field_name );
						if( !$field && $required ){
							array_push( $group_errors, $field_name );
						} elseif( $field ) {
							$entry .= $this->_get_loggified_field_value( $field );
						}	
					}
					if( !empty($group_errors ) ){
						array_push( $errors, $group_errors );
					}
				}
			}	
		}
		/**
		 Kill the process before writing to the transaction file
		 */
		try {
			if( !empty( $errors ) ){
				throw new Exception( print_r( $errors, true ) );
			}
		} catch ( Exception $e ) {
			return $e;
		}

		return $entry;
	}
	

	

	public function add_fields( $fields_array ){
		$errors = array();

		if( !empty( $fields_array ) ){
			foreach( $fields_array as $field_name => $field_value ){
				if( !$this->_add_field( $field_name, $field_value ) ){
					array_push($errors, $field_name);
				}
			}
			return empty( $errors ) ? true : false;
		} else {
			return false;
		}
	}

	private function _add_error( $error ){
		array_push( $this->errors, $error );
	}

	private function  _write_to_log( $entry ){
		if( is_file( __DIR__ . $this->log_file ) ){
			$log_file = fopen( __DIR__ . $this->log_file, 'a' );
			if( $log_file ){
				if( fwrite( $log_file, $entry . "\r\n" ) ){
					fclose( $log_file );
					$this->_send_email_notification();
					return true;
				} else {
					$this->_add_error( 'The transaction could not be written to the log file. ' );
				}
			} else {
				$this->_add_error( 'The log file could not be opened.' );
			}
		} else {
			$this->_add_error( 'The log file could not be found. ' );	
		}
		return false;
	}

	private function _is_group_empty( $group ){
		$total_count = count( $group );

		$incremental_count = 0;
		foreach( $group as $field_name => $required ){
			$field = $this->_get_field_by_name( $field_name );
			if( !$field ){
				$incremental_count++;
			}
		}
		return ( $total_count == $incremental_count ) ? true : false;
	}
	private function _set_field_value( $field, $field_value ){
		return ( $field->set_value( $field_value ) ) ? $this->_get_field_value( $field ) : false;
	}

	private function _get_field_value( $field ){
		return $field->value();
	}

	private function _get_loggified_field_value( $field ){
		$field_value = $this->_get_field_value( $field );

		if( $field_value ){
			if( !is_null( $this->log_field_wrapper ) ){
				return sprintf(
					"%s%s%s",
					$this->log_field_wrapper,
					$field_value,
					$this->log_field_wrapper
					);
			} else {
				return str_replace($this->log_field_delimiter, $this->log_field_delimiter_replacement, $field_value);
			}
		} else {
			return false;
		}
	}

	private function _add_field( $field_name, $field_value ){
		$field = new TransactionField( $field_name, $field_value );

		$desired_number_of_fields = count( $this->fields ) + 1;
		$this->fields[$field_name] = $field;
		$actual_number_of_fields = count( $this->fields );
		
		if( ( $desired_number_of_fields == $actual_number_of_fields )
			&& ( get_class( end( $this->fields ) ) == 'TransactionField' ) ) {
			return true; //$this->_get_field_value( $field );
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

	private function _send_email_notification(){
		$pre_message = "<h1>New Transaction from Paris</h1>";
		$body 			  = $pre_message . $this->_pretty_html_fields();

		$mail             = new PHPMailer(); // defaults to using php "mail()"
		$mail->SetFrom("info@thejakegroup.com","Jake Admin");
		$mail->AddReplyTo("info@thejakegroup.com","Jake Admin");
		$to_address = "lawson.kurtz@gmail.com";
		$mail->AddAddress($to_address, "Lawson Kurtz");
		$mail->Subject    = "New Transaction";
		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$mail->MsgHTML($body);

		return $mail->Send();
	}

	private function _pretty_html_fields(){
		$html = "";
		if( !empty( $this->fields ) ){
			foreach( $this->fields as $field ) {
				$html .= sprintf("<p><strong>%s</strong>: %s</p>",
					$field->name(),
					$field->value()
					);
			}
			return $html;
		}
		return false;
	}


}
