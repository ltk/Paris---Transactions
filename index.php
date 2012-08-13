<?php
require_once('lib/Transaction.php');
require_once('lib/WaitlistTransaction.php');
require_once('lib/RecurringCCTransaction.php');
require_once('lib/RecurringACHTransaction.php');
require_once('lib/CashOrCheckTransaction.php');

//$transaction = new WaitlistTransaction();
//$transaction = new RecurringCCTransaction();
$transaction = new RecurringACHTransaction();
//$transaction = new CashOrCheckTransaction();

$transaction_data = array(
	'facility_id' => 123,
	'account_name' => '^^Duke',
	'address_1' => '3214 Biltmore St',
	'address_2' => 'Floor 2',
	'city' => 'Washington',
	'state' => 'DC',
	'zip' => '20009',
	'phone' => '(202) 333-2850',
	'email_address' => 'lkurtz@thejakegroup.com',
	'comment' => 'This is a comment. Woo hoo! Yay!'
	);

$transaction->add_fields( $transaction_data );

// $transaction->field('facility_id', '123');
// $transaction->field('account_name', 'Blah');
// $transaction->field('address_1', '3214 O St NW');
// $transaction->field('address_2', 'Floor 2');
// $transaction->field('address_3', '');
// $transaction->field('city', 'New York');
// $transaction->field('state', 'NW');
// $transaction->field('zip', '10001');
// $transaction->field('phone', '(203) 333-2850');
// $transaction->field('email_address', 'tbruffy@thejakegroup.com');

// $transaction->field('credit_card_name', 'Tyler Bruffy');
// $transaction->field('profile_id', '12345');
// $transaction->field('credit_card_month', '01');
// $transaction->field('credit_card_year', '2012');
// $transaction->field('credit_card_type', 'VISA');

// $transaction->field('comment', 'This is a much longer comment... woo hoo!.');

//$transaction->field('ach_routing_number', '12345678');



if( $transaction->commit() ){
	$this->send_notifications();
	echo 'Thank You. Your transaction has been processed.';
} else {
	echo 'There was an error. Your transaction was not processed.';
	echo "<pre>" . print_r( $transaction->get_errors(), true ) . "</pre>";
}