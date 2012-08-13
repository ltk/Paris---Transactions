<?php
class CashOrCheckTransaction extends Transaction {
	protected $type = 22;

	public $log_entry_field_groups = array(
		//Don't include Transaction Type
		array(
			'timestamp' => true,
			'_blank' => false,
			'ip_address' => true,
			'facility_id' => true,
			'account_name' => true,
			'address_1' => true,
			'address_2' => false,
			'address_3' => false,
			'city' => true,
			'state' => true,
			'zip' => true,
			'phone' => true,
			'email_address' => true
			),
		array(
			'credit_card_name' => true,
			'profile_id' => true,
			'credit_card_month' => true,
			'credit_card_year' => true,
			'credit_card_type' => true
			),
		array(
			'license_plate' => false,
			'license_plate_state' => false,
			'auto_make' => false
			),
		array(
			'parking_rate' => false,
			'user_defined_field' => false,
			'comment' => false
			),
		array(
			'ach_routing_number' => true,
			'ach_bank_account_number' => true,
			'ach_account_type' => true
			)
		);
	
}