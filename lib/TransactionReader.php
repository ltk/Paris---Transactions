<?php
require_once('Transaction.php');
require_once('TransactionField.php');
//require_once('PHPMailer.php');

class TransactionReader {
	/**
	 * Set the path to the transactions file.
	 *
	 * @var string
	 * @access private
	 */
	private $log_file = '/../transactions.txt';

	public $transactions = array();

	/**
	 * Holds an array of integer transaction types, indexed by their common name. 
	 *
	 * @var array
	 * @access private
	 */
	private static $types = array(
		'08' => 'WaitlistTransaction',
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

	private $html = '';

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

		if( $this->_read_from_log() ){
			// echo "<pre>";
			// 	print_r($this->transactions);
			// echo "</pre>";

			foreach( $this->transactions as $transaction ){
				$this->html .= $transaction->pretty_html_fields() . "\n";
			}
			return true;
		} else {
			return false;
		}
		
	}

	public function html(){
		return $this->html;
	}

	private function  _read_from_log(){
		/**
		 Use file_get_contents() instead
		 */
		if( is_file( __DIR__ . $this->log_file ) ){
			$log_file_lines = file( __DIR__ . $this->log_file );
			if( $log_file_lines !== false ){
				if( !empty( $log_file_lines ) ){
					foreach( $log_file_lines as $line ){
						$transaction = $this->_create_transaction( $line );
						array_push($this->transactions, $transaction);
					}
				}
				return true;

			} else {
				$this->_add_error( 'The log file could not be opened.' );
			}
		} else {
			$this->_add_error( 'The log file could not be found. ' );	
		}
		return false;
	}

	private function _create_transaction( $line ){
		$type = self::_get_transaction_type( $line );
		if( $type ){
			require_once( $type . ".php" );
			$transaction = new $type();
			self::_populate_transaction_data( $transaction, $line );
			return $transaction;
		}
		return false;
	}
	private static function _populate_transaction_data( $transaction, $line ){
		$field_names = array();
		if( !empty( $transaction->log_entry_field_groups ) ){
			foreach( $transaction->log_entry_field_groups as $group ){
				if( !empty($group) ){
					foreach( $group as $field_name => $field_required){
						array_push( $field_names, $field_name );
					}
				}
			}
		}

		$field_values = explode($transaction->log_field_delimiter, $line);
		$field_values[0] = get_class( $transaction );
		
		array_unshift($field_names, 'type');

		$field_names = array_slice( $field_names, 0, count( $field_values ) );

		$fields = array_combine( $field_names, $field_values );

		if( !empty( $fields ) ){
			foreach( $fields as $field_name => $field_value ){
				$transaction->field( $field_name, $field_value );
			}
		}


		
	}
	private static function _get_transaction_type( $line ){
		$type_code = substr( $line, 0, 2);
		if( $type_code && is_numeric( $type_code ) ){
			if( array_key_exists( $type_code, self::$types ) ){
				return self::$types[$type_code];
			}
		}
		return false;
	}
}
