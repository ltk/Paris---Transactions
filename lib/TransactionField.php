<?php
class TransactionField {
	private $value = null;
	private $name = null;

	public function __construct( $name, $value ){
		$this->set_value( $value );
		$this->name = $name;
	}

	public function value(){
		return $this->value;
	}

	public function set_value( $new_value ){
		$this->value = $new_value;
		return ($this->value == $new_value) ? true : false;
	}
	public function name(){
		return $this->name;
	}
}