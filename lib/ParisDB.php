<?php
class ParisDB {
	private $development_mode = false;

	private $db_hosts = array(
		'production' => 'localhost',
		'development' => 'localhost'
		);

	private $db_names = array(
		'production' => 'paris',
		'development' => 'test-paris'
		);

	private $db_users = array(
		'production' => 'user',
		'development' => 'testuser'
		);
	private $db_passwords = array(
		'production' => 'password',
		'development' => 'testpass'
		);

	private $host = null;
	private $name = null;
	private $user = null;
	private $password = null;

	private $db_handler = null;
	private $statement_handler = null;
	private $results = null;


	public $errors = array();

	
	public function __construct( $development_mode = false ){
		$this->development_mode = $development_mode;

		$this->_setup_db_credentials();
		
	}

	private function _setup_db_credentials(){
		if( $this->development_mode !== false ){
			$this->host = $this->db_hosts[$this->development_mode];
			$this->name = $this->db_names[$this->development_mode];
			$this->user = $this->db_users[$this->development_mode];
			$this->password = $this->db_passwords[$this->development_mode];
		} else {
			$this->host = $this->db_hosts['production'];
			$this->name = $this->db_names['production'];
			$this->user = $this->db_users['production'];
			$this->password = $this->db_passwords['production'];
		}
	}

	public function connect(){
		try {
		    $db_handler = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->name . "", $this->user, $this->password);
		    $this->db_handler = $db_handler;
		    return true;
		} catch (PDOException $e) {
		    array_push($this->errors,$e->getMessage()); 
		    die( "DB Error:<br/><pre>" . print_r( $this->errors, true ) . "</pre>" );
		}
	}

	public function disconnect(){
		$this->db_handler = null;
		if( is_null( $this->db_handler ) ){
			return true;
		}
		return false;
	}

	public function prepare_query( $statement, $options = array() ){
		$statement_handler = $this->db_handler->prepare( $statement, $options );

		if( $statment_handler ){
			$this->statement_handler = $statment_handler;
			return true;
		} else {
			return false;
		}
	}

	public function execute_query( $values_array ){
		if( $this->statement_handler ){
			$results = $this->statement_handler->execute( $values_array );

		} else {
			array_push($this->errors, "Couldn't execute DB query.");
			return false;
		}
	}
}
