<?php
class Paris {
	private $unauthorized_page = "ParisWeb-Welcome.php";

	public __construct( $type ){
		session_start();
		if ( !isset( $_SESSION["UserID"] )
			|| !isset( $_SESSION["FacilityID"] ) ) {
			mysql_close($db);
			header("Location: " . $this->unauthorized_page );
		}
		if($_SERVER['PHP_SELF'] != )
	}
}
?>