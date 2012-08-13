<?php
class ParisPage extends Paris {
	/**
	 * What type of transaction this page will handle
	 * @var string
	 */
	private $transaction_type = null;

	/**
	 * The form object associated with this page
	 * @var Form
	 */
	private $form = null;

	/**
	 * A static list of the possible transaction types
	 * @var array
	 */
	private static $transaction_types = array(
		'08' => 'WaitlistTransaction',
		'20' => 'RecurringCCTransaction',
		'21' => 'RecurringACHTransaction',
		'22' => 'CashOrCheckTransaction'
		);

	public __construct( $type ){
		parent::__construct();
		$this->type = $type;
	}
}
?>