<html>
<head>
	<title>Paris Transaction Reader</title>
	<style type="text/css">
		body{
			font-family:sans-serif;
			color:#444;
		}
		h1 {
			cursor:pointer;
			font-size:1.5em;
		}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
	<script type="text/javascript">
		$(function(){
			$("p").hide();

			$('div').toggle(function(){
				$(this).children('p').show();
			}, function(){
				$(this).children('p').hide();
			});
		});
	</script>
</head>
<body>

<?php
require_once('lib/TransactionReader.php');

$reader = new TransactionReader();

if( !empty( $reader->transactions ) ){
	foreach( $reader->transactions as $trans ){
		echo "<div>";
		echo "<h1>" . $trans->field('type') . ": " . $trans->field('timestamp') . "</h1>";
		echo $trans->pretty_html_fields();
		echo "</div>";
	}
}
echo $reader->html();

?>
</body>
</html>