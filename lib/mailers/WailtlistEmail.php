<?php
class WaitlistEmail extends PHPMailer {

	public function __construct( $transaction, $type = 'customer' ) {
		parent::__construct( false );

		$pre_message = "<h3 style='color: #295A54;'>Colonial Parking On-line Account Request</h3><p>A request has been made for access to our Colonial Online Customer Care Center. The request was made for:</p>";
		$body 			  = $pre_message . $transaction->pretty_html_fields();

		
		$this->SetFrom("info@thejakegroup.com","Jake Admin");
		$this->AddReplyTo("info@thejakegroup.com","Jake Admin");
		// $to_address = "lawson.kurtz@gmail.com";
		$this->AddAddress("lawson.kurtz@gmail.com", "Lawson Kurtz");
		$this->Subject    = "New Transaction";
		$this->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$this->MsgHTML($body);
	}
}