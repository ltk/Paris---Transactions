<?php
class RecurringACHTransaction extends Transaction {
	protected $type = 21;

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

	protected function _before_commit(){
		$anet_info = array(
			'first_name' => $this->field( 'first_name' ),
			'last_name' => $this->field( 'last_name' ),
			'address_1' => $this->field( 'address_1' ),
			'address_2' => $this->field( 'address_2' )
			);

		$anet_transaction = new AuthorizeNetTransaction( 'ach', $anet_info );
		if( $anet_transaction ){
			$this->field( 'profile_id', trim( $anet_transaction->xml->customerProfileId ) );
			return true;
		} else {
			$this->errors( 'Authorize.net errors could be added to the transaction here.' );
			return false; // This prevents the transaction from being written to the transaction file.
		}
	}

	public function send_notifications( $type = null ){
		if( is_null( $type ) ){
			$this->_send_admin_email();
			$this->_send_customer_email();
		} else {
			$mail_function = '_send' . $type . '_email';
			if( method_exists( $this, $mail_function ) ){
				$this->{$mail_function}();
			}
		}	
	}

	private function _send_admin_email(){
		// $pre_message = "<h3 style='color: #295A54;'>Colonial Parking On-line Account Request</h3><p>A request has been made for access to our Colonial Online Customer Care Center. The request was made for:</p>";
		// $body 			  = $pre_message . $this->pretty_html_fields();

		$mail             = new WaitlistEmail( $this ); // defaults to using php "mail()"
		// $mail->SetFrom("info@thejakegroup.com","Jake Admin");
		// $mail->AddReplyTo("info@thejakegroup.com","Jake Admin");
		// $to_address = "lawson.kurtz@gmail.com";
		// $mail->AddAddress($to_address, "Lawson Kurtz");
		// $mail->Subject    = "New Transaction";
		// $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		// $mail->MsgHTML($body);

		return $mail->Send();
	}

	public function _send_customer_email(){
		$pre_message = "<h3 style='color: #295A54;'>Colonial Parking On-line Account Request</h3><p>A request has been made for access to our Colonial Online Customer Care Center. The request was made for:</p>";
		$body 			  = $pre_message . $this->pretty_html_fields();

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

	private function _after_commit(){

	}	
}