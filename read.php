<html>
<head>
	<title>Paris Transaction Reader</title>
	<style type="text/css">
		body{
			font-family:sans-serif;
			color:#444;
		}
		h1 {
			font-size:1.5em;
		}
		h2 {
			cursor:pointer;
			font-size:1.1em;
		}
		strong {
			text-transform:capitalize;
		}
		p {
			margin:2px 0 2px 15px;
		}
		ul {
			margin:0;
			padding:0;
		}
		li {
			padding:3px;
			background:#eee;
			margin:3px 0;
		}
		li:nth-child(even) {
			background:#fff;
		}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
	<script type="text/javascript">
		$(function(){
			$("p").hide();

			$('li').toggle(function(){
				$(this).children('p').show();
			}, function(){
				$(this).children('p').hide();
			});
		});
	</script>
</head>
<body>
<h1>Transactions</h1>
<ul>
<?php
require_once('lib/TransactionReader.php');

$reader = new TransactionReader();

if( !empty( $reader->transactions ) ){
	foreach( $reader->transactions as $trans ){
		echo "<li>";
		echo "<h2>" . $trans->field('type') . ": " . $trans->field('timestamp') . "</h2>";
		echo $trans->pretty_html_fields();
		echo "</li>";
	}
}
echo $reader->html();

?>
</ul>
</body>
</html>