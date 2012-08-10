<?php
require_once('lib/Transaction.php');
require_once('lib/WaitlistTransaction.php');

$transaction = new WaitlistTransaction();

// $transaction_data = array(
// 	'facility_id' => 44,
// 	'account_name' => 'Jake',
// 	'address_1' => '3214 O St NW',
// 	'address_2' => 'Floor 2',
// 	'city' => 'Washington',
// 	'state' => 'DC',
// 	'zip' => '20007',
// 	'phone' => '(202) 333-2850',
// 	'email_address' => 'tbruffy@thejakegroup.com'
// 	);

// $transaction->add_fields( $transaction_data );

//$transaction->field('_blank', '');
$transaction->field('facility_id', '^^44');
$transaction->field('account_name', 'Jake');
$transaction->field('address_1', '3214 O St NW');
$transaction->field('address_2', 'Floor 2');
$transaction->field('address_3', '');
$transaction->field('city', 'Washington');
$transaction->field('state', 'DC');
$transaction->field('zip', '20007');
$transaction->field('phone', '(203) 333-2850');
$transaction->field('email_address', 'tbruffy@thejakegroup.com');

$transaction->field('credit_card_name', 'Tyler Bruffy');
$transaction->field('profile_id', '12345');
$transaction->field('credit_card_month', '01');
$transaction->field('credit_card_year', '2012');
$transaction->field('credit_card_type', 'VISA');

$transaction->field('comment', 'This is my comment.');

//$transaction->field('ach_routing_number', '12345678');



echo $transaction->commit();